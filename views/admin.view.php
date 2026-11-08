<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Administrator Operations Dashboard View (Sidebar Layout)
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

require_once __DIR__ . '/../includes/inventory.php';
inv_ensure_schema($pdo);

// 1. Today's Sales KPIs
$revStmt = $pdo->query("SELECT COALESCE(SUM(net_total), 0) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'Cancelled'");
$todayRevenue = (float)$revStmt->fetchColumn();

$ordCountStmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$todayOrdersCount = (int)$ordCountStmt->fetchColumn();

$resCountStmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status IN ('Confirmed', 'Checked-In')");
$activeReservationsCount = (int)$resCountStmt->fetchColumn();

$lowStockStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock_alert");
$lowStockCount = (int)$lowStockStmt->fetchColumn();

// 2. User Accounts Management (Cashiers, Baristas, Customers, Admins)
$usersStmt = $pdo->query("SELECT * FROM users ORDER BY FIELD(role, 'admin', 'cashier', 'barista', 'customer'), name ASC");
$users = $usersStmt->fetchAll();

$adminCount = 0; $cashierCount = 0; $baristaCount = 0; $customerCount = 0;
foreach ($users as $u) {
    if ($u['role'] === 'admin') $adminCount++;
    elseif ($u['role'] === 'cashier') $cashierCount++;
    elseif ($u['role'] === 'barista') $baristaCount++;
    elseif ($u['role'] === 'customer') $customerCount++;
}

// 3. Products for inventory adjustment & tracking valuation
$prodStmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.stock_quantity ASC, p.name ASC");
$products = $prodStmt->fetchAll();

$outOfStockCount = 0;
$totalInventoryCost = 0.00;
$totalInventoryRetail = 0.00;
foreach ($products as $p) {
    if ($p['stock_quantity'] <= 0) $outOfStockCount++;
    $totalInventoryCost += ($p['stock_quantity'] * (float)($p['cost_price'] ?? 0));
    $totalInventoryRetail += ($p['stock_quantity'] * (float)($p['base_price'] ?? $p['price'] ?? 0));
}

