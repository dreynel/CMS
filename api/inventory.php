<?php
/**
 * Raeden’s Bakery and Café - Inventory Management API
 *
 * Modules: Item Master, Recipes (BOM), Stock In, Stock Out,
 *          Item Conversion, Brew Production, Inventory Ledger.
 *
 * Every POSTED document writes immutable rows to `inventory_ledger`
 * through inv_post_movement(). Drafts never touch stock.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/inventory.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
inv_ensure_schema($pdo);

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? '';
if (!in_array($role, ['admin', 'barista', 'cashier', 'staff', 'concierge'], true)) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized. Please sign in to access inventory data.'], 401);
}
$isAdmin = $role === 'admin';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && !in_array($role, ['admin', 'barista'], true)) {
    jsonResponse(['success' => false, 'message' => 'Only Administrators and Kitchen/Barista staff can make inventory adjustments.'], 403);
}

/** Admin-only guard for master data & stock documents. */
function requireAdmin(bool $isAdmin): void
{
    if (!$isAdmin) {
        jsonResponse(['success' => false, 'message' => 'Only Administrators can perform this action.'], 403);
    }
}

/**
 * Ingredient requirement vs availability for a brew of $qty units
 * plus any selected add-ons ($selectedAddons = [ ['addon_id'=>int, 'qty'=>float], ... ]).
 */
