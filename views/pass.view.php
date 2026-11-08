<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Digital QR Pass, T-15m Scheduled Order Tracker & Post-Booking Order Manager
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

require_once __DIR__ . '/../includes/inventory.php';

// The booking secures the table reservation and generates this Digital QR Pass.
// Pre-orders are scanned and processed directly at the POS register upon customer arrival.

$token = trim($_GET['token'] ?? '');
$resId = (int)($_GET['id'] ?? 0);
$reservation = null;
$items = [];
$linkedOrder = null;
$schedule = null;
$menuProducts = [];

if (!empty($token) || $resId > 0) {
    try {
        if (!empty($token)) {
            $stmt = $pdo->prepare("
                SELECT r.*, t.name AS table_name, t.zone, t.table_code, t.has_power_outlet
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.qr_token = ? OR r.reservation_number = ? OR r.phone = ?
                ORDER BY r.id DESC
                LIMIT 1
            ");
            $stmt->execute([$token, $token, $token]);
        } else {
            $stmt = $pdo->prepare("
                SELECT r.*, t.name AS table_name, t.zone, t.table_code, t.has_power_outlet
                FROM reservations r
                LEFT JOIN dining_tables t ON r.table_id = t.id
                WHERE r.id = ?
                LIMIT 1
            ");
            $stmt->execute([$resId]);
        }
        $reservation = $stmt->fetch();

        if ($reservation) {
            $itemStmt = $pdo->prepare("SELECT * FROM reservation_items WHERE reservation_id = ? ORDER BY id ASC");
            $itemStmt->execute([(int)$reservation['id']]);
            $items = $itemStmt->fetchAll();

            $ordStmt = $pdo->prepare("SELECT * FROM orders WHERE reservation_id = ? AND status != 'Cancelled' ORDER BY id DESC LIMIT 1");
            $ordStmt->execute([(int)$reservation['id']]);
            $linkedOrder = $ordStmt->fetch() ?: null;

            $schedule = inv_parse_reservation_schedule($reservation);

            $menuProducts = $pdo->query("
                SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_available = 1
                ORDER BY p.category_id, p.name
            ")->fetchAll();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container py-4 py-lg-5">
    <?php if (!$reservation): ?>
        <!-- Token Lookup Form if no valid pass loaded -->
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 p-4 text-center glass-panel">
                    <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-xs" style="width: 76px; height: 76px; font-size: 2.2rem;">
                        <i class="bi bi-qr-code text-dark"></i>
                    </div>
                    <h4 class="fw-bold text-navy mb-2">Find Your Digital QR Pass & Order Tracker</h4>
                    <p class="text-muted small mb-4">Enter your QR token, booking reference number (e.g., <code>RES-20261026-001</code>), or registered mobile phone number.</p>
                    
                    <form method="GET" action="index.php" class="d-flex gap-2 mb-3">
                        <input type="hidden" name="page" value="pass">
                        <input type="text" name="token" class="form-control rounded-pill px-3" placeholder="Enter RES-Code, QR Token, or Phone..." required value="<?= htmlspecialchars($token) ?>">
                        <button type="submit" class="btn btn-navy px-4 rounded-pill fw-bold">Lookup</button>
                    </form>
                    <a href="booking" class="btn btn-link text-decoration-none small text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Customer Booking</a>
                </div>
            </div>
        </div>
    <?php else:
        $orderStatus = $linkedOrder['status'] ?? null;
        $orderNumber = $linkedOrder['order_number'] ?? null;
        $canManageOrder = in_array($reservation['status'], ['Confirmed', 'Checked-In'], true);
        $canPrepareNow = $canManageOrder && !empty($items) && !$linkedOrder;
        $canCancelBooking = ($reservation['status'] === 'Confirmed') && (!in_array($orderStatus, ['Brewing', 'Ready', 'Completed'], true));
    ?>
        <!-- Valid Digital Boarding Pass & Post-Booking Order Command Center -->
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                
                <div class="card boarding-pass-card border-0 shadow-xl overflow-hidden mb-4" id="printablePass">
                    
                    <!-- Pass Header -->
                    <div class="boarding-pass-header p-4" style="background: var(--brand-primary-gradient);">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow border border-white border-2" style="width: 54px; height: 54px; object-fit: cover;">
                                <div>
                                    <div class="badge text-white fw-bold text-uppercase px-2.5 py-0.5 mb-1" style="background-color: var(--brand-secondary); font-size: 0.68rem; letter-spacing: 0.05em;">Raeden's</div>
                                    <h4 class="fw-black mb-0 text-white tracking-tight">Raeden’s Bakery and Café</h4>
                                    <div class="text-white-50 small">Artisanal Bakery &amp; Specialty Café • Digital Pass</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge text-white fw-bold px-3 py-2 rounded-pill fs-6 shadow-xs" style="background-color: var(--brand-secondary);" id="livePassResStatusBadge"><?= htmlspecialchars($reservation['status']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Scheduled Order & Reservation Progress Tracker -->
                    <div class="bg-light border-bottom px-4 py-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <div class="small fw-bold text-navy">
                                <i class="bi bi-broadcast-pin text-danger me-1"></i>
                                Live Reservation & Order Status
                            </div>
                            <span class="badge bg-white text-secondary border small" id="liveTrackerSummaryBadge">
                                <?php if ($linkedOrder): ?>
                                    <i class="bi bi-fire text-danger me-1"></i>POS Order #<?= htmlspecialchars($orderNumber) ?>: <strong><?= htmlspecialchars($orderStatus) ?></strong>
                                <?php elseif (!empty($items)): ?>
                                    <i class="bi bi-qr-code-scan text-primary me-1"></i>Pre-Order Ready on Pass — Present at Cashier to load to POS
                                <?php else: ?>
                                    <i class="bi bi-calendar2-check text-success me-1"></i>Table Reserved — Add Pre-Order Items Anytime Below
                                <?php endif; ?>
                            </span>
                        </div>

                        <!-- 4-Step Visual Progress Pills -->
                        <div class="row g-2 text-center small" id="liveOrderProgressSteps">
                            <?php
                            $step1Done = ($reservation['status'] !== 'Cancelled');
                            $step2Done = true; // QR pass is ready
                            $step3Done = ($linkedOrder !== null); // Order loaded and placed at POS
                            $step4Done = in_array($orderStatus, ['Ready', 'Completed'], true) || $reservation['status'] === 'Completed';
                            ?>
                            <div class="col-6 col-md-3">
                                <div class="p-2 rounded-3 border <?= $step1Done ? 'bg-success-subtle border-success text-success fw-bold' : 'bg-white text-muted' ?>">
                                    <i class="bi bi-1-circle-fill me-1"></i>Seat Reserved
                                    <div style="font-size: 0.68rem;" class="fw-normal"><?= htmlspecialchars($reservation['table_code'] ?? 'Table') ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 rounded-3 border <?= $step2Done ? 'bg-primary-subtle border-primary text-primary fw-bold' : 'bg-white text-muted' ?>">
                                    <i class="bi bi-2-circle-fill me-1"></i>QR Pass Ready
                                    <div style="font-size: 0.68rem;" class="fw-normal">Scan at POS Counter</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 rounded-3 border <?= $step3Done ? 'bg-warning-subtle border-warning text-dark fw-bold' : 'bg-white text-muted' ?>">
                                    <i class="bi bi-3-circle-fill me-1"></i>POS Confirmed
                                    <div style="font-size: 0.68rem;" class="fw-normal"><?= $orderNumber ? ('#' . htmlspecialchars($orderNumber)) : 'Awaiting Counter Scan' ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 rounded-3 border <?= $step4Done ? 'bg-success text-white fw-bold' : 'bg-white text-muted' ?>">
                                    <i class="bi bi-4-circle-fill me-1"></i>Kitchen / Served
                                    <div style="font-size: 0.68rem;" class="fw-normal"><?= $orderStatus === 'Ready' ? 'Ready for Pickup!' : ($orderStatus === 'Completed' ? 'Served!' : 'Barista Queue') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pass Body -->
                    <div class="card-body p-4 p-md-5 bg-white">
                        <div class="row align-items-center gy-4">
                            
                            <!-- Left: Pass Metadata -->
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.05em;">Reservation Reference</span>
                                    <h3 class="fw-black text-navy fw-mono mb-0"><?= htmlspecialchars($reservation['reservation_number']) ?></h3>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <span class="text-muted small text-uppercase">Guest Name</span>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($reservation['customer_name']) ?></div>
                                        <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.72rem;"><?= htmlspecialchars($reservation['customer_type']) ?></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted small text-uppercase">Assigned Table</span>
                                        <div class="fw-black text-navy fs-5">
                                            <span class="badge bg-warning text-dark me-1 shadow-xs">Table #<?= $reservation['table_id'] ?></span>
                                            <?= htmlspecialchars($reservation['table_code'] ?? 'TBL') ?> - <?= htmlspecialchars($reservation['table_name'] ?? 'Assigned') ?>
                                        </div>
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;"><i class="bi bi-geo-alt me-1 text-primary"></i><?= htmlspecialchars($reservation['zone'] ?? 'General') ?></span>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <span class="text-muted small text-uppercase">Scheduled Date & Slot</span>
                                        <div class="fw-bold text-dark"><i class="bi bi-calendar3 me-1 text-warning"></i> <?= date('M d, Y', strtotime($reservation['reservation_date'])) ?></div>
                                        <div class="small text-secondary"><i class="bi bi-clock me-1 text-warning"></i> <?= htmlspecialchars($reservation['time_slot']) ?></div>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted small text-uppercase">15-Min Holding Policy</span>
                                        <div class="fw-bold text-danger"><i class="bi bi-alarm me-1"></i> Grace Period</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Held 15 mins past arrival; unclaimed seats released to walk-ins</div>
                                    </div>
                                </div>

                                <!-- 15-Minute Seating & Onsite Steps Notice -->
                                <div class="alert alert-warning border-warning-subtle rounded-3 p-2.5 mb-3 small shadow-xs">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-shield-exclamation text-warning-emphasis fw-bold"></i>
                                        <strong class="text-dark">Seating Policy &amp; Onsite Steps:</strong>
                                    </div>
                                    <div class="text-secondary" style="font-size: 0.74rem;">
                                        <strong>1. Staff:</strong> Present this pass at the entrance to verify you are onsite and get seated at your table.<br>
                                        <strong>2. Cashier POS:</strong> Present this pass at the counter to load, edit, and pay for your pre-order drinks/meals.<br>
                                        <strong>3. 15-Minute Hold:</strong> Tables are held for 15 minutes past arrival time. Unclaimed seats are released to available.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <span class="text-muted small text-uppercase">Party Size & Notes</span>
                                    <div class="small text-secondary">
                                        <strong><?= $reservation['guest_count'] ?> Guest(s)</strong>
                                        <?= !empty($reservation['special_notes']) ? '• ' . htmlspecialchars($reservation['special_notes']) : '' ?>
                                        <?php if (!empty($reservation['has_power_outlet'])): ?>
                                            • <span class="text-success"><i class="bi bi-plug-fill"></i> Dedicated Power Outlet</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Pre-Ordered Beverages & Meals List + Post-Booking Controls -->
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small text-uppercase fw-bold">Scheduled Order Items</span>
                                        <?php if ($canManageOrder): ?>
                                            <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-xs no-print" data-bs-toggle="modal" data-bs-target="#addMoreItemsModal">
                                                <i class="bi bi-plus-circle me-1"></i> Add Items to My Order
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="bg-light p-3 rounded-4 border small" id="passOrderItemsContainer">
                                        <?php if (empty($items)): ?>
                                            <div class="text-center py-2 text-muted">
                                                No pre-ordered items yet. Click <strong>"Add Items to My Order"</strong> above so our Barista Kitchen can prepare your food & drinks 15 minutes before you arrive!
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($items as $it): 
                                                $addonArr = !empty($it['addons_json']) ? json_decode($it['addons_json'], true) : [];
                                                $isPastry = ($it['sugar_level'] === 'Pastry' || $it['size_name'] === 'Standard Serving');
                                            ?>
                                                <div class="d-flex justify-content-between mb-1.5 pb-1 border-bottom">
                                                    <div>
                                                        <strong><?= $it['quantity'] ?>x <?= htmlspecialchars($it['product_name']) ?></strong> 
                                                        <span class="badge bg-light text-secondary border"><?= htmlspecialchars($it['size_name']) ?></span>
                                                        <div class="text-muted" style="font-size: 0.72rem;">
                                                            <?php if ($isPastry): ?>
                                                                <i class="bi bi-cake2 text-warning me-1"></i><?= htmlspecialchars($it['ice_level']) ?>
                                                            <?php else: ?>
                                                                <?= htmlspecialchars($it['sugar_level']) ?> • <?= htmlspecialchars($it['ice_level']) ?>
                                                                <?= !empty($addonArr) ? '• ' . implode(', ', array_map('htmlspecialchars', $addonArr)) : '' ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <span class="fw-bold text-navy">₱<?= number_format($it['line_total'], 2) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="d-flex justify-content-between pt-2 mt-1 fw-bold text-navy fs-6">
                                                <span>Total Due at Counter:</span>
                                                <span id="passTotalDueDisplay">₱<?= number_format($reservation['total_amount'], 2) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: QR Code Visual & Customer Order Actions -->
                            <div class="col-md-5 text-center border-start-md">
                                <div class="p-3 bg-light rounded-4 border d-inline-block shadow-xs mb-3">
                                    <div id="passQrCode" class="d-flex justify-content-center"></div>
                                </div>
                                <div class="small text-muted mb-2">Scan at Staff Desk or Cashier Counter</div>
                                <div class="badge bg-light text-secondary border px-2.5 py-1 text-wrap fw-mono mb-3" style="font-size: 0.68rem; word-break: break-all;">
                                    <?= htmlspecialchars($reservation['qr_token']) ?>
                                </div>

                                <!-- Customer Post-Booking Action Buttons -->
                                <div class="d-grid gap-2 text-start no-print">
                                    <?php if ($canPrepareNow): ?>
                                        <button type="button" class="btn btn-success rounded-pill py-2 px-3 fw-bold shadow-xs text-center" id="btnCustomerPrepNow" onclick="triggerCustomerPrepareNow()">
                                            <i class="bi bi-fire me-1"></i> I'm Arriving Early — Prepare My Order Now
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($canManageOrder): ?>
                                        <button type="button" class="btn btn-outline-navy rounded-pill py-2 px-3 fw-bold text-center" data-bs-toggle="modal" data-bs-target="#addMoreItemsModal">
                                            <i class="bi bi-bag-plus me-1"></i> Add / Order More Items
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($canCancelBooking): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-1.5 px-3 fw-bold text-center" onclick="cancelCustomerBooking()">
                                            <i class="bi bi-x-circle me-1"></i> Cancel Reservation
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Perforated Ticket Notches -->
                    <div class="perforated-separator">
                        <div class="notch-left"></div>
                        <div class="notch-right"></div>
                    </div>

                    <!-- Pass Footer / Actions -->
                    <div class="card-footer bg-light p-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-0">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1 text-warning"></i> Present this digital pass to the Staff upon arrival to claim your table. Unclaimed tables are released after 15 minutes.
                        </div>
                        <div class="d-flex gap-2 no-print">
                            <button type="button" class="btn btn-navy btn-sm rounded-pill px-4 fw-bold shadow-xs" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print / Save PDF
                            </button>
                            <a href="index.php?page=booking" data-page="booking" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                                Back to Booking
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Modal: Add / Order More Items After Booking -->
        <div class="modal fade" id="addMoreItemsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--brand-primary-gradient);">
                        <div>
                            <h5 class="modal-title fw-bold mb-0"><i class="bi bi-bag-plus text-warning me-2"></i>Add Items to Booking <?= htmlspecialchars($reservation['reservation_number']) ?></h5>
                            <small class="text-white-50">Scheduled Prep at <?= htmlspecialchars($schedule['prep_formatted'] ?? 'T-15m') ?> (or sent directly to Kitchen if already active)</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <?php foreach ($menuProducts as $mp): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border rounded-4 p-3 d-flex flex-row align-items-center justify-content-between gap-2 shadow-xs">
                                        <div>
                                            <div class="fw-bold text-navy"><?= htmlspecialchars($mp['name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($mp['category_name']) ?></div>
                                            <div class="fw-black text-dark mt-1">₱<?= number_format((float)$mp['base_price'], 2) ?></div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="number" class="form-control form-control-sm text-center rounded-pill pass-add-qty"
                                                   data-id="<?= (int)$mp['id'] ?>"
                                                   data-name="<?= htmlspecialchars($mp['name']) ?>"
                                                   data-price="<?= (float)$mp['base_price'] ?>"
                                                   data-cat="<?= (int)$mp['category_id'] ?>"
                                                   value="0" min="0" max="20" style="width: 68px;">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Select quantities above to add to your reservation order.</span>
                        <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" id="btnSubmitPassAddItems" onclick="submitPassAdditionalItems()">
                            <i class="bi bi-check2-circle me-1"></i> Confirm & Add to My Order
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            window.PASS_QR_TOKEN = <?= json_encode($reservation['qr_token']) ?>;
            window.PASS_RES_ID = <?= (int)$reservation['id'] ?>;

            (function initPassQr() {
                const qrContainer = document.getElementById('passQrCode');
                if (qrContainer) {
                    qrContainer.innerHTML = '';
                    new QRCode(qrContainer, {
                        text: PASS_QR_TOKEN,
                        width: 170,
                        height: 170,
                        colorDark: '#0c2340',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            })();

            async function triggerCustomerPrepareNow() {
                const btn = document.getElementById('btnCustomerPrepNow');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending to Barista Kitchen...`;
                }
                try {
                    const res = await fetch('api/reservations.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'prepare_now',
                            qr_token: PASS_QR_TOKEN,
                            reservation_id: PASS_RES_ID
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 900);
                    } else {
                        showToast(data.message || 'Could not dispatch order.', 'danger');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = `<i class="bi bi-fire me-1"></i> I'm Arriving Early — Prepare My Order Now`;
                        }
                    }
                } catch (e) {
                    showToast('Network error contacting server.', 'danger');
                    if (btn) btn.disabled = false;
                }
            }

            async function submitPassAdditionalItems() {
                const inputs = document.querySelectorAll('.pass-add-qty');
                const items = [];
                inputs.forEach(inp => {
                    const qty = parseInt(inp.value, 10) || 0;
                    if (qty > 0) {
                        const catId = parseInt(inp.dataset.cat, 10) || 4;
                        const isPastry = (catId === 4);
                        items.push({
                            product_id: parseInt(inp.dataset.id, 10),
                            product_name: inp.dataset.name,
                            unit_price: parseFloat(inp.dataset.price),
                            quantity: qty,
                            size_name: isPastry ? 'Standard Serving' : 'Regular (12oz)',
                            sugar_level: isPastry ? 'Pastry' : '50%',
                            ice_level: isPastry ? 'Warm / Lightly Toasted' : 'Regular Ice',
                            addons: []
                        });
                    }
                });

                if (items.length === 0) {
                    showToast('Please set a quantity of at least 1 for the item(s) you wish to add.', 'warning');
                    return;
                }

                const btn = document.getElementById('btnSubmitPassAddItems');
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Updating Order...`;

                try {
                    const res = await fetch('api/reservations.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'add_preorder_items',
                            qr_token: PASS_QR_TOKEN,
                            reservation_id: PASS_RES_ID,
                            items: items
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 900);
                    } else {
                        showToast(data.message || 'Could not add items.', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Confirm & Add to My Order`;
                    }
                } catch (e) {
                    showToast('Network error adding items.', 'danger');
                    btn.disabled = false;
                }
            }

            async function cancelCustomerBooking() {
                if (!confirm('Are you sure you want to cancel this reservation and release your table?')) {
                    return;
                }
                try {
                    const res = await fetch('api/reservations.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'cancel_booking',
                            qr_token: PASS_QR_TOKEN,
                            reservation_id: PASS_RES_ID
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast(data.message, 'info');
                        setTimeout(() => window.location.reload(), 900);
                    } else {
                        showToast(data.message || 'Could not cancel booking.', 'danger');
                    }
                } catch (e) {
                    showToast('Network error cancelling reservation.', 'danger');
                }
            }

            // Poll every 12 seconds so if T-15m triggers or Barista updates Kitchen status, the customer's pass updates live
            window.lastKnownOrderStatus = <?= json_encode($orderStatus) ?>;
            window.lastKnownResStatus = <?= json_encode($reservation['status']) ?>;
            setInterval(async () => {
                try {
                    const res = await fetch(`api/reservations.php?action=lookup_booking&token=${encodeURIComponent(PASS_QR_TOKEN)}`);
                    const data = await res.json();
                    if (data.success && data.reservation) {
                        const newOrdSt = data.order ? data.order.status : null;
                        const newResSt = data.reservation.status;
                        if (newOrdSt !== lastKnownOrderStatus || newResSt !== lastKnownResStatus) {
                            window.location.reload();
                        }
                    }
                } catch (e) {}
            }, 12000);
        </script>
    <?php endif; ?>
</div>
