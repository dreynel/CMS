<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Staff Seating Operations Dashboard View (Sidebar Layout)
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

require_once __DIR__ . '/../includes/inventory.php';

// Staff manages seating confirmation; orders are loaded and processed at POS register
$conciergeScheduledSync = ['dispatched' => [], 'upcoming' => []];

// 1. Daily Concierge Metrics (Real Database Counts)
$todayExpectedStmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE DATE(reservation_date) = CURDATE() AND status != 'Cancelled'");
$todayExpectedCount = (int)$todayExpectedStmt->fetchColumn();

$seatedStmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Checked-In'");
$currentlySeatedCount = (int)$seatedStmt->fetchColumn();

$pendingCheckinStmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'Confirmed'");
$pendingCheckinCount = (int)$pendingCheckinStmt->fetchColumn();

$availTablesStmt = $pdo->query("SELECT COUNT(*) FROM dining_tables WHERE status = 'available'");
$availTablesCount = (int)$availTablesStmt->fetchColumn();

// 2. Dining Tables for Live Floor Plan mini-matrix
$tablesStmt = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code");
$floorTables = $tablesStmt->fetchAll();

// 3. Recent Reservations with Schedule & Linked Kitchen Order
$recentStmt = $pdo->query("
    SELECT r.*, t.name as table_name, t.zone, t.table_code,
           (SELECT COUNT(*) FROM reservation_items ri WHERE ri.reservation_id = r.id) AS item_count,
           (SELECT o.order_number FROM orders o WHERE o.reservation_id = r.id AND o.status != 'Cancelled' ORDER BY o.id DESC LIMIT 1) AS order_number,
           (SELECT o.status FROM orders o WHERE o.reservation_id = r.id AND o.status != 'Cancelled' ORDER BY o.id DESC LIMIT 1) AS order_status
    FROM reservations r 
    LEFT JOIN dining_tables t ON r.table_id = t.id 
    ORDER BY
        CASE WHEN DATE(r.reservation_date) = CURDATE() THEN 0 ELSE 1 END,
        r.created_at DESC 
    LIMIT 8
");
$recentReservations = $recentStmt->fetchAll();
?>

<!-- HTML5-QRCode Scanner Library CDN -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<div class="dashboard-layout">
    
    <!-- Left Staff Concierge Navigation Sidebar -->
    <aside class="dashboard-sidebar p-3 shadow-xs">
        <div>
            <!-- Station Brand & Logo -->
            <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow-xs border border-secondary-subtle" style="width: 40px; height: 40px; object-fit: cover;">
                <div>
                    <div class="fw-black text-navy small mb-0">Raeden's Staff</div>
                    <span class="badge bg-navy text-white fw-bold" style="background-color: var(--brand-primary) !important; font-size: 0.65rem;">STAFF DESK</span>
                </div>
            </div>

            <?php
            $conciergeUser = $_SESSION['user'] ?? ['name' => 'Maria Santos', 'role' => 'staff'];
            $conciergeInitials = strtoupper(substr($conciergeUser['name'], 0, 2));
            ?>
            <!-- Staff Profile Card -->
            <div class="sidebar-profile-card mb-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-navy text-warning fw-black d-flex align-items-center justify-content-center shadow-xs" style="width: 40px; height: 40px; font-size: 0.95rem;">
                        <?= htmlspecialchars($conciergeInitials) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-6" style="line-height: 1.2;"><?= htmlspecialchars($conciergeUser['name']) ?></div>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Staff Floor Attendant • Dining Floor</small>
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-top d-flex justify-content-between align-items-center text-muted" style="font-size: 0.72rem;">
                    <span>Desk Shift:</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active on Floor</span>
                </div>
            </div>

            <!-- Staff Station Navigation -->
            <div class="sidebar-section-title">Staff Modules</div>
            <nav class="nav flex-column gap-1 mb-3">
                <a href="#scanner-terminal-section" class="sidebar-nav-item active">
                    <i class="bi bi-camera-video-fill text-warning"></i>
                    <span>Optical QR Scanner</span>
                </a>
                <a href="#quick-tray-section" class="sidebar-nav-item">
                    <i class="bi bi-lightning-charge-fill text-primary"></i>
                    <span>Recent VIP Passes</span>
                </a>
                <a href="#validation-hud-section" class="sidebar-nav-item">
                    <i class="bi bi-card-checklist text-success"></i>
                    <span>Validation & Seating</span>
                </a>
                <a href="#floor-matrix-section" class="sidebar-nav-item">
                    <i class="bi bi-grid-3x3-gap-fill text-info"></i>
                    <span>Floor Plan Matrix</span>
                </a>
            </nav>

            <!-- Shift Capacity Mini-Summary -->
            <div class="sidebar-section-title">Shift Capacity</div>
            <div class="p-2.5 rounded-3 bg-light border mb-3 small">
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="text-muted"><i class="bi bi-people-fill text-primary me-1"></i>Expected:</span>
                    <strong class="text-navy"><?= $todayExpectedCount ?> Guests</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="text-muted"><i class="bi bi-person-check-fill text-success me-1"></i>Seated:</span>
                    <strong class="text-success"><?= $currentlySeatedCount ?> Tables</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="text-muted"><i class="bi bi-grid text-info me-1"></i>Free Pods:</span>
                    <strong class="text-info-emphasis"><?= $availTablesCount ?> Pods</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><i class="bi bi-hourglass-split text-warning me-1"></i>Awaiting:</span>
                    <strong class="text-warning"><?= $pendingCheckinCount ?> Passes</strong>
                </div>
            </div>

            <!-- Station Session -->
            <div class="sidebar-section-title">Active Session</div>
            <div class="p-2.5 rounded-3 bg-light border mb-3 small">
                <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Staff Station Online</span>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-bold py-1.5" onclick="globalStaffLogout()">
                    <i class="bi bi-box-arrow-right me-1.5"></i> Sign Out
                </button>
            </div>
        </div>

        <!-- Sidebar Footer Status -->
        <div class="pt-3 border-top mt-3">
            <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.72rem;">
                <span class="d-flex align-items-center gap-1.5">
                    <i class="bi bi-qr-code text-navy"></i> QR Engine
                </span>
                <span class="badge bg-light text-secondary border">HTML5-QRCode v2.3</span>
            </div>
        </div>
    </aside>

    <!-- Main Staff Workspace -->
    <main class="dashboard-main p-3 p-lg-4">
        
        <!-- Top Action Banner -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
            <div>
                <h2 class="fw-black text-navy mb-0 tracking-tight fs-3">Staff Check-In &amp; Seating Hub</h2>
                <p class="text-muted small mb-0">Raeden’s Bakery and Café • Staff Seating &amp; Optical QR Verification</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> Scanner Ready
                </span>
                <a href="booking" target="_blank" class="btn btn-outline-navy btn-sm rounded-pill px-3 py-1.5 fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> New Walk-in Reservation
                </a>
            </div>
        </div>

        <!-- 4 Modern Metric KPI Cards -->
        <div class="row g-3 mb-4">
            
            <!-- Metric 1: Expected Bookings Today -->
            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-primary">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Expected Today</span>
                            <h3 class="fw-black text-navy mb-0 mt-1"><?= $todayExpectedCount ?> Guests</h3>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small mt-1">
                                Scheduled Reservations
                            </span>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 54px; height: 54px;">
                            <i class="bi bi-calendar2-event"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Currently Seated -->
            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-success">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Currently Seated</span>
                            <h3 class="fw-black text-success mb-0 mt-1"><?= $currentlySeatedCount ?> Tables</h3>
                            <span class="badge bg-success-subtle text-success border border-success-subtle small mt-1">
                                Active in Mini Hotel
                            </span>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 54px; height: 54px;">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Free Tables / Study Pods -->
            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-info">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Free Tables / Pods</span>
                            <h3 class="fw-black text-navy mb-0 mt-1"><?= $availTablesCount ?> Available</h3>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle small mt-1">
                                Ready for Walk-ins
                            </span>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 54px; height: 54px;">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Pending Check-Ins -->
            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white card-lift overflow-hidden border-start border-4 border-warning">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Pending Check-Ins</span>
                            <h3 class="fw-black text-warning mb-0 mt-1"><?= $pendingCheckinCount ?> Awaiting</h3>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle small mt-1">
                                Pass Not Scanned Yet
                            </span>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-dark d-flex align-items-center justify-content-center p-3 fs-3 shadow-xs" style="width: 54px; height: 54px;">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 15-Minute Arrival & Scheduled Order Auto-Dispatch Alert Banner -->
        <div id="concierge15mAlertBanner" class="mb-4" style="display: none;"></div>

        <!-- Main Concierge Dashboard Split Layout -->
        <div class="row g-4">
            
            <!-- Left Column: Optical Scanner & Quick Test Tray -->
            <div class="col-lg-6">
                
                <!-- Scanner Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="scanner-terminal-section">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <h5 class="fw-bold text-navy mb-1">
                            <i class="bi bi-camera-video-fill text-warning me-2"></i>Optical QR Camera Scanner Terminal
                        </h5>
                        <p class="text-muted small mb-0">Aim device camera at customer's smartphone screen or printed VIP pass.</p>
                    </div>

                    <div class="card-body p-4">
                        
                        <!-- Viewfinder Frame with Laser Scan Simulation -->
                        <div class="qr-viewfinder position-relative rounded-4 overflow-hidden mb-3 shadow-xs" style="min-height: 280px;">
                            <div id="qrReader" style="width: 100%;"></div>
                            <div id="laserLine" class="qr-laser-line" style="display: none;"></div>
                            <div id="scannerOverlay" class="position-absolute top-50 start-50 translate-middle text-center text-white" style="display: none;">
                                <div class="spinner-border text-warning mb-2" role="status"></div>
                                <div class="small fw-bold">Connecting Optical Camera Feed...</div>
                            </div>
                        </div>

                        <!-- Camera Control Buttons -->
                        <div class="d-flex gap-2 mb-4">
                            <button type="button" class="btn btn-warning flex-fill rounded-pill fw-bold text-dark shadow-xs" id="btnStartScan" onclick="startScanner()">
                                <i class="bi bi-camera me-1"></i> Start Camera Scan
                            </button>
                            <button type="button" class="btn btn-outline-danger flex-fill rounded-pill fw-bold" id="btnStopScan" onclick="stopScanner()" disabled>
                                <i class="bi bi-stop-circle me-1"></i> Stop Camera
                            </button>
                        </div>

                        <!-- Manual Input Fallback -->
                        <div class="border-top pt-3">
                            <label class="form-label small fw-bold text-secondary">Manual Reservation Code / QR Token / Phone Lookup:</label>
                            <form onsubmit="handleManualLookup(event)" class="d-flex gap-2">
                                <input type="text" id="manualTokenInput" class="form-control rounded-pill" placeholder="e.g. RES-20261026-001, QR Token, or Phone">
                                <button type="submit" class="btn btn-navy px-4 rounded-pill fw-bold text-nowrap" id="btnManualVerify">
                                    Verify Pass
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- Active Bookings & Scheduled Orders Tray -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="quick-tray-section">
                    <div class="card-header bg-white border-0 pt-3 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-navy mb-0">
                                <i class="bi bi-clock-history text-warning me-1"></i> Active Bookings & Scheduled Pre-Orders
                            </h6>
                            <small class="text-muted" style="font-size: 0.72rem;">Pre-orders auto-dispatch to Kitchen 15 mins before arrival</small>
                        </div>
                        <button type="button" class="btn btn-light btn-sm border rounded-pill fw-bold small" onclick="refreshConciergeDashboard()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2" id="conciergeRecentGrid">
                            <?php if (empty($recentReservations)): ?>
                                <div class="col-12 text-center py-4 text-muted small">
                                    <i class="bi bi-calendar2-x fs-3 d-block mb-1 opacity-50"></i>
                                    No reservations recorded yet. Click <strong>New Walk-in Reservation</strong> above to create a booking.
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentReservations as $rr):
                                    $rrSched = inv_parse_reservation_schedule($rr);
                                    $hasItems = ((int)($rr['item_count'] ?? 0) > 0);
                                    $ordNum = $rr['order_number'] ?? null;
                                    $ordStatus = $rr['order_status'] ?? null;
                                ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 border rounded-3 p-2.5 cursor-pointer card-lift shadow-xs bg-light bg-opacity-50" 
                                             onclick="lookupCode('<?= htmlspecialchars($rr['qr_token']) ?>')">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong class="small text-navy fw-mono"><?= htmlspecialchars($rr['reservation_number']) ?></strong>
                                                <span class="badge <?= $rr['status'] === 'Checked-In' ? 'bg-success' : ($rr['status'] === 'Confirmed' ? 'bg-warning text-dark' : 'bg-secondary') ?> small" style="font-size: 0.68rem;"><?= htmlspecialchars($rr['status']) ?></span>
                                            </div>
                                            <div class="small fw-bold text-dark text-truncate"><?= htmlspecialchars($rr['customer_name']) ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars($rr['table_code'] ?? 'TBL') ?> (<?= htmlspecialchars($rr['zone'] ?? '') ?>) • Arrival: <strong><?= htmlspecialchars($rrSched['arrival_formatted']) ?></strong>
                                            </div>
                                            <?php if ($hasItems): ?>
                                                <div class="mt-1 pt-1 border-top d-flex justify-content-between align-items-center" style="font-size: 0.68rem;">
                                                    <?php if ($ordNum): ?>
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                            <i class="bi bi-fire me-1"></i>KDS <?= htmlspecialchars($ordNum) ?> (<?= htmlspecialchars($ordStatus) ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                                                            <i class="bi bi-alarm me-1"></i>Prep @ <?= htmlspecialchars($rrSched['prep_formatted']) ?> (T-15m)
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="fw-bold text-navy">₱<?= number_format((float)$rr['total_amount'], 2) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Verification Result HUD & Live Floor Matrix -->
            <div class="col-lg-6">
                
                <!-- Live Pass Validation Result HUD -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="validation-hud-section">
                    <div class="card-header bg-navy text-white rounded-top-4 py-3 px-4" style="background: var(--brand-primary-gradient);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-card-checklist text-warning me-2"></i>Pass Validation & Seating Result
                            </h5>
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">Live Station</span>
                        </div>
                    </div>

                    <div class="card-body p-4" id="validationResultBox">
                        
                        <!-- Placeholder State (When no scan yet) -->
                        <div class="text-center py-5 my-auto" id="noScanState">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center text-secondary mb-3 shadow-xs border" style="width: 84px; height: 84px; font-size: 2.4rem;">
                                <i class="bi bi-qr-code-scan text-navy"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Ready to Scan Customer Pass</h5>
                            <p class="text-muted small mb-0" style="max-width: 340px; margin: 0 auto;">Scan a customer QR pass or select a booking on the left to inspect their table assignment, T-15m scheduled order, and check in or release their table.</p>
                        </div>

                        <!-- Scanned Card Result (Rendered when valid token scanned) -->
                        <div id="scannedCard" style="display: none;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1 mb-1" id="resStatusBadge">Confirmed</span>
                                    <h4 class="fw-black text-navy fw-mono mb-0" id="resNumberDisplay">RES-2026-000</h4>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-secondary border px-3 py-2 fw-mono small d-block mb-1" id="resTokenDisplay">--</span>
                                    <a href="#" target="_blank" id="btnOpenCustomerPass" class="btn btn-outline-navy btn-sm rounded-pill py-0 px-2" style="font-size: 0.7rem;">
                                        <i class="bi bi-qr-code me-1"></i>View Guest Pass
                                    </a>
                                </div>
                            </div>

                            <!-- Prominent Assigned Seating Display for Staff Concierge -->
                            <div class="p-3.5 rounded-4 text-center mb-3 shadow-xs border" style="background: linear-gradient(135deg, #0b1a30 0%, #17325c 100%); color: #fff;">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-1 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                    <i class="bi bi-geo-alt-fill me-1"></i> Customer Assigned Seating
                                </span>
                                <div class="display-6 fw-black text-warning my-1" id="resTableCodeBig">TBL-01</div>
                                <div class="fw-bold fs-6 text-white" id="resTableNameBig">Assigned Dining Table / Seat</div>
                                <div class="small text-light text-opacity-75" id="resZoneNameBig">Dining Floor • Ready for Seating</div>
                            </div>

                            <!-- 15-Minute Grace Period Expired Warning on Scanned Pass -->
                            <div id="cardOverdueAlert" class="alert alert-danger border-2 border-danger rounded-4 p-2.5 mb-3 small d-flex align-items-center justify-content-between shadow-xs" style="display: none;">
                                <div>
                                    <i class="bi bi-exclamation-triangle-fill text-danger me-1 fs-6"></i>
                                    <strong>15-Minute Grace Period Expired:</strong>
                                    <span class="text-danger-emphasis">Guest is <strong id="cardOverdueMins">0</strong> mins late. You may seat guest or release this table to Available.</span>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm rounded-pill fw-bold text-nowrap px-3 shadow-xs" onclick="triggerForfeitCurrentLoaded()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Release Table
                                </button>
                            </div>

                            <!-- Guest & Table Info Callout -->
                            <div class="bg-light p-3 rounded-4 border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                        <i class="bi bi-shield-check me-1"></i> Verified Reservation Pass
                                    </span>
                                    <span class="badge bg-light text-muted border small" id="resPhoneBadge"><i class="bi bi-telephone me-1"></i>--</span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <span class="text-muted small">Guest Name:</span>
                                        <strong class="d-block text-dark fs-6" id="resGuestName">--</strong>
                                        <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.72rem;" id="resGuestType">Student</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted small">Assigned Seating:</span>
                                        <strong class="d-block text-navy fs-6" id="resTableName">--</strong>
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;" id="resZoneName">Study Hub</span>
                                    </div>
                                    <div class="col-sm-6 border-top pt-2 mt-2">
                                        <span class="text-muted small">Date & Arrival Time:</span>
                                        <div class="fw-bold text-dark small" id="resDateTime">--</div>
                                    </div>
                                    <div class="col-sm-6 border-top pt-2 mt-2">
                                        <span class="text-muted small">Seating Policy:</span>
                                        <div class="fw-bold text-warning small" id="resPrepSchedule"><i class="bi bi-clock-history me-1"></i>15-Min Holding Grace Period</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pre-Ordered Drinks & Meals List -->
                            <div class="card border rounded-4 mb-3 overflow-hidden">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
                                    <span class="fw-bold small text-navy"><i class="bi bi-cup-hot-fill text-warning me-1"></i> Pre-Ordered Refreshments (Editable in POS)</span>
                                    <span class="badge bg-navy text-warning rounded-pill" id="resItemCountBadge">0 items</span>
                                </div>
                                <div class="card-body p-3" id="resItemsList" style="max-height: 160px; overflow-y: auto;">
                                    <div class="text-muted small text-center py-2">No items pre-ordered.</div>
                                </div>
                                <div class="card-footer bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                    <span class="small fw-bold text-secondary">Total Amount Due at Counter:</span>
                                    <span class="fw-bold text-navy fs-6" id="resTotalAmount">₱0.00</span>
                                </div>
                            </div>

                            <!-- Concierge Action Buttons -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success w-100 py-3 rounded-pill fw-black fs-5 shadow-sm" id="btnCheckIn" onclick="submitCheckIn()">
                                    <i class="bi bi-person-check-fill me-2"></i> Confirm Guest Onsite &amp; Occupy Table
                                </button>
                                <a href="pos" id="btnOpenInPosRegister" class="btn btn-warning text-dark w-100 py-2.5 rounded-pill fw-bold fs-6 shadow-xs">
                                    <i class="bi bi-cart3 me-2"></i> Load &amp; Process Pre-Order in POS Register
                                </a>
                                <button type="button" class="btn btn-outline-danger w-100 py-2 rounded-pill fw-bold" id="btnReleaseOverdue" style="display: none;" onclick="triggerForfeitCurrentLoaded()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Release Table to Available (15m Expired)
                                </button>
                                <button type="button" class="btn btn-outline-secondary w-100 py-2 rounded-pill fw-bold" id="btnCompleteVisit" style="display: none;" onclick="completeVisitAndFreeTable()">
                                    <i class="bi bi-door-open me-1"></i> Complete Visit &amp; Free Table
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Mini Hotel Live Seating & Floor Occupancy Matrix Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="floor-matrix-section">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-navy mb-0">
                                <i class="bi bi-grid-3x3-gap-fill text-warning me-1"></i> Floor Plan Seating Matrix
                            </h6>
                            <small class="text-muted">Live table states inside Mini Hotel</small>
                        </div>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Free</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Occupied</span>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle">Reserved</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <?php foreach ($floorTables as $ft): 
                                $st = strtolower($ft['status']);
                                $chipColor = $st === 'available' ? 'border-success text-success bg-success-subtle' : ($st === 'reserved' ? 'border-warning text-dark bg-warning-subtle' : 'border-danger text-danger bg-danger-subtle');
                            ?>
                                <div class="col-sm-3 col-4">
                                    <div class="p-2 text-center rounded-3 border <?= $chipColor ?> shadow-xs cursor-pointer card-lift" title="<?= htmlspecialchars($ft['name']) ?> (<?= $ft['zone'] ?>)">
                                        <div class="fw-bold fw-mono small"><?= htmlspecialchars($ft['table_code']) ?></div>
                                        <div class="text-truncate" style="font-size: 0.65rem;"><?= htmlspecialchars($ft['zone']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>
</div>

<script>
let html5QrCode = null;
let currentLoadedReservation = null;

// Start HTML5 QR Scanner
async function startScanner() {
    const readerDiv = document.getElementById('qrReader');
    const overlay = document.getElementById('scannerOverlay');
    const laser = document.getElementById('laserLine');

    overlay.style.display = 'block';

    try {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qrReader");
        }

        const config = { fps: 10, qrbox: { width: 230, height: 230 } };

        await html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                // QR scanned successfully
                handleScanSuccess(decodedText);
            },
            (errorMessage) => {
                // parse error, ignore as camera feeds continuously
            }
        );

        overlay.style.display = 'none';
        laser.style.display = 'block';
        document.getElementById('btnStartScan').disabled = true;
        document.getElementById('btnStopScan').disabled = false;
        showToast('Camera active. Point at customer QR pass.', 'info');
    } catch (err) {
        overlay.style.display = 'none';
        showToast('Camera error: ' + (err.message || err), 'danger');
    }
}