// 4. Seating Zones (Admin Manageable)
$zoneStmt = $pdo->query("SELECT z.*, 
    COUNT(t.id) as table_count, 
    COALESCE(SUM(t.capacity), 0) as total_capacity,
    SUM(CASE WHEN t.status = 'available' THEN 1 ELSE 0 END) as available_tables
    FROM zones z 
    LEFT JOIN dining_tables t ON (t.zone_id = z.id OR t.zone = z.name) 
    GROUP BY z.id 
    ORDER BY z.id ASC");
$zones = $zoneStmt->fetchAll();

// 5. Dining Tables & Study Pods
$tableStmt = $pdo->query("SELECT t.*, z.badge_color, z.icon as zone_icon 
    FROM dining_tables t 
    LEFT JOIN zones z ON (t.zone_id = z.id OR t.zone = z.name) 
    ORDER BY t.zone, t.table_code");
$diningTables = $tableStmt->fetchAll();

// 6. Inventory Tracking Movement Logs
$invLogsStmt = $pdo->query("SELECT l.*, p.name as product_name, p.product_code, p.unit, c.name as category_name 
    FROM inventory_logs l 
    JOIN products p ON l.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY l.created_at DESC 
    LIMIT 35");
$invLogs = $invLogsStmt->fetchAll();

// 7. All Reservations for master manager
$resStmt = $pdo->query("SELECT r.*, t.name as table_name, t.table_code, t.zone 
    FROM reservations r 
    LEFT JOIN dining_tables t ON r.table_id = t.id 
    ORDER BY r.created_at DESC 
    LIMIT 30");
$reservations = $resStmt->fetchAll();

// 8. Recent Orders
$orderStmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 15");
$recentOrders = $orderStmt->fetchAll();
?>


<div class="dashboard-layout">
    
    <!-- Left Admin Navigation Sidebar -->
    <aside class="dashboard-sidebar p-3 shadow-xs">
        <div>
            <!-- Sidebar Header / Brand Indicator -->
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow-xs border border-secondary-subtle" style="width: 32px; height: 32px; object-fit: cover;">
                    <span class="badge bg-navy text-warning fw-bold px-2.5 py-1 rounded-pill shadow-xs">
                        <i class="bi bi-shield-lock-fill me-1"></i> Management Console
                    </span>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 0.65rem;">Active</span>
            </div>

            <?php
            $adminUser = $_SESSION['user'] ?? ['name' => 'Admin Supervisor', 'role' => 'admin'];
            $adminInitials = strtoupper(substr($adminUser['name'], 0, 2));
            ?>
            <!-- Compact Profile Strip -->
            <div class="sidebar-profile-card mb-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-navy text-warning fw-black d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px; font-size: 0.88rem;">
                        <?= htmlspecialchars($adminInitials) ?>
                    </div>
                    <div class="text-truncate">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem; line-height: 1.2;"><?= htmlspecialchars($adminUser['name']) ?></div>
                        <small class="text-muted" style="font-size: 0.72rem;">Raeden's Management • Admin</small>
                    </div>
                </div>
            </div>

            <!-- Core Management Navigation Links -->
            <div class="sidebar-section-title">Operations & Modules</div>
            <nav class="nav flex-column gap-1 mb-3" id="adminSidebarNav" role="tablist">
                <a class="sidebar-nav-item active" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" role="tab">
                    <i class="bi bi-speedometer2 text-primary"></i>
                    <span>Executive Overview</span>
                </a>
                <a class="sidebar-nav-item" id="tab-users-btn" data-bs-toggle="pill" data-bs-target="#tab-users" role="tab">
                    <i class="bi bi-people-fill text-warning"></i>
                    <span>Staff & Accounts</span>
                    <span class="badge bg-navy text-warning rounded-pill ms-auto"><?= count($users) ?></span>
                </a>
                <a class="sidebar-nav-item" id="tab-inventory-btn" data-bs-toggle="pill" data-bs-target="#tab-inventory" role="tab">
                    <i class="bi bi-boxes text-info"></i>
                    <span>Inventory & Tracking</span>
                    <?php if ($lowStockCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $lowStockCount ?></span>
                    <?php endif; ?>
                </a>
                <a class="sidebar-nav-item" id="tab-reservations-btn" data-bs-toggle="pill" data-bs-target="#tab-reservations" role="tab">
                    <i class="bi bi-calendar2-range text-success"></i>
                    <span>Master Reservations</span>
                    <span class="badge bg-primary rounded-pill ms-auto"><?= $activeReservationsCount ?></span>
                </a>
                <a class="sidebar-nav-item" id="tab-tables-btn" data-bs-toggle="pill" data-bs-target="#tab-tables" role="tab">
                    <i class="bi bi-grid-3x3-gap-fill text-warning"></i>
                    <span>Zones & Tables</span>
                    <span class="badge bg-navy text-warning rounded-pill ms-auto"><?= count($zones) ?></span>
                </a>
                <a class="sidebar-nav-item" id="tab-reports-btn" data-bs-toggle="pill" data-bs-target="#tab-reports" role="tab">
                    <i class="bi bi-graph-up-arrow text-primary"></i>
                    <span>Sales Reports & Audit</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="pt-3 border-top mt-auto">
            <div class="d-flex justify-content-between align-items-center text-muted px-1" style="font-size: 0.72rem;">
                <span>Raeden’s Bakery &amp; Café</span>
                <span class="text-success"><i class="bi bi-shield-check"></i> System Active</span>
            </div>
        </div>
    </aside>

    <!-- Right Dashboard Main Content Area -->
    <main class="dashboard-main p-3 p-lg-4">
        
        <!-- Top Workspace Action Header Bar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
            <div>
                <h2 class="fw-black text-navy mb-0 tracking-tight fs-3">Executive Operations Workspace</h2>
                <p class="text-muted small mb-0">Raeden’s Bakery and Café • POS with Online Reservation and QR Code</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus-fill me-1 text-warning"></i> + New User
                </button>
                <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs text-dark" data-bs-toggle="modal" data-bs-target="#addTableModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> + New Table
                </button>
                <a href="pos" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-bold">
                    <i class="bi bi-calculator me-1"></i> Open POS
                </a>
            </div>
        </div>

        <!-- Tab Content Panes -->
        <div class="tab-content" id="adminTabsContent">
            
            <!-- ========================================== -->
            <!-- TAB 1: EXECUTIVE OVERVIEW & KPIS -->
            <!-- ========================================== -->
            <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                
                <!-- 4 Modern Metric KPI Cards -->
                <div class="row g-3 mb-4">
                    
                    <!-- KPI 1: Today's Revenue -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-warning">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Today's Gross Sales</span>
                                    <h3 class="fw-black text-navy mb-1 mt-1">₱<?= number_format($todayRevenue, 2) ?></h3>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                        <i class="bi bi-graph-up me-1"></i> Live POS & Pre-orders
                                    </span>
                                </div>
                                <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 58px; height: 58px;">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Completed Orders -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-primary">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Transactions Processed</span>
                                    <h3 class="fw-black text-navy mb-1 mt-1"><?= $todayOrdersCount ?></h3>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                        Counter & Study Pods
                                    </span>
                                </div>
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 58px; height: 58px;">
                                    <i class="bi bi-receipt-cutoff"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: Active Bookings -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-info">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Active Reservations</span>
                                    <h3 class="fw-black text-navy mb-1 mt-1"><?= $activeReservationsCount ?></h3>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle small">
                                        Confirmed / Seated
                                    </span>
                                </div>
                                <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 58px; height: 58px;">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: Low Stock Alert -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 <?= $lowStockCount > 0 ? 'border-danger' : 'border-success' ?>">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Low Stock Alert</span>
                                    <h3 class="fw-black <?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?> mb-1 mt-1"><?= $lowStockCount ?></h3>
                                    <span class="badge <?= $lowStockCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> small">
                                        <?= $lowStockCount > 0 ? 'Replenishment Needed' : 'Inventory Healthy' ?>
                                    </span>
                                </div>
                                <div class="rounded-circle <?= $lowStockCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 58px; height: 58px;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Analytics Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold text-navy mb-1">
                                        <i class="bi bi-graph-up-arrow text-warning me-2"></i>Weekly Revenue Analytics
                                    </h5>
                                    <p class="text-muted small mb-0">Daily gross coffee and pastry sales trajectory</p>
                                </div>
                                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">7-Day Trajectory</span>
                            </div>
                            <div class="card-body p-4">
                                <canvas id="revenueTrendChart" style="max-height: 280px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                            <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                                <h5 class="fw-bold text-navy mb-1">
                                    <i class="bi bi-pie-chart-fill text-warning me-2"></i>Sales by Category
                                </h5>
                                <p class="text-muted small mb-0">Product popularity distribution</p>
                            </div>
                            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                                <canvas id="categoryChart" style="max-height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Feed -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-clock-history text-warning me-2"></i>Live POS Transaction Stream
                            </h5>
                            <p class="text-muted small mb-0">Real-time orders processed by Cashiers & Online Reservations</p>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">Latest 15 Orders</span>
                    </div>
                    <div class="card-body p-4 overflow-auto">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer / Table</th>
                                        <th>Order Type</th>
                                        <th>Subtotal</th>
                                        <th>Discount</th>
                                        <th>12% VAT</th>
                                        <th>Net Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentOrders)): ?>
                                        <tr><td colspan="10" class="text-center py-4 text-muted">No orders recorded yet today.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($recentOrders as $ro): 
                                            $statusClass = $ro['status'] === 'Completed' ? 'bg-success' : ($ro['status'] === 'Processing' ? 'bg-primary' : 'bg-warning text-dark');
                                        ?>
                                            <tr>
                                                <td class="fw-mono fw-bold text-navy small"><?= htmlspecialchars($ro['order_number']) ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($ro['customer_name'] ?? 'Walk-in Guest') ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($ro['table_assigned'] ?? 'Takeout Counter') ?></small>
                                                </td>
                                                <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($ro['order_type']) ?></span></td>
                                                <td>₱<?= number_format($ro['subtotal'], 2) ?></td>
                                                <td class="text-danger"><?= $ro['discount_amount'] > 0 ? '-₱' . number_format($ro['discount_amount'], 2) : '—' ?></td>
                                                <td class="text-muted small">₱<?= number_format($ro['vat_amount'], 2) ?></td>
                                                <td class="fw-black text-navy">₱<?= number_format($ro['net_total'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-credit-card me-1 text-primary"></i><?= htmlspecialchars($ro['payment_method']) ?>
                                                    </span>
                                                </td>
                                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($ro['status']) ?></span></td>
                                                <td class="text-muted small"><?= date('h:i A', strtotime($ro['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 2: STAFF & USER ACCOUNTS MANAGEMENT -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-users" role="tabpanel">
                
                <!-- User Role Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 flex-row border-start border-4 border-dark">
                            <div class="rounded-circle bg-dark bg-opacity-10 text-dark p-3 fs-4"><i class="bi bi-shield-shaded"></i></div>
                            <div>
                                <div class="text-muted small fw-bold">Administrators</div>
                                <h4 class="fw-black text-navy mb-0"><?= $adminCount ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 flex-row border-start border-4 border-warning">
                            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 fs-4"><i class="bi bi-calculator-fill"></i></div>
                            <div>
                                <div class="text-muted small fw-bold">Cashiers</div>
                                <h4 class="fw-black text-navy mb-0"><?= $cashierCount ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 flex-row border-start border-4 border-danger">
                            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 fs-4"><i class="bi bi-cup-hot-fill"></i></div>
                            <div>
                                <div class="text-muted small fw-bold">Baristas</div>
                                <h4 class="fw-black text-navy mb-0"><?= $baristaCount ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex align-items-center gap-3 flex-row border-start border-4 border-primary">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4"><i class="bi bi-person-fill"></i></div>
                            <div>
                                <div class="text-muted small fw-bold">Registered Customers</div>
                                <h4 class="fw-black text-navy mb-0"><?= $customerCount ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Accounts Directory Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-person-badge-fill text-warning me-2"></i>Multi-User Accounts & Role Permissions
                            </h5>
                            <p class="text-muted small mb-0">Admin assigns roles for Cashiers, Baristas, and Customer accounts.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-person-plus-fill me-1"></i> Add New User / Staff
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-4 overflow-auto">
                        <!-- Role Filter Tabs -->
                        <div class="d-flex gap-2 mb-3 overflow-auto flex-nowrap pb-1">
                            <button type="button" class="btn btn-sm btn-navy rounded-pill px-3 user-filter-btn active" onclick="filterUserTable('all', this)">All (<?= count($users) ?>)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 user-filter-btn" onclick="filterUserTable('cashier', this)">Cashiers (<?= $cashierCount ?>)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 user-filter-btn" onclick="filterUserTable('barista', this)">Baristas (<?= $baristaCount ?>)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 user-filter-btn" onclick="filterUserTable('customer', this)">Customers (<?= $customerCount ?>)</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 user-filter-btn" onclick="filterUserTable('admin', this)">Admins (<?= $adminCount ?>)</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="usersTable">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th>Staff / User</th>
                                        <th>Email & Contact</th>
                                        <th>System Role</th>
                                        <th>Customer Classification</th>
                                        <th>Customer / ID Number</th>
                                        <th>Account Status</th>
                                        <th class="text-end">Manage Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): 
                                        $roleBadge = 'bg-secondary';
                                        if ($u['role'] === 'admin') $roleBadge = 'bg-dark';
                                        elseif ($u['role'] === 'cashier') $roleBadge = 'bg-warning text-dark';
                                        elseif ($u['role'] === 'barista') $roleBadge = 'bg-danger';
                                        elseif ($u['role'] === 'customer') $roleBadge = 'bg-primary';

                                        $affilBadge = 'bg-light text-secondary border';
                                        if ($u['affiliation'] === 'faculty') $affilBadge = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                        elseif ($u['affiliation'] === 'student') $affilBadge = 'bg-primary-subtle text-primary border border-primary-subtle';
                                        elseif ($u['affiliation'] === 'pwd_senior' || $u['affiliation'] === 'senior_pwd') $affilBadge = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                        elseif ($u['affiliation'] === 'vip') $affilBadge = 'bg-success-subtle text-success border border-success-subtle';
                                        elseif ($u['affiliation'] === 'regular') $affilBadge = 'bg-light text-dark border';
                                    ?>
                                        <tr class="user-row" data-role="<?= $u['role'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="rounded-circle bg-navy text-warning fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                                        <?= strtoupper(substr($u['name'], 0, 2)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-navy"><?= htmlspecialchars($u['name']) ?></div>
                                                        <small class="text-muted">User ID: #USR-<?= str_pad($u['id'], 3, '0', STR_PAD_LEFT) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-medium text-dark"><?= htmlspecialchars($u['email']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($u['phone'] ?? 'No phone recorded') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge <?= $roleBadge ?> px-3 py-1.5 rounded-pill text-uppercase fw-bold" style="font-size: 0.72rem;">
                                                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($u['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $affilBadge ?> px-2.5 py-1 rounded-pill text-capitalize">
                                                    <?= htmlspecialchars($u['affiliation'] ?? 'Visitor') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-mono text-secondary small"><?= htmlspecialchars($u['id_number'] ?? '—') ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Active
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <select class="form-select form-select-sm rounded-pill w-auto" onchange="updateUserRole(<?= $u['id'] ?>, this.value, '<?= $u['affiliation'] ?>')">
                                                        <option value="cashier" <?= $u['role'] === 'cashier' ? 'selected' : '' ?>>Cashier</option>
                                                        <option value="barista" <?= $u['role'] === 'barista' ? 'selected' : '' ?>>Barista</option>
                                                        <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- TAB 3: INVENTORY TRACKING & STOCK SUITE -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-inventory" role="tabpanel">
                <?php require __DIR__ . '/partials/inventory_suite.view.php'; ?>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: MASTER RESERVATIONS & SEATING LOG -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-reservations" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-calendar2-range text-warning me-2"></i>Master Reservation & Seating Log
                            </h5>
                            <p class="text-muted small mb-0">Monitor online customer reservations, verified guest IDs, and seating statuses.</p>
                        </div>
                        <a href="booking" target="_blank" class="btn btn-sm btn-outline-navy rounded-pill px-3 fw-bold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Customer Booking Portal
                        </a>
                    </div>
                    <div class="card-body p-4 overflow-auto">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th>Res #</th>
                                        <th>Guest</th>
                                        <th>Affiliation</th>
                                        <th>Table / Zone</th>
                                        <th>Schedule</th>
                                        <th>Pre-Order</th>
                                        <th>Anti-Spam Verification</th>
                                        <th>Status</th>
                                        <th class="text-end">Update Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $res): 
                                        $badgeClass = $res['status'] === 'Checked-In' ? 'bg-success' : ($res['status'] === 'Confirmed' ? 'bg-primary' : ($res['status'] === 'Completed' ? 'bg-secondary' : 'bg-danger'));
                                    ?>
                                        <tr>
                                            <td class="fw-mono fw-bold text-navy small">
                                                <a href="index.php?page=pass&token=<?= urlencode($res['qr_token']) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($res['reservation_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($res['customer_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($res['phone']) ?></div>
                                            </td>
                                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($res['customer_type']) ?></span></td>
                                            <td>
                                                <span class="fw-bold text-navy"><?= htmlspecialchars($res['table_code'] ?? 'TBL') ?> - <?= htmlspecialchars($res['table_name'] ?? 'Pod') ?></span>
                                                <div class="text-muted small"><?= htmlspecialchars($res['zone'] ?? 'General') ?></div>
                                            </td>
                                            <td class="small">
                                                <div><?= date('M d, Y', strtotime($res['reservation_date'])) ?></div>
                                                <div class="text-muted"><?= htmlspecialchars($res['time_slot']) ?></div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">₱<?= number_format($res['total_amount'], 2) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-shield-check me-1"></i> OTP / ID Verified
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($res['status']) ?></span>
                                            </td>
                                            <td class="text-end">
                                                <select class="form-select form-select-sm d-inline-block w-auto rounded-pill" onchange="changeReservationStatus(<?= $res['id'] ?>, this.value)">
                                                    <option value="Confirmed" <?= $res['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="Checked-In" <?= $res['status'] === 'Checked-In' ? 'selected' : '' ?>>Checked-In</option>
                                                    <option value="Completed" <?= $res['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="Cancelled" <?= $res['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 5: ZONES & SEATING FLOOR PLAN -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-tables" role="tabpanel">
                
                <!-- Zone Management Console -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-geo-alt-fill text-warning me-2"></i>Dining &amp; Seating Zones Management
                            </h5>
                            <p class="text-muted small mb-0">Manage cafe zones, dining areas, facility descriptions, and capacity quotas. Configured zones appear live on the Customer Booking map.</p>
                        </div>
                        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold" onclick="openAddZoneModal()">
                            <i class="bi bi-plus-circle me-1"></i> Add New Seating Zone
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php foreach ($zones as $zn): 
                                $zActive = (int)($zn['is_active'] ?? 1) === 1;
                                $zColor = $zn['badge_color'] ?? 'primary';
                                $zIcon = $zn['icon'] ?? 'bi-grid';
                            ?>
                                <div class="col-xl-3 col-lg-6 col-md-6">
                                    <div class="card h-100 border rounded-4 p-3 shadow-xs bg-white border-top border-4 border-<?= htmlspecialchars($zColor) ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-<?= htmlspecialchars($zColor) ?>-subtle text-<?= htmlspecialchars($zColor) ?> p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                    <i class="bi <?= htmlspecialchars($zIcon) ?> fs-6"></i>
                                                </div>
                                                <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($zn['name']) ?></h6>
                                            </div>
                                            <span class="badge <?= $zActive ? 'bg-success' : 'bg-secondary' ?> px-2 py-0.5 rounded-pill small">
                                                <?= $zActive ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-3" style="min-height: 38px;"><?= htmlspecialchars($zn['description'] ?? 'No description provided.') ?></p>
                                        <div class="d-flex align-items-center justify-content-between small text-secondary bg-light p-2 rounded-3 mb-3">
                                            <span><i class="bi bi-grid-3x3 me-1"></i>Tables: <strong><?= $zn['table_count'] ?></strong></span>
                                            <span><i class="bi bi-people me-1"></i>Cap: <strong><?= $zn['total_capacity'] ?></strong></span>
                                            <span><i class="bi bi-check-circle me-1 text-success"></i>Avail: <strong><?= $zn['available_tables'] ?></strong></span>
                                        </div>
                                        <div class="d-flex gap-1.5 mt-auto pt-2 border-top">
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 rounded-pill fw-semibold py-1" onclick="openEditZoneModal(<?= htmlspecialchars(json_encode($zn)) ?>)">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-outline-<?= $zActive ? 'warning' : 'success' ?> btn-sm rounded-pill fw-semibold py-1 px-2.5" onclick="toggleZoneActive(<?= $zn['id'] ?>, <?= $zActive ? 0 : 1 ?>)" title="<?= $zActive ? 'Deactivate Zone' : 'Activate Zone' ?>">
                                                <i class="bi bi-power"></i>
                                            </button>
                                            <?php if ((int)$zn['table_count'] === 0): ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill fw-semibold py-1 px-2.5" onclick="deleteZone(<?= $zn['id'] ?>, '<?= addslashes($zn['name']) ?>')" title="Delete Zone">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Dining Tables & Study Pods Grid -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i>Floor Plan Dining Tables & Study Pods
                            </h5>
                            <p class="text-muted small mb-0">Manage individual seating units, capacity rules, electrical outlets, and real-time statuses.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill"><?= count($diningTables) ?> Total Units</span>
                            <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold" onclick="openAddTableModal()">
                                <i class="bi bi-plus-lg me-1"></i> Add New Table / Pod
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php foreach ($diningTables as $dt): 
                                $st = strtolower($dt['status']);
                                $stBadge = $st === 'available' ? 'bg-success' : ($st === 'reserved' ? 'bg-warning text-dark' : 'bg-danger');
                            ?>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="card h-100 border rounded-4 p-3 shadow-xs bg-white">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-xs"><i class="bi bi-hash"></i> Table #<?= $dt['id'] ?></span>
                                                <span class="badge bg-navy text-white fw-bold px-2 py-1"><?= htmlspecialchars($dt['table_code']) ?></span>
                                            </div>
                                            <span class="badge <?= $stBadge ?> px-2.5 py-1 text-capitalize fw-bold"><?= htmlspecialchars($dt['status']) ?></span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            <span class="text-navy">Table #<?= $dt['id'] ?>:</span> <?= htmlspecialchars($dt['name']) ?>
                                        </h6>
                                        <div class="small text-muted mb-2"><i class="bi bi-geo-alt me-1 text-primary"></i>Zone: <strong><?= htmlspecialchars($dt['zone']) ?></strong></div>
                                        <div class="d-flex align-items-center gap-2 small text-secondary mb-3">
                                            <span><i class="bi bi-people me-1 text-warning"></i>Cap: <strong><?= $dt['capacity'] ?></strong></span>
                                            <?php if ($dt['has_power_outlet']): ?>
                                                <span class="badge bg-warning-subtle text-dark border"><i class="bi bi-plug-fill me-1"></i>AC Outlet</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-auto pt-2 border-top">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label class="form-label small fw-bold text-muted mb-0">Status:</label>
                                                <select class="form-select form-select-sm rounded-pill w-auto py-0.5" onchange="updateTableStatus(<?= $dt['id'] ?>, this.value)">
                                                    <option value="available" <?= $st === 'available' ? 'selected' : '' ?>>Available</option>
                                                    <option value="reserved" <?= $st === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                                    <option value="occupied" <?= $st === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                                    <option value="maintenance" <?= $st === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                                </select>
                                            </div>
                                            <div class="d-flex gap-1.5">
                                                <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 rounded-pill py-1 small fw-semibold" onclick="openEditTableModal(<?= htmlspecialchars(json_encode($dt)) ?>)">
                                                    <i class="bi bi-pencil me-1"></i> Edit Details
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-1 px-2.5 small" onclick="deleteTable(<?= $dt['id'] ?>, 'Table #<?= $dt['id'] ?> (<?= addslashes($dt['table_code']) ?>)')" title="Delete Table">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 6: SALES & REVENUE REPORTS SUITE -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="tab-reports" role="tabpanel">
                
                <!-- Print-Only Official Header -->
                <div class="print-only-header mb-4 pb-3 border-bottom text-center">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #C37960; color: #FFF6F6; font-weight: 900; font-size: 1.4rem;">R</div>
                        <div class="text-start">
                            <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.05em; color: #894E3F;">Raeden’s Bakery and Café</h5>
                            <div class="small text-muted">Specialty Bakes &amp; POS Management • Audit &amp; Revenue Operations</div>
                        </div>
                    </div>
                    <h4 class="fw-black text-navy mt-2 mb-0" id="printReportTitle">Raeden’s Bakery and Café Official Sales & Revenue Report</h4>
                    <div class="small text-muted" id="printReportSubtitle">--</div>
                </div>

                <!-- Top Header & Actions -->
                <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden bg-white">
                    <div class="card-body p-3 p-lg-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-navy text-warning fw-bold px-2.5 py-1 rounded-pill">
                                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Sales Intelligence
                                    </span>
                                    <span class="badge bg-warning-subtle text-dark border px-2.5 py-1 rounded-pill small" id="reportActivePeriodBadge">
                                        DAILY REPORT
                                    </span>
                                </div>
                                <h4 class="fw-bold text-navy mb-0" id="reportPeriodTitle">Daily Sales Report</h4>
                                <small class="text-muted" id="reportPeriodSubtitle">Loading live POS & reservation sales data...</small>
                            </div>
                            <div class="d-flex flex-wrap gap-2 no-print">
                                <button type="button" class="btn btn-outline-navy btn-sm rounded-pill px-3 fw-bold" onclick="exportSalesReportCsv()" id="btnExportCsv">
                                    <i class="bi bi-filetype-csv me-1"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold text-white shadow-xs" onclick="printSalesReport()">
                                    <i class="bi bi-printer me-1 text-warning"></i> Print Official Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtering Toolbar (Card) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white no-print">
                    <div class="card-body p-3 p-lg-4">
                        
                        <!-- 1. Period Selector Tabs -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="small fw-bold text-secondary me-1"><i class="bi bi-calendar3 me-1"></i>Period:</span>
                                <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group">
                                    <button type="button" class="btn rounded-pill px-3 fw-bold btn-navy" id="btnPeriodDaily" onclick="setReportPeriod('daily')">Daily</button>
                                    <button type="button" class="btn rounded-pill px-3 fw-medium btn-light text-secondary" id="btnPeriodMonthly" onclick="setReportPeriod('monthly')">Monthly</button>
                                    <button type="button" class="btn rounded-pill px-3 fw-medium btn-light text-secondary" id="btnPeriodYearly" onclick="setReportPeriod('yearly')">Yearly</button>
                                    <button type="button" class="btn rounded-pill px-3 fw-medium btn-light text-secondary" id="btnPeriodCustom" onclick="setReportPeriod('custom')">Date Range</button>
                                </div>
                            </div>
                            
                            <!-- Quick Presets -->
                            <div class="d-flex flex-wrap align-items-center gap-1.5" id="reportQuickPresets">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 small" onclick="setQuickDate('today')">Today</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 small" onclick="setQuickDate('yesterday')">Yesterday</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 small" onclick="setQuickDate('this_month')">This Month</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 small" onclick="setQuickDate('last_month')">Last Month</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0.5 px-2.5 small" onclick="setQuickDate('this_year')">This Year</button>
                            </div>
                        </div>

                        <!-- 2. Dynamic Date Inputs & Filters Row -->
                        <div class="row g-2.5 align-items-end">
                            
                            <!-- Daily Date Picker -->
                            <div class="col-lg-3 col-md-4" id="ctrlDailyContainer">
                                <label class="form-label small fw-bold text-secondary mb-1">Select Date</label>
                                <input type="date" class="form-control form-control-sm rounded-3" id="repDateInput" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" onchange="loadSalesReport()">
                            </div>

                            <!-- Monthly Month Picker -->
                            <div class="col-lg-3 col-md-4 d-none" id="ctrlMonthlyContainer">
                                <label class="form-label small fw-bold text-secondary mb-1">Select Month & Year</label>
                                <input type="month" class="form-control form-control-sm rounded-3" id="repMonthInput" value="<?= date('Y-m') ?>" max="<?= date('Y-m') ?>" onchange="loadSalesReport()">
                            </div>

                            <!-- Yearly Year Picker -->
                            <div class="col-lg-3 col-md-4 d-none" id="ctrlYearlyContainer">
                                <label class="form-label small fw-bold text-secondary mb-1">Select Fiscal Year</label>
                                <select class="form-select form-select-sm rounded-3" id="repYearSelect" onchange="loadSalesReport()">
                                    <?php 
                                    $curY = (int)date('Y');
                                    for ($y = $curY; $y >= $curY - 4; $y--): ?>
                                        <option value="<?= $y ?>" <?= $y === $curY ? 'selected' : '' ?>><?= $y ?> Calendar Year</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <!-- Custom Date Range Pickers -->
                            <div class="col-lg-4 col-md-6 d-none" id="ctrlRangeContainer">
                                <label class="form-label small fw-bold text-secondary mb-1">Custom Range (Start & End)</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" class="form-control rounded-start-3" id="repRangeStart" value="<?= date('Y-m-01') ?>" onchange="loadSalesReport()">
                                    <span class="input-group-text bg-light text-muted">to</span>
                                    <input type="date" class="form-control rounded-end-3" id="repRangeEnd" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" onchange="loadSalesReport()">
                                </div>
                            </div>

                            <!-- Payment Method Filter -->
                            <div class="col-lg-2 col-md-3 col-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Payment Method</label>
                                <select class="form-select form-select-sm rounded-3" id="repPaymentFilter" onchange="loadSalesReport()">
                                    <option value="all" selected>All Methods</option>
                                    <option value="Cash">Cash</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Maya">Maya</option>
                                </select>
                            </div>

                            <!-- Order Type Filter -->
                            <div class="col-lg-2 col-md-3 col-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Dining Mode</label>
                                <select class="form-select form-select-sm rounded-3" id="repOrderTypeFilter" onchange="loadSalesReport()">
                                    <option value="all" selected>All Channels</option>
                                    <option value="Dine-In">Dine-In</option>
                                    <option value="Takeout">Takeout</option>
                                </select>
                            </div>

                            <!-- Refresh / Apply Button -->
                            <div class="col-lg-2 col-md-2">
                                <button type="button" class="btn btn-navy btn-sm w-100 rounded-3 fw-bold" onclick="loadSalesReport()" id="btnApplyFilter">
                                    <i class="bi bi-arrow-repeat me-1" id="filterRefreshIcon"></i> Refresh
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 4 Executive Financial Summary Metric Cards -->
                <div class="row g-3 mb-4">
                    
                    <!-- KPI 1: Net Sales Revenue -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-warning">
                            <div class="card-body p-3.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Net Revenue</span>
                                    <div class="rounded-circle bg-warning-subtle text-dark p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-cash-stack text-warning fs-5"></i>
                                    </div>
                                </div>
                                <h3 class="fw-black text-navy mb-1" id="kpiNetSales">₱0.00</h3>
                                <div class="small text-muted d-flex justify-content-between border-top pt-1.5 mt-2">
                                    <span>Gross Sales:</span>
                                    <strong class="text-dark" id="kpiGrossSales">₱0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Total Orders & Volume -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-primary">
                            <div class="card-body p-3.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Order Transactions</span>
                                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-receipt-cutoff fs-5"></i>
                                    </div>
                                </div>
                                <h3 class="fw-black text-navy mb-1" id="kpiTotalOrders">0</h3>
                                <div class="small text-muted d-flex justify-content-between border-top pt-1.5 mt-2">
                                    <span>Items Handcrafted:</span>
                                    <strong class="text-dark" id="kpiTotalItems">0 items</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: Average Order Value (AOV) -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-info">
                            <div class="card-body p-3.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Average Ticket (AOV)</span>
                                    <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-calculator fs-5"></i>
                                    </div>
                                </div>
                                <h3 class="fw-black text-navy mb-1" id="kpiAvgOrderValue">₱0.00</h3>
                                <div class="small text-muted d-flex justify-content-between border-top pt-1.5 mt-2">
                                    <span>Total Discounts:</span>
                                    <strong class="text-danger" id="kpiTotalDiscounts">-₱0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: 12% Output VAT & Audit Base -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-success">
                            <div class="card-body p-3.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">12% Output VAT</span>
                                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-shield-check fs-5"></i>
                                    </div>
                                </div>
                                <h3 class="fw-black text-navy mb-1" id="kpiVatAmount">₱0.00</h3>
                                <div class="small text-muted d-flex justify-content-between border-top pt-1.5 mt-2">
                                    <span>Vatable Base:</span>
                                    <strong class="text-success" id="kpiVatableSales">₱0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Visual Analytics Row (Chart + Channels) -->
                <div class="row g-3 mb-4">
                    
                    <!-- Left: Interactive Sales Trend Timeline -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold text-navy mb-0">Sales & Revenue Progression</h6>
                                    <small class="text-muted" id="chartTimeScopeText">Timeline revenue performance</small>
                                </div>
                                <span class="badge bg-light text-dark border px-2.5 py-1 small fw-normal">
                                    <i class="bi bi-circle-fill text-warning me-1" style="font-size: 0.5rem;"></i> Net Sales (₱)
                                </span>
                            </div>
                            <div style="height: 260px; position: relative;">
                                <canvas id="salesReportTrendChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Payment Methods & Dining Channels -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white p-4 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="fw-bold text-navy mb-3 pb-2 border-bottom">Payment Method Distribution</h6>
                                <div class="d-flex flex-column gap-3 mb-4" id="reportPaymentList">
                                    <!-- Rendered dynamically -->
                                    <div class="text-muted small text-center py-3">Loading payment mix...</div>
                                </div>
                            </div>

                            <div>
                                <h6 class="fw-bold text-navy mb-2.5 pb-2 border-bottom">Dining Channel Share</h6>
                                <div class="d-flex align-items-center justify-content-between mb-1" id="diningChannelBreakdown">
                                    <div class="small"><span class="fw-bold text-navy" id="dineInCount">0</span> Dine-In (<span id="dineInPct">0%</span>)</div>
                                    <div class="small"><span class="fw-bold text-warning" id="takeoutCount">0</span> Takeout (<span id="takeoutPct">0%</span>)</div>
                                </div>
                                <div class="progress rounded-pill" style="height: 10px;">
                                    <div class="progress-bar bg-navy" id="dineInBar" role="progressbar" style="width: 50%;"></div>
                                    <div class="progress-bar bg-warning" id="takeoutBar" role="progressbar" style="width: 50%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Top Products Leaderboard -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-0 pt-3.5 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-navy mb-0">Top Performing Handcrafted Beverages & Pastries</h6>
                            <small class="text-muted">Ranked by volume sold and revenue contribution during this report period</small>
                        </div>
                        <span class="badge bg-light text-secondary border px-2.5 py-1 small">Best Sellers</span>
                    </div>
                    <div class="table-responsive px-4 pb-3">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">Rank</th>
                                    <th>Product Item</th>
                                    <th>Category</th>
                                    <th class="text-center">Units Sold</th>
                                    <th class="text-end">Total Revenue Generated</th>
                                    <th class="text-end" style="width: 120px;">Share of Sales</th>
                                </tr>
                            </thead>
                            <tbody id="reportTopProductsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Loading top selling products...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Transactions Ledger Table -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-journal-text text-warning"></i> Itemized Sales & Transaction Ledger
                                <span class="badge bg-navy text-warning rounded-pill" id="ledgerCountBadge">0 Orders</span>
                            </h5>
                            <small class="text-muted">Audited transaction records with line item customization details</small>
                        </div>
                        <div class="d-flex align-items-center gap-2 no-print">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="ledgerSearchInput" placeholder="Filter order # or guest..." oninput="filterLedgerTable()">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="salesLedgerTable">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">Order #</th>
                                    <th>Date & Time</th>
                                    <th>Guest / Customer</th>
                                    <th>Channel / Table</th>
                                    <th>Items Ordered</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Net Total</th>
                                    <th>Payment</th>
                                    <th class="text-center no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody class="small" id="reportLedgerTableBody">
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">Loading transaction records...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Official Signatures (Print & Audit View) -->
                <div class="print-only-header mt-5 pt-4 border-top">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-3">
                                <strong class="d-block small text-dark"><?= htmlspecialchars($adminUser['name']) ?></strong>
                                <span class="small text-muted">POS Cashier / Shift Lead</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-3">
                                <strong class="d-block small text-dark">Management Supervisor</strong>
                                <span class="small text-muted">Operations &amp; Floor Manager</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-top border-dark pt-1 mx-3">
                                <strong class="d-block small text-dark">Proprietor / Store Manager</strong>
                                <span class="small text-muted">Raeden's Resto, Bakery, Cafe</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>
</div>

<!-- Modal: Add New Staff / User Account -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold" id="addUserModalLabel">Create New Staff / User Account</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="newUserForm" onsubmit="handleCreateUser(event)">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="newUserName" placeholder="e.g. Christine Joy Alcala" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3" id="newUserEmail" placeholder="e.g. staff@raedens.com" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">System Role <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="newUserRole" required>
                                <option value="cashier" selected>Cashier (POS)</option>
                                <option value="barista">Barista (KDS)</option>
                                <option value="customer">Customer</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Customer Classification</label>
                            <select class="form-select rounded-3" id="newUserAffil">
                                <option value="regular" selected>Regular Diner / Walk-in</option>
                                <option value="pwd_senior">Senior Citizen / PWD</option>
                                <option value="student">Student</option>
                                <option value="vip">VIP / Member</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">ID Number</label>
                            <input type="text" class="form-control rounded-3" id="newUserIdNumber" placeholder="e.g. RAEDEN-2024-001">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Mobile Phone</label>
                            <input type="tel" class="form-control rounded-3" id="newUserPhone" placeholder="e.g. 0917-000-1111">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Initial Password</label>
                        <input type="password" class="form-control rounded-3" id="newUserPassword" value="password123" required>
                        <small class="text-muted">Default password: <code>password123</code></small>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-xs" id="btnSubmitNewUser">
                            <i class="bi bi-check2-circle me-1"></i> Save Account
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Restock Shipment -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam-fill text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold">Log Inventory Restock Shipment</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="restockForm" onsubmit="handleRestockSubmission(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Select Product / Item <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="restockProductId" required>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Current: <?= $p['stock_quantity'] ?> <?= $p['unit'] ?? 'units' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Added Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-3 fw-bold text-navy" id="restockQuantity" min="1" value="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Movement Type</label>
                            <input type="text" class="form-control rounded-3 bg-light" value="Restock Delivery" readonly>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Supplier / Delivery Invoice Note</label>
                        <input type="text" class="form-control rounded-3" id="restockNote" placeholder="e.g. Weekly bakery supply delivery PO #RDN-089">
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy rounded-pill px-4 fw-bold" id="btnSubmitRestock">
                            <i class="bi bi-check2-circle me-1"></i> Confirm Restock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Spoilage / Loss -->
<div class="modal fade" id="wasteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-danger text-white px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-octagon-fill text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold">Log Inventory Waste / Write-Off</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="wasteForm" onsubmit="handleWasteSubmission(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Select Product / Item <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="wasteProductId" required>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Current: <?= $p['stock_quantity'] ?> <?= $p['unit'] ?? 'units' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Quantity Deducted <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-3 fw-bold text-danger" id="wasteQuantity" min="1" value="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Reason Category</label>
                            <select class="form-select rounded-3" id="wasteReasonCategory">
                                <option value="Expired / Spoiled Batch">Expired / Spoiled</option>
                                <option value="Damaged / Broken Container">Damaged / Broken</option>
                                <option value="Spillage / Preparation Error">Spillage / Error</option>
                                <option value="Machine Calibration Loss">Calibration Loss</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Explanation Note</label>
                        <input type="text" class="form-control rounded-3" id="wasteNote" placeholder="e.g. Expired dairy carton discarded during morning audit">
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold" id="btnSubmitWaste">
                            <i class="bi bi-trash-fill me-1"></i> Record Write-Off
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Edit Seating Zone -->
<div class="modal fade" id="zoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt-fill text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold" id="zoneModalTitle">Add New Seating Zone</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="zoneForm" onsubmit="handleZoneSubmission(event)">
                    <input type="hidden" id="zoneId" value="">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Zone Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="zoneName" placeholder="e.g. Mezzanine Study Lounge" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Bootstrap Icon</label>
                            <select class="form-select rounded-3" id="zoneIcon">
                                <option value="bi-laptop" selected>bi-laptop (Study)</option>
                                <option value="bi-cup-straw">bi-cup-straw (Lounge)</option>
                                <option value="bi-people">bi-people (Main Hall)</option>
                                <option value="bi-sun">bi-sun (Terrace)</option>
                                <option value="bi-book">bi-book (Library)</option>
                                <option value="bi-stars">bi-stars (VIP / Special)</option>
                                <option value="bi-building">bi-building (Hotel)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Color Theme</label>
                            <select class="form-select rounded-3" id="zoneBadgeColor">
                                <option value="primary" selected>Primary Blue</option>
                                <option value="warning">Warning Gold</option>
                                <option value="info">Info Cyan</option>
                                <option value="success">Success Green</option>
                                <option value="secondary">Secondary Slate</option>
                                <option value="dark">Dark Navy</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Description / Amenities</label>
                        <textarea class="form-control rounded-3" id="zoneDescription" rows="3" placeholder="Brief description of amenities and environment..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy rounded-pill px-4 fw-bold" id="btnSubmitZone">
                            <i class="bi bi-check2-circle me-1"></i> Save Zone
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Edit Dining Table -->
<div class="modal fade" id="tableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold" id="tableModalTitle">Add New Dining Table / Pod</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="tableForm" onsubmit="handleTableSubmission(event)">
                    <input type="hidden" id="tableFormId" value="">
                    <div class="row g-2 mb-3">
                        <div class="col-5">
                            <label class="form-label small fw-bold text-secondary">Table Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 fw-mono text-uppercase" id="tableFormCode" placeholder="e.g. POD-04" required>
                        </div>
                        <div class="col-7">
                            <label class="form-label small fw-bold text-secondary">Unit Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="tableFormName" placeholder="e.g. Quiet Study Pod Delta" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Seating Zone <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="tableFormZone" required>
                                <?php foreach ($zones as $zn): ?>
                                    <option value="<?= htmlspecialchars($zn['name']) ?>"><?= htmlspecialchars($zn['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Seating Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control rounded-3" id="tableFormCapacity" min="1" max="20" value="2" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Initial Status</label>
                            <select class="form-select rounded-3" id="tableFormStatus">
                                <option value="available" selected>Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-6 pt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="tableFormPowerOutlet" checked>
                                <label class="form-check-label small fw-bold text-secondary" for="tableFormPowerOutlet">AC Power Outlet</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">Description / Location Notes</label>
                        <textarea class="form-control rounded-3" id="tableFormDesc" rows="2" placeholder="e.g. Positioned near south windows with garden view..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy rounded-pill px-4 fw-bold" id="btnSubmitTable">
                            <i class="bi bi-check2-circle me-1"></i> Save Table
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detailed Sales Order Receipt View -->
<div class="modal fade" id="salesOrderReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(740px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="receiptModalOrderNum">Order Receipt</h5>
                        <small class="text-white-50" id="receiptModalOrderDate">--</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white" id="salesOrderReceiptBody">
                <!-- Dynamically rendered order receipt details -->
            </div>
            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold" onclick="printModalReceipt()">
                    <i class="bi bi-printer me-1 text-warning"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Chart 1: Revenue Trend Chart
(function initAdminCharts() {
    const ctxTrend = document.getElementById('revenueTrendChart');
    if (ctxTrend) {
        if (window.adminTrendChartInstance) {
            window.adminTrendChartInstance.destroy();
        }
        window.adminTrendChartInstance = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Today'],
                datasets: [{
                    label: 'Gross Sales (₱)',
                    data: [1850, 2400, 3120, 2890, 4200, 5600, <?= max(1200, (int)$todayRevenue) ?>],
                    borderColor: '#0c2340',
                    backgroundColor: 'rgba(12, 35, 64, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffc72c',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ₱${ctx.raw.toLocaleString()}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (v) => `₱${v}`
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Chart 2: Category Popularity
    const ctxCat = document.getElementById('categoryChart');
    if (ctxCat) {
        if (window.adminCatChartInstance) {
            window.adminCatChartInstance.destroy();
        }
        window.adminCatChartInstance = new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: ['Hot Coffee', 'Iced Espresso', 'Cold Brew', 'Non-Coffee', 'Pastries'],
                datasets: [{
                    data: [35, 45, 20, 15, 25],
                    backgroundColor: ['#0c2340', '#1d3557', '#ffc72c', '#0d6efd', '#20c997'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    }
})();

// Filter Users Table
function filterUserTable(role, btn) {
    document.querySelectorAll('.user-filter-btn').forEach(b => {
        b.classList.remove('btn-navy', 'active');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-navy', 'active');

    document.querySelectorAll('.user-row').forEach(row => {
        if (role === 'all' || row.getAttribute('data-role') === role) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Create New User Account
async function handleCreateUser(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitNewUser');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const payload = {
        action: 'create_user',
        name: document.getElementById('newUserName').value,
        email: document.getElementById('newUserEmail').value,
        role: document.getElementById('newUserRole').value,
        affiliation: document.getElementById('newUserAffil').value,
        id_number: document.getElementById('newUserIdNumber').value,
        phone: document.getElementById('newUserPhone').value,
        password: document.getElementById('newUserPassword').value
    };

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            if (modal) modal.hide();
            setTimeout(() => {
                window.location.reload();
            }, 600);
        } else {
            showToast(data.message || 'Error creating user', 'danger');
        }
    } catch (err) {
        showToast('Connection error', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Account';
    }
}

// Update User Role
async function updateUserRole(userId, newRole, affil) {
    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_user_role',
                user_id: userId,
                role: newRole,
                affiliation: affil
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating role', 'danger');
        }
    } catch (err) {
        showToast('Connection error', 'danger');
    }
}

// Update Dining Table Status
async function updateTableStatus(tableId, newStatus) {
    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_table_status',
                table_id: tableId,
                status: newStatus
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating table', 'danger');
        }
    } catch (err) {
        showToast('Connection error', 'danger');
    }
}

// Direct Stock Update
async function updateDirectStock(productId, newStock) {
    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_stock',
                product_id: productId,
                new_stock: parseInt(newStock)
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating stock', 'danger');
        }
    } catch (err) {
        showToast('Network error updating stock', 'danger');
    }
}

// Delta Stock Adjust (-1, +5, +10)
async function adjustDelta(productId, delta) {
    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_stock',
                product_id: productId,
                delta: delta
            })
        });
        const data = await res.json();
        if (data.success) {
            const inputEl = document.querySelector(`#prodRow_${productId} input[type="number"]`);
            if (inputEl) inputEl.value = data.new_stock;
            showToast(`Stock updated to ${data.new_stock}`, 'success');
        } else {
            showToast(data.message || 'Error adjusting stock', 'danger');
        }
    } catch (err) {
        showToast('Network error adjusting stock', 'danger');
    }
}

// Change Reservation Status
async function changeReservationStatus(resId, newStatus) {
    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_res_status',
                reservation_id: resId,
                status: newStatus
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating reservation', 'danger');
        }
    } catch (err) {
        showToast('Network error updating reservation', 'danger');
    }
}

// ==========================================
// INVENTORY TRACKING & RESTOCK HANDLERS
// ==========================================
function openRestockModal() {
    const modalEl = document.getElementById('restockModal');
    if (modalEl) {
        new bootstrap.Modal(modalEl).show();
    }
}

function openWasteModal() {
    const modalEl = document.getElementById('wasteModal');
    if (modalEl) {
        new bootstrap.Modal(modalEl).show();
    }
}

async function handleRestockSubmission(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitRestock');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    const payload = {
        action: 'log_restock',
        product_id: parseInt(document.getElementById('restockProductId').value),
        quantity: parseInt(document.getElementById('restockQuantity').value),
        note: document.getElementById('restockNote').value.trim()
    };

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('restockModal'));
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Restock failed', 'danger');
        }
    } catch (err) {
        showToast('Network error logging restock', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Confirm Restock';
    }
}

async function handleWasteSubmission(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitWaste');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

    const cat = document.getElementById('wasteReasonCategory').value;
    const noteText = document.getElementById('wasteNote').value.trim();
    const combinedNote = noteText ? `[${cat}] ${noteText}` : cat;

    const payload = {
        action: 'log_waste',
        product_id: parseInt(document.getElementById('wasteProductId').value),
        quantity: parseInt(document.getElementById('wasteQuantity').value),
        note: combinedNote
    };

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('wasteModal'));
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Waste logging failed', 'danger');
        }
    } catch (err) {
        showToast('Network error logging waste', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Record Write-Off';
    }
}

// ==========================================
// SEATING ZONE MANAGEMENT HANDLERS
// ==========================================
function openAddZoneModal() {
    document.getElementById('zoneForm').reset();
    document.getElementById('zoneId').value = '';
    document.getElementById('zoneModalTitle').textContent = 'Add New Seating Zone';
    new bootstrap.Modal(document.getElementById('zoneModal')).show();
}

function openEditZoneModal(zone) {
    document.getElementById('zoneId').value = zone.id;
    document.getElementById('zoneName').value = zone.name;
    document.getElementById('zoneIcon').value = zone.icon || 'bi-grid';
    document.getElementById('zoneBadgeColor').value = zone.badge_color || 'primary';
    document.getElementById('zoneDescription').value = zone.description || '';
    document.getElementById('zoneModalTitle').textContent = `Edit Seating Zone: ${zone.name}`;
    new bootstrap.Modal(document.getElementById('zoneModal')).show();
}

async function handleZoneSubmission(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitZone');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const zoneId = document.getElementById('zoneId').value;
    const isEdit = zoneId !== '';
    const payload = {
        action: isEdit ? 'update_zone' : 'create_zone',
        id: isEdit ? parseInt(zoneId) : undefined,
        name: document.getElementById('zoneName').value.trim(),
        icon: document.getElementById('zoneIcon').value,
        badge_color: document.getElementById('zoneBadgeColor').value,
        description: document.getElementById('zoneDescription').value.trim()
    };

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('zoneModal'));
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Error saving zone', 'danger');
        }
    } catch (err) {
        showToast('Network error saving zone', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Zone';
    }
}

async function toggleZoneActive(id, newStatus) {
    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_zone',
                id: id,
                is_active: newStatus,
                name: (document.querySelector(`button[onclick*="toggleZoneActive(${id}"]`)?.closest('.card')?.querySelector('h6')?.textContent || '').trim()
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Error toggling zone', 'danger');
        }
    } catch (err) {
        showToast('Network error toggling zone', 'danger');
    }
}

async function deleteZone(id, name) {
    const confirmRes = await showConfirm({
        title: `Delete Seating Zone '${name}'?`,
        text: 'This zone will be permanently removed. Only zones with 0 assigned tables can be deleted.',
        icon: 'warning',
        confirmButtonText: 'Yes, Delete Zone',
        isDestructive: true
    });
    if (!confirmRes.isConfirmed) return;

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_zone', id: id })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Error deleting zone', 'danger');
        }
    } catch (err) {
        showToast('Network error deleting zone', 'danger');
    }
}

