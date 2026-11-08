<?php
/**
 * ISAT U Coffee Shop - Customer Reservations API
 * Native PHP / PDO Backend
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/inventory.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Booking secures seating; pre-orders are fetched and confirmed at POS upon arrival
$scheduledSync = ['dispatched' => [], 'upcoming' => []];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    // Lookup a specific booking by QR token, Reservation Number, or Phone for Post-Booking Customer Pass / Lookup
    if ($action === 'lookup_booking') {
        $q = trim((string)($_GET['token'] ?? $_GET['query'] ?? ''));
        if ($q === '') {
            jsonResponse(['success' => false, 'message' => 'Please enter a QR Token, Reservation Number, or Phone Number.'], 422);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT r.*, dt.table_code, dt.name AS table_name, dt.zone, dt.capacity, dt.has_power_outlet
                FROM reservations r
                LEFT JOIN dining_tables dt ON r.table_id = dt.id
                WHERE r.qr_token = ? OR r.reservation_number = ? OR r.phone = ?
                ORDER BY r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$q, $q, $q]);
            $res = $stmt->fetch();

            if (!$res) {
                jsonResponse(['success' => false, 'message' => 'No reservation found matching "' . htmlspecialchars($q) . '".'], 404);
            }

            $itemStmt = $pdo->prepare("SELECT * FROM reservation_items WHERE reservation_id = ? ORDER BY id ASC");
            $itemStmt->execute([(int)$res['id']]);
            $items = $itemStmt->fetchAll();

            $ordStmt = $pdo->prepare("SELECT * FROM orders WHERE reservation_id = ? AND status != 'Cancelled' ORDER BY id DESC LIMIT 1");
            $ordStmt->execute([(int)$res['id']]);
            $order = $ordStmt->fetch() ?: null;

            $sched = inv_parse_reservation_schedule($res);

            jsonResponse([
                'success'     => true,
                'reservation' => $res,
                'items'       => $items,
                'order'       => $order,
                'schedule'    => $sched,
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Dynamic Seating Availability & Trapping Checker by Date & Time Slot
    if ($action === 'check_availability') {
        $selDate = sanitize($_GET['date'] ?? date('Y-m-d'));
        $selSlot = sanitize($_GET['time_slot'] ?? '');

        try {
            // Fetch all dining tables
            $tablesStmt = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code");
            $allTables = $tablesStmt->fetchAll();

            // Fetch active reservations on this date and time slot
            $resMap = [];
            if (!empty($selSlot)) {
                $resStmt = $pdo->prepare("SELECT table_id, reservation_number, customer_name, status 
                    FROM reservations 
                    WHERE reservation_date = ? 
                      AND time_slot = ? 
                      AND status IN ('Confirmed', 'Checked-In')");
                $resStmt->execute([$selDate, $selSlot]);
                $slotRes = $resStmt->fetchAll();
                foreach ($slotRes as $sr) {
                    $resMap[$sr['table_id']] = $sr;
                }
            }

            $availability = [];
            foreach ($allTables as $t) {
                $tid = $t['id'];
                $baseStatus = strtolower($t['status']); // available, reserved, occupied, maintenance
                $effectiveStatus = 'available';
                $isSelectable = true;
                $reason = 'Available for Booking';

                if ($baseStatus === 'maintenance') {
                    $effectiveStatus = 'maintenance';
                    $isSelectable = false;
                    $reason = 'Under Maintenance';
                } elseif (isset($resMap[$tid])) {
                    // Direct time slot conflict
                    $effectiveStatus = 'reserved';
                    $isSelectable = false;
                    $reason = 'Booked for this Time Slot (' . htmlspecialchars($resMap[$tid]['customer_name']) . ')';
                } elseif ($baseStatus === 'occupied' && $selDate === date('Y-m-d')) {
                    $effectiveStatus = 'occupied';
                    $isSelectable = false;
                    $reason = 'Currently Seated / In Service';
                } elseif ($baseStatus === 'reserved' && $selDate === date('Y-m-d')) {
                    // Reserved in table matrix
                    $effectiveStatus = 'reserved';
                    $isSelectable = false;
                    $reason = 'Reserved by Reception';
                } elseif ($baseStatus !== 'available') {
                    $effectiveStatus = $baseStatus;
                    $isSelectable = false;
                    $reason = ucfirst($baseStatus);
                }

                $availability[] = [
                    'id' => (int)$t['id'],
                    'table_code' => $t['table_code'],
                    'name' => $t['name'],
                    'zone' => $t['zone'],
                    'capacity' => (int)$t['capacity'],
                    'has_power_outlet' => (bool)$t['has_power_outlet'],
                    'base_status' => $baseStatus,
                    'effective_status' => $effectiveStatus,
                    'is_selectable' => $isSelectable,
                    'reason' => $reason,
                    'reservation' => $resMap[$tid] ?? null
                ];
            }

            jsonResponse([
                'success' => true,
                'date' => $selDate,
                'time_slot' => $selSlot,
                'tables' => $availability
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Fetch active tables and seating status
    try {
        $stmt = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code");
        $tables = $stmt->fetchAll();

        // Fetch active seating zones
        $zonesStmt = $pdo->query("SELECT * FROM zones WHERE is_active = 1 ORDER BY id");
        $zones = $zonesStmt->fetchAll();

        // Also fetch active categories and products
        $catStmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
        $categories = $catStmt->fetchAll();

        $prodStmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.category_id, p.name");
        $products = $prodStmt->fetchAll();

        // Fetch sizes and addons
        $sizes = $pdo->query("SELECT * FROM product_sizes ORDER BY product_id, price_additional")->fetchAll();
        $addons = $pdo->query("SELECT * FROM product_addons WHERE is_available = 1")->fetchAll();

        jsonResponse([
            'success' => true,
            'tables' => $tables,
            'zones' => $zones,
            'categories' => $categories,
            'products' => $products,
            'sizes' => $sizes,
            'addons' => $addons,
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $postAction = $data['action'] ?? '';

    // -------------------------------------------------------------------------
    // POST ACTION 1: Customer / Staff triggers "Prepare My Order Now"
    // (Dispatches scheduled pre-order to Kitchen KDS ahead of or at arrival)
    // -------------------------------------------------------------------------
    if ($postAction === 'prepare_now') {
        $token = trim((string)($data['qr_token'] ?? $data['token'] ?? ''));
        $resId = (int)($data['reservation_id'] ?? 0);

        try {
            if ($resId > 0) {
                $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? LIMIT 1");
                $stmt->execute([$resId]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM reservations WHERE qr_token = ? OR reservation_number = ? LIMIT 1");
                $stmt->execute([$token, $token]);
            }
            $res = $stmt->fetch();

            if (!$res) {
                jsonResponse(['success' => false, 'message' => 'Reservation not found.'], 404);
            }
            if ($res['status'] === 'Cancelled') {
                jsonResponse(['success' => false, 'message' => 'Cannot prepare order for a cancelled reservation.'], 422);
            }

            $order = inv_create_order_from_reservation($pdo, (int)$res['id'], 'Early Prep Triggered by Guest/Staff');
            if (!$order) {
                jsonResponse(['success' => false, 'message' => 'No pre-ordered items found on this reservation. Please add items to your booking first!'], 422);
            }

            $msg = !empty($order['already_exists'])
                ? "Your order ({$order['order_number']}) is already in the Barista Kitchen queue ({$order['status']})."
                : "Order {$order['order_number']} has been dispatched to the Barista Kitchen for immediate preparation!";

            jsonResponse([
                'success' => true,
                'message' => $msg,
                'order'   => $order,
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST ACTION 2: Customer adds / updates Pre-Order Items after booking
    // -------------------------------------------------------------------------
    if ($postAction === 'add_preorder_items') {
        $token = trim((string)($data['qr_token'] ?? $data['token'] ?? ''));
        $resId = (int)($data['reservation_id'] ?? 0);
        $newItems = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        if (empty($newItems)) {
            jsonResponse(['success' => false, 'message' => 'Please select at least one item to add to your booking.'], 422);
        }

        try {
            $pdo->beginTransaction();

            if ($resId > 0) {
                $stmt = $pdo->prepare("SELECT r.*, dt.table_code FROM reservations r LEFT JOIN dining_tables dt ON r.table_id = dt.id WHERE r.id = ? FOR UPDATE");
                $stmt->execute([$resId]);
            } else {
                $stmt = $pdo->prepare("SELECT r.*, dt.table_code FROM reservations r LEFT JOIN dining_tables dt ON r.table_id = dt.id WHERE r.qr_token = ? OR r.reservation_number = ? FOR UPDATE");
                $stmt->execute([$token, $token]);
            }
            $res = $stmt->fetch();

            if (!$res) {
                $pdo->rollBack();
                jsonResponse(['success' => false, 'message' => 'Reservation not found.'], 404);
            }
            if (!in_array($res['status'], ['Confirmed', 'Checked-In'], true)) {
                $pdo->rollBack();
                jsonResponse(['success' => false, 'message' => 'Items can only be added to Confirmed or Checked-In reservations.'], 422);
            }

            $reservationId = (int)$res['id'];
            $itemStmt = $pdo->prepare("INSERT INTO reservation_items (
                reservation_id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, quantity, unit_price, line_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $prodCheckStmt = $pdo->prepare("SELECT id, name, category_id, base_price FROM products WHERE id = ?");

            $addedTotal = 0.0;
            $insertedItems = [];

            foreach ($newItems as $it) {
                $prodId = (int)($it['product_id'] ?? 0);
                $prodDb = null;
                if ($prodId > 0) {
                    $prodCheckStmt->execute([$prodId]);
                    $prodDb = $prodCheckStmt->fetch();
                }

                $prodName = $prodDb ? $prodDb['name'] : sanitize($it['product_name'] ?? $it['name'] ?? 'Custom Item');
                $isPastry = $prodDb ? ((int)$prodDb['category_id'] === 4) : false;
                $sizeName = sanitize($it['size_name'] ?? $it['size'] ?? ($isPastry ? 'Standard Serving' : 'Regular (12oz)'));
                if ($sizeName === '') {
                    $sizeName = $isPastry ? 'Standard Serving' : 'Regular (12oz)';
                }
                $sugarLevel = sanitize($it['sugar_level'] ?? $it['sugar'] ?? ($isPastry ? 'Pastry' : '50%'));
                $iceLevel   = sanitize($it['ice_level'] ?? $it['ice'] ?? ($isPastry ? 'Warm / Lightly Toasted' : 'Regular Ice'));
                $addonsJson = isset($it['addons']) && is_array($it['addons']) ? json_encode($it['addons']) : '[]';
                $qty        = max(1, (int)($it['quantity'] ?? $it['qty'] ?? 1));
                $unitPrice  = max(0.00, (float)($it['unit_price'] ?? $it['price'] ?? ($prodDb ? $prodDb['base_price'] : 0.00)));
                $lineTotal  = round($unitPrice * $qty, 2);

                $itemStmt->execute([
                    $reservationId, $prodId ?: null, $prodName, $sizeName, $sugarLevel, $iceLevel, $addonsJson, $qty, $unitPrice, $lineTotal
                ]);

                $addedTotal += $lineTotal;
                $insertedItems[] = [
                    'product_id'   => $prodId ?: null,
                    'product_name' => $prodName,
                    'size_name'    => $sizeName,
                    'sugar_level'  => $sugarLevel,
                    'ice_level'    => $iceLevel,
                    'addons_json'  => $addonsJson,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'line_total'   => $lineTotal,
                ];
            }

            // Recalculate reservation total_amount
            $sumStmt = $pdo->prepare("SELECT COALESCE(SUM(line_total), 0) FROM reservation_items WHERE reservation_id = ?");
            $sumStmt->execute([$reservationId]);
            $newResTotal = (float)$sumStmt->fetchColumn();

            $updRes = $pdo->prepare("UPDATE reservations SET total_amount = ?, payment_status = 'Pending (Pay at Counter)' WHERE id = ?");
            $updRes->execute([$newResTotal, $reservationId]);

            // Sync with existing Kitchen Order if one exists
            $ordStmt = $pdo->prepare("SELECT * FROM orders WHERE reservation_id = ? AND status != 'Cancelled' ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $ordStmt->execute([$reservationId]);
            $existingOrder = $ordStmt->fetch();

            $sched = inv_parse_reservation_schedule($res);
            $orderActionMsg = "Scheduled for Kitchen prep at {$sched['prep_formatted']} (15 mins before your {$sched['arrival_formatted']} arrival).";

            if ($existingOrder) {
                if ($existingOrder['status'] === 'Pending') {
                    // Append directly to the Pending kitchen ticket
                    $oiStmt = $pdo->prepare("
                        INSERT INTO order_items (
                            order_id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, special_notes, quantity, unit_price, line_total
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    foreach ($insertedItems as $ins) {
                        $oiStmt->execute([
                            (int)$existingOrder['id'],
                            $ins['product_id'],
                            $ins['product_name'],
                            $ins['size_name'],
                            $ins['sugar_level'],
                            $ins['ice_level'],
                            $ins['addons_json'],
                            'Added by Guest via Digital QR Pass',
                            $ins['quantity'],
                            $ins['unit_price'],
                            $ins['line_total'],
                        ]);
                    }
                    $newOrdSub = (float)$existingOrder['subtotal'] + $addedTotal;
                    $isExempt = (stripos((string)$existingOrder['discount_type'], 'Senior') !== false || stripos((string)$existingOrder['discount_type'], 'PWD') !== false);
                    if ($isExempt) {
                        $vex = $newOrdSub / 1.12;
                        $disc = round($vex * 0.20, 2);
                        $vat = 0.00;
                        $net = round($vex - $disc, 2);
                    } else {
                        $disc = 0.00;
                        $vat = round($newOrdSub - ($newOrdSub / 1.12), 2);
                        $net = round($newOrdSub, 2);
                    }
                    $pdo->prepare("UPDATE orders SET subtotal = ?, discount_amount = ?, vat_amount = ?, net_total = ? WHERE id = ?")
                        ->execute([$newOrdSub, $disc, $vat, $net, (int)$existingOrder['id']]);
                    $orderActionMsg = "Added directly to your active Kitchen Ticket ({$existingOrder['order_number']})!";
                } else {
                    // Initial order is already Brewing/Ready/Completed -> create a follow-up Kitchen ticket for the new items
                    $addOrderNum = 'ORD-ADD-' . strtoupper(substr(uniqid(), -5));
                    $tableLabel = !empty($res['table_code'])
                        ? ('Table #' . $res['table_id'] . ' (' . $res['table_code'] . ')')
                        : ('Table #' . $res['table_id']);
                    $vat = round($addedTotal - ($addedTotal / 1.12), 2);
                    $insOrd = $pdo->prepare("
                        INSERT INTO orders (
                            order_number, reservation_id, order_type, table_number, customer_name,
                            subtotal, discount_type, discount_amount, vat_amount, net_total,
                            payment_method, payment_reference, status
                        ) VALUES (?, ?, 'Scheduled Pre-order', ?, ?, ?, 'None', 0.00, ?, ?, 'Pay at Counter', ?, 'Pending')
                    ");
                    $insOrd->execute([
                        $addOrderNum,
                        $reservationId,
                        $tableLabel,
                        $res['customer_name'],
                        $addedTotal,
                        $vat,
                        $addedTotal,
                        substr($res['reservation_number'] . ' | Guest Add-on Order', 0, 100),
                    ]);
                    $newOrdId = (int)$pdo->lastInsertId();
                    $oiStmt = $pdo->prepare("
                        INSERT INTO order_items (
                            order_id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, special_notes, quantity, unit_price, line_total
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    foreach ($insertedItems as $ins) {
                        $oiStmt->execute([
                            $newOrdId,
                            $ins['product_id'],
                            $ins['product_name'],
                            $ins['size_name'],
                            $ins['sugar_level'],
                            $ins['ice_level'],
                            $ins['addons_json'],
                            'Additional Order from Guest QR Pass',
                            $ins['quantity'],
                            $ins['unit_price'],
                            $ins['line_total'],
                        ]);
                    }
                    $orderActionMsg = "Sent as Additional Kitchen Ticket ({$addOrderNum}) to Barista!";
                }
            } elseif ($sched['is_due_for_prep'] || $res['status'] === 'Checked-In') {
                $pdo->commit();
                $dispatchedOrd = inv_create_order_from_reservation($pdo, $reservationId, 'Guest Added Pre-Order (Within T-15m)');
                jsonResponse([
                    'success'      => true,
                    'message'      => "Items added and immediately dispatched to Kitchen ({$dispatchedOrd['order_number']})!",
                    'total_amount' => $newResTotal,
                    'order'        => $dispatchedOrd,
                ]);
            }

            $pdo->commit();

            jsonResponse([
                'success'      => true,
                'message'      => "Pre-order updated! " . $orderActionMsg,
                'total_amount' => $newResTotal,
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST ACTION 4: Staff / Admin releases an overdue table to Available (No-Show)
    // -------------------------------------------------------------------------
    if ($postAction === 'release_table' || $postAction === 'forfeit_no_show') {
        $token = trim((string)($data['qr_token'] ?? $data['token'] ?? ''));
        $resId = (int)($data['reservation_id'] ?? 0);
        $tableId = (int)($data['table_id'] ?? 0);
        $reason = sanitize($data['reason'] ?? '15-minute seating grace period expired (No-Show). Released by Staff.');

        try {
            $pdo->beginTransaction();

            $resNumber = '';
            $customerName = '';
            $tableCode = '';

            if ($resId > 0) {
                $stmt = $pdo->prepare("SELECT r.*, t.table_code FROM reservations r LEFT JOIN dining_tables t ON r.table_id = t.id WHERE r.id = ? FOR UPDATE");
                $stmt->execute([$resId]);
            } else {
                $stmt = $pdo->prepare("SELECT r.*, t.table_code FROM reservations r LEFT JOIN dining_tables t ON r.table_id = t.id WHERE r.qr_token = ? OR r.reservation_number = ? FOR UPDATE");
                $stmt->execute([$token, $token]);
            }
            $res = $stmt->fetch();
            if ($res) {
                $resId = (int)$res['id'];
                $resNumber = $res['reservation_number'];
                $customerName = $res['customer_name'];
                if (!$tableId && !empty($res['table_id'])) {
                    $tableId = (int)$res['table_id'];
                }
                $tableCode = $res['table_code'] ?: ('Table #' . $tableId);

                $notes = trim(($res['special_notes'] ?? '') . " [Forfeited: {$reason}]");
                $pdo->prepare("UPDATE reservations SET status = 'No-Show', special_notes = ? WHERE id = ?")->execute([$notes, $resId]);
                $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE reservation_id = ? AND status = 'Pending'")->execute([$resId]);
            }

            if ($tableId > 0) {
                $pdo->prepare("UPDATE dining_tables SET status = 'available' WHERE id = ?")->execute([$tableId]);
            }

            $pdo->commit();
            jsonResponse([
                'success' => true,
                'message' => "Table has been successfully released to Available status! Booking {$resNumber} marked as No-Show.",
                'table_id' => $tableId,
                'reservation_id' => $resId,
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    if ($postAction === 'cancel_booking') {
        $token = trim((string)($data['qr_token'] ?? $data['token'] ?? ''));
        $resId = (int)($data['reservation_id'] ?? 0);

        try {
            $pdo->beginTransaction();
            if ($resId > 0) {
                $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? FOR UPDATE");
                $stmt->execute([$resId]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM reservations WHERE qr_token = ? OR reservation_number = ? FOR UPDATE");
                $stmt->execute([$token, $token]);
            }
            $res = $stmt->fetch();
            if (!$res) {
                $pdo->rollBack();
                jsonResponse(['success' => false, 'message' => 'Reservation not found.'], 404);
            }

            $ordStmt = $pdo->prepare("SELECT * FROM orders WHERE reservation_id = ? AND status IN ('Brewing', 'Ready', 'Completed') LIMIT 1");
            $ordStmt->execute([(int)$res['id']]);
            if ($ordStmt->fetch()) {
                $pdo->rollBack();
                jsonResponse(['success' => false, 'message' => 'Cannot cancel reservation because the Barista Kitchen has already started brewing your order.'], 422);
            }

            $pdo->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ?")->execute([(int)$res['id']]);
            $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE reservation_id = ? AND status = 'Pending'")->execute([(int)$res['id']]);
            $pdo->prepare("UPDATE dining_tables SET status = 'available' WHERE id = ?")->execute([(int)$res['table_id']]);

            $pdo->commit();
            jsonResponse(['success' => true, 'message' => 'Reservation has been cancelled and the table is now available.']);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    $tableId = isset($data['table_id']) ? (int)$data['table_id'] : 0;
    $customerName = sanitize($data['customer_name'] ?? '');
    $customerType = sanitize($data['customer_type'] ?? 'Regular');
    $idNumber = sanitize($data['id_number'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $reservationDate = sanitize($data['reservation_date'] ?? date('Y-m-d'));
    $timeSlot = sanitize($data['time_slot'] ?? '09:00 AM - 11:00 AM');
    $arrivalTimeCustom = sanitize($data['arrival_time'] ?? '');
    if ($arrivalTimeCustom !== '') {
        // If customer specified an exact arrival time (e.g. "13:30" or "01:30 PM"), build a 2-hour slot starting at that exact arrival time
        $parsedArr = strtotime($reservationDate . ' ' . $arrivalTimeCustom);
        if ($parsedArr !== false) {
            $timeSlot = date('h:i A', $parsedArr) . ' - ' . date('h:i A', $parsedArr + 7200);
        }
    }
    $guestCount = max(1, (int)($data['guest_count'] ?? 1));
    $specialNotes = sanitize($data['special_notes'] ?? '');
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

    if (!$tableId || empty($customerName) || empty($phone)) {
        jsonResponse(['success' => false, 'message' => 'Please fill in all required fields (Table, Name, Phone).'], 422);
    }

    try {
        $pdo->beginTransaction();

        // Verify table exists & lock row for concurrency protection
        $tblStmt = $pdo->prepare("SELECT * FROM dining_tables WHERE id = ? FOR UPDATE");
        $tblStmt->execute([$tableId]);
        $table = $tblStmt->fetch();

        if (!$table) {
            throw new Exception("Selected table or study pod does not exist.");
        }

        // 1. Base status trapping on dining_tables (status must be available)
        $currentTableStatus = strtolower($table['status']);
        if ($currentTableStatus === 'maintenance') {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => "Table #{$table['id']} ({$table['table_code']} - {$table['name']}) is currently out of service for maintenance. Please choose another table."], 409);
        }
        if ($currentTableStatus === 'occupied') {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => "Table #{$table['id']} ({$table['table_code']} - {$table['name']}) is currently occupied by guests. Please choose an available table."], 409);
        }
        if ($currentTableStatus === 'reserved') {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => "Table #{$table['id']} ({$table['table_code']} - {$table['name']}) is already marked as reserved. Please select another available table."], 409);
        }

        // 2. Validate trapping on tables within selected date and time slot
        $slotCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
            WHERE table_id = ? 
              AND reservation_date = ? 
              AND time_slot = ? 
              AND status IN ('Confirmed', 'Checked-In')");
        $slotCheckStmt->execute([$tableId, $reservationDate, $timeSlot]);
        $conflictCount = (int)$slotCheckStmt->fetchColumn();

        if ($conflictCount > 0) {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => "Table #{$table['id']} ({$table['table_code']}) is already booked for {$reservationDate} during {$timeSlot}. Please choose another available table or time slot."], 409);
        }

        // Generate reservation number & unique QR token (Collision-proof)
        $dateCode = date('Ymd');
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE DATE(created_at) = CURDATE()");
        $countStmt->execute();
        $countToday = ((int)$countStmt->fetchColumn()) + 1;

        do {
            $resNumber = 'RES-' . $dateCode . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);
            $chk = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE reservation_number = ?");
            $chk->execute([$resNumber]);
            $exists = (int)$chk->fetchColumn() > 0;
            if ($exists) {
                $countToday++;
            }
        } while ($exists);

        $randomHash = strtoupper(bin2hex(random_bytes(2)));
        $qrToken = 'RAEDEN-' . $resNumber . '-' . $randomHash;

        // Calculate items total
        $totalAmount = 0.00;
        foreach ($items as $it) {
            $qty = max(1, (int)($it['quantity'] ?? $it['qty'] ?? 1));
            $price = (float)($it['unit_price'] ?? $it['price'] ?? 0.00);
            $totalAmount += ($price * $qty);
        }

        // Determine payment status
        $paymentStatus = $totalAmount > 0 ? 'Pending (Pay at Counter)' : 'Reserved';

        // Insert Reservation
        $insStmt = $pdo->prepare("INSERT INTO reservations (
            reservation_number, qr_token, table_id, customer_name, customer_type, 
            id_number, phone, email, reservation_date, time_slot, guest_count, 
            special_notes, total_amount, payment_status, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");

        $insStmt->execute([
            $resNumber, $qrToken, $tableId, $customerName, $customerType,
            $idNumber, $phone, $email, $reservationDate, $timeSlot, $guestCount,
            $specialNotes, $totalAmount, $paymentStatus
        ]);

        $reservationId = $pdo->lastInsertId();

        // Update table status to reserved
        $updTbl = $pdo->prepare("UPDATE dining_tables SET status = 'reserved' WHERE id = ?");
        $updTbl->execute([$tableId]);

        // Insert Pre-order items
        if (!empty($items)) {
            $itemStmt = $pdo->prepare("INSERT INTO reservation_items (
                reservation_id, product_id, product_name, size_name, sugar_level, ice_level, addons_json, quantity, unit_price, line_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $prodCheckStmt = $pdo->prepare("SELECT id, name, category_id, base_price FROM products WHERE id = ?");
            foreach ($items as $it) {
                $prodId = (int)($it['product_id'] ?? 0);
                $prodDb = null;
                if ($prodId > 0) {
                    $prodCheckStmt->execute([$prodId]);
                    $prodDb = $prodCheckStmt->fetch();
                }

                $prodName = $prodDb ? $prodDb['name'] : sanitize($it['product_name'] ?? $it['name'] ?? 'Custom Item');
                $isPastry = $prodDb ? ((int)$prodDb['category_id'] === 4) : false;

                $sizeName = sanitize($it['size_name'] ?? $it['size'] ?? ($isPastry ? 'Standard Serving' : 'Regular'));
                if (empty($sizeName)) {
                    $sizeName = $isPastry ? 'Standard Serving' : 'Regular';
                }

                $sugarLevel = sanitize($it['sugar_level'] ?? $it['sugar'] ?? ($isPastry ? 'Pastry' : '50% (Half Sweet)'));
                $iceLevel = sanitize($it['ice_level'] ?? $it['ice'] ?? ($isPastry ? 'Warm / Lightly Toasted' : 'Regular Ice'));
                $addonsJson = isset($it['addons']) && is_array($it['addons']) ? json_encode($it['addons']) : (isset($it['addons']) && is_string($it['addons']) ? $it['addons'] : '[]');
                $qty = max(1, (int)($it['quantity'] ?? $it['qty'] ?? 1));
                $unitPrice = max(0.00, (float)($it['unit_price'] ?? $it['price'] ?? ($prodDb ? $prodDb['base_price'] : 0.00)));
                $lineTotal = $unitPrice * $qty;

                $itemStmt->execute([
                    $reservationId, $prodId, $prodName, $sizeName, $sugarLevel, $iceLevel, $addonsJson, $qty, $unitPrice, $lineTotal
                ]);
            }
        }

        $pdo->commit();

        $sched = inv_parse_reservation_schedule([
            'reservation_date' => $reservationDate,
            'time_slot'        => $timeSlot,
        ]);

        // The booking secures the seat and generates the Digital QR Pass with pre-orders.
        // Orders are fetched and confirmed at the POS counter when the guest arrives (just like walk-in ordering).
        jsonResponse([
            'success'             => true,
            'message'             => 'Reservation confirmed! Your seat is secured and your Digital QR Pass is ready. Present your QR Pass at the cashier counter upon arrival to load your order to the POS (items can still be modified at the counter)!',
            'reservation_id'      => (int)$reservationId,
            'reservation_number'  => $resNumber,
            'qr_token'            => $qrToken,
            'customer_name'       => $customerName,
            'table_id'            => $tableId,
            'table_number'        => 'Table #' . $table['id'],
            'table_name'          => 'Table #' . $table['id'] . ' (' . $table['table_code'] . ' - ' . $table['name'] . ')',
            'table_code'          => $table['table_code'],
            'zone'                => $table['zone'],
            'reservation_date'    => $reservationDate,
            'time_slot'           => $timeSlot,
            'arrival_formatted'   => $sched['arrival_formatted'],
            'prep_formatted'      => $sched['prep_formatted'],
            'schedule'            => $sched,
            'total_amount'        => $totalAmount,
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