// Stop Scanner
async function stopScanner() {
    if (html5QrCode) {
        try {
            await html5QrCode.stop();
            document.getElementById('laserLine').style.display = 'none';
            document.getElementById('btnStartScan').disabled = false;
            document.getElementById('btnStopScan').disabled = true;
            showToast('Camera stopped.', 'secondary');
        } catch (e) {
            console.error('Stop error:', e);
        }
    }
}

// Cleanup camera stream upon dynamic navigation
window.addEventListener('pageUnload', () => {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().catch(console.error);
    }
}, { once: true });

function handleScanSuccess(token) {
    stopScanner();
    lookupCode(token);
}

function handleManualLookup(e) {
    e.preventDefault();
    const code = document.getElementById('manualTokenInput').value.trim();
    if (!code) {
        showToast('Please enter a reservation code, token, or phone number.', 'warning');
        return;
    }
    lookupCode(code);
}

async function lookupCode(token) {
    showToast('Validating QR pass credentials...', 'info');

    try {
        const res = await fetch(`api/scanner.php?action=lookup&token=${encodeURIComponent(token)}`);
        const data = await res.json();

        if (data.success && data.reservation) {
            currentLoadedReservation = data.reservation;
            renderReservationDetails(data.reservation, data.items || [], data.order || data.reservation.order || null, data.schedule || data.reservation.schedule || null);
            showToast('Pass verified successfully!', 'success');
        } else {
            showToast(data.message || 'Pass not found or invalid token.', 'danger');
        }
    } catch (err) {
        showToast('Network error during lookup.', 'danger');
    }
}