// ==========================================
// DINING TABLE & STUDY POD MANAGEMENT
// ==========================================
function openAddTableModal() {
    document.getElementById('tableForm').reset();
    document.getElementById('tableFormId').value = '';
    document.getElementById('tableModalTitle').textContent = 'Add New Dining Table / Pod';
    new bootstrap.Modal(document.getElementById('tableModal')).show();
}

function openEditTableModal(tbl) {
    document.getElementById('tableFormId').value = tbl.id;
    document.getElementById('tableFormCode').value = tbl.table_code;
    document.getElementById('tableFormName').value = tbl.name;
    document.getElementById('tableFormZone').value = tbl.zone;
    document.getElementById('tableFormCapacity').value = tbl.capacity;
    document.getElementById('tableFormStatus').value = tbl.status;
    document.getElementById('tableFormPowerOutlet').checked = parseInt(tbl.has_power_outlet) === 1;
    document.getElementById('tableFormDesc').value = tbl.description || '';
    document.getElementById('tableModalTitle').textContent = `Edit Table #${tbl.id}: ${tbl.table_code} (${tbl.name})`;
    new bootstrap.Modal(document.getElementById('tableModal')).show();
}

async function handleTableSubmission(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitTable');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const tableId = document.getElementById('tableFormId').value;
    const isEdit = tableId !== '';
    const payload = {
        action: isEdit ? 'update_table' : 'create_table',
        id: isEdit ? parseInt(tableId) : undefined,
        table_code: document.getElementById('tableFormCode').value.trim().toUpperCase(),
        name: document.getElementById('tableFormName').value.trim(),
        zone: document.getElementById('tableFormZone').value,
        capacity: parseInt(document.getElementById('tableFormCapacity').value),
        status: document.getElementById('tableFormStatus').value,
        has_power_outlet: document.getElementById('tableFormPowerOutlet').checked ? 1 : 0,
        description: document.getElementById('tableFormDesc').value.trim()
    };

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('tableModal'));
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Error saving table', 'danger');
        }
    } catch (err) {
        showToast('Network error saving table', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Table';
    }
}

