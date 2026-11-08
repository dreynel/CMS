<?php
/**
 * Raeden's Resto, Bakery, Cafe - Staff QR Pass Scanner & Seating Controller
 * Native PHP / PDO Backend
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/inventory.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Reservations focus on securing seating; pre-orders are loaded & verified at POS upon arrival
$scheduledSync = ['dispatched' => [], 'upcoming' => []];

/**
 * Helper to enrich a reservation row with schedule, linked order, and normalized aliases
 */
function scanner_enrich_reservation(PDO $pdo, array $res): array {
    $resId = (int)$res['id'];
    $sched = inv_parse_reservation_schedule($res);

    $tableLabel = !empty($res['table_code'])
        ? ('Table #' . $res['table_id'] . ' (' . $res['table_code'] . ' - ' . ($res['table_name'] ?? '') . ')')
        : ('Table #' . ($res['table_id'] ?? '1'));

    $res['reference_code'] = $res['reservation_number'] ?? ('RES-' . $resId);
    $res['customer_phone'] = $res['phone'] ?? '';
    $res['table_number']   = !empty($res['table_code']) ? $res['table_code'] : ('T-' . ($res['table_id'] ?? '1'));
    $res['table_display']  = $tableLabel;
    $res['schedule']       = $sched;

    // Determine if 15-minute grace period has passed without check-in
    $isOverdue = ($res['status'] === 'Confirmed' && $sched['is_today'] && $sched['minutes_until_arrival'] <= -15);
    $res['is_overdue'] = $isOverdue;
    $res['minutes_overdue'] = $isOverdue ? abs($sched['minutes_until_arrival']) : 0;

    // Fetch pre-ordered items
    $itemStmt = $pdo->prepare("
        SELECT ri.*, ri.line_total AS subtotal, p.image_url
        FROM reservation_items ri
        LEFT JOIN products p ON ri.product_id = p.id
        WHERE ri.reservation_id = ?
        ORDER BY ri.id ASC
    ");
    $itemStmt->execute([$resId]);
    $items = $itemStmt->fetchAll();
    foreach ($items as &$it) {
        $it['addons'] = !empty($it['addons_json']) ? json_decode($it['addons_json'], true) : [];
        $it['subtotal'] = (float)($it['line_total'] ?? ((float)$it['unit_price'] * (int)$it['quantity']));
    }
    unset($it);
    $res['items'] = $items;

    // Fetch linked Kitchen Order if already dispatched
    $ordStmt = $pdo->prepare("
        SELECT id, order_number, order_type, status, subtotal, discount_type, discount_amount, vat_amount, net_total, payment_method, payment_reference, created_at
        FROM orders
        WHERE reservation_id = ? AND status != 'Cancelled'
        ORDER BY id DESC
        LIMIT 1
    ");
    $ordStmt->execute([$resId]);
    $res['order'] = $ordStmt->fetch() ?: null;

    return $res;
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    // 1. Lookup single reservation by QR Token, Reservation Number, or Phone
    if ($action === 'lookup') {
        $token = trim((string)($_GET['token'] ?? $_GET['code'] ?? ''));
        if ($token === '') {
            jsonResponse(['success' => false, 'message' => 'Please enter or scan a QR token or reservation code.'], 400);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT r.*, t.name AS table_name, t.zone, t.table_code, t.status AS table_status
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.qr_token = ? OR r.reservation_number = ? OR r.phone = ?
                ORDER BY r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$token, $token, $token]);
            $res = $stmt->fetch();

            if (!$res) {
                jsonResponse(['success' => false, 'message' => 'QR Pass or Booking Reference not found. Please verify the code.'], 404);
            }

            $enriched = scanner_enrich_reservation($pdo, $res);

            jsonResponse([
                'success'     => true,
                'reservation' => $enriched,
                'items'       => $enriched['items'],
                'order'       => $enriched['order'],
                'schedule'    => $enriched['schedule'],
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Staff Concierge Live Dashboard & Reservations Feed
    try {
        $totalToday = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE DATE(reservation_date) = CURDATE() AND status != 'Cancelled'")->fetchColumn();
        $checkedIn  = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE DATE(reservation_date) = CURDATE() AND status = 'Checked-In'")->fetchColumn();
        $pendingArr = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE DATE(reservation_date) = CURDATE() AND status = 'Confirmed'")->fetchColumn();

        $stmt = $pdo->query("
            SELECT r.*, t.name AS table_name, t.zone, t.table_code, t.status AS table_status
            FROM reservations r
            LEFT JOIN dining_tables t ON r.table_id = t.id
            ORDER BY
                CASE WHEN DATE(r.reservation_date) = CURDATE() THEN 0 ELSE 1 END,
                r.created_at DESC
            LIMIT 25
        ");
        $rows = $stmt->fetchAll();

        $reservations = [];
        $urgentAlerts = [];
        $overdueAlerts = [];
        $upcomingArrivals = [];
        $scheduledOrdersCount = 0;

        foreach ($rows as $row) {
            $enriched = scanner_enrich_reservation($pdo, $row);
            if (!empty($enriched['items'])) {
                $scheduledOrdersCount++;
            }
            $sched = $enriched['schedule'];

            // 15-Minute Grace Period Expired Check (Overdue / Unclaimed Seating)
            if ($enriched['status'] === 'Confirmed' && $sched['is_today'] && $sched['minutes_until_arrival'] <= -15) {
                $overdueAlerts[] = $enriched;
            } elseif ($enriched['status'] === 'Confirmed' && $sched['is_today'] && $sched['minutes_until_arrival'] > -15 && $sched['minutes_until_arrival'] <= 15) {
                $upcomingArrivals[] = $enriched;
            }

            if (
                $enriched['status'] === 'Confirmed' &&
                $sched['is_today'] &&
                $sched['minutes_until_arrival'] <= 15 &&
                $sched['minutes_until_arrival'] >= -60
            ) {
                $urgentAlerts[] = $enriched;
            }
            $reservations[] = $enriched;
        }

        $tables = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code")->fetchAll();
        $availCount = 0;
        $occCount = 0;
        $resCount = 0;
        foreach ($tables as $t) {
            $st = strtolower($t['status']);
            if ($st === 'available') $availCount++;
            elseif ($st === 'occupied') $occCount++;
            elseif ($st === 'reserved') $resCount++;
        }

        jsonResponse([
            'success'              => true,
            'kpis'                 => [
                'total_today'      => $totalToday,
                'checked_in'       => $checkedIn,
                'pending_arrival'  => $pendingArr,
                'overdue_count'    => count($overdueAlerts),
                'available_tables' => $availCount,
                'occupied_tables'  => $occCount,
                'reserved_tables'  => $resCount,
                'scheduled_orders' => $scheduledOrdersCount,
            ],
            'overdue_alerts'       => $overdueAlerts,
            'upcoming_arrivals'    => $upcomingArrivals,
            'urgent_15m_alerts'    => $urgentAlerts,
            'scheduled_dispatched' => $scheduledSync['dispatched'],
            'scheduled_upcoming'   => $scheduledSync['upcoming'],
            'reservations'         => $reservations,
            'tables'               => $tables,
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

    $action = $data['action'] ?? $_GET['action'] ?? 'verify';

    // 1. VERIFY QR TOKEN
    if ($action === 'verify') {
        $code = trim((string)($data['code'] ?? $data['token'] ?? ''));

        if ($code === '') {
            jsonResponse(['success' => false, 'message' => 'Please enter or scan a QR token.'], 400);
        }

        try {
            $stmt = $pdo->prepare("
                SELECT r.*, t.name AS table_name, t.zone, t.table_code, t.status AS table_status
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.qr_token = ? OR r.reservation_number = ? OR r.phone = ?
                ORDER BY r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$code, $code, $code]);
            $res = $stmt->fetch();

            if (!$res) {
                jsonResponse(['success' => false, 'message' => 'QR Pass not found. Ensure code is valid.'], 404);
            }

            $enriched = scanner_enrich_reservation($pdo, $res);

            jsonResponse([
                'success'     => true,
                'reservation' => $enriched,
                'items'       => $enriched['items'],
                'order'       => $enriched['order'],
                'schedule'    => $enriched['schedule'],
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. ONE-CLICK CHECK-IN & CONFIRM ONSITE SEATING (Table Occupied)
    if ($action === 'checkin') {
        $reservationId = (int)($data['reservation_id'] ?? 0);

        if (!$reservationId) {
            jsonResponse(['success' => false, 'message' => 'Reservation ID is required.'], 422);
        }

        try {
            $pdo->beginTransaction();

            $resStmt = $pdo->prepare("
                SELECT r.*, t.name AS table_name, t.table_code, t.zone
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.id = ?
                FOR UPDATE
            ");
            $resStmt->execute([$reservationId]);
            $res = $resStmt->fetch();

            if (!$res) {
                throw new Exception("Reservation ticket not found.");
            }

            // Update reservation status to Checked-In
            $updRes = $pdo->prepare("UPDATE reservations SET status = 'Checked-In', checked_in_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updRes->execute([$reservationId]);

            // Update dining table to occupied
            if (!empty($res['table_id'])) {
                $updTbl = $pdo->prepare("UPDATE dining_tables SET status = 'occupied' WHERE id = ?");
                $updTbl->execute([(int)$res['table_id']]);
            }

            $pdo->commit();

            $tableDisp = !empty($res['table_code'])
                ? "{$res['table_code']} ({$res['table_name']})"
                : ($res['table_name'] ?: 'Reserved Table');

            jsonResponse([
                'success'       => true,
                'message'       => "Guest {$res['customer_name']} verified onsite! Seated at {$tableDisp} ({$res['zone']}). Table status updated to Occupied.",
                'table_code'    => $res['table_code'],
                'table_name'    => $res['table_name'],
                'zone'          => $res['zone'],
                'table_display' => $tableDisp,
                'customer_name' => $res['customer_name'],
                'instructions'  => "Guest is onsite. Direct guest to their assigned table and advise them to present their QR Pass at the Cashier POS to load, customize, and pay for their order.",
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. RELEASE TABLE TO AVAILABLE & FORFEIT NO-SHOW (15-Minute Grace Period Expired)
    if ($action === 'release_table' || $action === 'forfeit_no_show') {
        $reservationId = (int)($data['reservation_id'] ?? 0);
        $tableId       = (int)($data['table_id'] ?? 0);
        $reason        = sanitize($data['reason'] ?? '15-minute seating grace period expired without customer arrival. Released by Staff for walk-in guests.');

        try {
            $pdo->beginTransaction();

            $resNumber = '';
            $customerName = '';
            $tableCode = '';

            if ($reservationId > 0) {
                $resStmt = $pdo->prepare("SELECT r.*, t.table_code, t.name as table_name FROM reservations r LEFT JOIN dining_tables t ON r.table_id = t.id WHERE r.id = ? FOR UPDATE");
                $resStmt->execute([$reservationId]);
                $res = $resStmt->fetch();
                if ($res) {
                    $resNumber = $res['reservation_number'];
                    $customerName = $res['customer_name'];
                    if (!$tableId && !empty($res['table_id'])) {
                        $tableId = (int)$res['table_id'];
                    }
                    $tableCode = $res['table_code'] ?: ('Table #' . $tableId);

                    // Update reservation to No-Show
                    $notes = trim(($res['special_notes'] ?? '') . " [Forfeited by Staff: {$reason}]");
                    $updRes = $pdo->prepare("UPDATE reservations SET status = 'No-Show', special_notes = ? WHERE id = ?");
                    $updRes->execute([$notes, $reservationId]);

                    // Cancel any un-brewed pending order
                    $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE reservation_id = ? AND status = 'Pending'")->execute([$reservationId]);
                }
            }

            if ($tableId > 0) {
                $pdo->prepare("UPDATE dining_tables SET status = 'available' WHERE id = ?")->execute([$tableId]);
                if (empty($tableCode)) {
                    $tbl = $pdo->query("SELECT table_code FROM dining_tables WHERE id = {$tableId}")->fetch();
                    $tableCode = $tbl['table_code'] ?? ('Table #' . $tableId);
                }
            }

            $pdo->commit();

            jsonResponse([
                'success'        => true,
                'message'        => "Table {$tableCode} has been successfully released to Available status! Booking {$resNumber} for {$customerName} marked as No-Show.",
                'reservation_id' => $reservationId,
                'table_id'       => $tableId,
                'table_code'     => $tableCode,
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 4. MANUAL / EARLY DISPATCH OF SCHEDULED PRE-ORDER TO KITCHEN KDS (Fallback)
    if ($action === 'dispatch_order') {
        $reservationId = (int)($data['reservation_id'] ?? 0);
        if (!$reservationId) {
            jsonResponse(['success' => false, 'message' => 'Reservation ID is required.'], 422);
        }

        try {
            $order = inv_create_order_from_reservation($pdo, $reservationId, 'Staff Early Prep Dispatch');
            if (!$order) {
                jsonResponse(['success' => false, 'message' => 'This reservation does not have any pre-ordered items to dispatch.'], 422);
            }

            $msg = !empty($order['already_exists'])
                ? "Order {$order['order_number']} is already in the Barista Kitchen queue ({$order['status']})."
                : "Scheduled Pre-Order {$order['order_number']} dispatched to Barista Kitchen KDS!";

            jsonResponse([
                'success' => true,
                'message' => $msg,
                'order'   => $order,
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 5. COMPLETE VISIT & RELEASE TABLE ("Check-Out Guest & Free Table")
    if ($action === 'checkout') {
        $reservationId = (int)($data['reservation_id'] ?? 0);
        $tableId       = (int)($data['table_id'] ?? 0);

        try {
            $pdo->beginTransaction();

            if ($reservationId > 0) {
                $resStmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? FOR UPDATE");
                $resStmt->execute([$reservationId]);
                $res = $resStmt->fetch();
                if ($res) {
                    $pdo->prepare("UPDATE reservations SET status = 'Completed' WHERE id = ?")->execute([$reservationId]);
                    $tableId = (int)$res['table_id'];
                }
            }

            if ($tableId > 0) {
                $pdo->prepare("UPDATE dining_tables SET status = 'available' WHERE id = ?")->execute([$tableId]);
            }

            $pdo->commit();
            jsonResponse([
                'success' => true,
                'message' => 'Guest visit completed and dining table released to Available!',
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