function renderReservationDetails(res, items, linkedOrder = null, sched = null) {
    document.getElementById('noScanState').style.display = 'none';
    document.getElementById('scannedCard').style.display = 'block';

    const statusBadge = document.getElementById('resStatusBadge');
    statusBadge.textContent = res.status;
    statusBadge.className = `badge ${res.status === 'Checked-In' ? 'bg-success' : (res.status === 'Confirmed' ? 'bg-primary' : 'bg-secondary')} fw-bold px-2 py-1 mb-1`;

    document.getElementById('resNumberDisplay').textContent = res.reservation_number || res.reference_code;
    document.getElementById('resTokenDisplay').textContent = (res.qr_token || '').substring(0, 18) + '...';
    document.getElementById('btnOpenCustomerPass').href = `pass?token=${encodeURIComponent(res.qr_token || '')}`;
    document.getElementById('resPhoneBadge').innerHTML = `<i class="bi bi-telephone me-1"></i>${res.phone || res.customer_phone || 'N/A'} • ${res.guest_count || 1} Guest(s)`;
    document.getElementById('resGuestName').textContent = res.customer_name;
    document.getElementById('resGuestType').textContent = res.customer_type || 'Guest';
    document.getElementById('resTableName').textContent = `${res.table_code || 'TBL'} - ${res.table_name || 'Assigned'}`;
    document.getElementById('resZoneName').textContent = res.zone || 'Main Hall';
    document.getElementById('resDateTime').textContent = `${res.reservation_date} • ${res.time_slot}`;

    document.getElementById('resTableCodeBig').textContent = res.table_code || ('TBL-' + res.table_id);
    document.getElementById('resTableNameBig').textContent = `${res.table_name || 'Assigned Table'} • ${res.zone || 'Mini Hotel'}`;
    document.getElementById('resZoneNameBig').textContent = `Customer: ${res.customer_name} • Capacity: ${res.guest_count || 1} Guest(s)`;

    // Check if 15-minute grace period has passed (Overdue Alert)
    const cardOverdue = document.getElementById('cardOverdueAlert');
    const btnReleaseOverdue = document.getElementById('btnReleaseOverdue');
    const scheduleInfo = sched || res.schedule || null;
    const isOverdue = (res.status === 'Confirmed' && (res.is_overdue || (scheduleInfo && scheduleInfo.minutes_until_arrival <= -15)));

    if (isOverdue) {
        const minsLate = res.minutes_overdue || (scheduleInfo ? Math.abs(scheduleInfo.minutes_until_arrival) : 15);
        cardOverdue.style.display = 'flex';
        document.getElementById('cardOverdueMins').textContent = minsLate;
        if (btnReleaseOverdue) btnReleaseOverdue.style.display = 'block';
    } else {
        cardOverdue.style.display = 'none';
        if (btnReleaseOverdue) btnReleaseOverdue.style.display = 'none';
    }

    if (scheduleInfo) {
        document.getElementById('resPrepSchedule').innerHTML = `<i class="bi bi-clock-history me-1 text-warning"></i>Arrival: <strong>${scheduleInfo.arrival_formatted}</strong> (Held for 15m)`;
    } else {
        document.getElementById('resPrepSchedule').textContent = '15 mins holding grace period';
    }

    document.getElementById('resItemCountBadge').textContent = `${items.length} item(s)`;
    document.getElementById('resTotalAmount').textContent = formatPeso(res.total_amount);

    // Update POS button link with token
    const btnOpenInPos = document.getElementById('btnOpenInPosRegister');
    if (btnOpenInPos) {
        btnOpenInPos.href = `pos?scan=${encodeURIComponent(res.qr_token || res.reservation_number)}`;
    }

    const itemsContainer = document.getElementById('resItemsList');
    if (items.length === 0) {
        itemsContainer.innerHTML = `<div class="text-muted small text-center py-2">No pre-ordered items. Guest can order directly at the POS Cashier.</div>`;
    } else {
        itemsContainer.innerHTML = items.map(it => `
            <div class="d-flex justify-content-between align-items-center mb-1.5 pb-1 border-bottom small">
                <div>
                    <strong class="text-dark">${it.quantity}x ${it.product_name}</strong>
                    <span class="badge bg-light text-secondary border ms-1">${it.size_name || 'Standard'}</span>
                    <div class="text-muted" style="font-size: 0.72rem;">
                        ${(it.sugar_level === 'Pastry' || it.size_name === 'Standard Serving') ? `<i class="bi bi-cake2 text-warning me-1"></i>${it.ice_level}` : `${it.sugar_level || '50%'} • ${it.ice_level || 'Regular Ice'}`}
                    </div>
                </div>
                <span class="fw-bold text-navy">${formatPeso(it.line_total || it.subtotal || (it.unit_price * it.quantity))}</span>
            </div>
        `).join('');
    }

    const checkInBtn = document.getElementById('btnCheckIn');
    const btnCompleteVisit = document.getElementById('btnCompleteVisit');

    if (res.status === 'Checked-In') {
        checkInBtn.disabled = true;
        checkInBtn.className = 'btn btn-secondary w-100 py-2.5 rounded-pill fw-bold fs-6';
        checkInBtn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Guest Seated & Verified Onsite (Occupied)';
        btnCompleteVisit.style.display = 'block';
    } else if (res.status === 'Confirmed') {
        checkInBtn.disabled = false;
        checkInBtn.className = 'btn btn-success w-100 py-3 rounded-pill fw-black fs-5 shadow-sm';
        checkInBtn.innerHTML = '<i class="bi bi-person-check-fill me-2"></i> Confirm Guest Onsite & Occupy Table';
        btnCompleteVisit.style.display = 'none';
    } else if (res.status === 'No-Show') {
        checkInBtn.disabled = true;
        checkInBtn.className = 'btn btn-outline-danger w-100 py-2.5 rounded-pill fw-bold fs-6';
        checkInBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i> Booking Forfeited: 15-Minute Grace Period Expired';
        btnCompleteVisit.style.display = 'none';
    } else {
        checkInBtn.disabled = true;
        checkInBtn.className = 'btn btn-outline-secondary w-100 py-2.5 rounded-pill fw-bold fs-6';
        checkInBtn.innerHTML = `<i class="bi bi-info-circle me-1"></i> Status: ${res.status}`;
        btnCompleteVisit.style.display = 'none';
    }

    if (window.innerWidth < 992) {
        const hud = document.getElementById('validation-hud-section');
        if (hud) hud.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// 1-Click Check-In Action
async function submitCheckIn() {
    if (!currentLoadedReservation) return;

    const btn = document.getElementById('btnCheckIn');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Seating Guest & Occupying Table...`;

    try {
        const res = await fetch('api/scanner.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'checkin', reservation_id: currentLoadedReservation.id })
        });
        const data = await res.json();

        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Guest Verified & Seated!',
                    html: `
                        <div class="mb-2 fs-6"><strong>${data.message}</strong></div>
                        <div class="alert alert-light border small text-start p-2.5 rounded-3 mb-0">
                            <strong><i class="bi bi-info-circle text-primary me-1"></i>Next Step:</strong> Guide customer to <strong>${data.table_display}</strong>. Customer can now present their Dynamic QR Pass at the <strong>Cashier POS</strong> to load, customize, and pay for their order!
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Understood',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'btn btn-navy px-4 py-2 rounded-pill fw-bold'
                    }
                });
            } else {
                showToast(data.message, 'success');
            }
            await lookupCode(currentLoadedReservation.qr_token);
            refreshConciergeDashboard();
        } else {
            showToast(data.message || 'Check-in failed.', 'danger');
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Network error during check-in.', 'danger');
        btn.disabled = false;
    }
}