async function deleteTable(id, tableName) {
    const confirmRes = await showConfirm({
        title: `Delete Table #${id}?`,
        text: `Are you sure you want to remove '${tableName}' from the floor plan? Tables with active reservations cannot be deleted.`,
        icon: 'warning',
        confirmButtonText: 'Yes, Delete Table',
        isDestructive: true
    });
    if (!confirmRes.isConfirmed) return;

    try {
        const res = await fetch('api/admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_table', id: id })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Error deleting table', 'danger');
        }
    } catch (err) {
        showToast('Network error deleting table', 'danger');
    }
}
// ==========================================
// SALES & REVENUE REPORTS CONTROLLER
// ==========================================
let currentReportPeriod = 'daily';
let currentReportData = null;
let reportTrendChartInstance = null;
let currentViewingOrder = null;

function formatPeso(amount) {
    const num = parseFloat(amount || 0);
    return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}

function setReportPeriod(period) {
    currentReportPeriod = period;

    ['daily', 'monthly', 'yearly', 'custom'].forEach(p => {
        const btnId = `btnPeriod${p.charAt(0).toUpperCase() + p.slice(1)}`;
        const btn = document.getElementById(btnId);
        if (btn) {
            if (p === period) {
                btn.className = 'btn rounded-pill px-3 fw-bold btn-navy text-white';
            } else {
                btn.className = 'btn rounded-pill px-3 fw-medium btn-light text-secondary';
            }
        }
    });

    document.getElementById('ctrlDailyContainer').classList.toggle('d-none', period !== 'daily');
    document.getElementById('ctrlMonthlyContainer').classList.toggle('d-none', period !== 'monthly');
    document.getElementById('ctrlYearlyContainer').classList.toggle('d-none', period !== 'yearly');
    document.getElementById('ctrlRangeContainer').classList.toggle('d-none', period !== 'custom');

    loadSalesReport();
}