function brewRequirements(PDO $pdo, int $finishedId, float $qty, array $selectedAddons = []): array
{
    $s = $pdo->prepare("SELECT r.ingredient_item_id, r.qty_per_unit, i.name, i.unit, i.current_stock, i.item_code
        FROM product_recipes r JOIN inventory_items i ON i.id = r.ingredient_item_id
        WHERE r.finished_item_id = ? ORDER BY i.name");
    $s->execute([$finishedId]);

    $byItem = [];
    foreach ($s->fetchAll() as $r) {
        $iid = (int)$r['ingredient_item_id'];
        $req = round((float)$r['qty_per_unit'] * $qty, 4);
        $byItem[$iid] = [
            'item_id' => $iid,
            'item_code' => $r['item_code'],
            'name' => $r['name'],
            'unit' => $r['unit'],
            'qty_per_unit' => (float)$r['qty_per_unit'],
            'base_required' => $req,
            'addon_required' => 0.0,
            'required' => $req,
            'available' => (float)$r['current_stock'],
            'source_type' => 'Base Recipe',
            'addon_names' => [],
        ];
    }

    if (!empty($selectedAddons)) {
        $aStmt = $pdo->prepare("SELECT a.id, a.addon_name, a.ingredient_item_id, a.qty_per_unit, i.name, i.unit, i.current_stock, i.item_code
            FROM product_item_addons a
            JOIN inventory_items i ON i.id = a.ingredient_item_id
            WHERE a.finished_item_id = ? AND a.id = ?");

        foreach ($selectedAddons as $sel) {
            $aid = (int)($sel['addon_id'] ?? $sel['id'] ?? 0);
            $aqty = max(0, (float)($sel['qty'] ?? $qty));
            if ($aid <= 0 || $aqty <= 0) continue;

            $aStmt->execute([$finishedId, $aid]);
            $arow = $aStmt->fetch();
            if (!$arow) continue;

            $iid = (int)$arow['ingredient_item_id'];
            $addReq = round((float)$arow['qty_per_unit'] * $aqty, 4);

            if (!isset($byItem[$iid])) {
                $byItem[$iid] = [
                    'item_id' => $iid,
                    'item_code' => $arow['item_code'],
                    'name' => $arow['name'],
                    'unit' => $arow['unit'],
                    'qty_per_unit' => (float)$arow['qty_per_unit'],
                    'base_required' => 0.0,
                    'addon_required' => $addReq,
                    'required' => $addReq,
                    'available' => (float)$arow['current_stock'],
                    'source_type' => 'Add-on: ' . $arow['addon_name'],
                    'addon_names' => [$arow['addon_name']],
                ];
            } else {
                $byItem[$iid]['addon_required'] = round($byItem[$iid]['addon_required'] + $addReq, 4);
                $byItem[$iid]['required'] = round($byItem[$iid]['base_required'] + $byItem[$iid]['addon_required'], 4);
                $byItem[$iid]['addon_names'][] = $arow['addon_name'];
                $byItem[$iid]['source_type'] = $byItem[$iid]['base_required'] > 0
                    ? 'Base + Add-on (' . implode(', ', array_unique($byItem[$iid]['addon_names'])) . ')'
                    : 'Add-on (' . implode(', ', array_unique($byItem[$iid]['addon_names'])) . ')';
            }
        }
    }

    $out = [];
    foreach ($byItem as $row) {
        $row['sufficient'] = $row['available'] + 0.00001 >= $row['required'];
        $out[] = $row;
    }
    return $out;
}

// =====================================================================
// GET
// =====================================================================
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'items';

    try {
        if ($action === 'items') {
            $items = $pdo->query("SELECT i.*, COALESCE(NULLIF(i.image_url, ''), p.image_url, '') AS image_url, p.price AS selling_price, c.name AS category_name,
                    (SELECT COUNT(*) FROM product_recipes r WHERE r.finished_item_id = i.id) AS recipe_lines,
                    (SELECT COUNT(*) FROM product_item_addons a WHERE a.finished_item_id = i.id) AS addon_lines
                FROM inventory_items i
                LEFT JOIN products p ON p.id = i.product_id
                LEFT JOIN categories c ON c.id = p.category_id
                ORDER BY i.item_type DESC, i.name ASC")->fetchAll();
            $categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order")->fetchAll();

            $summary = ['raw' => 0, 'finished' => 0, 'low' => 0, 'zero' => 0, 'value' => 0.0];
            foreach ($items as $it) {
                if (!$it['is_active']) continue;
                $summary[$it['item_type']]++;
                if ($it['item_type'] === 'raw') {
                    if ((float)$it['current_stock'] <= 0) $summary['zero']++;
                    elseif ((float)$it['current_stock'] <= (float)$it['reorder_level']) $summary['low']++;
                    $summary['value'] += (float)$it['current_stock'] * (float)$it['unit_cost'];
                }
            }
            jsonResponse(['success' => true, 'items' => $items, 'categories' => $categories, 'summary' => $summary, 'is_admin' => $isAdmin]);
        }

        if ($action === 'recipe') {
            $fid = (int)($_GET['item_id'] ?? ($_GET['product_id'] ?? 0));
            $s = $pdo->prepare("SELECT r.*, i.name, i.unit, i.item_code, i.current_stock FROM product_recipes r
                JOIN inventory_items i ON i.id = r.ingredient_item_id WHERE r.finished_item_id = ? ORDER BY i.name");
            $s->execute([$fid]);
            $a = $pdo->prepare("SELECT a.*, i.name AS ingredient_name, i.unit, i.item_code, i.current_stock
                FROM product_item_addons a
                JOIN inventory_items i ON i.id = a.ingredient_item_id
                WHERE a.finished_item_id = ? ORDER BY a.id");
            $a->execute([$fid]);
            jsonResponse(['success' => true, 'lines' => $s->fetchAll(), 'addons' => $a->fetchAll()]);
        }

        if ($action === 'addons') {
            $fid = (int)($_GET['item_id'] ?? ($_GET['product_id'] ?? 0));
            $a = $pdo->prepare("SELECT a.*, i.name AS ingredient_name, i.unit, i.item_code, i.current_stock
                FROM product_item_addons a
                JOIN inventory_items i ON i.id = a.ingredient_item_id
                WHERE a.finished_item_id = ? ORDER BY a.id");
            $a->execute([$fid]);
            jsonResponse(['success' => true, 'addons' => $a->fetchAll()]);
        }

        if ($action === 'brew_check') {
            $fid = (int)($_GET['item_id'] ?? ($_GET['product_id'] ?? 0));
            $qty = max(0, (float)($_GET['qty'] ?? 1));
            $selectedAddons = [];
            if (!empty($_GET['addons'])) {
                $decoded = json_decode($_GET['addons'], true);
                if (is_array($decoded)) $selectedAddons = $decoded;
            }

            $baseStmt = $pdo->prepare("SELECT r.*, i.name, i.unit, i.item_code, i.current_stock
                FROM product_recipes r JOIN inventory_items i ON i.id = r.ingredient_item_id
                WHERE r.finished_item_id = ? ORDER BY i.name");
            $baseStmt->execute([$fid]);
            $recipeLines = $baseStmt->fetchAll();

            $addonStmt = $pdo->prepare("SELECT a.*, i.name AS ingredient_name, i.unit, i.item_code, i.current_stock
                FROM product_item_addons a JOIN inventory_items i ON i.id = a.ingredient_item_id
                WHERE a.finished_item_id = ? ORDER BY a.id");
            $addonStmt->execute([$fid]);
            $availableAddons = $addonStmt->fetchAll();

            $req = brewRequirements($pdo, $fid, $qty, $selectedAddons);
            $max = null;
            foreach ($recipeLines as $r) {
                if ((float)$r['qty_per_unit'] <= 0) continue;
                $can = floor(((float)$r['current_stock'] + 0.00001) / (float)$r['qty_per_unit']);
                $max = $max === null ? $can : min($max, $can);
            }
            jsonResponse([
                'success' => true,
                'requirements' => $req,
                'recipe_lines' => $recipeLines,
                'available_addons' => $availableAddons,
                'max_brewable' => (int)($max ?? 0),
                'has_recipe' => count($recipeLines) > 0
            ]);
        }

        // FINISHED PRODUCT YIELD CALCULATOR (Calculate finished product output based on ingredient stock)
        if ($action === 'yield_calculator') {
            $fid = (int)($_GET['item_id'] ?? ($_GET['product_id'] ?? 0));
            $targetQty = max(0.01, (float)($_GET['target_qty'] ?? 1));
            $customStockJson = $_GET['custom_stocks'] ?? '';
            $customStocks = [];
            if ($customStockJson) {
                $decoded = json_decode($customStockJson, true);
                if (is_array($decoded)) {
                    $customStocks = $decoded;
                }
            }

            // Calculation closure for a finished product
            $calcItemYield = function(int $finishedId) use ($pdo, $targetQty, $customStocks) {
                $fStmt = $pdo->prepare("SELECT i.*, p.name AS pos_product_name, p.product_code AS pos_code, p.base_price AS selling_price, COALESCE(NULLIF(i.image_url, ''), p.image_url, '') AS image_url, c.name AS category_name
                    FROM inventory_items i
                    LEFT JOIN products p ON p.id = i.product_id
                    LEFT JOIN categories c ON c.id = p.category_id
                    WHERE (i.id = ? OR i.product_id = ?) AND i.item_type = 'finished' LIMIT 1");
                $fStmt->execute([$finishedId, $finishedId]);
                $finished = $fStmt->fetch();
                if (!$finished) return null;
                $finishedItemId = (int)$finished['id'];

                $rStmt = $pdo->prepare("SELECT r.id AS recipe_id, r.qty_per_unit,
                        i.id AS ingredient_id, i.item_code AS ingredient_code, i.name AS ingredient_name,
                        i.unit AS ingredient_unit, i.current_stock AS raw_current_stock, i.unit_cost
                    FROM product_recipes r
                    JOIN inventory_items i ON i.id = r.ingredient_item_id
                    WHERE r.finished_item_id = ?
                    ORDER BY i.name ASC");
                $rStmt->execute([$finishedItemId]);
                $recipeLines = $rStmt->fetchAll();

                $minYield = null;
                $bottleneck = null;
                $totalCostPerUnit = 0.0;
                $ingredients = [];

                foreach ($recipeLines as $line) {
                    $ingId = (int)$line['ingredient_id'];
                    $qtyPerUnit = (float)$line['qty_per_unit'];
                    $stock = isset($customStocks[$ingId]) ? (float)$customStocks[$ingId] : (float)$line['raw_current_stock'];
                    $unitCost = (float)$line['unit_cost'];
                    $lineCost = round($qtyPerUnit * $unitCost, 4);
                    $totalCostPerUnit += $lineCost;

                    $possibleFromIng = ($qtyPerUnit > 0) ? floor(($stock + 0.00001) / $qtyPerUnit) : 0;
                    if ($possibleFromIng < 0) $possibleFromIng = 0;

                    $reqForTarget = round($qtyPerUnit * $targetQty, 4);
                    $shortage = max(0, round($reqForTarget - $stock, 4));

                    $ingData = [
                        'ingredient_id' => $ingId,
                        'code' => $line['ingredient_code'],
                        'name' => $line['ingredient_name'],
                        'unit' => $line['ingredient_unit'],
                        'qty_per_unit' => $qtyPerUnit,
                        'unit_cost' => $unitCost,
                        'line_cost' => $lineCost,
                        'available_stock' => $stock,
                        'max_units' => (int)$possibleFromIng,
                        'target_required' => $reqForTarget,
                        'shortage' => $shortage,
                        'is_sufficient' => ($stock + 0.00001 >= $reqForTarget),
                    ];

                    if ($minYield === null || $possibleFromIng < $minYield) {
                        $minYield = (int)$possibleFromIng;
                        $bottleneck = $ingData;
                    }

                    $ingredients[] = $ingData;
                }

                $maxYield = ($minYield !== null && count($recipeLines) > 0) ? $minYield : 0;
                $hasRecipe = count($recipeLines) > 0;

                $sellingPrice = (float)($finished['selling_price'] ?? 0);
                $estimatedMargin = ($sellingPrice > 0 && $totalCostPerUnit > 0)
                    ? round((($sellingPrice - $totalCostPerUnit) / $sellingPrice) * 100, 1)
                    : 0;

                return [
                    'item_id' => (int)$finished['id'],
                    'product_id' => (int)($finished['product_id'] ?? 0),
                    'item_code' => $finished['item_code'],
                    'name' => $finished['name'],
                    'unit' => $finished['unit'],
                    'category_name' => $finished['category_name'] ?? 'Bakery & Café',
                    'image_url' => $finished['image_url'] ?? '',
                    'selling_price' => $sellingPrice,
                    'cost_per_unit' => round($totalCostPerUnit, 2),
                    'profit_margin_pct' => $estimatedMargin,
                    'current_finished_stock' => (float)$finished['current_stock'],
                    'has_recipe' => $hasRecipe,
                    'recipe_count' => count($recipeLines),
                    'max_yield' => $maxYield,
                    'bottleneck' => $bottleneck,
                    'target_qty' => $targetQty,
                    'can_produce_target' => ($hasRecipe && $maxYield >= $targetQty),
                    'total_target_cost' => round($totalCostPerUnit * $targetQty, 2),
                    'ingredients' => $ingredients
                ];
            };

            if ($fid > 0) {
                $detail = $calcItemYield($fid);
                if (!$detail) {
                    jsonResponse(['success' => false, 'message' => 'Finished product not found.'], 404);
                }
                jsonResponse(['success' => true, 'product' => $detail]);
            } else {
                $allFinished = $pdo->query("SELECT id FROM inventory_items WHERE item_type = 'finished' AND is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
                $results = [];
                foreach ($allFinished as $id) {
                    $itemYield = $calcItemYield((int)$id);
                    if ($itemYield) {
                        $results[] = $itemYield;
                    }
                }
                jsonResponse([
                    'success' => true,
                    'products' => $results,
                    'total_finished' => count($results),
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }

        if ($action === 'stock_txns') {
            $type = ($_GET['type'] ?? 'IN') === 'OUT' ? 'OUT' : 'IN';
            $s = $pdo->prepare("SELECT t.*,
                    (SELECT COUNT(*) FROM stock_transaction_lines l WHERE l.txn_id = t.id) AS line_count,
                    (SELECT COALESCE(SUM(l.quantity * l.unit_cost),0) FROM stock_transaction_lines l WHERE l.txn_id = t.id) AS total_cost
                FROM stock_transactions t WHERE t.txn_type = ? ORDER BY t.id DESC LIMIT 200");
            $s->execute([$type]);
            jsonResponse(['success' => true, 'transactions' => $s->fetchAll()]);
        }

        if ($action === 'stock_txn') {
            $id = (int)($_GET['id'] ?? 0);
            $h = $pdo->prepare("SELECT * FROM stock_transactions WHERE id = ?");
            $h->execute([$id]);
            $txn = $h->fetch();
            if (!$txn) jsonResponse(['success' => false, 'message' => 'Document not found.'], 404);
            $l = $pdo->prepare("SELECT l.*, i.name, i.unit, i.item_code, i.current_stock FROM stock_transaction_lines l
                JOIN inventory_items i ON i.id = l.item_id WHERE l.txn_id = ? ORDER BY l.id");
            $l->execute([$id]);
            $txn['lines'] = $l->fetchAll();
            jsonResponse(['success' => true, 'transaction' => $txn]);
        }

        if ($action === 'conversions') {
            $rows = $pdo->query("SELECT c.*, s.name AS source_name, s.unit AS source_unit, d.name AS dest_name, d.unit AS dest_unit
                FROM item_conversions c
                JOIN inventory_items s ON s.id = c.source_item_id
                JOIN inventory_items d ON d.id = c.dest_item_id
                ORDER BY c.id DESC LIMIT 200")->fetchAll();
            jsonResponse(['success' => true, 'conversions' => $rows]);
        }

        if ($action === 'brews') {
            $rows = $pdo->query("SELECT b.*, i.name AS item_name, i.unit
                FROM brew_batches b JOIN inventory_items i ON i.id = b.finished_item_id
                ORDER BY b.id DESC LIMIT 200")->fetchAll();
            jsonResponse(['success' => true, 'brews' => $rows]);
        }

        if ($action === 'ledger') {
            $itemId = (int)($_GET['item_id'] ?? 0);
            $from = $_GET['from'] ?? date('Y-m-01');
            $to = $_GET['to'] ?? date('Y-m-d');
            $type = $_GET['txn_type'] ?? '';
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = date('Y-m-d');
            if ($from > $to) [$from, $to] = [$to, $from];

            $where = "l.entry_date BETWEEN ? AND ?";
            $params = [$from . ' 00:00:00', $to . ' 23:59:59'];
            if ($itemId) { $where .= " AND l.item_id = ?"; $params[] = $itemId; }
            if ($type !== '') { $where .= " AND l.txn_type = ?"; $params[] = $type; }

            $s = $pdo->prepare("SELECT l.*, i.name AS item_name, i.item_code, i.unit, i.item_type
                FROM inventory_ledger l JOIN inventory_items i ON i.id = l.item_id
                WHERE $where ORDER BY l.id ASC LIMIT 2000");
            $s->execute($params);
            $entries = $s->fetchAll();

            $opening = null;
            if ($itemId) {
                $o = $pdo->prepare("SELECT COALESCE(SUM(qty_in - qty_out), 0) FROM inventory_ledger WHERE item_id = ? AND entry_date < ?");
                $o->execute([$itemId, $from . ' 00:00:00']);
                $opening = (float)$o->fetchColumn();
            }
            $totIn = 0; $totOut = 0;
            foreach ($entries as $e) { $totIn += (float)$e['qty_in']; $totOut += (float)$e['qty_out']; }

            jsonResponse(['success' => true, 'entries' => $entries, 'opening' => $opening,
                'total_in' => $totIn, 'total_out' => $totOut,
                'closing' => $opening !== null ? $opening + $totIn - $totOut : null,
                'filter' => ['from' => $from, 'to' => $to, 'item_id' => $itemId, 'txn_type' => $type]]);
        }

        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// =====================================================================
// POST
// =====================================================================
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = $data['action'] ?? $_GET['action'] ?? '';

    try {
        // -------------------------------------------------------------
        // ITEM MASTER (create / update). New items ALWAYS start at 0 stock.
        // -------------------------------------------------------------
        if ($action === 'save_item') {
            requireAdmin($isAdmin);
            $id = (int)($data['id'] ?? 0);
            $code = strtoupper(trim(sanitize($data['item_code'] ?? '')));
            $name = trim(sanitize($data['name'] ?? ''));
            $type = ($data['item_type'] ?? 'raw') === 'finished' ? 'finished' : 'raw';
            $unit = trim(sanitize($data['unit'] ?? 'pc')) ?: 'pc';
            $cost = max(0, (float)($data['unit_cost'] ?? 0));
            $reorder = max(0, (float)($data['reorder_level'] ?? 0));
            $active = isset($data['is_active']) ? (int)!!$data['is_active'] : 1;

            if ($code === '' || $name === '') {
                jsonResponse(['success' => false, 'message' => 'Item code and name are required.'], 422);
            }
            $dup = $pdo->prepare("SELECT id FROM inventory_items WHERE item_code = ? AND id <> ?");
            $dup->execute([$code, $id]);
            if ($dup->fetch()) jsonResponse(['success' => false, 'message' => "Item code '$code' already exists."], 409);

            $pdo->beginTransaction();
            $isNewItem = ($id === 0);
            $savedProductId = null;
            $savedPrice = (float)($data['selling_price'] ?? 0);

            // Optional image thumbnail upload or URL (for Finished Goods or Raw Materials)
            $imageUrl = null;
            if (!empty($data['image_base64']) && preg_match('#^data:image/(\w+);base64,#i', $data['image_base64'], $m)) {
                $ext = strtolower($m[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                if (!in_array($ext, ['jpg', 'png', 'webp', 'gif'], true)) $ext = 'png';
                $rawBase64 = substr($data['image_base64'], strpos($data['image_base64'], ',') + 1);
                $decoded = base64_decode($rawBase64);
                if ($decoded !== false) {
                    $uploadDir = __DIR__ . '/../assets/images/products/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $filename = 'item_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (@file_put_contents($uploadDir . $filename, $decoded) !== false) {
                        $imageUrl = 'assets/images/products/' . $filename;
                    }
                }
            } elseif (isset($data['image_url'])) {
                $trimmed = trim((string)$data['image_url']);
                $imageUrl = $trimmed !== '' ? $trimmed : '';
            }

            if ($id) {
                $cur = $pdo->prepare("SELECT * FROM inventory_items WHERE id = ? FOR UPDATE");
                $cur->execute([$id]);
                $existing = $cur->fetch();
                if (!$existing) throw new Exception('Item not found.');
                $type = $existing['item_type'];
                $savedProductId = $existing['product_id'] ? (int)$existing['product_id'] : null;
                $savedImageUrl = $imageUrl !== null ? ($imageUrl ?: null) : ($existing['image_url'] ?? null);

                // Item type and stock are never editable here (stock moves only through the ledger)
                $pdo->prepare("UPDATE inventory_items SET item_code=?, name=?, unit=?, unit_cost=?, reorder_level=?, is_active=?, image_url=? WHERE id=?")
                    ->execute([$code, $name, $unit, $cost, $reorder, $active, $savedImageUrl, $id]);
                if ($existing['product_id']) {
                    $pdo->prepare("UPDATE products SET product_code=?, name=?, cost_price=?, min_stock_alert=?, is_available=?, image_url=? WHERE id=?")
                        ->execute([$code, $name, $cost, (int)ceil($reorder), $active, $savedImageUrl, $existing['product_id']]);
                    if (isset($data['selling_price']) && (float)$data['selling_price'] > 0) {
                        $savedPrice = (float)$data['selling_price'];
                        $pdo->prepare("UPDATE products SET price=?, base_price=? WHERE id=?")
                            ->execute([$savedPrice, $savedPrice, $existing['product_id']]);
                    }
                }
                $msg = "Item '$name' updated.";
            } else {
                $productId = null;
                $savedImageUrl = ($imageUrl !== null && $imageUrl !== '') ? $imageUrl : null;
                if ($type === 'finished') {
                    $catId = (int)($data['category_id'] ?? 0);
                    $price = max(0, (float)($data['selling_price'] ?? 0));
                    $savedPrice = $price;
                    if (!$catId || $price <= 0) throw new Exception('Finished products require a POS category and selling price.');
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')) . '-' . strtolower($code);
                    $hasCustom = (int)!empty($data['has_customizations']);
                    $pdo->prepare("INSERT INTO products (category_id, product_code, name, slug, description, price, base_price, cost_price,
                            stock_quantity, min_stock_alert, unit, has_customizations, is_available, image_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)")
                        ->execute([$catId, $code, $name, $slug, sanitize($data['description'] ?? ''), $price, $price, $cost,
                            (int)ceil($reorder), $unit, $hasCustom, $active, $savedImageUrl]);
                    $productId = (int)$pdo->lastInsertId();
                    $savedProductId = $productId;
                }
                $pdo->prepare("INSERT INTO inventory_items (item_code, name, item_type, unit, product_id, unit_cost, reorder_level, current_stock, is_active, image_url)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)")
                    ->execute([$code, $name, $type, $unit, $productId, $cost, $reorder, $active, $savedImageUrl]);
                $id = (int)$pdo->lastInsertId();
                $msg = "Item '$name' added to the master list with 0 stock." . ($productId ? ' Linked to POS catalog & QR Code generated.' : '');
            }
            $pdo->commit();
            jsonResponse([
                'success' => true,
                'message' => $msg,
                'item' => [
                    'id' => $id,
                    'item_code' => $code,
                    'name' => $name,
                    'item_type' => $type,
                    'unit' => $unit,
                    'selling_price' => $savedPrice,
                    'product_id' => $savedProductId,
                    'image_url' => $savedImageUrl ?: '',
                    'is_new' => $isNewItem
                ]
            ]);
        }

        // -------------------------------------------------------------
        // RECIPE / BILL OF MATERIALS & PRODUCT ADD-ONS (replace lines)
        // -------------------------------------------------------------
        if ($action === 'save_recipe') {
            requireAdmin($isAdmin);
            $fid = (int)($data['finished_item_id'] ?? 0);
            $lines = is_array($data['lines'] ?? null) ? $data['lines'] : [];
            $chk = $pdo->prepare("SELECT name, product_id FROM inventory_items WHERE id = ? AND item_type = 'finished'");
            $chk->execute([$fid]);
            $fgRow = $chk->fetch();
            if (!$fgRow) jsonResponse(['success' => false, 'message' => 'Select a finished product.'], 422);
            $fgName = $fgRow['name'];

            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM product_recipes WHERE finished_item_id = ?")->execute([$fid]);
            $ins = $pdo->prepare("INSERT INTO product_recipes (finished_item_id, ingredient_item_id, qty_per_unit) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE qty_per_unit = VALUES(qty_per_unit)");
            $isRaw = $pdo->prepare("SELECT COUNT(*) FROM inventory_items WHERE id = ? AND item_type = 'raw'");
            foreach ($lines as $ln) {
                $ing = (int)($ln['ingredient_item_id'] ?? 0);
                $q = (float)($ln['qty_per_unit'] ?? 0);
                if (!$ing || $q <= 0) continue;
                $isRaw->execute([$ing]);
                if (!(int)$isRaw->fetchColumn()) throw new Exception('Recipe ingredients must be raw materials.');
                $ins->execute([$fid, $ing, $q]);
            }

            if (isset($data['addons']) && is_array($data['addons'])) {
                saveFinishedItemAddons($pdo, $fid, (int)($fgRow['product_id'] ?? 0), $data['addons']);
            }

            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "Recipe & add-ons for '$fgName' saved."]);
        }

        if ($action === 'save_addons') {
            requireAdmin($isAdmin);
            $fid = (int)($data['finished_item_id'] ?? 0);
            $addons = is_array($data['addons'] ?? null) ? $data['addons'] : [];
            $chk = $pdo->prepare("SELECT name, product_id FROM inventory_items WHERE id = ? AND item_type = 'finished'");
            $chk->execute([$fid]);
            $fgRow = $chk->fetch();
            if (!$fgRow) jsonResponse(['success' => false, 'message' => 'Select a finished product.'], 422);

            $pdo->beginTransaction();
            saveFinishedItemAddons($pdo, $fid, (int)($fgRow['product_id'] ?? 0), $addons);
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "Add-ons for '{$fgRow['name']}' saved."]);
        }

        // -------------------------------------------------------------
        // STOCK IN / STOCK OUT
        // -------------------------------------------------------------
        if ($action === 'save_stock_txn') {
            requireAdmin($isAdmin);
            $type = ($data['txn_type'] ?? 'IN') === 'OUT' ? 'OUT' : 'IN';
            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['txn_date'] ?? '') ? $data['txn_date'] : date('Y-m-d');
            $lines = is_array($data['lines'] ?? null) ? $data['lines'] : [];
            $clean = [];
            foreach ($lines as $ln) {
                $iid = (int)($ln['item_id'] ?? 0);
                $q = round((float)($ln['quantity'] ?? 0), 4);
                if ($iid && $q > 0) $clean[] = [$iid, $q, max(0, (float)($ln['unit_cost'] ?? 0)), sanitize($ln['remarks'] ?? '')];
            }
            if (!$clean) jsonResponse(['success' => false, 'message' => 'Add at least one item line with quantity greater than zero.'], 422);
            if ($type === 'OUT' && trim($data['party'] ?? '') === '') {
                jsonResponse(['success' => false, 'message' => 'Please state the reason for this Stock Out.'], 422);
            }

            $pdo->beginTransaction();
            $no = inv_next_no($pdo, $type === 'IN' ? 'SI' : 'SO', 'stock_transactions', 'txn_no');
            $pdo->prepare("INSERT INTO stock_transactions (txn_no, txn_type, txn_date, reference_no, party, remarks, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, 'Draft', ?)")
                ->execute([$no, $type, $date, sanitize($data['reference_no'] ?? ''), sanitize($data['party'] ?? ''),
                    sanitize($data['remarks'] ?? ''), inv_user()]);
            $txnId = (int)$pdo->lastInsertId();
            $li = $pdo->prepare("INSERT INTO stock_transaction_lines (txn_id, item_id, quantity, unit_cost, remarks) VALUES (?, ?, ?, ?, ?)");
            foreach ($clean as $c) $li->execute([$txnId, $c[0], $c[1], $c[2], $c[3]]);

            if (!empty($data['post'])) postStockTxn($pdo, $txnId);
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "$no " . (!empty($data['post']) ? 'posted to inventory ledger.' : 'saved as draft.'), 'txn_no' => $no]);
        }

        if ($action === 'post_stock_txn') {
            requireAdmin($isAdmin);
            $pdo->beginTransaction();
            $no = postStockTxn($pdo, (int)($data['id'] ?? 0));
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "$no posted to inventory ledger."]);
        }

        if ($action === 'cancel_stock_txn') {
            requireAdmin($isAdmin);
            $s = $pdo->prepare("UPDATE stock_transactions SET status = 'Cancelled' WHERE id = ? AND status = 'Draft'");
            $s->execute([(int)($data['id'] ?? 0)]);
            if (!$s->rowCount()) jsonResponse(['success' => false, 'message' => 'Only draft documents can be cancelled. Posted entries are final; create a reversing document instead.'], 409);
            jsonResponse(['success' => true, 'message' => 'Draft cancelled.']);
        }

        // -------------------------------------------------------------
        // ITEM CONVERSION (e.g. 1 pack -> 1000 ml, 1 kg -> 1000 g)
        // -------------------------------------------------------------
        if ($action === 'save_conversion') {
            requireAdmin($isAdmin);
            $src = (int)($data['source_item_id'] ?? 0);
            $dst = (int)($data['dest_item_id'] ?? 0);
            $sq = round((float)($data['source_qty'] ?? 0), 4);
            $factor = round((float)($data['factor'] ?? 0), 4);
            if (!$src || !$dst || $src === $dst) jsonResponse(['success' => false, 'message' => 'Select two different items (source and destination).'], 422);
            if ($sq <= 0 || $factor <= 0) jsonResponse(['success' => false, 'message' => 'Source quantity and conversion factor must be greater than zero.'], 422);

            $pdo->beginTransaction();
            $no = inv_next_no($pdo, 'CV', 'item_conversions', 'conv_no');
            $pdo->prepare("INSERT INTO item_conversions (conv_no, conv_date, source_item_id, source_qty, dest_item_id, factor, dest_qty, remarks, status, created_by)
                VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, 'Draft', ?)")
                ->execute([$no, $src, $sq, $dst, $factor, round($sq * $factor, 4), sanitize($data['remarks'] ?? ''), inv_user()]);
            $cid = (int)$pdo->lastInsertId();
            if (!empty($data['post'])) postConversion($pdo, $cid);
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "$no " . (!empty($data['post']) ? 'posted.' : 'saved as draft.')]);
        }

        if ($action === 'post_conversion') {
            requireAdmin($isAdmin);
            $pdo->beginTransaction();
            $no = postConversion($pdo, (int)($data['id'] ?? 0));
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "$no posted."]);
        }

        if ($action === 'cancel_conversion') {
            requireAdmin($isAdmin);
            $s = $pdo->prepare("UPDATE item_conversions SET status = 'Cancelled' WHERE id = ? AND status = 'Draft'");
            $s->execute([(int)($data['id'] ?? 0)]);
            if (!$s->rowCount()) jsonResponse(['success' => false, 'message' => 'Only draft conversions can be cancelled.'], 409);
            jsonResponse(['success' => true, 'message' => 'Draft cancelled.']);
        }

        // -------------------------------------------------------------
        // BREW PRODUCTION (-ingredients & -addons, +finished product)
        // -------------------------------------------------------------
        if ($action === 'save_brew') {
            $fid = (int)($data['finished_item_id'] ?? 0);
            $qty = round((float)($data['quantity'] ?? 0), 4);
            $addons = is_array($data['addons'] ?? null) ? $data['addons'] : [];
            if (!$fid || $qty <= 0) jsonResponse(['success' => false, 'message' => 'Select a product and a quantity greater than zero.'], 422);

            $cleanAddons = [];
            foreach ($addons as $ad) {
                $aid = (int)($ad['addon_id'] ?? $ad['id'] ?? 0);
                $aq = round((float)($ad['qty'] ?? $qty), 4);
                if ($aid > 0 && $aq > 0) {
                    $cleanAddons[] = ['addon_id' => $aid, 'qty' => $aq, 'addon_name' => sanitize($ad['addon_name'] ?? '')];
                }
            }
            $addonsJson = !empty($cleanAddons) ? json_encode($cleanAddons) : null;

            $pdo->beginTransaction();
            $no = inv_next_no($pdo, 'BR', 'brew_batches', 'batch_no');
            $pdo->prepare("INSERT INTO brew_batches (batch_no, batch_date, finished_item_id, quantity, addons_json, remarks, status, created_by)
                VALUES (?, CURDATE(), ?, ?, ?, ?, 'Draft', ?)")
                ->execute([$no, $fid, $qty, $addonsJson, sanitize($data['remarks'] ?? ''), inv_user()]);
            $bid = (int)$pdo->lastInsertId();
            if (!empty($data['post'])) postBrew($pdo, $bid);
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "$no " . (!empty($data['post']) ? 'brewed & posted.' : 'saved as draft.')]);
        }

        if ($action === 'post_brew') {
            $pdo->beginTransaction();
            $no = postBrew($pdo, (int)($data['id'] ?? 0));
            $pdo->commit();
            jsonResponse(['success' => true, 'message' => "$no brewed & posted."]);
        }

        if ($action === 'cancel_brew') {
            $s = $pdo->prepare("UPDATE brew_batches SET status = 'Cancelled' WHERE id = ? AND status = 'Draft'");
            $s->execute([(int)($data['id'] ?? 0)]);
            if (!$s->rowCount()) jsonResponse(['success' => false, 'message' => 'Only draft batches can be cancelled.'], 409);
            jsonResponse(['success' => true, 'message' => 'Draft cancelled.']);
        }

        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 422);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

// =====================================================================
// POSTING ROUTINES (called inside an open transaction)
// =====================================================================

function postStockTxn(PDO $pdo, int $id): string
{
    $h = $pdo->prepare("SELECT * FROM stock_transactions WHERE id = ? FOR UPDATE");
    $h->execute([$id]);
    $txn = $h->fetch();
    if (!$txn) throw new Exception('Document not found.');
    if ($txn['status'] !== 'Draft') throw new Exception("{$txn['txn_no']} is already {$txn['status']}.");

    $l = $pdo->prepare("SELECT l.*, i.name FROM stock_transaction_lines l JOIN inventory_items i ON i.id = l.item_id WHERE l.txn_id = ? ORDER BY l.item_id");
    $l->execute([$id]);
    $lines = $l->fetchAll();
    if (!$lines) throw new Exception('Document has no lines.');

    $isIn = $txn['txn_type'] === 'IN';
    $particulars = ($isIn ? 'Stock In' : 'Stock Out') . ($txn['party'] ? ' - ' . $txn['party'] : '') . ($txn['reference_no'] ? ' (Ref ' . $txn['reference_no'] . ')' : '');
    foreach ($lines as $ln) {
        $q = (float)$ln['quantity'];
        inv_post_movement($pdo, (int)$ln['item_id'], $isIn ? $q : 0, $isIn ? 0 : $q,
            $isIn ? 'STOCK_IN' : 'STOCK_OUT', 'stock_transactions', $id, $txn['txn_no'], $particulars,
            (float)$ln['unit_cost'] > 0 ? (float)$ln['unit_cost'] : null);
        if ($isIn && (float)$ln['unit_cost'] > 0) {
            $pdo->prepare("UPDATE inventory_items SET unit_cost = ? WHERE id = ?")->execute([(float)$ln['unit_cost'], $ln['item_id']]);
        }
    }
    $pdo->prepare("UPDATE stock_transactions SET status = 'Posted', posted_by = ?, posted_at = NOW() WHERE id = ?")->execute([inv_user(), $id]);
    return $txn['txn_no'];
}

function postConversion(PDO $pdo, int $id): string
{
    $h = $pdo->prepare("SELECT c.*, s.name AS sname, s.unit AS sunit, d.name AS dname, d.unit AS dunit
        FROM item_conversions c JOIN inventory_items s ON s.id = c.source_item_id JOIN inventory_items d ON d.id = c.dest_item_id
        WHERE c.id = ? FOR UPDATE");
    $h->execute([$id]);
    $c = $h->fetch();
    if (!$c) throw new Exception('Conversion not found.');
    if ($c['status'] !== 'Draft') throw new Exception("{$c['conv_no']} is already {$c['status']}.");

    $desc = sprintf('Convert %s %s %s -> %s %s %s', inv_fmt($c['source_qty']), $c['sunit'], $c['sname'], inv_fmt($c['dest_qty']), $c['dunit'], $c['dname']);
    // Source first: validates that the item to convert actually has stock
    inv_post_movement($pdo, (int)$c['source_item_id'], 0, (float)$c['source_qty'], 'CONV_OUT', 'item_conversions', $id, $c['conv_no'], $desc);

    // Carry cost over to the destination unit (cost per source unit / factor)
    $srcCost = (float)$pdo->query("SELECT unit_cost FROM inventory_items WHERE id = " . (int)$c['source_item_id'])->fetchColumn();
    $dstCost = $c['factor'] > 0 ? round($srcCost / (float)$c['factor'], 4) : null;
    inv_post_movement($pdo, (int)$c['dest_item_id'], (float)$c['dest_qty'], 0, 'CONV_IN', 'item_conversions', $id, $c['conv_no'], $desc, $dstCost);

    $pdo->prepare("UPDATE item_conversions SET status = 'Posted', posted_by = ?, posted_at = NOW() WHERE id = ?")->execute([inv_user(), $id]);
    return $c['conv_no'];
}

function saveFinishedItemAddons(PDO $pdo, int $fid, int $productId, array $addons): void
{
    $pdo->prepare("DELETE FROM product_item_addons WHERE finished_item_id = ?")->execute([$fid]);
    $ins = $pdo->prepare("INSERT INTO product_item_addons (finished_item_id, addon_name, ingredient_item_id, qty_per_unit, price)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE ingredient_item_id = VALUES(ingredient_item_id), qty_per_unit = VALUES(qty_per_unit), price = VALUES(price)");
    $isRaw = $pdo->prepare("SELECT COUNT(*) FROM inventory_items WHERE id = ? AND item_type = 'raw'");
    $chkGlobalAddon = $pdo->prepare("SELECT id FROM product_addons WHERE LOWER(name) = LOWER(?) LIMIT 1");
    $insGlobalAddon = $pdo->prepare("INSERT INTO product_addons (product_id, name, addon_name, price, is_available) VALUES (NULL, ?, ?, ?, 1)");

    foreach ($addons as $ad) {
        $name = trim(sanitize($ad['addon_name'] ?? ''));
        $ing = (int)($ad['ingredient_item_id'] ?? 0);
        $q = round((float)($ad['qty_per_unit'] ?? 0), 4);
        $price = max(0, round((float)($ad['price'] ?? 0), 2));
        if ($name === '' || !$ing || $q <= 0) continue;
        $isRaw->execute([$ing]);
        if (!(int)$isRaw->fetchColumn()) throw new Exception("Add-on ingredient for '$name' must be a raw material.");
        $ins->execute([$fid, $name, $ing, $q, $price]);

        // Ensure the add-on also exists in `product_addons` so POS can offer it
        $chkGlobalAddon->execute([$name]);
        if (!$chkGlobalAddon->fetchColumn()) {
            $insGlobalAddon->execute([$name, $name, $price]);
        }
    }
}

function postBrew(PDO $pdo, int $id): string
{
    $h = $pdo->prepare("SELECT b.*, i.name, i.item_type FROM brew_batches b JOIN inventory_items i ON i.id = b.finished_item_id WHERE b.id = ? FOR UPDATE");
    $h->execute([$id]);
    $b = $h->fetch();
    if (!$b) throw new Exception('Brew batch not found.');
    if ($b['status'] !== 'Draft') throw new Exception("{$b['batch_no']} is already {$b['status']}.");
    if ($b['item_type'] !== 'finished') throw new Exception('Only finished products can be brewed.');

    $selectedAddons = !empty($b['addons_json']) ? (json_decode($b['addons_json'], true) ?: []) : [];
    $req = brewRequirements($pdo, (int)$b['finished_item_id'], (float)$b['quantity'], $selectedAddons);
    if (!$req) throw new Exception("'{$b['name']}' has no recipe. Define its ingredients first (Recipes tab).");

    // Validate ALL ingredients + add-ons up-front for a complete shortage report
    $short = [];
    foreach ($req as $r) {
        if (!$r['sufficient']) $short[] = sprintf('%s (need %s %s, have %s)', $r['name'], inv_fmt($r['required']), $r['unit'], inv_fmt($r['available']));
    }
    if ($short) throw new Exception('Insufficient ingredients/add-ons to brew ' . inv_fmt($b['quantity']) . " x {$b['name']}: " . implode('; ', $short));

    $addonSummary = '';
    if (!empty($selectedAddons)) {
        $names = array_filter(array_map(fn($a) => ($a['addon_name'] ?? 'Add-on') . ' x' . inv_fmt($a['qty'] ?? $b['quantity']), $selectedAddons));
        if ($names) $addonSummary = ' [+' . implode(', +', $names) . ']';
    }
    $desc = 'Brew ' . inv_fmt($b['quantity']) . ' x ' . $b['name'] . $addonSummary;
    foreach ($req as $r) {
        $part = $desc . ' (' . ($r['source_type'] ?? 'Recipe') . ')';
        inv_post_movement($pdo, $r['item_id'], 0, $r['required'], 'BREW_OUT', 'brew_batches', $id, $b['batch_no'], $part);
    }

    // Finished products are not stocked units — only raw ingredients & add-ons are deducted (BREW_OUT -)
    $pdo->prepare("UPDATE brew_batches SET status = 'Posted', posted_by = ?, posted_at = NOW() WHERE id = ?")->execute([inv_user(), $id]);
    return $b['batch_no'];
}