// 1-Click Release Overdue Table to Available (15-Minute Grace Period Expired)
async function releaseTableToAvailable(reservationId, tableId, tableCode, customerName) {
    let confirmRelease = false;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: `Release ${tableCode} to Available?`,
            html: `
                Booking for <strong>${escapeHtml(customerName)}</strong> is past the <strong>15-minute seating grace period</strong> without onsite arrival.<br><br>
                Releasing this table will mark the booking as <strong>No-Show</strong> and set <strong>${tableCode}</strong> to <strong>Available</strong> for walk-in guests and diners immediately.
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> Yes, Release Table',
            cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-danger px-4 py-2 rounded-pill fw-bold me-2',
                cancelButton: 'btn btn-outline-secondary px-4 py-2 rounded-pill fw-bold'
            }
        });
        confirmRelease = result.isConfirmed;
    } else {
        confirmRelease = confirm(`Release table ${tableCode} to Available? (Mark ${customerName} as No-Show)`);
    }

    if (!confirmRelease) return;

    try {
        const res = await fetch('api/scanner.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'release_table',
                reservation_id: reservationId,
                table_id: tableId,
                reason: '15-minute seating grace period expired without customer arrival.'
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            refreshConciergeDashboard();
            if (currentLoadedReservation && currentLoadedReservation.id == reservationId) {
                lookupCode(currentLoadedReservation.qr_token);
            }
        } else {
            showToast(data.message || 'Could not release table.', 'danger');
        }
    } catch (err) {
        showToast('Network error releasing table.', 'danger');
    }
}

// Forfeit the currently viewed reservation in HUD
function triggerForfeitCurrentLoaded() {
    if (!currentLoadedReservation) return;
    releaseTableToAvailable(
        currentLoadedReservation.id,
        currentLoadedReservation.table_id,
        currentLoadedReservation.table_code || ('Table #' + currentLoadedReservation.table_id),
        currentLoadedReservation.customer_name
    );
}

// Complete Guest Visit & Release Dining Table to Available
async function completeVisitAndFreeTable() {
    if (!currentLoadedReservation) return;

    try {
        const res = await fetch('api/scanner.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'checkout',
                reservation_id: currentLoadedReservation.id,
                table_id: currentLoadedReservation.table_id
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            await lookupCode(currentLoadedReservation.qr_token);
            refreshConciergeDashboard();
        } else {
            showToast(data.message || 'Could not complete visit.', 'danger');
        }
    } catch (err) {
        showToast('Network error releasing table.', 'danger');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Auto-poll Staff Concierge Dashboard & 15-Minute Seating Grace Alerts every 12 seconds
async function refreshConciergeDashboard() {
    try {
        const res = await fetch('api/scanner.php');
        const data = await res.json();
        if (!data.success) return;

        // Render 15-Minute Seating Grace Period Overdue Alerts Banner
        const alertBanner = document.getElementById('concierge15mAlertBanner');
        const overdue = data.overdue_alerts || [];
        const upcoming = data.upcoming_arrivals || [];

        if (overdue.length > 0 || upcoming.length > 0) {
            alertBanner.style.display = 'block';
            let html = '';

            // 1. Critical Overdue Alert (15+ Minutes Past Schedule)
            if (overdue.length > 0) {
                html += `
                    <div class="alert alert-danger border-2 border-danger shadow-sm rounded-4 mb-2 p-3 bg-danger-subtle bg-opacity-75">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="spinner-grow spinner-grow-sm text-danger" role="status"></span>
                                <h6 class="fw-black text-danger mb-0 fs-6">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> 15-MINUTE GRACE PERIOD EXPIRED (${overdue.length} Unclaimed Table(s))
                                </h6>
                            </div>
                            <span class="badge bg-danger text-white fw-bold px-2.5 py-1 rounded-pill">Action: Forfeit or Seat</span>
                        </div>
                        <p class="small text-danger-emphasis mb-2.5" style="font-size: 0.8rem;">
                            These reserved seats have not been claimed within 15 minutes of the scheduled arrival time. As Staff, you can release the table to <strong>Available</strong> for walk-in guests:
                        </p>
                        <div class="row g-2">
                            ${overdue.map(ov => `
                                <div class="col-md-6 col-12">
                                    <div class="bg-white border border-danger-subtle rounded-3 p-2.5 shadow-xs d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="d-flex align-items-center gap-1.5 mb-1">
                                                <strong class="text-navy fw-black">${escapeHtml(ov.table_code || 'TBL')}</strong>
                                                <span class="badge bg-danger text-white fw-bold" style="font-size: 0.65rem;">+${ov.minutes_overdue}m LATE</span>
                                            </div>
                                            <div class="small fw-bold text-dark text-truncate" style="max-width: 200px;">${escapeHtml(ov.customer_name)}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">Arrival: <strong>${escapeHtml(ov.schedule.arrival_formatted)}</strong> • ${escapeHtml(ov.zone || '')}</div>
                                        </div>
                                        <div class="d-flex flex-column gap-1 ms-2">
                                            <button type="button" class="btn btn-danger btn-sm rounded-pill fw-bold text-nowrap py-1 px-2.5 shadow-xs" style="font-size: 0.72rem;" onclick="releaseTableToAvailable(${ov.id}, ${ov.table_id}, '${escapeHtml(ov.table_code)}', '${escapeHtml(ov.customer_name)}')">
                                                <i class="bi bi-arrow-repeat me-1"></i> Free Table
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill fw-bold text-nowrap py-0.5 px-2" style="font-size: 0.68rem;" onclick="lookupCode('${ov.qr_token}')">
                                                <i class="bi bi-person-check me-1"></i> Inspect Pass
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            // 2. Arriving Soon / Currently Within 15-Minute Grace Window
            if (upcoming.length > 0) {
                html += `
                    <div class="alert alert-warning border-warning-subtle shadow-xs rounded-4 mb-0 p-3 bg-light">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="badge bg-warning text-dark me-2"><i class="bi bi-clock-history me-1"></i>EXPECTED ARRIVALS</span>
                                <strong class="text-navy">${upcoming.length} guest(s) expected right now / on schedule:</strong>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            ${upcoming.map(u => `
                                <button type="button" class="btn btn-sm btn-white bg-white border border-warning-subtle rounded-pill fw-bold shadow-xs" onclick="lookupCode('${u.qr_token}')">
                                    <i class="bi bi-person text-navy me-1"></i>${escapeHtml(u.customer_name)} (${escapeHtml(u.table_code)}) • Scheduled ${escapeHtml(u.schedule.arrival_formatted)}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            alertBanner.innerHTML = html;
        } else {
            alertBanner.style.display = 'none';
        }

        // Live Dynamic Update of Floor Plan Seating Matrix
        if (Array.isArray(data.tables)) {
            const overdueTableIds = new Set((data.overdue_alerts || []).map(o => parseInt(o.table_id)));
            const matrixContainer = document.querySelector('#floor-matrix-section .card-body .row');
            if (matrixContainer) {
                matrixContainer.innerHTML = data.tables.map(ft => {
                    const st = String(ft.status).toLowerCase();
                    const isOverdueTable = overdueTableIds.has(parseInt(ft.id));
                    let chipColor = st === 'available' 
                        ? 'border-success text-success bg-success-subtle' 
                        : (st === 'reserved' 
                            ? (isOverdueTable ? 'border-danger text-danger bg-danger-subtle border-2' : 'border-warning text-dark bg-warning-subtle') 
                            : 'border-danger text-danger bg-danger-subtle');
                    return `
                        <div class="col-sm-3 col-4">
                            <div class="p-2 text-center rounded-3 border ${chipColor} shadow-xs cursor-pointer card-lift position-relative" 
                                 title="${escapeHtml(ft.name)} (${escapeHtml(ft.zone)})"
                                 onclick="handleTableClick(${ft.id}, '${escapeHtml(ft.table_code)}', '${st}', ${isOverdueTable})">
                                ${isOverdueTable ? '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">15m+</span>' : ''}
                                <div class="fw-bold fw-mono small">${escapeHtml(ft.table_code)}</div>
                                <div class="text-truncate" style="font-size: 0.65rem;">${escapeHtml(ft.zone || '')}</div>
                                <span class="badge ${st === 'available' ? 'bg-success' : (st === 'reserved' ? (isOverdueTable ? 'bg-danger' : 'bg-warning text-dark') : 'bg-danger')}" style="font-size: 0.6rem;">
                                    ${isOverdueTable ? 'Overdue' : (st.charAt(0).toUpperCase() + st.slice(1))}
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

    } catch (e) {
        // Ignore background poll error
    }
}

// Click on Table Chip in Floor Matrix
function handleTableClick(tableId, tableCode, status, isOverdue) {
    if (isOverdue) {
        showToast(`Table ${tableCode} is reserved but 15+ minutes overdue. You can release it from the alert banner above.`, 'warning');
    } else if (status === 'reserved') {
        showToast(`Table ${tableCode} is currently reserved. Awaiting customer QR scan.`, 'info');
    } else if (status === 'occupied') {
        showToast(`Table ${tableCode} is occupied by seated guests.`, 'secondary');
    } else {
        showToast(`Table ${tableCode} is Available for walk-in seating.`, 'success');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    refreshConciergeDashboard();
    setInterval(refreshConciergeDashboard, 15000);
});
</script>