function setQuickDate(type) {
    const today = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const toYMD = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    if (type === 'today') {
        document.getElementById('repDateInput').value = toYMD(today);
        setReportPeriod('daily');
    } else if (type === 'yesterday') {
        const yest = new Date(today);
        yest.setDate(yest.getDate() - 1);
        document.getElementById('repDateInput').value = toYMD(yest);
        setReportPeriod('daily');
    } else if (type === 'this_month') {
        document.getElementById('repMonthInput').value = `${today.getFullYear()}-${pad(today.getMonth() + 1)}`;
        setReportPeriod('monthly');
    } else if (type === 'last_month') {
        const lastM = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        document.getElementById('repMonthInput').value = `${lastM.getFullYear()}-${pad(lastM.getMonth() + 1)}`;
        setReportPeriod('monthly');
    } else if (type === 'this_year') {
        document.getElementById('repYearSelect').value = today.getFullYear();
        setReportPeriod('yearly');
    }
}

async function loadSalesReport() {
    const icon = document.getElementById('filterRefreshIcon');
    if (icon) icon.classList.add('spin-animation');

    const params = new URLSearchParams({
        action: 'sales_report',
        period: currentReportPeriod,
        date: document.getElementById('repDateInput').value,
        month: document.getElementById('repMonthInput').value,
        year: document.getElementById('repYearSelect').value,
        start_date: document.getElementById('repRangeStart').value,
        end_date: document.getElementById('repRangeEnd').value,
        payment_method: document.getElementById('repPaymentFilter').value,
        order_type: document.getElementById('repOrderTypeFilter').value
    });

    try {
        const res = await fetch(`api/reports.php?${params.toString()}`);
        const data = await res.json();
        if (data.success) {
            currentReportData = data;
            renderSalesReport(data);
        } else {
            showToast(data.message || 'Error generating sales report', 'danger');
        }
    } catch (err) {
        showToast('Network error while generating sales report', 'danger');
    } finally {
        if (icon) icon.classList.remove('spin-animation');
    }
}

