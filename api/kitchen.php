<?php
/**
 * ISAT U Coffee Shop - Barista Kitchen Display System (KDS) API
 * Native PHP / PDO Backend
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/inventory.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
inv_ensure_schema($pdo);
$method = $_SERVER['REQUEST_METHOD'];

// Kitchen Display System receives orders placed and confirmed at the POS counter
$scheduledSync = ['dispatched' => [], 'upcoming' => []];

// GET: Fetch Active Orders for Kitchen Display
if ($method === 'GET') {
    try {
        // Preload finished product recipes & add-on ingredient mappings (with live raw material stock)
        $recipesByProduct = [];
        $recipesByName = [];
        $recRows = $pdo->query("SELECT fg.id AS finished_item_id, fg.product_id, LOWER(fg.name) AS fg_name,
                r.qty_per_unit, ing.id AS ingredient_item_id, ing.name AS ingredient_name, ing.unit, ing.item_code, ing.current_stock AS available_stock
            FROM product_recipes r
            JOIN inventory_items fg ON fg.id = r.finished_item_id
            JOIN inventory_items ing ON ing.id = r.ingredient_item_id
            ORDER BY ing.name")->fetchAll();
        foreach ($recRows as $rr) {
            if (!empty($rr['product_id'])) {
                $recipesByProduct[(int)$rr['product_id']][] = $rr;
            }
            $recipesByName[$rr['fg_name']][] = $rr;
        }

        $addonsByProduct = [];
        $addonsByName = [];
        $adRows = $pdo->query("SELECT fg.id AS finished_item_id, fg.product_id, LOWER(fg.name) AS fg_name,
                a.addon_name, a.qty_per_unit, a.price, ing.id AS ingredient_item_id, ing.name AS ingredient_name, ing.unit, ing.item_code, ing.current_stock AS available_stock
            FROM product_item_addons a
            JOIN inventory_items fg ON fg.id = a.finished_item_id
            JOIN inventory_items ing ON ing.id = a.ingredient_item_id
            ORDER BY a.id")->fetchAll();
        foreach ($adRows as $ar) {
            $key = strtolower(trim($ar['addon_name']));
            if (!empty($ar['product_id'])) {
                $addonsByProduct[(int)$ar['product_id']][$key] = $ar;
            }
            $addonsByName[$ar['fg_name']][$key] = $ar;
        }

        // Fetch today's active orders + whether ingredients were already deducted in ledger
        $stmt = $pdo->prepare("SELECT o.*,
                EXISTS(
                    SELECT 1 FROM inventory_ledger l
                    WHERE l.ref_table = 'orders' AND l.ref_id = o.id AND l.txn_type = 'BREW_OUT'
                ) AS ingredients_deducted
            FROM orders o
            WHERE o.status IN ('Pending', 'Brewing', 'Ready', 'Completed') 
            AND DATE(o.created_at) = CURDATE() 
            ORDER BY o.created_at DESC");
        $stmt->execute();
        $orders = $stmt->fetchAll();

        // Attach items to each order
        $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");

        $pendingCount = 0;
        $brewingCount = 0;
        $readyCount = 0;
        $completedCount = 0;

        foreach ($orders as &$ord) {
            $ord['ingredients_deducted'] = (bool)($ord['ingredients_deducted'] ?? false);
            $itemStmt->execute([$ord['id']]);
            $ord['items'] = $itemStmt->fetchAll();

            // Decode addons_json & attach recipe + add-on ingredient breakdown
            foreach ($ord['items'] as &$it) {
                if (!empty($it['addons_json'])) {
                    $it['addons'] = json_decode($it['addons_json'], true) ?: [];
                } else {
                    $it['addons'] = [];
                }

                $pid = (int)($it['product_id'] ?? 0);
                $pnameKey = strtolower(trim($it['product_name'] ?? ''));
                $qty = max(1, (int)($it['quantity'] ?? 1));

                $baseLines = ($pid && isset($recipesByProduct[$pid]))
                    ? $recipesByProduct[$pid]
                    : ($recipesByName[$pnameKey] ?? []);

                $it['finished_item_id'] = !empty($baseLines) ? (int)$baseLines[0]['finished_item_id'] : 0;

                $packagingCodes = ['RM-CUP12-PC', 'RM-CUP16-PC', 'RM-STRAW-PC', 'RM-TOBOX-PC'];
                $filteredBaseLines = array_values(array_filter($baseLines, function ($ln) use ($packagingCodes) {
                    return !in_array($ln['item_code'] ?? '', $packagingCodes, true);
                }));

                $it['recipe_lines'] = array_map(function ($ln) use ($qty) {
                    $perUnit = (float)$ln['qty_per_unit'];
                    $totReq = round($perUnit * $qty, 4);
                    $avail = round((float)$ln['available_stock'], 4);
                    return [
                        'ingredient_name' => $ln['ingredient_name'],
                        'unit' => $ln['unit'],
                        'qty_per_unit' => $perUnit,
                        'total_qty' => $totReq,
                        'available_stock' => $avail,
                        'sufficient' => ($avail + 1e-9 >= $totReq),
                    ];
                }, $filteredBaseLines);

                $prodAddonMap = ($pid && isset($addonsByProduct[$pid]))
                    ? $addonsByProduct[$pid]
                    : ($addonsByName[$pnameKey] ?? []);

                $addonRecipeLines = [];
                foreach ($it['addons'] as $addonLabel) {
                    $akey = strtolower(trim((string)$addonLabel));
                    if (isset($prodAddonMap[$akey])) {
                        $m = $prodAddonMap[$akey];
                        $perUnit = (float)$m['qty_per_unit'];
                        $totReq = round($perUnit * $qty, 4);
                        $avail = round((float)$m['available_stock'], 4);
                        $addonRecipeLines[] = [
                            'addon_name' => $m['addon_name'],
                            'ingredient_name' => $m['ingredient_name'],
                            'unit' => $m['unit'],
                            'qty_per_unit' => $perUnit,
                            'total_qty' => $totReq,
                            'available_stock' => $avail,
                            'sufficient' => ($avail + 1e-9 >= $totReq),
                        ];
                    }
                }
                $it['addon_recipe_lines'] = $addonRecipeLines;
            }

            // Attach Cups, Straws, and Takeout Box requirements for this order
            $ord['packaging_lines'] = array_values(inv_calculate_order_packaging($pdo, $ord['items'], (string)($ord['order_type'] ?? 'Dine-in')));

            // Count statuses
            if ($ord['status'] === 'Pending') $pendingCount++;
            elseif ($ord['status'] === 'Brewing') $brewingCount++;
            elseif ($ord['status'] === 'Ready') $readyCount++;
            elseif ($ord['status'] === 'Completed') $completedCount++;
        }

        jsonResponse([
            'success' => true,
            'orders' => $orders,
            'packaging_stock' => inv_get_packaging_stock_map($pdo),
            'scheduled_dispatched' => $scheduledSync['dispatched'],
            'scheduled_upcoming' => $scheduledSync['upcoming'],
            'counts' => [
                'pending' => $pendingCount,
                'brewing' => $brewingCount,
                'ready' => $readyCount,
                'completed' => $completedCount,
                'scheduled_upcoming' => count($scheduledSync['upcoming']),
            ]
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// POST: Update Order Workflow Status
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $orderId = isset($data['order_id']) ? (int)$data['order_id'] : (int)($_GET['id'] ?? 0);
    $status = sanitize($data['status'] ?? '');

    $allowedStatuses = ['Pending', 'Brewing', 'Ready', 'Completed', 'Cancelled'];
    if (!$orderId || !in_array($status, $allowedStatuses)) {
        jsonResponse(['success' => false, 'message' => 'Invalid order ID or status provided.'], 422);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Order not found.");
        }

        // Once brewed and served (Completed), deduct raw material ingredients (base recipe + ordered add-ons) from inventory
        $deductedIngredients = [];
        if ($status === 'Completed') {
            $deductedIngredients = inv_deduct_kitchen_order_ingredients($pdo, $orderId, (string)$order['order_number']);
        }

        // Update status
        $upd = $pdo->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $upd->execute([$status, $orderId]);

        // Note: For reservation pre-orders (especially T-15m scheduled pre-orders prepared before guest arrival),
        // we keep the reservation Confirmed / Checked-In so their reserved table is preserved until Staff checks them out.
        if ($status === 'Cancelled' && !empty($order['reservation_id'])) {
            $resStmt = $pdo->prepare("SELECT id, status, table_id FROM reservations WHERE id = ?");
            $resStmt->execute([$order['reservation_id']]);
            $resRow = $resStmt->fetch();
            if ($resRow && $resRow['status'] === 'Cancelled' && !empty($resRow['table_id'])) {
                $updTbl = $pdo->prepare("UPDATE dining_tables SET status = 'available' WHERE id = ?");
                $updTbl->execute([$resRow['table_id']]);
            }
        }

        $pdo->commit();

        $deductMsg = '';
        if ($status === 'Completed' && !empty($deductedIngredients)) {
            $deductMsg = ' Deducted ' . count($deductedIngredients) . ' raw ingredient(s) from Inventory Ledger (BREW_OUT -).';
        }

        jsonResponse([
            'success' => true,
            'message' => "Order #{$order['order_number']} status advanced to {$status}.{$deductMsg}",
            'order_id' => $orderId,
            'new_status' => $status,
            'deducted_ingredients' => $deductedIngredients
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

