<?php
/**
 * ISAT U Coffee Shop - Manager Admin API
 * Native PHP / PDO Backend
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// GET: Summary Statistics and Analytics
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    // Fetch Inventory Movement Logs
    if ($action === 'get_inventory_logs') {
        try {
            $stmt = $pdo->query("SELECT l.*, p.name as product_name, p.product_code, p.unit, c.name as category_name 
                FROM inventory_logs l 
                JOIN products p ON l.product_id = p.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY l.created_at DESC 
                LIMIT 100");
            $logs = $stmt->fetchAll();
            jsonResponse(['success' => true, 'logs' => $logs]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Fetch Seating Zones & Table Counts
    if ($action === 'get_zones') {
        try {
            $stmt = $pdo->query("SELECT z.*, 
                COUNT(t.id) as table_count,
                COALESCE(SUM(t.capacity), 0) as total_capacity,
                SUM(CASE WHEN t.status = 'available' THEN 1 ELSE 0 END) as available_tables
                FROM zones z 
                LEFT JOIN dining_tables t ON (t.zone_id = z.id OR t.zone = z.name)
                GROUP BY z.id 
                ORDER BY z.id ASC");
            $zones = $stmt->fetchAll();
            jsonResponse(['success' => true, 'zones' => $zones]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    try {
        // Today's Sales KPIs
        $revStmt = $pdo->query("SELECT COALESCE(SUM(net_total), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'Cancelled'");
        $todayRevenue = (float)$revStmt->fetchColumn();

        $ordCountStmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $todayOrdersCount = (int)$ordCountStmt->fetchColumn();

        $resCountStmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status IN ('Confirmed', 'Checked-In')");
        $activeReservationsCount = (int)$resCountStmt->fetchColumn();

        $lowStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_alert");
        $lowStockCount = (int)$lowStockStmt->fetchColumn();

        // Best sellers
        $topStmt = $pdo->query("SELECT product_name, SUM(quantity) as total_qty, SUM(line_total) as total_sales 
            FROM order_items 
            GROUP BY product_name 
            ORDER BY total_qty DESC 
            LIMIT 5");
        $topProducts = $topStmt->fetchAll();

        // Recent 10 Orders
        $ordStmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
        $recentOrders = $ordStmt->fetchAll();

        jsonResponse([
            'success' => true,
            'stats' => [
                'today_revenue' => $todayRevenue,
                'today_orders' => $todayOrdersCount,
                'active_reservations' => $activeReservationsCount,
                'low_stock_count' => $lowStockCount,
            ],
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// POST: Actions
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        $data = $_POST;
    }

    $action = $data['action'] ?? $_GET['action'] ?? '';

    // 1. UPDATE PRODUCT INVENTORY STOCK & LOG MOVEMENT
    if ($action === 'update_stock' || $action === 'log_restock' || $action === 'log_waste') {
        $productId = (int)($data['product_id'] ?? 0);
        $delta = (int)($data['delta'] ?? 0);
        $newStock = isset($data['new_stock']) ? (int)$data['new_stock'] : null;
        $changeType = sanitize($data['change_type'] ?? ($action === 'log_restock' ? 'Restock' : ($action === 'log_waste' ? 'Waste / Loss' : 'Manual Count')));
        $note = sanitize($data['note'] ?? ($action === 'log_restock' ? 'Supplier shipment received' : ($action === 'log_waste' ? 'Damaged / expired batch write-off' : 'Direct inventory adjustment')));
        $staffName = $_SESSION['user']['name'] ?? 'Admin Supervisor';

        if (!$productId) {
            jsonResponse(['success' => false, 'message' => 'Product ID is required.'], 422);
        }

        try {
            $prevStmt = $pdo->prepare("SELECT stock_quantity, name FROM products WHERE id = ?");
            $prevStmt->execute([$productId]);
            $prod = $prevStmt->fetch();
            if (!$prod) {
                jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
            }
            $prevStock = (int)$prod['stock_quantity'];

            if ($action === 'log_restock') {
                $qty = max(1, (int)($data['quantity'] ?? 1));
                $finalStock = $prevStock + $qty;
                $changeQty = $qty;
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $stmt->execute([$finalStock, $productId]);
            } elseif ($action === 'log_waste') {
                $qty = max(1, (int)($data['quantity'] ?? 1));
                $finalStock = max(0, $prevStock - $qty);
                $changeQty = -($prevStock - $finalStock);
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $stmt->execute([$finalStock, $productId]);
            } elseif ($newStock !== null) {
                $finalStock = max(0, $newStock);
                $changeQty = $finalStock - $prevStock;
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $stmt->execute([$finalStock, $productId]);
            } else {
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity + ?) WHERE id = ?");
                $stmt->execute([$delta, $productId]);
                
                $fetchStmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                $fetchStmt->execute([$productId]);
                $finalStock = (int)$fetchStmt->fetchColumn();
                $changeQty = $finalStock - $prevStock;
            }

            // Record into inventory_logs
            try {
                $logStmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, change_type, quantity_change, previous_stock, new_stock, reference_note, performed_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $logStmt->execute([$productId, $changeType, $changeQty, $prevStock, $finalStock, $note, $staffName]);
            } catch (Exception $e) {
                // Ignore if log table not created yet
            }

            jsonResponse([
                'success' => true,
                'message' => "Stock for '{$prod['name']}' updated to {$finalStock} ($changeType: " . ($changeQty >= 0 ? "+$changeQty" : "$changeQty") . ").",
                'product_id' => $productId,
                'previous_stock' => $prevStock,
                'new_stock' => $finalStock,
                'change_type' => $changeType
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. UPDATE RESERVATION STATUS
    if ($action === 'update_res_status') {
        $reservationId = (int)($data['reservation_id'] ?? 0);
        $status = sanitize($data['status'] ?? '');

        $allowed = ['Confirmed', 'Checked-In', 'Completed', 'Cancelled'];
        if (!$reservationId || !in_array($status, $allowed)) {
            jsonResponse(['success' => false, 'message' => 'Invalid reservation ID or status.'], 422);
        }

        try {
            $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $reservationId]);

            // If checked in or completed, adjust table
            $resStmt = $pdo->prepare("SELECT table_id FROM reservations WHERE id = ?");
            $resStmt->execute([$reservationId]);
            $tableId = $resStmt->fetchColumn();

            if ($tableId) {
                if ($status === 'Checked-In') {
                    $pdo->prepare("UPDATE dining_tables SET status = 'occupied' WHERE id = ?")->execute([$tableId]);
                } elseif (in_array($status, ['Completed', 'Cancelled'])) {
                    $pdo->prepare("UPDATE dining_tables SET status = 'available' WHERE id = ?")->execute([$tableId]);
                }
            }

            jsonResponse(['success' => true, 'message' => "Reservation marked as $status."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. CREATE / ADD NEW USER ACCOUNT
    if ($action === 'create_user') {
        $name = sanitize($data['name'] ?? '');
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $data['password'] ?? 'password123';
        $role = sanitize($data['role'] ?? 'customer');
        $affiliation = sanitize($data['affiliation'] ?? 'regular');
        $idNumber = sanitize($data['id_number'] ?? '');
        $phone = sanitize($data['phone'] ?? '');

        $allowedRoles = ['admin', 'cashier', 'barista', 'customer'];
        $allowedAffil = ['regular', 'pwd_senior', 'senior_pwd', 'student', 'vip', 'visitor', 'faculty'];

        if (!$name || !$email) {
            jsonResponse(['success' => false, 'message' => 'Full name and valid email are required.'], 422);
        }
        if (!in_array($role, $allowedRoles)) {
            $role = 'customer';
        }
        if (!in_array($affiliation, $allowedAffil)) {
            $affiliation = 'regular';
        }

        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Email address is already registered.'], 409);
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, affiliation, id_number, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role, $affiliation, $idNumber, $phone]);

            $newId = (int)$pdo->lastInsertId();
            jsonResponse([
                'success' => true,
                'message' => "User account for '$name' ($role) created successfully.",
                'user_id' => $newId
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 4. UPDATE USER ROLE & AFFILIATION
    if ($action === 'update_user_role') {
        $userId = (int)($data['user_id'] ?? 0);
        $role = sanitize($data['role'] ?? '');
        $affiliation = sanitize($data['affiliation'] ?? '');

        $allowedRoles = ['admin', 'cashier', 'barista', 'customer'];
        if (!$userId || !in_array($role, $allowedRoles)) {
            jsonResponse(['success' => false, 'message' => 'Invalid user ID or role specified.'], 422);
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? " . ($affiliation ? ", affiliation = ?" : "") . " WHERE id = ?");
            if ($affiliation) {
                $stmt->execute([$role, $affiliation, $userId]);
            } else {
                $stmt->execute([$role, $userId]);
            }

            jsonResponse(['success' => true, 'message' => "User role updated to " . ucfirst($role) . "."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 5. UPDATE DINING TABLE STATUS
    if ($action === 'update_table_status') {
        $tableId = (int)($data['table_id'] ?? 0);
        $status = strtolower(sanitize($data['status'] ?? ''));

        $allowedStatus = ['available', 'reserved', 'occupied', 'maintenance'];
        if (!$tableId || !in_array($status, $allowedStatus)) {
            jsonResponse(['success' => false, 'message' => 'Invalid table ID or status.'], 422);
        }

        try {
            $stmt = $pdo->prepare("UPDATE dining_tables SET status = ? WHERE id = ?");
            $stmt->execute([$status, $tableId]);

            jsonResponse(['success' => true, 'message' => "Table status updated to " . ucfirst($status) . "."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 7. CREATE NEW SEATING ZONE
    if ($action === 'create_zone') {
        $name = sanitize($data['name'] ?? '');
        $icon = sanitize($data['icon'] ?? 'bi-grid');
        $description = sanitize($data['description'] ?? '');
        $badgeColor = sanitize($data['badge_color'] ?? 'primary');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if (empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Zone name is required.'], 422);
        }

        try {
            $check = $pdo->prepare("SELECT id FROM zones WHERE name = ? OR slug = ?");
            $check->execute([$name, $slug]);
            if ($check->fetch()) {
                jsonResponse(['success' => false, 'message' => "A zone named '$name' already exists."], 409);
            }

            $stmt = $pdo->prepare("INSERT INTO zones (name, slug, icon, description, badge_color, is_active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$name, $slug, $icon, $description, $badgeColor]);

            jsonResponse(['success' => true, 'message' => "Seating Zone '$name' created successfully.", 'zone_id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 8. UPDATE SEATING ZONE
    if ($action === 'update_zone') {
        $id = (int)($data['id'] ?? 0);
        $name = sanitize($data['name'] ?? '');
        $icon = sanitize($data['icon'] ?? 'bi-grid');
        $description = sanitize($data['description'] ?? '');
        $badgeColor = sanitize($data['badge_color'] ?? 'primary');
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if (!$id || empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Zone ID and name are required.'], 422);
        }

        try {
            $oldStmt = $pdo->prepare("SELECT name FROM zones WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldName = $oldStmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE zones SET name = ?, icon = ?, description = ?, badge_color = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $description, $badgeColor, $isActive, $id]);

            if ($oldName && $oldName !== $name) {
                $updTbl = $pdo->prepare("UPDATE dining_tables SET zone = ? WHERE zone = ? OR zone_id = ?");
                $updTbl->execute([$name, $oldName, $id]);
            }

            jsonResponse(['success' => true, 'message' => "Seating Zone '$name' updated successfully."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 9. DELETE SEATING ZONE
    if ($action === 'delete_zone') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Zone ID is required.'], 422);
        }

        try {
            $tblCheck = $pdo->prepare("SELECT COUNT(*) FROM dining_tables WHERE zone_id = ? OR zone = (SELECT name FROM zones WHERE id = ?)");
            $tblCheck->execute([$id, $id]);
            $count = (int)$tblCheck->fetchColumn();

            if ($count > 0) {
                jsonResponse(['success' => false, 'message' => "Cannot delete zone: $count dining table(s) are assigned to this zone. Please reassign or delete those tables first."], 409);
            }

            $stmt = $pdo->prepare("DELETE FROM zones WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(['success' => true, 'message' => "Zone deleted successfully."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 10. CREATE NEW DINING TABLE
    if ($action === 'create_table') {
        $tableCode = strtoupper(sanitize($data['table_code'] ?? ''));
        $name = sanitize($data['name'] ?? '');
        $zone = sanitize($data['zone'] ?? 'Main Hall');
        $capacity = max(1, (int)($data['capacity'] ?? 2));
        $hasPower = !empty($data['has_power_outlet']) ? 1 : 0;
        $status = strtolower(sanitize($data['status'] ?? 'available'));
        $description = sanitize($data['description'] ?? '');

        if (empty($tableCode) || empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Table Code and Name are required.'], 422);
        }

        try {
            $chk = $pdo->prepare("SELECT id FROM dining_tables WHERE table_code = ?");
            $chk->execute([$tableCode]);
            if ($chk->fetch()) {
                jsonResponse(['success' => false, 'message' => "Table code '$tableCode' already exists."], 409);
            }

            $zStmt = $pdo->prepare("SELECT id FROM zones WHERE name = ? LIMIT 1");
            $zStmt->execute([$zone]);
            $zoneId = $zStmt->fetchColumn() ?: null;

            $stmt = $pdo->prepare("INSERT INTO dining_tables (table_code, name, zone, zone_id, capacity, has_power_outlet, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tableCode, $name, $zone, $zoneId, $capacity, $hasPower, $status, $description]);

            $newId = $pdo->lastInsertId();
            jsonResponse(['success' => true, 'message' => "Table #$newId ($tableCode - $name) added successfully.", 'table_id' => $newId]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 11. UPDATE DINING TABLE
    if ($action === 'update_table') {
        $id = (int)($data['id'] ?? 0);
        $tableCode = strtoupper(sanitize($data['table_code'] ?? ''));
        $name = sanitize($data['name'] ?? '');
        $zone = sanitize($data['zone'] ?? 'Main Hall');
        $capacity = max(1, (int)($data['capacity'] ?? 2));
        $hasPower = !empty($data['has_power_outlet']) ? 1 : 0;
        $status = strtolower(sanitize($data['status'] ?? 'available'));
        $description = sanitize($data['description'] ?? '');

        if (!$id || empty($tableCode) || empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Table ID, Code, and Name are required.'], 422);
        }

        try {
            $zStmt = $pdo->prepare("SELECT id FROM zones WHERE name = ? LIMIT 1");
            $zStmt->execute([$zone]);
            $zoneId = $zStmt->fetchColumn() ?: null;

            $stmt = $pdo->prepare("UPDATE dining_tables SET table_code = ?, name = ?, zone = ?, zone_id = ?, capacity = ?, has_power_outlet = ?, status = ?, description = ? WHERE id = ?");
            $stmt->execute([$tableCode, $name, $zone, $zoneId, $capacity, $hasPower, $status, $description, $id]);

            jsonResponse(['success' => true, 'message' => "Table #$id ($tableCode - $name) updated successfully."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 12. DELETE DINING TABLE
    if ($action === 'delete_table') {
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Table ID is required.'], 422);
        }

        try {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE table_id = ? AND status IN ('Confirmed', 'Checked-In')");
            $chk->execute([$id]);
            if ((int)$chk->fetchColumn() > 0) {
                jsonResponse(['success' => false, 'message' => 'Cannot delete this table because it has active reservations.'], 409);
            }

            $stmt = $pdo->prepare("DELETE FROM dining_tables WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(['success' => true, 'message' => "Table #$id deleted successfully."]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);