function renderSalesReport(data) {
    const f = data.filter;
    const s = data.summary;

    // 1. Headers & Subtitles
    document.getElementById('reportPeriodTitle').textContent = f.period_label.split(' • ')[0] || 'Sales Report';
    document.getElementById('reportPeriodSubtitle').textContent = f.period_label;
    document.getElementById('printReportSubtitle').textContent = `Scope: ${f.period_label} • Generated on ${new Date().toLocaleString()}`;
    document.getElementById('reportActivePeriodBadge').textContent = f.period.toUpperCase();

    // 2. Financial KPIs
    document.getElementById('kpiNetSales').textContent = formatPeso(s.net_sales);
    document.getElementById('kpiGrossSales').textContent = formatPeso(s.gross_sales);
    document.getElementById('kpiTotalOrders').textContent = s.total_orders;
    document.getElementById('kpiTotalItems').textContent = `${s.total_items_sold} items`;
    document.getElementById('kpiAvgOrderValue').textContent = formatPeso(s.average_order_value);
    document.getElementById('kpiTotalDiscounts').textContent = s.total_discounts > 0 ? `-${formatPeso(s.total_discounts)}` : '₱0.00';
    document.getElementById('kpiVatAmount').textContent = formatPeso(s.output_vat);
    document.getElementById('kpiVatableSales').textContent = formatPeso(s.vatable_sales);

    // 3. Chart.js Revenue Trend Timeline
    const chartCanvas = document.getElementById('salesReportTrendChart');
    if (chartCanvas && data.chart_data) {
        if (reportTrendChartInstance) {
            reportTrendChartInstance.destroy();
        }

        const ctx = chartCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(0, 32, 128, 0.22)');
        gradient.addColorStop(1, 'rgba(0, 32, 128, 0.00)');

        reportTrendChartInstance = new Chart(ctx, {
            type: (f.chart_mode === 'hourly' || f.chart_mode === 'range') ? 'line' : 'bar',
            data: {
                labels: data.chart_data.labels,
                datasets: [{
                    label: 'Net Revenue (₱)',
                    data: data.chart_data.sales,
                    borderColor: '#002080',
                    backgroundColor: (f.chart_mode === 'hourly' || f.chart_mode === 'range') ? gradient : '#f59e0b',
                    borderWidth: 2.5,
                    borderRadius: 6,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` Sales: ₱${ctx.raw.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (v) => '₱' + v.toLocaleString()
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
        document.getElementById('chartTimeScopeText').textContent = `${f.period_label} (${data.chart_data.labels.length} points)`;
    }

    // 4. Payment Methods Mix
    const payContainer = document.getElementById('reportPaymentList');
    if (data.payment_breakdown && data.payment_breakdown.length > 0) {
        payContainer.innerHTML = data.payment_breakdown.map(p => {
            let badgeBg = 'bg-primary';
            let icon = 'bi-cash-coin';
            if (p.method === 'GCash') { badgeBg = 'bg-info'; icon = 'bi-phone'; }
            else if (p.method === 'Maya') { badgeBg = 'bg-success'; icon = 'bi-credit-card'; }

            return `
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small text-dark"><i class="bi ${icon} me-1.5 text-muted"></i>${escapeHtml(p.method)} (${p.count} orders)</span>
                        <span class="fw-bold text-navy small">${formatPeso(p.total)} <span class="text-muted fw-normal">(${p.percentage}%)</span></span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar ${badgeBg}" role="progressbar" style="width: ${p.percentage}%;"></div>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        payContainer.innerHTML = `<div class="text-muted small text-center py-2">No transactions recorded for this period.</div>`;
    }

    // 5. Dining Channels Breakdown
    let dineInCount = 0;
    let takeoutCount = 0;
    (data.order_type_breakdown || []).forEach(ot => {
        if (ot.type.toLowerCase() === 'takeout') {
            takeoutCount = ot.count;
        } else {
            dineInCount = ot.count;
        }
    });
    const totalChannelCount = dineInCount + takeoutCount;
    const dineInPct = totalChannelCount > 0 ? Math.round((dineInCount / totalChannelCount) * 100) : 50;
    const takeoutPct = 100 - dineInPct;

    document.getElementById('dineInCount').textContent = dineInCount;
    document.getElementById('takeoutCount').textContent = takeoutCount;
    document.getElementById('dineInPct').textContent = `${dineInPct}%`;
    document.getElementById('takeoutPct').textContent = `${takeoutPct}%`;
    document.getElementById('dineInBar').style.width = `${dineInPct}%`;
    document.getElementById('takeoutBar').style.width = `${takeoutPct}%`;

    // 6. Top Products Leaderboard
    const prodTbody = document.getElementById('reportTopProductsTableBody');
    if (data.top_products && data.top_products.length > 0) {
        prodTbody.innerHTML = data.top_products.map((p, idx) => {
            const sharePct = s.gross_sales > 0 ? ((p.total_sales / s.gross_sales) * 100).toFixed(1) : 0;
            return `
                <tr>
                    <td class="fw-bold text-center">
                        ${idx === 0 ? '<span class="badge bg-warning text-dark rounded-circle" style="width:24px;height:24px;line-height:16px;">1</span>' :
                          idx === 1 ? '<span class="badge bg-secondary text-white rounded-circle" style="width:24px;height:24px;line-height:16px;">2</span>' :
                          idx === 2 ? '<span class="badge bg-dark text-white rounded-circle" style="width:24px;height:24px;line-height:16px;">3</span>' :
                          `<span class="text-muted">${idx + 1}</span>`}
                    </td>
                    <td><strong class="text-navy">${escapeHtml(p.product_name)}</strong></td>
                    <td><span class="badge bg-light text-secondary border">${escapeHtml(p.category_name || 'Handcrafted')}</span></td>
                    <td class="text-center fw-bold">${p.total_qty} units</td>
                    <td class="text-end fw-bold text-navy">${formatPeso(p.total_sales)}</td>
                    <td class="text-end"><span class="badge bg-warning-subtle text-dark border">${sharePct}%</span></td>
                </tr>
            `;
        }).join('');
    } else {
        prodTbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No product sales in this selected timeframe.</td></tr>`;
    }

    // 7. Itemized Ledger Table
    renderLedgerRows(data.orders || []);
}

function renderLedgerRows(orders) {
    const tbody = document.getElementById('reportLedgerTableBody');
    const badge = document.getElementById('ledgerCountBadge');
    badge.textContent = `${orders.length} ${orders.length === 1 ? 'Order' : 'Orders'}`;

    if (orders.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block text-secondary text-opacity-50 mb-1"></i>No transaction orders recorded for the selected filter.</td></tr>`;
        return;
    }

    tbody.innerHTML = orders.map(o => {
        const isTakeout = o.order_type.toLowerCase() === 'takeout';
        const channelBadge = isTakeout
            ? '<span class="badge bg-warning text-dark"><i class="bi bi-bag me-1"></i>Takeout</span>'
            : `<span class="badge bg-navy text-white"><i class="bi bi-cup-hot me-1"></i>${escapeHtml(o.table_number || 'Dine-In')}</span>`;

        let payBadge = `<span class="badge bg-light text-dark border">${escapeHtml(o.payment_method)}</span>`;
        if (o.payment_method === 'GCash') payBadge = `<span class="badge bg-info-subtle text-dark border">GCash</span>`;
        else if (o.payment_method === 'Maya') payBadge = `<span class="badge bg-success-subtle text-dark border">Maya</span>`;
        if (o.payment_reference) payBadge += `<br><small class="text-muted fw-mono" style="font-size:0.68rem;">${escapeHtml(o.payment_reference)}</small>`;

        const discStr = parseFloat(o.discount_amount || 0) > 0
            ? `<span class="text-danger">-${formatPeso(o.discount_amount)}</span><br><small class="text-muted" style="font-size:0.68rem;">${escapeHtml(o.discount_type)}</small>`
            : `<span class="text-muted">₱0.00</span>`;

        return `
            <tr class="ledger-row" data-search="${escapeHtml(o.order_number + ' ' + o.customer_name + ' ' + (o.table_number || '') + ' ' + (o.payment_reference || '')).toLowerCase()}">
                <td class="ps-4 fw-mono fw-bold text-navy">${escapeHtml(o.order_number)}</td>
                <td class="text-nowrap small text-muted">${escapeHtml(o.formatted_created_at)}</td>
                <td><strong class="text-dark">${escapeHtml(o.customer_name)}</strong></td>
                <td>${channelBadge}</td>
                <td>
                    <div class="text-truncate small" style="max-width: 240px;" title="${escapeHtml(o.items_summary)}">
                        ${escapeHtml(o.items_summary || 'Standard order')}
                    </div>
                </td>
                <td class="text-end text-muted small">${formatPeso(o.subtotal)}</td>
                <td class="text-end small">${discStr}</td>
                <td class="text-end fw-bold text-navy fs-6">${formatPeso(o.net_total)}</td>
                <td>${payBadge}</td>
                <td class="text-center no-print">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5" onclick="openSalesReceiptModal(${o.id})" title="View Complete Receipt Breakdown">
                        <i class="bi bi-eye me-1"></i> View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function filterLedgerTable() {
    const q = (document.getElementById('ledgerSearchInput').value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.ledger-row');
    rows.forEach(r => {
        if (!q || r.dataset.search.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function openSalesReceiptModal(orderId) {
    if (!currentReportData || !currentReportData.orders) return;
    const order = currentReportData.orders.find(o => parseInt(o.id) === parseInt(orderId));
    if (!order) return;

    currentViewingOrder = order;
    document.getElementById('receiptModalOrderNum').textContent = `Receipt • ${order.order_number}`;
    document.getElementById('receiptModalOrderDate').textContent = `${order.formatted_created_at} • ${order.order_type}`;

    let itemsHtml = '';
    (order.items || []).forEach(it => {
        let addonsArr = [];
        try { addonsArr = JSON.parse(it.addons_json || '[]'); } catch (e) {}

        const isPastry = (it.sugar_level === 'Pastry' || it.size_name === 'Standard Serving');
        let modText = isPastry ? it.ice_level : `${it.sugar_level} • ${it.ice_level}`;
        if (addonsArr.length) modText += ` • +${addonsArr.join(', ')}`;
        if (it.special_notes) modText += ` (${it.special_notes})`;

        itemsHtml += `
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom small">
                <div>
                    <div class="fw-bold text-dark">${it.quantity}x ${escapeHtml(it.product_name)} <span class="badge bg-light text-secondary border">${escapeHtml(it.size_name)}</span></div>
                    <div class="text-muted" style="font-size: 0.72rem;">${escapeHtml(modText)}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-navy">${formatPeso(it.line_total)}</div>
                    <small class="text-muted" style="font-size: 0.7rem;">@ ${formatPeso(it.unit_price)}</small>
                </div>
            </div>
        `;
    });

    const bodyHtml = `
        <div class="border rounded-4 p-3 bg-light mb-3 text-center">
            <h6 class="fw-bold mb-0 text-navy">Raeden’s Bakery and Café</h6>
            <small class="text-muted">Artisanal Bakery &amp; Specialty Café</small>
            <div class="badge bg-navy text-warning px-3 py-1 rounded-pill mt-2 fw-mono">${escapeHtml(order.order_number)}</div>
        </div>

        <div class="small mb-3">
            <div class="row g-1">
                <div class="col-6"><span class="text-muted">Guest:</span> <strong>${escapeHtml(order.customer_name)}</strong></div>
                <div class="col-6"><span class="text-muted">Table/Channel:</span> <strong>${escapeHtml(order.table_number || order.order_type)}</strong></div>
                <div class="col-6"><span class="text-muted">Payment:</span> <strong>${escapeHtml(order.payment_method)} ${order.payment_reference ? `(${escapeHtml(order.payment_reference)})` : ''}</strong></div>
                <div class="col-6"><span class="text-muted">Status:</span> <span class="badge bg-success-subtle text-success">${escapeHtml(order.status)}</span></div>
            </div>
        </div>

        <div class="mb-3">
            <div class="fw-bold small text-navy text-uppercase mb-1" style="letter-spacing: 0.05em;">Purchased Items</div>
            ${itemsHtml || '<div class="text-muted small py-2">No items found for this order.</div>'}
        </div>

        <div class="bg-light p-3 rounded-4 border small">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Gross Subtotal:</span>
                <strong>${formatPeso(order.subtotal)}</strong>
            </div>
            ${parseFloat(order.discount_amount || 0) > 0 ? `
                <div class="d-flex justify-content-between mb-1 text-danger">
                    <span>Discount (${escapeHtml(order.discount_type)}):</span>
                    <strong>-${formatPeso(order.discount_amount)}</strong>
                </div>
            ` : ''}
            <div class="d-flex justify-content-between mb-1 text-muted">
                <span>12% Output VAT:</span>
                <span>${formatPeso(order.vat_amount)}</span>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2 fs-6 fw-bold text-navy">
                <span>Net Total Paid:</span>
                <span>${formatPeso(order.net_total)}</span>
            </div>
        </div>
    `;

    document.getElementById('salesOrderReceiptBody').innerHTML = bodyHtml;
    new bootstrap.Modal(document.getElementById('salesOrderReceiptModal')).show();
}

function printSingleReceipt() {
    window.print();
}

function exportSalesReportCsv() {
    const params = new URLSearchParams({
        action: 'sales_report',
        period: currentReportPeriod,
        date: document.getElementById('repDateInput').value,
        month: document.getElementById('repMonthInput').value,
        year: document.getElementById('repYearSelect').value,
        start_date: document.getElementById('repRangeStart').value,
        end_date: document.getElementById('repRangeEnd').value,
        payment_method: document.getElementById('repPaymentFilter').value,
        order_type: document.getElementById('repOrderTypeFilter').value,
        format: 'csv'
    });
    window.location.href = `api/reports.php?${params.toString()}`;
}

function printSalesReport() {
    window.print();
}

// Hook into Bootstrap Tab Activation
document.addEventListener('DOMContentLoaded', () => {
    const repTabBtn = document.getElementById('tab-reports-btn');
    if (repTabBtn) {
        repTabBtn.addEventListener('shown.bs.tab', () => {
            if (!currentReportData) {
                loadSalesReport();
            }
        });
    }

    // Check query params: /admin?tab=reports or /admin?report=daily
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'reports' || urlParams.get('report')) {
        if (repTabBtn) {
            new bootstrap.Tab(repTabBtn).show();
            if (urlParams.get('report')) {
                setReportPeriod(urlParams.get('report'));
            } else {
                loadSalesReport();
            }
        }
    }
});
</script>
