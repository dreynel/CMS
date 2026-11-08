<?php
/**
 * ISAT U Coffee Shop - Cashier POS API
 * Native PHP / PDO Backend
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/inventory.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
inv_ensure_schema($pdo); // DDL (first run only) must happen before any transaction
$method = $_SERVER['REQUEST_METHOD'];

// GET Requests
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'menu';

    // 1. Reception Table Management & 15-Minute Reservation Alerts
    if ($action === 'tables' || $action === 'get_tables_and_alerts') {
        try {
            // Fetch all tables with zone styling
            $tableStmt = $pdo->query("SELECT t.*, z.badge_color, z.icon as zone_icon 
                FROM dining_tables t 
                LEFT JOIN zones z ON (t.zone_id = z.id OR t.zone = z.name) 
                ORDER BY t.zone, t.table_code");
            $rawTables = $tableStmt->fetchAll();

            // Fetch today's reservations for live status mapping & alert detection
            $resStmt = $pdo->query("SELECT r.*, t.table_code, t.name as table_name, t.status as current_table_status 
                FROM reservations r 
                JOIN dining_tables t ON r.table_id = t.id 
                WHERE DATE(r.reservation_date) = CURDATE() 
                  AND r.status IN ('Confirmed', 'Checked-In') 
                ORDER BY r.created_at ASC");
            $todayReservations = $resStmt->fetchAll();

            $now = time();
            $urgentAlerts = [];
            $tableActiveRes = [];

            foreach ($todayReservations as $r) {
                // Parse slot start time (e.g. "01:00 PM - 03:00 PM" -> "01:00 PM")
                $parts = explode('-', $r['time_slot']);
                $startStr = trim($parts[0]);
                $slotTimestamp = strtotime($r['reservation_date'] . ' ' . $startStr);

                $diffMinutes = null;
                $isUrgent = false;

                if ($slotTimestamp !== false) {
                    $diffMinutes = (int)round(($slotTimestamp - $now) / 60);

                    // 15-Minute Window Alert: Starts in <= 15 mins (or started within last 45 mins)
                    // and table has not been updated to 'occupied'
                    $tblStatus = strtolower($r['current_table_status']);
                    if ($diffMinutes <= 15 && $diffMinutes >= -45 && $tblStatus !== 'occupied') {
                        $isUrgent = true;
                        $urgentAlerts[] = [
                            'reservation_id' => (int)$r['id'],
                            'reservation_number' => $r['reservation_number'],
                            'customer_name' => $r['customer_name'],
                            'phone' => $r['phone'],
                            'guest_count' => (int)$r['guest_count'],
                            'table_id' => (int)$r['table_id'],
                            'table_code' => $r['table_code'],
                            'table_name' => $r['table_name'],
                            'table_status' => $tblStatus,
                            'time_slot' => $r['time_slot'],
                            'minutes_diff' => $diffMinutes,
                            'is_past_start' => ($diffMinutes < 0),
                            'display_time' => ($diffMinutes <= 0) 
                                ? 'Started ' . abs($diffMinutes) . ' min ago' 
                                : 'Starts in ' . $diffMinutes . ' min',
                            'special_notes' => $r['special_notes'] ?? ''
                        ];
                    }
                }

                if (!isset($tableActiveRes[$r['table_id']])) {
                    $tableActiveRes[$r['table_id']] = [
                        'reservation_id' => (int)$r['id'],
                        'reservation_number' => $r['reservation_number'],
                        'customer_name' => $r['customer_name'],
                        'phone' => $r['phone'],
                        'time_slot' => $r['time_slot'],
                        'status' => $r['status'],
                        'diff_minutes' => $diffMinutes,
                        'is_urgent' => $isUrgent
                    ];
                }
            }

            // Combine tables with live reservation status
            $tables = [];
            foreach ($rawTables as $t) {
                $tid = (int)$t['id'];
                $hasRes = $tableActiveRes[$tid] ?? null;
                $tables[] = [
                    'id' => $tid,
                    'table_code' => $t['table_code'],
                    'name' => $t['name'],
                    'zone' => $t['zone'],
                    'capacity' => (int)$t['capacity'],
                    'has_power_outlet' => (bool)$t['has_power_outlet'],
                    'status' => strtolower($t['status']),
                    'badge_color' => $t['badge_color'] ?? 'primary',
                    'zone_icon' => $t['zone_icon'] ?? 'bi-geo-alt',
                    'active_reservation' => $hasRes
                ];
            }

            jsonResponse([
                'success' => true,
                'tables' => $tables,
                'urgent_alerts' => $urgentAlerts,
                'alert_count' => count($urgentAlerts)
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Direct lookup of reservation by ID (for Table Manager / Arrival alerts)
    if ($action === 'load_reservation' || $action === 'get_reservation') {
        $resId = (int)($_GET['id'] ?? $_GET['reservation_id'] ?? 0);
        if (!$resId) {
            jsonResponse(['success' => false, 'message' => 'Reservation ID required.'], 400);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT r.*, t.table_code, t.name AS table_name, t.zone, t.status AS table_status
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.id = ?
                LIMIT 1
            ");
            $stmt->execute([$resId]);
            $res = $stmt->fetch();

            if (!$res) {
                jsonResponse(['success' => false, 'message' => "Reservation #$resId not found."], 404);
            }

            $itemStmt = $pdo->prepare("
                SELECT ri.*, p.name AS catalog_name, p.category_id, p.base_price, p.image_url, c.name AS category_name
                FROM reservation_items ri
                LEFT JOIN products p ON ri.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE ri.reservation_id = ?
                ORDER BY ri.id ASC
            ");
            $itemStmt->execute([$resId]);
            $rawItems = $itemStmt->fetchAll();

            $formattedItems = [];
            foreach ($rawItems as $ri) {
                $addonsArr = [];
                if (!empty($ri['addons_json'])) {
                    $decoded = json_decode($ri['addons_json'], true);
                    if (is_array($decoded)) $addonsArr = $decoded;
                }
                $qty = max(1, (int)($ri['quantity'] ?? 1));
                $unitPrice = (float)($ri['unit_price'] ?? 0);
                $lineTotal = (float)($ri['line_total'] ?? ($unitPrice * $qty));

                $formattedItems[] = [
                    'id' => (int)$ri['id'],
                    'product_id' => (int)$ri['product_id'],
                    'product_name' => $ri['product_name'] ?: ($ri['catalog_name'] ?: 'Custom Item'),
                    'category_id' => (int)($ri['category_id'] ?: 1),
                    'category_name' => $ri['category_name'] ?: 'Beverage',
                    'size_name' => $ri['size_name'] ?: 'Regular',
                    'sugar_level' => $ri['sugar_level'] ?: '50% Half Sweet',
                    'ice_level' => $ri['ice_level'] ?: 'Regular Ice',
                    'addons' => $addonsArr,
                    'special_notes' => 'Pre-order from QR Pass',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal
                ];
            }

            $chkOrderStmt = $pdo->prepare("SELECT id, order_number, status, net_total FROM orders WHERE reservation_id = ? AND status != 'Cancelled' ORDER BY id DESC LIMIT 1");
            $chkOrderStmt->execute([$resId]);
            $existingOrder = $chkOrderStmt->fetch();

            jsonResponse([
                'success' => true,
                'type' => 'reservation_pass',
                'reservation' => [
                    'id' => $resId,
                    'reservation_number' => $res['reservation_number'],
                    'qr_token' => $res['qr_token'],
                    'customer_name' => $res['customer_name'],
                    'phone' => $res['phone'] ?? '',
                    'customer_type' => $res['customer_type'] ?? 'Regular',
                    'table_id' => (int)$res['table_id'],
                    'table_code' => $res['table_code'] ?? ('T-' . $res['table_id']),
                    'table_name' => $res['table_name'] ?? ('Table #' . $res['table_id']),
                    'zone' => $res['zone'] ?? 'Main Hall',
                    'reservation_date' => $res['reservation_date'],
                    'time_slot' => $res['time_slot'],
                    'status' => $res['status'],
                    'guest_count' => (int)($res['guest_count'] ?? 1),
                    'special_notes' => $res['special_notes'] ?? '',
                    'items' => $formattedItems,
                    'existing_order' => $existingOrder ?: null
                ]
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Optical QR Code / Barcode / Reservation QR Pass Auto-read lookup
    if ($action === 'qr' || $action === 'barcode' || $action === 'scan') {
        $rawCode = trim($_GET['code'] ?? '');
        if ($rawCode === '') {
            jsonResponse(['success' => false, 'message' => 'Empty product QR code or Pass token'], 400);
        }

        // Normalize QR payload if prefixed or URL (e.g. ISATU-PROD:SKU-BF-001, token=XXXX, pass?token=..., or JSON)
        $code = $rawCode;
        if (str_starts_with($code, '{')) {
            $decoded = json_decode($code, true);
            if (is_array($decoded) && !empty($decoded['code'])) {
                $code = trim((string)$decoded['code']);
            } elseif (is_array($decoded) && !empty($decoded['product_code'])) {
                $code = trim((string)$decoded['product_code']);
            } elseif (is_array($decoded) && !empty($decoded['token'])) {
                $code = trim((string)$decoded['token']);
            }
        } elseif (preg_match('/^(?:RAEDEN-PROD|RAEDEN-MENU|ISATU-PROD|ISATU-MENU|PROD|SKU|QR)\s*:\s*(.+)$/i', $code, $m)) {
            $code = trim($m[1]);
        } elseif (str_contains($code, 'token=') || str_contains($code, 'code=') || str_contains($code, 'sku=')) {
            $parts = parse_url($code);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $qParams);
                $code = trim((string)($qParams['token'] ?? $qParams['code'] ?? $qParams['sku'] ?? $code));
            }
        }

        try {
            // 1. Check if this code matches an active menu product
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN inventory_items ii ON ii.product_id = p.id
                WHERE (UPPER(p.product_code) = UPPER(?) OR UPPER(ii.item_code) = UPPER(?) OR p.id = ? OR LOWER(p.slug) = LOWER(?) OR LOWER(p.name) = LOWER(?))
                  AND p.is_available = 1
                LIMIT 1");
            $stmt->execute([$code, $code, $code, $code, $code]);
            $product = $stmt->fetch();

            if ($product) {
                // Fetch sizes and recipe/addons for this product
                $sizeStmt = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ?");
                $sizeStmt->execute([$product['id']]);
                $product['sizes'] = $sizeStmt->fetchAll();

                $product['recipe_ingredients'] = [];
                $product['allowed_addons'] = [];
                $prodFin = inv_finished_item_for_product($pdo, (int)$product['id'], $product['name']);
                if ($prodFin) {
                    $recStmt = $pdo->prepare("SELECT r.qty_per_unit, ing.name AS ingredient_name, ing.unit 
                        FROM product_recipes r 
                        JOIN inventory_items ing ON ing.id = r.ingredient_item_id 
                        WHERE r.finished_item_id = ?");
                    $recStmt->execute([$prodFin['id']]);
                    $product['recipe_ingredients'] = $recStmt->fetchAll();
                }

                jsonResponse([
                    'success' => true,
                    'type' => 'product',
                    'product' => $product,
                    'scanned_code' => $code
                ]);
            }

            // 2. If not a product, check if this code is a Customer Booking QR Pass / Reservation
            $resStmt = $pdo->prepare("
                SELECT r.*, t.table_code, t.name AS table_name, t.zone, t.status AS table_status
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.qr_token = ? OR r.reservation_number = ? OR r.phone = ?
                ORDER BY r.id DESC LIMIT 1
            ");
            $resStmt->execute([$code, $code, $code]);
            $res = $resStmt->fetch();

            if ($res) {
                $resId = (int)$res['id'];

                $itemStmt = $pdo->prepare("
                    SELECT ri.*, p.name AS catalog_name, p.category_id, p.base_price, p.image_url, c.name AS category_name
                    FROM reservation_items ri
                    LEFT JOIN products p ON ri.product_id = p.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE ri.reservation_id = ?
                    ORDER BY ri.id ASC
                ");
                $itemStmt->execute([$resId]);
                $rawItems = $itemStmt->fetchAll();

                $formattedItems = [];
                foreach ($rawItems as $ri) {
                    $addonsArr = [];
                    if (!empty($ri['addons_json'])) {
                        $decoded = json_decode($ri['addons_json'], true);
                        if (is_array($decoded)) $addonsArr = $decoded;
                    }
                    $qty = max(1, (int)($ri['quantity'] ?? 1));
                    $unitPrice = (float)($ri['unit_price'] ?? 0);
                    $lineTotal = (float)($ri['line_total'] ?? ($unitPrice * $qty));

                    $formattedItems[] = [
                        'id' => (int)$ri['id'],
                        'product_id' => (int)$ri['product_id'],
                        'product_name' => $ri['product_name'] ?: ($ri['catalog_name'] ?: 'Custom Item'),
                        'category_id' => (int)($ri['category_id'] ?: 1),
                        'category_name' => $ri['category_name'] ?: 'Beverage',
                        'size_name' => $ri['size_name'] ?: 'Regular',
                        'sugar_level' => $ri['sugar_level'] ?: '50% Half Sweet',
                        'ice_level' => $ri['ice_level'] ?: 'Regular Ice',
                        'addons' => $addonsArr,
                        'special_notes' => 'Pre-order from QR Pass',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal
                    ];
                }

                $chkOrderStmt = $pdo->prepare("SELECT id, order_number, status, net_total FROM orders WHERE reservation_id = ? AND status != 'Cancelled' ORDER BY id DESC LIMIT 1");
                $chkOrderStmt->execute([$resId]);
                $existingOrder = $chkOrderStmt->fetch();

                jsonResponse([
                    'success' => true,
                    'type' => 'reservation_pass',
                    'reservation' => [
                        'id' => $resId,
                        'reservation_number' => $res['reservation_number'],
                        'qr_token' => $res['qr_token'],
                        'customer_name' => $res['customer_name'],
                        'phone' => $res['phone'] ?? '',
                        'customer_type' => $res['customer_type'] ?? 'Regular',
                        'table_id' => (int)$res['table_id'],
                        'table_code' => $res['table_code'] ?? ('T-' . $res['table_id']),
                        'table_name' => $res['table_name'] ?? ('Table #' . $res['table_id']),
                        'zone' => $res['zone'] ?? 'Main Hall',
                        'reservation_date' => $res['reservation_date'],
                        'time_slot' => $res['time_slot'],
                        'status' => $res['status'],
                        'guest_count' => (int)($res['guest_count'] ?? 1),
                        'special_notes' => $res['special_notes'] ?? '',
                        'items' => $formattedItems,
                        'existing_order' => $existingOrder ?: null
                    ],
                    'scanned_code' => $code
                ]);
            }

            // Neither product nor reservation found
            jsonResponse([
                'success' => false,
                'message' => "Code '$code' was not recognized as a menu product QR code or customer booking pass."
            ], 404);

        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Default: Fetch full POS menu
    try {
        $categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
        $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.category_id, p.name")->fetchAll();
        $tables = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code")->fetchAll();
        $sizes = $pdo->query("SELECT * FROM product_sizes ORDER BY price_additional")->fetchAll();
        $addons = $pdo->query("SELECT * FROM product_addons WHERE is_available = 1")->fetchAll();

        jsonResponse([
            'success' => true,
            'categories' => $categories,
            'products' => $products,
            'tables' => $tables,
            'sizes' => $sizes,
            'addons' => $addons,
            'packaging_stock' => inv_get_packaging_stock_map($pdo),
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// POST Requests: Checkout Transaction & Reception Desk Actions
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $action = $_GET['action'] ?? ($data['action'] ?? 'checkout');

    // 1. Reception Desk: Update Dining Table Status
    if ($action === 'update_table_status') {
        $tableId = (int)($data['table_id'] ?? 0);
        $status = strtolower(sanitize($data['status'] ?? ''));
        $allowed = ['available', 'reserved', 'occupied', 'maintenance'];

        if (!$tableId || !in_array($status, $allowed)) {
            jsonResponse(['success' => false, 'message' => 'Invalid table ID or status.'], 422);
        }

        try {
            $pdo->beginTransaction();

            // Update table status
            $stmt = $pdo->prepare("UPDATE dining_tables SET status = ? WHERE id = ?");
            $stmt->execute([$status, $tableId]);

            // Sync with today's reservations
            if ($status === 'occupied') {
                $updRes = $pdo->prepare("UPDATE reservations SET status = 'Checked-In', checked_in_at = NOW() 
                    WHERE table_id = ? AND DATE(reservation_date) = CURDATE() AND status = 'Confirmed' LIMIT 1");
                $updRes->execute([$tableId]);
            } elseif ($status === 'available') {
                $updRes = $pdo->prepare("UPDATE reservations SET status = 'Completed' 
                    WHERE table_id = ? AND DATE(reservation_date) = CURDATE() AND status = 'Checked-In' LIMIT 1");
                $updRes->execute([$tableId]);
            }

            $pdo->commit();
            jsonResponse([
                'success' => true,
                'message' => "Table #$tableId status successfully set to " . ucfirst($status) . ".",
                'table_id' => $tableId,
                'status' => $status
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Reception Desk: Quick Seat Upcoming Reservation
    if ($action === 'quick_seat_reservation') {
        $resId = (int)($data['reservation_id'] ?? 0);
        if (!$resId) {
            jsonResponse(['success' => false, 'message' => 'Reservation ID is required.'], 422);
        }

        try {
            $pdo->beginTransaction();

            $resStmt = $pdo->prepare("SELECT r.*, t.table_code, t.name as table_name FROM reservations r JOIN dining_tables t ON r.table_id = t.id WHERE r.id = ?");
            $resStmt->execute([$resId]);
            $res = $resStmt->fetch();

            if (!$res) {
                throw new Exception("Reservation #$resId not found.");
            }

            // Set table occupied and reservation Checked-In
            $pdo->prepare("UPDATE dining_tables SET status = 'occupied' WHERE id = ?")->execute([$res['table_id']]);
            $pdo->prepare("UPDATE reservations SET status = 'Checked-In', checked_in_at = NOW() WHERE id = ?")->execute([$resId]);

            $pdo->commit();
            jsonResponse([
                'success' => true,
                'message' => "Seated guest {$res['customer_name']} at Table #{$res['table_id']} ({$res['table_code']}). Table marked Occupied.",
                'table_id' => (int)$res['table_id']
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. POS Checkout Transaction

    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
    if (empty($items)) {
        jsonResponse(['success' => false, 'message' => 'Cart is empty. Add at least one item.'], 422);
    }

    $orderType = sanitize($data['order_type'] ?? 'Dine-In');
    $tableNumber = sanitize($data['table_number'] ?? 'Counter Takeout');
    $customerName = sanitize($data['customer_name'] ?? 'Walk-in Customer');
    $cashierId = (int)($_SESSION['user']['id'] ?? 2);
    $discountType = sanitize($data['discount_type'] ?? 'None');
    $discountAmount = max(0.00, (float)($data['discount_amount'] ?? 0.00));
    $paymentMethod = sanitize($data['payment_method'] ?? 'Cash');
    $amountTendered = max(0.00, (float)($data['amount_tendered'] ?? $data['cash_tendered'] ?? 0.00));
    $paymentReference = sanitize($data['payment_reference'] ?? $data['ewallet_ref'] ?? '');

    // Strictly accept only Cash and GCash
    if (!in_array($paymentMethod, ['Cash', 'GCash'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid payment method. Only Cash and GCash payments are accepted.'], 422);
    }

    try {
        $pdo->beginTransaction();

        // 1. Generate Order Number (Collision-proof)
        $maxStmt = $pdo->query("SELECT MAX(id) FROM orders");
        $maxId = (int)$maxStmt->fetchColumn();
        $orderCounter = 1000 + $maxId + 1;
        do {
            $orderNumber = 'ORD-' . $orderCounter;
            $chkStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
            $chkStmt->execute([$orderNumber]);
            $exists = (int)$chkStmt->fetchColumn() > 0;
            if ($exists) {
                $orderCounter++;
            }
        } while ($exists);

        // 2. Process items (stock is validated & deducted via the inventory ledger after the order is created)
        $subtotal = 0.00;
        $processedItems = [];

        $checkProdStmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");

        foreach ($items as $it) {
            $productId = (int)($it['product_id'] ?? $it['id'] ?? 0);
            $qty = max(1, (int)($it['qty'] ?? $it['quantity'] ?? 1));
            $unitPrice = (float)($it['unit_price'] ?? $it['price'] ?? 0.00);
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;

            $checkProdStmt->execute([$productId]);
            $product = $checkProdStmt->fetch();

            if (!$product) {
                throw new Exception("Product #$productId no longer exists in the catalog.");
            }

            $rawAddons = $it['addons'] ?? ($it['addons_json'] ?? []);
            $addonsJson = is_array($rawAddons) ? json_encode($rawAddons) : (string)$rawAddons;

            $processedItems[] = [
                'product_id' => $productId,
                'product_name' => $product ? $product['name'] : sanitize($it['product_name'] ?? $it['name'] ?? 'Product'),
                'size_name' => sanitize($it['size_name'] ?? $it['size'] ?? 'Standard'),
                'sugar_level' => sanitize($it['sugar_level'] ?? $it['sugar'] ?? '50%'),
                'ice_level' => sanitize($it['ice_level'] ?? $it['ice'] ?? 'Normal Ice'),
                'addons_json' => $addonsJson ?: '[]',
                'special_notes' => sanitize($it['special_notes'] ?? $it['notes'] ?? ''),
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        // 2b. Validate that Cups, Straws, and Takeout Boxes have sufficient stock BEFORE ordering
        //     (Actual deduction happens in the Kitchen upon Brew & Serve -> Completed)
        inv_validate_order_packaging_stock($pdo, $processedItems, $orderType);

        // 3. Compute Totals, Discounts, VAT & Validate Tender
        $netTotal = max(0.00, $subtotal - $discountAmount);
        $vatAmount = $netTotal * (0.12 / 1.12); // 12% Philippine VAT included in gross price

        if ($paymentMethod === 'Cash') {
            if ($amountTendered < $netTotal) {
                throw new Exception("Cash tendered (₱" . number_format($amountTendered, 2) . ") is less than the net amount due (₱" . number_format($netTotal, 2) . ").");
            }
            $amountChange = $amountTendered - $netTotal;
        } elseif ($paymentMethod === 'GCash') {
            if (empty($paymentReference)) {
                throw new Exception("GCash Reference Number is required for digital payments.");
            }
            $amountTendered = $netTotal;
            $amountChange = 0.00;
        } else {
            $amountChange = 0.00;
        }

        // 4. Insert Order (linked to reservation_id if loaded from booking QR pass)
        $reservationId = !empty($data['reservation_id']) ? (int)$data['reservation_id'] : null;

        $orderStmt = $pdo->prepare("INSERT INTO orders (
            order_number, cashier_id, order_type, table_number, customer_name,
            subtotal, discount_type, discount_amount, vat_amount, net_total,
            payment_method, payment_reference, status, reservation_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");

        $orderStmt->execute([
            $orderNumber, $cashierId, $orderType, $tableNumber, $customerName,
            $subtotal, $discountType, $discountAmount, $vatAmount, $netTotal,
            $paymentMethod, $paymentReference, $reservationId
        ]);

        $orderId = $pdo->lastInsertId();

        // 4b. If linked to an online reservation, mark booking Checked-In & table occupied
        if ($reservationId > 0) {
            $pdo->prepare("UPDATE reservations SET status = 'Checked-In', checked_in_at = NOW() WHERE id = ?")->execute([$reservationId]);
            $resTableId = (int)$pdo->query("SELECT table_id FROM reservations WHERE id = $reservationId")->fetchColumn();
            if ($resTableId > 0) {
                $pdo->prepare("UPDATE dining_tables SET status = 'occupied' WHERE id = ?")->execute([$resTableId]);
            }
        }

        // 5. Insert Order Items (POS records Sales only and dispatches order to Kitchen/Barista;
        //    raw material ingredients are deducted in the Kitchen once brewed and served)
        $itemStmt = $pdo->prepare("INSERT INTO order_items (
            order_id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, special_notes, quantity, unit_price, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($processedItems as $pi) {
            $itemStmt->execute([
                $orderId, $pi['product_id'], $pi['product_name'], $pi['size_name'],
                $pi['sugar_level'], $pi['ice_level'], $pi['addons_json'], $pi['special_notes'],
                $pi['quantity'], $pi['unit_price'], $pi['line_total']
            ]);
        }

        // 6. Insert Payment Record
        $payStmt = $pdo->prepare("INSERT INTO payments (
            order_id, payment_method, amount_tendered, amount_change, payment_reference, status
        ) VALUES (?, ?, ?, ?, ?, 'Paid')");

        $payStmt->execute([
            $orderId, $paymentMethod, $amountTendered, $amountChange, $paymentReference
        ]);

        $pdo->commit();

        jsonResponse([
            'success' => true,
            'message' => "Order $orderNumber completed successfully! Ticket dispatched to Kitchen Display.",
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'vat_amount' => $vatAmount,
            'net_total' => $netTotal,
            'amount_tendered' => $amountTendered,
            'amount_change' => $amountChange,
            'order_type' => $orderType,
            'table_number' => $tableNumber,
            'customer_name' => $customerName,
            'payment_method' => $paymentMethod,
            'items' => $processedItems,
            'packaging_stock' => inv_get_packaging_stock_map($pdo),
            'date_time' => date('Y-m-d H:i:s'),
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
