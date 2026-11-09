<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Customer Booking & Pre-Order Portal View
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

// Fetch dining tables grouped by zone
$tableStmt = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code");
$tables = $tableStmt->fetchAll();

// Fetch active seating zones (Manageable by Admin)
$zoneStmt = $pdo->query("SELECT * FROM zones WHERE is_active = 1 ORDER BY id");
$zones = $zoneStmt->fetchAll();
if (empty($zones)) {
    $zones = [
        ['id' => 1, 'name' => 'Study Hub', 'icon' => 'bi-laptop', 'badge_color' => 'primary'],
        ['id' => 2, 'name' => 'Lounge', 'icon' => 'bi-cup-straw', 'badge_color' => 'warning'],
        ['id' => 3, 'name' => 'Main Hall', 'icon' => 'bi-people', 'badge_color' => 'info'],
        ['id' => 4, 'name' => 'Terrace', 'icon' => 'bi-sun', 'badge_color' => 'success']
    ];
}

// Fetch categories and products for pre-order
$catStmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
$categories = $catStmt->fetchAll();

$prodStmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.category_id, p.name");
$products = $prodStmt->fetchAll();

// Fetch all sizes grouped by product_id
$allSizes = $pdo->query("SELECT * FROM product_sizes ORDER BY product_id, price_additional")->fetchAll();
$sizesByProduct = [];
foreach ($allSizes as $s) {
    $sizesByProduct[$s['product_id']][] = [
        'id' => (int)$s['id'],
        'size_name' => $s['size_name'],
        'price_additional' => (float)$s['price_additional']
    ];
}

// Fetch available addons
$addons = $pdo->query("SELECT * FROM product_addons WHERE is_available = 1 ORDER BY id")->fetchAll();
?>

<!-- Hero Section with Brand Coffee Brown Gradient, Cafe Background & Official Logo -->
<section class="py-4 py-lg-5 text-white position-relative overflow-hidden border-bottom border-3" style="background: linear-gradient(135deg, rgba(137, 78, 63, 0.92), rgba(92, 45, 34, 0.95)), url('assets/images/cafe-bg.jpg') center/cover no-repeat; border-color: var(--brand-secondary) !important;">
    <div class="container-margins-padded position-relative">
        <div class="row align-items-center gy-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow border border-white border-2 flex-shrink-0" style="width: 58px; height: 58px; object-fit: cover;">
                    <div>
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white bg-opacity-10 small border shadow-xs" style="border-color: rgba(238, 225, 186, 0.4); color: #EEE1BA;">
                            <i class="bi bi-shop"></i> Raeden’s Bakery and Café • Fresh Bakes &amp; Specialty Brews
                        </div>
                        <div class="small text-white-50 mt-1 d-none d-sm-block">Warm Traditional Bakery • Artisan Cafe • POS &amp; Table Booking</div>
                    </div>
                </div>
                <h1 class="display-5 text-white mb-2">
                    Reserve Your Table &amp; <span style="color: #EEE1BA;">Pre-Order Pastries &amp; Drinks</span>
                </h1>
                <p class="lead text-light text-opacity-85 mb-3" style="max-width: 680px;">
                    Experience welcoming café seating, handcrafted espresso beverages, and fresh artisan baked goods at Raeden’s Bakery and Café. Book your table and get an instant QR pass for easy cashier checkout.
                </p>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                        <i class="bi bi-cup-hot-fill me-1" style="color: #EEE1BA;"></i> Specialty Coffee &amp; Drinks
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                        <i class="bi bi-cake2-fill me-1" style="color: #EEE1BA;"></i> Artisanal Bakery &amp; Pastries
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-light border border-white border-opacity-10 px-3 py-2 rounded-pill small">
                        <i class="bi bi-qr-code me-1" style="color: #EEE1BA;"></i> Instant Digital QR Pass
                    </span>
                    <span class="badge px-3 py-2 rounded-pill fw-bold small text-white" style="background-color: var(--brand-secondary);">
                        <i class="bi bi-shield-check me-1"></i> 15-Minute Reserved Seating
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-inline-flex flex-column align-items-lg-end gap-2 bg-black bg-opacity-35 p-3 p-xl-4 rounded-4 border border-white border-opacity-15 shadow-sm">
                    <div class="d-flex align-items-center gap-2 text-warning fw-bold small">
                        <i class="bi bi-clock-history"></i> Café Operating Hours
                    </div>
                    <div class="text-white fw-bold fs-5">Monday – Saturday</div>
                    <div class="text-white-50 small">8:00 AM – 7:00 PM</div>
                    <div class="mt-2 pt-2 border-top border-white border-opacity-15 w-100 text-start text-lg-end">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <span class="pulse-available me-1"></span> Live Seating Available
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Visual 3-Step Booking Stepper -->
<div class="bg-white border-bottom shadow-xs py-3">
    <div class="container-margins-padded">
        <div class="row g-2 justify-content-between align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-navy text-warning fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">1</div>
                    <div>
                        <div class="small fw-bold text-navy">Step 1: Choose Seat / Pod</div>
                        <div class="text-muted" style="font-size: 0.78rem;">Pick study hub or lounge table</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light text-secondary border fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">2</div>
                    <div>
                        <div class="small fw-bold text-dark">Step 2: Pre-Select Refreshments (Optional)</div>
                        <div class="text-muted" style="font-size: 0.78rem;">Loaded & editable at POS register upon arrival</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-light text-secondary border fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">3</div>
                        <div>
                            <div class="small fw-bold text-dark">Step 3: Dynamic QR Pass (15m Policy)</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Show to Staff for seating & POS for order</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Returning Guest Lookup Bar: Manage Existing Booking & Order -->
        <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="small text-secondary">
                <i class="bi bi-ticket-perforated-fill text-warning me-1"></i>
                <strong>Already have a booking?</strong> Track your T-15m scheduled order, add more items, or notify the Barista you're arriving early:
            </div>
            <form class="d-flex gap-2" onsubmit="lookupExistingCustomerBooking(event)">
                <input type="text" id="existingBookingLookupInput" class="form-control form-control-sm rounded-pill px-3" style="min-width: 240px;" placeholder="Enter RES-Code, QR Token, or Phone">
                <button type="submit" class="btn btn-sm btn-navy rounded-pill px-3 fw-bold text-nowrap">
                    <i class="bi bi-search me-1"></i> Manage My Booking
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Main Interactive Booking Workspace -->
<div class="container-margins-padded py-4">
    <div class="row g-4">
        
        <!-- Left: Interactive Seating Map & Zone Filter -->
        <div class="col-xl-7 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">
                                <i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i>Dining Room Floor Plan &amp; Seating
                            </h5>
                            <p class="text-muted small mb-0">Select your preferred dining table, booth, or cafe seating.</p>
                        </div>
                        <!-- Legend -->
                        <div class="d-flex align-items-center gap-3 small">
                            <span class="d-flex align-items-center gap-1.5"><span class="pulse-available"></span> <span class="ms-1">Available</span></span>
                            <span class="d-flex align-items-center gap-1.5"><span class="badge rounded-pill bg-warning p-1"></span> Reserved</span>
                            <span class="d-flex align-items-center gap-1.5"><span class="badge rounded-pill bg-danger p-1"></span> Occupied</span>
                        </div>
                    </div>

                    <!-- Zone Filter Tabs (Dynamically Managed by Admin) -->
                    <ul class="nav nav-pills mt-3 gap-2 overflow-auto flex-nowrap pb-1" id="zoneFilterTabs">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-3 py-1.5 small fw-bold text-nowrap" onclick="filterZone('all', this)">
                                All Zones (<?= count($tables) ?>)
                            </button>
                        </li>
                        <?php foreach ($zones as $zn): 
                            $zColor = $zn['badge_color'] ?? 'primary';
                            $zIcon = $zn['icon'] ?? 'bi-geo-alt';
                            $tableCountInZone = 0;
                            foreach ($tables as $t) {
                                if (strcasecmp($t['zone'], $zn['name']) === 0) $tableCountInZone++;
                            }
                        ?>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill px-3 py-1.5 small fw-bold text-nowrap" onclick="filterZone('<?= htmlspecialchars($zn['name']) ?>', this)">
                                    <i class="bi <?= htmlspecialchars($zIcon) ?> me-1 text-<?= htmlspecialchars($zColor) ?>"></i> <?= htmlspecialchars($zn['name']) ?> (<?= $tableCountInZone ?>)
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <!-- Seating Cards Grid -->
                    <div class="row g-3" id="tablesGrid">
                        <?php foreach ($tables as $tbl): 
                            $status = strtolower($tbl['status']);
                            $statusBadge = $status === 'available' ? 'bg-success' : ($status === 'reserved' ? 'bg-warning text-dark' : 'bg-danger');
                            $isSelectable = $status === 'available';
                            $zoneClass = $tbl['zone'] === 'Study Hub' ? 'zone-header-study' : ($tbl['zone'] === 'Lounge' ? 'zone-header-lounge' : ($tbl['zone'] === 'Terrace' ? 'zone-header-terrace' : 'zone-header-main'));
                            $zoneIcon = $tbl['zone'] === 'Study Hub' ? 'bi-laptop' : ($tbl['zone'] === 'Lounge' ? 'bi-cup-straw' : ($tbl['zone'] === 'Terrace' ? 'bi-sun' : 'bi-people'));
                        ?>
                            <div class="col-md-6 col-sm-12 table-card-item" data-zone="<?= htmlspecialchars($tbl['zone']) ?>">
                                <div class="card h-100 table-card table-select-card card-lift <?= $zoneClass ?> <?= $isSelectable ? 'cursor-pointer' : 'opacity-75 bg-light' ?>" 
                                     id="tableCard_<?= $tbl['id'] ?>"
                                     onclick="<?= $isSelectable ? "selectTable({$tbl['id']}, '{$tbl['table_code']}', '" . addslashes($tbl['name']) . "', '" . addslashes($tbl['zone']) . "', {$tbl['capacity']})" : "showToast('This table is currently {$tbl['status']}. Please choose an available table.', 'warning')" ?>">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 shadow-xs"><i class="bi bi-hash"></i> Table #<?= $tbl['id'] ?></span>
                                                <span class="badge bg-navy text-white fw-bold px-2 py-1"><?= htmlspecialchars($tbl['table_code']) ?></span>
                                                <span class="badge bg-light text-muted border"><i class="bi <?= $zoneIcon ?> me-1"></i><?= htmlspecialchars($tbl['zone']) ?></span>
                                            </div>
                                            <span class="badge status-badge <?= $statusBadge ?> px-2.5 py-1 text-capitalize fw-bold">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> <?= htmlspecialchars($tbl['status']) ?>
                                            </span>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-1">
                                            <span class="text-navy">Table #<?= $tbl['id'] ?>:</span> <?= htmlspecialchars($tbl['name']) ?>
                                        </h6>
                                        <div class="d-flex align-items-center gap-3 text-secondary small mb-2">
                                            <span><i class="bi bi-person-fill text-warning me-1"></i> Capacity: <strong><?= $tbl['capacity'] ?> <?= $tbl['capacity'] > 1 ? 'Guests' : 'Guest' ?></strong></span>
                                        </div>

                                        <!-- Amenities / Features -->
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if ($tbl['has_power_outlet']): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.72rem;">
                                                    <i class="bi bi-plug-fill me-1"></i>AC Power Outlets
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">
                                                <i class="bi bi-wifi me-1"></i>Free High-Speed Wi-Fi
                                            </span>
                                            <?php if ($tbl['zone'] === 'Study Hub'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.72rem;">
                                                    <i class="bi bi-volume-mute-fill me-1"></i>Quiet Focus Pod
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pt-0 px-3 pb-3">
                                        <button type="button" class="btn btn-sm <?= $isSelectable ? 'btn-outline-navy' : 'btn-light disabled' ?> w-100 fw-bold rounded-pill" id="btnSelect_<?= $tbl['id'] ?>">
                                            <?= $isSelectable ? '<i class="bi bi-check2-circle me-1"></i> Select Table #' . $tbl['id'] : '<i class="bi bi-lock-fill me-1"></i> Occupied / Reserved' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Reservation Details & Pre-order Form -->
        <div class="col-xl-5 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 sticky-top glass-panel" id="reservationFormCard" style="top: 85px; z-index: 10;">
                <div class="card-header bg-navy text-white rounded-top-4 py-3 px-4" style="background: var(--brand-primary-gradient);">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-pencil-square text-warning me-2"></i>Booking Details
                        </h5>
                        <span class="badge text-white fw-bold px-3 py-1.5 rounded-pill" style="background-color: var(--brand-secondary);">Customer Portal</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form id="reservationForm" onsubmit="submitReservation(event)">
                        
                        <!-- Selected Table Callout Banner -->
                        <div class="alert alert-light border border-2 border-primary-subtle rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between shadow-xs" id="selectedTableBanner">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em;">Assigned Table</div>
                                <h6 class="fw-bold text-navy mb-0 fs-6" id="selectedTableDisplay">Please select a table from the floor map</h6>
                                <div class="small text-muted" id="selectedTableZone">Click any green "Available" card on the left</div>
                            </div>
                            <input type="hidden" name="table_id" id="formTableId" required>
                            <span class="badge bg-primary px-3 py-2 rounded-pill fs-6" id="selectedTableBadge"><i class="bi bi-arrow-left"></i> Pick</span>
                        </div>

                        <!-- Guest Name & Classification -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-secondary">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control border-start-0" placeholder="e.g. Maria Santos" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-secondary">Classification <span class="text-danger">*</span></label>
                                <select name="customer_type" id="customer_type" class="form-select" onchange="handleClassificationChange()">
                                    <option value="Regular" selected>Regular Diner / Customer</option>
                                    <option value="Senior/PWD">Senior Citizen / PWD (20% Off)</option>
                                    <option value="Student">Student (10% Off)</option>
                                    <option value="VIP">VIP / Member</option>
                                </select>
                            </div>
                        </div>

                        <!-- ID Number (for discount verification) & Contact Phone -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6" id="idNumberWrapper">
                                <label class="form-label small fw-bold text-secondary">ID Number / Reference <span class="text-muted small fw-normal">(Optional for Regular)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-heading"></i></span>
                                    <input type="text" name="id_number" id="id_number" class="form-control border-start-0" placeholder="Optional for regular diners">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Mobile Contact <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" id="phone" class="form-control border-start-0" placeholder="e.g. 0917-123-4567" required>
                                </div>
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Email Address (Optional - For digital pass copy)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control border-start-0" placeholder="name@raedens.com">
                            </div>
                        </div>

                        <!-- Date, Time Slot & Exact Arrival Time -->
                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">Reservation Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" name="reservation_date" id="reservation_date" class="form-control border-start-0" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" onchange="checkTableAvailability(); updateScheduledPrepPreview();" required>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-secondary">Time Slot <span class="text-danger">*</span></label>
                                <select name="time_slot" id="time_slot" class="form-select" onchange="onTimeSlotChanged()" required>
                                    <option value="08:00 AM - 10:00 AM">08:00 AM - 10:00 AM</option>
                                    <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                                    <option value="01:00 PM - 03:00 PM" selected>01:00 PM - 03:00 PM</option>
                                    <option value="03:00 PM - 05:00 PM">03:00 PM - 05:00 PM</option>
                                    <option value="05:00 PM - 07:00 PM">05:00 PM - 07:00 PM</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary" title="Exact Arrival Time (Optional)">Exact Arrival</label>
                                <input type="time" name="arrival_time" id="arrival_time" class="form-control" value="13:00" onchange="updateScheduledPrepPreview()">
                            </div>
                        </div>

                        <!-- 15-Minute Seating Grace Period Policy Callout -->
                        <div class="alert alert-warning border-warning-subtle rounded-4 py-2.5 px-3 mb-3 small d-flex align-items-center justify-content-between shadow-xs">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock-history text-warning-emphasis fs-5 mt-0.5"></i>
                                <div>
                                    <strong class="text-dark d-block">15-Minute Seating Grace Period Policy:</strong>
                                    <span class="text-secondary">Your reserved table/seat is held strictly for <strong>15 minutes</strong> past your scheduled arrival time. If unclaimed after 15 minutes, the Staff may release the table back to Available for walk-in guests.</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-secondary">Number of Guests</label>
                                <input type="number" name="guest_count" id="guest_count" class="form-control" value="1" min="1" max="10">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-secondary">Special Notes (Optional)</label>
                                <input type="text" name="special_notes" id="special_notes" class="form-control" placeholder="e.g. Study session, near outlet">
                            </div>
                        </div>

                        <!-- Pre-Order Section -->
                        <div class="card border rounded-4 mb-4 overflow-hidden shadow-xs">
                            <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3">
                                <div>
                                    <span class="fw-bold text-navy small"><i class="bi bi-cup-hot-fill text-warning me-1"></i> Pre-Select Refreshments (Fast-Food Counter Pickup)</span>
                                    <div class="text-muted" style="font-size: 0.72rem;">Pre-orders are loaded into the POS when you scan your QR Pass • Fully editable at counter before payment!</div>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3 shadow-xs" onclick="openMenuSpecialsModal(event)">
                                        <i class="bi bi-cup-hot-fill text-warning me-1"></i> Menu &amp; Specials (<span class="headerCartCount" id="cartCount">0</span>)
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-3" id="preorderCartList" style="max-height: 190px; overflow-y: auto;">
                                <div class="text-center py-3 text-muted small" id="emptyCartText">
                                    <i class="bi bi-cup-hot text-secondary fs-3 d-block mb-1 text-opacity-50"></i>
                                    No pre-ordered items yet. Click "Add Items" to select refreshments ahead!
                                </div>
                            </div>
                            <div class="card-footer bg-light py-2.5 px-3 d-flex justify-content-between align-items-center" id="cartFooter">
                                <span class="small fw-bold text-secondary">Pre-Order Subtotal:</span>
                                <span class="fw-bold text-navy fs-6" id="cartTotalDisplay">₱0.00</span>
                            </div>
                        </div>

                        <!-- Reservation Terms & Conditions Policy Agreement -->
                        <div class="card border rounded-4 p-3 mb-3 bg-light bg-opacity-75 shadow-xs">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-navy">
                                    <i class="bi bi-shield-check text-success me-1"></i> Seating Policy &amp; Terms Agreement
                                </span>
                                <span class="badge bg-warning text-dark fw-bold small">
                                    15-Min Grace Period
                                </span>
                            </div>
                            <ul class="text-muted small ps-3 mb-2" style="font-size: 0.76rem; line-height: 1.45;">
                                <li><strong>15-Minute Seating Hold:</strong> Seats and tables are reserved and held for 15 minutes past your scheduled arrival time.</li>
                                <li><strong>Staff Table Forfeiture:</strong> If you have not arrived and checked in onsite within 15 minutes, the Staff reserves the right to mark this booking as a No-Show and release the table back to Available for walk-in guests.</li>
                                <li><strong>Onsite Staff Verification:</strong> Upon arrival, present your Dynamic QR Pass to the Staff at the entrance to verify your attendance and display your table number.</li>
                                <li><strong>Fast-Food Counter Ordering:</strong> Present your QR Pass at the Cashier POS counter to load and modify pre-order items before payment and kitchen preparation.</li>
                            </ul>
                            <div class="form-check pt-2 border-top">
                                <input class="form-check-input" type="checkbox" id="agreeTermsCheck" required checked>
                                <label class="form-check-label small fw-bold text-dark" for="agreeTermsCheck">
                                    I agree to the 15-Minute Seating Reservation Policy and acknowledge that unclaimed seats may be released to available after 15 minutes. <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <!-- Anti-Spam Security & Customer Verification Badge -->
                        <div class="card border rounded-4 p-2.5 mb-3 bg-white shadow-xs">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="humanVerificationCheck" required checked>
                                <label class="form-check-label small text-secondary" for="humanVerificationCheck">
                                    I confirm I am reserving table space at Raeden’s Bakery and Café.
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill fw-black text-white shadow-sm fs-6" id="btnSubmitRes" style="background: var(--brand-secondary-gradient); border: none;">
                            <i class="bi bi-qr-code-scan me-2"></i> Confirm Reservation &amp; Generate QR Pass
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================
     MODAL: MODALIZED ARTISAN MENU & CHEF'S SPECIALS BROWSER
     ======================================================== -->
<div class="modal fade" id="menuSpecialsModal" tabindex="-1" aria-labelledby="menuSpecialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header text-white px-4 py-3 border-0" style="background: var(--brand-primary-gradient);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs" style="background-color: var(--brand-secondary); width: 42px; height: 42px;">
                        <i class="bi bi-cup-hot-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-black mb-0 text-white" id="menuSpecialsModalLabel" style="font-family: 'Outfit', sans-serif;">Menu &amp; Chef’s Specials</h5>
                        <small class="text-white-50" style="font-size: 0.75rem;">Raeden’s Bakery and Café • Fresh Bakes &amp; Specialty Brews</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-md-4" style="background-color: #FAF6F0;">
                
                <!-- Sticky Search & Category Filter Toolbar -->
                <div class="modal-menu-sticky-toolbar">
                    <!-- Search Input & Results Badge -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="input-group input-group-sm flex-grow-1">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control bg-light border-start-0 pe-2" id="modalMenuSearch" placeholder="Search menu by name or flavor..." oninput="searchModalMenu(this.value)">
                            <button type="button" class="btn btn-light border border-start-0 rounded-end-pill text-muted pe-3" id="modalMenuSearchClear" onclick="clearModalSearch()" style="display: none;" title="Clear search">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1.5 small text-nowrap d-none d-sm-inline-flex" id="modalMenuCountBadge">
                            <?= count($products) ?> Items
                        </span>
                    </div>

                    <!-- Category Filter Tabs: Full-width Touch-Friendly Scrollable Pills -->
                    <div class="modal-menu-tabs" id="modalMenuCatTabs">
                        <button type="button" class="btn btn-sm btn-navy text-white rounded-pill menu-cat-btn active" data-cat="all" onclick="filterMenu('all', this)">
                            <i class="bi bi-grid-fill me-1"></i> All Items
                        </button>
                        <?php foreach ($categories as $c): ?>
                            <?php
                            $catIcon = 'bi-cup-hot';
                            if ($c['id'] == 1) $catIcon = 'bi-cup-hot-fill text-warning';
                            elseif ($c['id'] == 2) $catIcon = 'bi-cup-straw text-primary';
                            elseif ($c['id'] == 3) $catIcon = 'bi-snow text-info';
                            elseif ($c['id'] == 4) $catIcon = 'bi-cake2 text-danger';
                            ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill menu-cat-btn" data-cat="<?= $c['id'] ?>" onclick="filterMenu('<?= $c['id'] ?>', this)">
                                <i class="bi <?= $catIcon ?> me-1"></i> <?= htmlspecialchars($c['name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row g-3" id="menuGrid">
                    <?php foreach ($products as $p): ?>
                        <div class="col-md-6 col-lg-4 menu-item-card" data-cat="<?= $p['category_id'] ?>" data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>">
                            <div class="card h-100 border-0 shadow-xs rounded-4 p-3 bg-white product-card d-flex flex-column justify-content-between card-lift" style="border: 1px solid rgba(213, 197, 181, 0.45) !important;">
                                <div class="d-flex gap-3 align-items-start mb-2.5">
                                    <div class="rounded-4 overflow-hidden bg-light d-flex align-items-center justify-content-center text-navy fw-bold border shadow-xs flex-shrink-0 position-relative" style="width: 74px; height: 74px;">
                                        <img src="<?= !empty($p['image_url']) ? htmlspecialchars($p['image_url']) : 'assets/images/placeholder-product.svg' ?>" 
                                             alt="<?= htmlspecialchars($p['name']) ?>" 
                                             class="w-100 h-100 object-fit-cover"
                                             onerror="this.onerror=null; this.src='assets/images/placeholder-product.svg';">
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                                            <h6 class="fw-bold mb-0 text-dark text-truncate" title="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                            <span class="badge bg-light text-secondary border small" style="font-size: 0.68rem;"><?= htmlspecialchars($p['category_name']) ?></span>
                                            <span class="fw-black text-navy fs-6">₱<?= number_format($p['base_price'], 2) ?></span>
                                        </div>
                                        <p class="text-muted small mb-0" style="font-size: 0.76rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($p['description'] ?? 'Artisan handcrafted recipe with fresh ingredients.') ?></p>
                                    </div>
                                </div>
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <small class="text-secondary" style="font-size: 0.72rem;"><i class="bi bi-clock-history me-1"></i> Fresh bakes</small>
                                    <button type="button" class="btn btn-sm btn-outline-navy fw-bold rounded-pill px-3 shadow-xs" onclick="openCustomizeModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                                        <i class="bi bi-sliders me-1"></i> Customize &amp; Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- No Results Alert -->
                <div id="modalMenuNoResults" class="alert alert-light border rounded-4 p-4 text-center text-muted my-3" style="display: none;">
                    <i class="bi bi-search fs-3 text-secondary d-block mb-2"></i>
                    <strong>No matching items found</strong>
                    <div class="small text-secondary mt-1">Try adjusting your category filter or search keywords.</div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-white border-top py-2.5 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 38px; height: 38px;">
                        <i class="bi bi-basket2 text-dark fs-6"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-navy small" style="line-height: 1.2;">Pre-Order Cart: <span class="modalCartCount fw-black text-warning">0</span> item(s)</div>
                        <small class="text-muted">Estimated Subtotal: <strong class="modalCartTotal text-navy">₱0.00</strong></small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-navy rounded-pill px-3.5 py-1.5 fw-bold shadow-xs" data-bs-dismiss="modal">
                        <i class="bi bi-check-lg me-1"></i> Continue Booking
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Customization (Size, Sweetness, Ice, Addons) -->
<div class="modal fade" id="customizeModal" tabindex="-1" aria-labelledby="customizeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--brand-primary-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-warning" id="customModalIcon"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="customItemTitle">Customize Item</h5>
                        <small class="text-white-50" id="customItemSubtitle">Handcrafted Raeden's Recipe</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="custProdId">
                <input type="hidden" id="custProdName">
                <input type="hidden" id="custProdCatId">
                <input type="hidden" id="custBasePrice">

                <!-- Beverage Sizing Section -->
                <div class="mb-3" id="custSizeContainer">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Cup Size</label>
                        <span class="badge bg-light text-muted border small fw-normal" id="custSizeBadge">Select Size</span>
                    </div>
                    <div class="row g-2" id="custSizeRadios">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Pastry Serving Notice (Category 4 only) -->
                <div class="mb-3 d-none" id="custPastryNotice">
                    <div class="alert alert-light border border-secondary-subtle rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning-subtle text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bi bi-cake2 fs-5 text-warning"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-navy">Fresh Artisan Bakery & Pastry</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Standard single serving portion included at listed base price. Freshly baked daily at Raeden’s Bakery.</div>
                        </div>
                    </div>
                </div>

                <!-- Sweetness / Sugar Level Selection (Beverages only) -->
                <div class="mb-3" id="custSugarContainer">
                    <label class="form-label small fw-bold text-secondary">Sweetness / Sugar Level</label>
                    <select class="form-select" id="custSugar">
                        <option value="100% (Standard Sweet)">100% (Standard Sweet)</option>
                        <option value="75% (Less Sweet)">75% (Less Sweet)</option>
                        <option value="50% (Half Sweet)" selected>50% (Half Sweet)</option>
                        <option value="25% (Mild Sweet)">25% (Mild Sweet)</option>
                        <option value="0% (Unsweetened)">0% (Unsweetened)</option>
                    </select>
                </div>

                <!-- Ice Level Selection (Cold Beverages only) -->
                <div class="mb-3" id="custIceContainer">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Ice Level</label>
                        <span class="badge bg-light text-muted border small fw-normal" id="custIceBadge">Chilled</span>
                    </div>
                    <select class="form-select" id="custIce">
                        <option value="Regular Ice" selected>Regular Ice</option>
                        <option value="Less Ice">Less Ice</option>
                        <option value="Extra Ice">Extra Ice</option>
                        <option value="No Ice">No Ice</option>
                    </select>
                    <div class="alert alert-light border py-2 px-3 mt-2 mb-0 d-none small text-secondary rounded-3" id="custHotNotice">
                        <i class="bi bi-cup-hot-fill text-warning me-1"></i> Freshly brewed & served hot. Ice is not applicable.
                    </div>
                </div>

                <!-- Pastry Serving Preference (Category 4 only) -->
                <div class="mb-3 d-none" id="custPastryPrepContainer">
                    <label class="form-label small fw-bold text-secondary mb-1">Serving Preference</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="custPastryPrep" id="prep_warm" value="Warm / Lightly Toasted" checked>
                            <label class="btn btn-outline-secondary w-100 rounded-3 text-start p-2.5 h-100" for="prep_warm">
                                <div class="fw-bold small text-navy"><i class="bi bi-fire text-warning me-1"></i> Warm / Toasted</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Lightly warmed in oven</div>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="custPastryPrep" id="prep_asis" value="Serve As-Is (Room Temp)">
                            <label class="btn btn-outline-secondary w-100 rounded-3 text-start p-2.5 h-100" for="prep_asis">
                                <div class="fw-bold small text-navy"><i class="bi bi-box2 text-primary me-1"></i> Serve As-Is</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Fresh at room temp</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Addons Selection (Beverages only) -->
                <div class="mb-3" id="custAddonContainer">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Beverage Add-ons</label>
                        <span class="badge bg-light text-muted border small fw-normal">Optional</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($addons as $ad): 
                            $addonLabel = $ad['addon_name'] ?? $ad['name'] ?? 'Add-on';
                        ?>
                            <label class="addon-card shadow-xs" for="addon_<?= $ad['id'] ?>">
                                <div class="d-flex align-items-center gap-2.5 overflow-hidden me-2">
                                    <input class="form-check-input cust-addon" type="checkbox" value="<?= htmlspecialchars($addonLabel) ?>" data-price="<?= $ad['price'] ?>" id="addon_<?= $ad['id'] ?>" onchange="updateCustomPrice()">
                                    <span class="fw-semibold small text-dark text-truncate"><?= htmlspecialchars($addonLabel) ?></span>
                                </div>
                                <span class="badge bg-warning-subtle text-dark border fw-bold flex-shrink-0">+₱<?= number_format($ad['price'], 2) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Special Barista Note -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Special Request / Note (Optional)</label>
                    <input type="text" class="form-control form-control-sm rounded-3" id="custSpecialNote" placeholder="e.g. Less sweet, honey on the side, extra napkin" maxlength="120">
                </div>

                <!-- Quantity & Calculated Line Total -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px;" onclick="changeCustQty(-1)"><i class="bi bi-dash"></i></button>
                        <span class="fw-bold px-2 fs-6" id="custQtyDisplay">1</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px;" onclick="changeCustQty(1)"><i class="bi bi-plus"></i></button>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Item Subtotal</div>
                        <h4 class="fw-black text-navy mb-0" id="custTotalDisplay">₱0.00</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-warning w-100 py-2.5 rounded-pill fw-bold text-dark shadow-xs" onclick="addCustomItemToCart()">
                    <i class="bi bi-bag-plus me-1"></i> Add to Pre-Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Instant QR Pass Confirmation (Airline Boarding Pass Ticket Aesthetic) -->
<div class="modal fade" id="qrSuccessModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="qrSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content boarding-pass-card border-0 shadow-lg text-center overflow-hidden">
            
            <!-- Boarding Pass Header -->
            <div class="boarding-pass-header text-white text-start" style="background: var(--brand-primary-gradient);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="badge text-white fw-bold text-uppercase px-2.5 py-0.5 mb-1" style="background-color: var(--brand-secondary); font-size: 0.68rem; letter-spacing: 0.05em;">Raeden's</div>
                        <h5 class="fw-black mb-0">Digital Reservation Pass</h5>
                        <div class="text-white-50 small">Raeden’s Bakery and Café</div>
                    </div>
                    <div class="rounded-circle text-white p-2 fs-4 d-flex align-items-center justify-content-center shadow-xs" style="background-color: var(--brand-secondary); width: 44px; height: 44px;">
                        <i class="bi bi-check2"></i>
                    </div>
                </div>
            </div>

            <div class="modal-body p-4 bg-white">
                <p class="text-muted small mb-3">Your table / seating reservation is confirmed! Present this scannable QR pass to the staff upon arrival.</p>
                
                <!-- High-contrast QR Pass Frame -->
                <div class="bg-light p-3 rounded-4 border d-inline-block mx-auto mb-3 shadow-xs position-relative">
                    <div id="reservationQrCode" class="d-flex justify-content-center"></div>
                </div>

                <div class="badge bg-navy text-warning px-3 py-2 rounded-pill fs-6 fw-mono mb-3" id="modalResNumber">RES-2026-000</div>

                <!-- Perforated Ticket Notches -->
                <div class="perforated-separator">
                    <div class="notch-left"></div>
                    <div class="notch-right"></div>
                </div>

                <div class="bg-light p-3 rounded-4 text-start small mb-3 border">
                    <div class="row g-2">
                        <div class="col-6"><span class="text-muted">Guest:</span> <strong class="d-block text-truncate" id="modalGuestName">--</strong></div>
                        <div class="col-6"><span class="text-muted">Table:</span> <strong class="d-block text-navy" id="modalTableName">--</strong></div>
                        <div class="col-6"><span class="text-muted">Zone:</span> <strong class="d-block" id="modalZone">--</strong></div>
                        <div class="col-6"><span class="text-muted">Time Slot:</span> <strong class="d-block" id="modalTimeSlot">--</strong></div>
                        <div class="col-12 border-top pt-2 mt-2" id="modalScheduledPrepRow">
                            <span class="text-muted"><i class="bi bi-qr-code text-primary me-1"></i>POS Counter Processing:</span>
                            <strong class="d-block text-success" id="modalScheduledPrepInfo">Scan QR Pass at POS counter upon arrival</strong>
                        </div>
                        <div class="col-12 border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                            <div><span class="text-muted">Pre-Order Estimate:</span> <strong class="text-navy fs-6 ms-1" id="modalTotalAmount">₱0.00</strong></div>
                            <span class="badge bg-warning-subtle text-dark border">Pay at Cashier</span>
                        </div>
                    </div>
                </div>

                <!-- Post-Booking Customer Next Steps Callout -->
                <div class="alert alert-info border-info-subtle rounded-4 p-2.5 text-start small mb-3">
                    <div class="fw-bold text-navy mb-1"><i class="bi bi-info-circle text-primary me-1"></i>How To Claim Your Reserved Seat & Order</div>
                    <ul class="mb-0 ps-3 text-secondary" style="font-size: 0.74rem;">
                        <li><strong>Reserved Seating:</strong> Your dining table or booth is confirmed and secured for your arrival.</li>
                        <li><strong>Counter Check-In & POS Scan:</strong> Present your scannable QR Pass at the Cashier POS counter. The cashier scans it to instantly load your pre-order.</li>
                        <li><strong>Fully Editable:</strong> Just like ordering at the counter, you can add more items, change sizes/quantities, or remove items before paying!</li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <a href="#" id="modalPassLink" class="btn btn-navy py-2.5 rounded-pill fw-bold shadow-xs">
                        <i class="bi bi-qr-code me-1"></i> Open Digital Pass & Live Order Tracker
                    </a>
                    <button type="button" class="btn btn-outline-secondary py-2 rounded-pill fw-bold" onclick="window.location.reload()">
                        Book Another Table
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.selectedTable = window.selectedTable || null;
window.preOrderCart = window.preOrderCart || [];
window.currentCustQty = window.currentCustQty || 1;

// Zone Filter
function filterZone(zone, btn) {
    document.querySelectorAll('#zoneFilterTabs .nav-link').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('.table-card-item');
    cards.forEach(card => {
        if (zone === 'all' || card.dataset.zone === zone) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Select Dining Table with Trapping Validation
function selectTable(id, code, name, zone, capacity, isSelectable = true, reason = '') {
    if (!isSelectable) {
        showToast(reason || `Table #${id} is currently unavailable for this schedule. Please pick an available table.`, 'warning');
        return;
    }
    selectedTable = { id, code, name, zone, capacity };
    document.getElementById('formTableId').value = id;
    document.getElementById('selectedTableDisplay').textContent = `Table #${id} (${code}) - ${name}`;
    document.getElementById('selectedTableZone').textContent = `Table #${id} • ${zone} • Max Capacity: ${capacity} Guest(s)`;
    document.getElementById('selectedTableBadge').innerHTML = `<i class="bi bi-check-lg"></i> Table #${id}`;
    document.getElementById('guest_count').max = capacity;

    document.querySelectorAll('.table-select-card').forEach(el => el.classList.remove('selected'));
    const chosenCard = document.getElementById(`tableCard_${id}`);
    if (chosenCard) {
        chosenCard.classList.add('selected');
    }
    showToast(`Selected: Table #${id} (${code} - ${name})`, 'success');
}

// Live Seating Availability & Time Slot Trapping Checker
async function checkTableAvailability() {
    const selDateInput = document.getElementById('reservation_date');
    const selSlotInput = document.getElementById('time_slot');
    if (!selDateInput || !selSlotInput) return;

    const selDate = selDateInput.value;
    const selSlot = selSlotInput.value;
    if (!selDate || !selSlot) return;

    try {
        const res = await fetch(`api/reservations.php?action=check_availability&date=${encodeURIComponent(selDate)}&time_slot=${encodeURIComponent(selSlot)}`);
        const data = await res.json();
        
        if (data.success && Array.isArray(data.tables)) {
            let reselectNeeded = false;

            data.tables.forEach(t => {
                const card = document.getElementById(`tableCard_${t.id}`);
                const btn = document.getElementById(`btnSelect_${t.id}`);
                const badge = card ? card.querySelector('.status-badge') : null;
                
                if (card && btn) {
                    card.dataset.status = t.effective_status;
                    card.dataset.selectable = t.is_selectable ? '1' : '0';

                    let badgeClass = 'bg-success';
                    let statusLabel = 'Available';

                    if (t.effective_status === 'reserved') {
                        badgeClass = 'bg-warning text-dark';
                        statusLabel = 'Reserved';
                    } else if (t.effective_status === 'occupied') {
                        badgeClass = 'bg-danger text-white';
                        statusLabel = 'Occupied';
                    } else if (t.effective_status === 'maintenance') {
                        badgeClass = 'bg-dark text-white';
                        statusLabel = 'Maintenance';
                    }

                    if (badge) {
                        badge.className = `badge status-badge ${badgeClass} px-2.5 py-1 text-capitalize fw-bold`;
                        badge.innerHTML = `<i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> ${statusLabel}`;
                    }

                    if (t.is_selectable) {
                        card.classList.remove('opacity-75', 'bg-light');
                        card.classList.add('cursor-pointer');
                        card.onclick = () => selectTable(t.id, t.table_code, t.name, t.zone, t.capacity, true);
                        
                        btn.className = 'btn btn-sm btn-outline-navy w-100 fw-bold rounded-pill';
                        btn.disabled = false;
                        btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Select Table #${t.id}`;
                    } else {
                        card.classList.add('opacity-75', 'bg-light');
                        card.classList.remove('cursor-pointer');
                        card.onclick = () => showToast(`Table #${t.id} is ${statusLabel} (${t.reason}). Please choose an available table.`, 'warning');

                        btn.className = 'btn btn-sm btn-light disabled w-100 fw-bold rounded-pill';
                        btn.disabled = true;
                        btn.innerHTML = `<i class="bi bi-lock-fill me-1"></i> ${statusLabel} (${t.reason})`;

                        // If user previously selected this table, unselect it!
                        if (selectedTable && selectedTable.id === t.id) {
                            reselectNeeded = true;
                        }
                    }
                }
            });

            if (reselectNeeded) {
                selectedTable = null;
                document.getElementById('formTableId').value = '';
                document.getElementById('selectedTableDisplay').textContent = 'Please choose an available dining table from the floor plan';
                document.getElementById('selectedTableZone').textContent = 'Live table occupancy reflected in real-time';
                document.getElementById('selectedTableBadge').innerHTML = '<i class="bi bi-dash-circle"></i> No Seat Selected';
                document.querySelectorAll('.table-select-card').forEach(el => el.classList.remove('selected'));
                showToast(`Your previously selected table is unavailable for ${selSlot}. Please select an available table.`, 'warning');
            }
        }
    } catch (err) {
        console.error('Error checking table availability:', err);
    }
}

// Initial availability check & open modal if requested via URL
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(checkTableAvailability, 250);
    if (window.location.search.includes('open_menu=1') || window.location.hash === '#menu' || window.location.hash === '#menuSpecialsModal' || window.location.hash === '#menuCatTabs') {
        setTimeout(() => {
            openMenuSpecialsModal();
        }, 300);
    }
});

// Open Modalized Menu & Specials Dialog
window.openMenuSpecialsModal = function(e) {
    if (e && e.preventDefault) e.preventDefault();
    const navCollapse = document.getElementById('raedensTopNav') || document.getElementById('isatuNav');
    if (navCollapse && navCollapse.classList.contains('show') && typeof bootstrap !== 'undefined') {
        const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
        if (bsCollapse) bsCollapse.hide();
    }
    const modalEl = document.getElementById('menuSpecialsModal') || document.getElementById('preorderModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};
window.handleNavbarMenuJump = window.openMenuSpecialsModal;

// Menu Category Filter inside Modal
window.filterMenu = function(catId, btn) {
    document.querySelectorAll('#modalMenuCatTabs .menu-cat-btn').forEach(el => {
        el.classList.remove('active', 'btn-navy', 'text-white');
        el.classList.add('btn-outline-secondary');
    });

    if (btn) {
        btn.classList.add('active', 'btn-navy', 'text-white');
        btn.classList.remove('btn-outline-secondary');
        try {
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } catch(e) {}
    }

    const searchInput = document.getElementById('modalMenuSearch');
    const q = searchInput ? (searchInput.value || '').trim().toLowerCase() : '';

    let visibleCount = 0;
    const items = document.querySelectorAll('.menu-item-card');
    items.forEach(it => {
        const catMatch = (catId === 'all' || String(it.dataset.cat) === String(catId));
        const nameMatch = !q || (it.dataset.name && it.dataset.name.includes(q));
        if (catMatch && nameMatch) {
            it.style.display = '';
            visibleCount++;
        } else {
            it.style.display = 'none';
        }
    });

    const countBadge = document.getElementById('modalMenuCountBadge');
    if (countBadge) {
        countBadge.textContent = `${visibleCount} Item${visibleCount === 1 ? '' : 's'}`;
    }

    const noResultsEl = document.getElementById('modalMenuNoResults');
    if (noResultsEl) {
        noResultsEl.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
};

// Clear Search Input inside Modal
window.clearModalSearch = function() {
    const input = document.getElementById('modalMenuSearch');
    if (input) {
        input.value = '';
        searchModalMenu('');
    }
};

// Live Menu Search inside Modal
window.searchModalMenu = function(query) {
    const q = (query || '').trim().toLowerCase();
    const clearBtn = document.getElementById('modalMenuSearchClear');
    if (clearBtn) {
        clearBtn.style.display = q ? 'inline-flex' : 'none';
    }

    const activeBtn = document.querySelector('#modalMenuCatTabs .menu-cat-btn.active');
    const activeCat = activeBtn ? activeBtn.getAttribute('data-cat') : 'all';

    let visibleCount = 0;
    const items = document.querySelectorAll('.menu-item-card');
    items.forEach(it => {
        const catMatch = (activeCat === 'all' || String(it.dataset.cat) === String(activeCat));
        const nameMatch = !q || (it.dataset.name && it.dataset.name.includes(q));
        if (catMatch && nameMatch) {
            it.style.display = '';
            visibleCount++;
        } else {
            it.style.display = 'none';
        }
    });

    const countBadge = document.getElementById('modalMenuCountBadge');
    if (countBadge) {
        countBadge.textContent = `${visibleCount} Item${visibleCount === 1 ? '' : 's'}`;
    }

    const noResultsEl = document.getElementById('modalMenuNoResults');
    if (noResultsEl) {
        noResultsEl.style.display = (visibleCount === 0) ? 'block' : 'none';
    }
};

// Database Sizing Map & Category Defaults
window.PRODUCT_SIZES_MAP = <?= json_encode($sizesByProduct) ?>;

// Default sizes matrix by category ID (fallback)
window.CATEGORY_DEFAULT_SIZES = {
    1: [ // Hot Coffee & Classics
        { id: 'cat1_reg', size_name: 'Regular (12oz)', price_additional: 0 },
        { id: 'cat1_lrg', size_name: 'Large (16oz)', price_additional: 20 }
    ],
    2: [ // Iced Espresso & Signatures
        { id: 'cat2_reg', size_name: 'Regular (16oz)', price_additional: 0 },
        { id: 'cat2_lrg', size_name: 'Large (22oz)', price_additional: 25 }
    ],
    3: [ // Non-Coffee & Frappes
        { id: 'cat3_reg', size_name: 'Regular (16oz)', price_additional: 0 },
        { id: 'cat3_lrg', size_name: 'Large (22oz)', price_additional: 25 }
    ],
    4: [] // Pastries & Snacks: No cup sizes
};

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}

// Open Item Customizer
function openCustomizeModal(product) {
    const catId = parseInt(product.category_id || 1);
    document.getElementById('custProdId').value = product.id;
    document.getElementById('custProdName').value = product.name;
    document.getElementById('custProdCatId').value = catId;
    document.getElementById('custBasePrice').value = product.base_price;
    document.getElementById('custSpecialNote').value = '';
    
    const isPastry = (catId === 4);
    const isHotDrink = (catId === 1);

    // Update modal title, icon and subtitle
    const modalIcon = document.getElementById('customModalIcon');
    const modalTitle = document.getElementById('customItemTitle');
    const modalSubtitle = document.getElementById('customItemSubtitle');

    if (isPastry) {
        modalIcon.className = 'bi bi-cake2 text-warning';
        modalTitle.textContent = `Customize ${product.name}`;
        modalSubtitle.textContent = 'Artisan Pastry & Bakery Selection';
    } else if (isHotDrink) {
        modalIcon.className = 'bi bi-cup-hot text-warning';
        modalTitle.textContent = `Customize ${product.name}`;
        modalSubtitle.textContent = 'Freshly Brewed Hot Coffee';
    } else if (catId === 2) {
        modalIcon.className = 'bi bi-cup-straw text-primary';
        modalTitle.textContent = `Customize ${product.name}`;
        modalSubtitle.textContent = 'Chilled Espresso & Signature Drink';
    } else {
        modalIcon.className = 'bi bi-snow text-info';
        modalTitle.textContent = `Customize ${product.name}`;
        modalSubtitle.textContent = 'Artisan Frappe & Non-Coffee Beverage';
    }

    // Toggle Sections for Pastry vs Beverage
    const sizeContainer = document.getElementById('custSizeContainer');
    const pastryNotice = document.getElementById('custPastryNotice');
    const sugarContainer = document.getElementById('custSugarContainer');
    const iceContainer = document.getElementById('custIceContainer');
    const pastryPrepContainer = document.getElementById('custPastryPrepContainer');
    const addonContainer = document.getElementById('custAddonContainer');

    if (isPastry) {
        sizeContainer.classList.add('d-none');
        pastryNotice.classList.remove('d-none');
        sugarContainer.classList.add('d-none');
        iceContainer.classList.add('d-none');
        pastryPrepContainer.classList.remove('d-none');
        addonContainer.classList.add('d-none');

        // Reset pastry prep radio
        const warmRadio = document.getElementById('prep_warm');
        if (warmRadio) warmRadio.checked = true;
    } else {
        sizeContainer.classList.remove('d-none');
        pastryNotice.classList.add('d-none');
        sugarContainer.classList.remove('d-none');
        iceContainer.classList.remove('d-none');
        pastryPrepContainer.classList.add('d-none');
        addonContainer.classList.remove('d-none');

        // Render dynamic cup sizes for this beverage
        const sizes = (PRODUCT_SIZES_MAP && PRODUCT_SIZES_MAP[product.id] && PRODUCT_SIZES_MAP[product.id].length > 0)
            ? PRODUCT_SIZES_MAP[product.id]
            : (CATEGORY_DEFAULT_SIZES[catId] || [
                { id: 'def_reg', size_name: 'Regular', price_additional: 0 },
                { id: 'def_lrg', size_name: 'Large', price_additional: 20 }
            ]);

        let sizeHtml = '';
        sizes.forEach((s, idx) => {
            const isChecked = (idx === 0) ? 'checked' : '';
            const priceAdd = parseFloat(s.price_additional || 0);
            const priceBadge = priceAdd > 0 ? `+₱${priceAdd.toFixed(2)}` : 'Base Price';
            const badgeClass = priceAdd > 0 ? 'bg-warning-subtle text-dark border' : 'bg-light text-muted border';
            const radioId = `custSize_${product.id}_${idx}`;

            sizeHtml += `
                <div class="col-6">
                    <input type="radio" class="btn-check" name="custSize" id="${radioId}" value="${escapeHtml(s.size_name)}" data-price="${priceAdd}" ${isChecked} onchange="updateCustomPrice()">
                    <label class="btn btn-outline-secondary w-100 rounded-3 text-start p-2.5 h-100" for="${radioId}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-navy">${escapeHtml(s.size_name)}</span>
                            <span class="badge ${badgeClass} small">${priceBadge}</span>
                        </div>
                    </label>
                </div>
            `;
        });
        document.getElementById('custSizeRadios').innerHTML = sizeHtml;

        // Sugar selection reset
        document.getElementById('custSugar').value = '50% (Half Sweet)';

        // Ice selection configuration
        const iceSelect = document.getElementById('custIce');
        const hotNotice = document.getElementById('custHotNotice');
        const iceBadge = document.getElementById('custIceBadge');

        if (isHotDrink) {
            iceSelect.classList.add('d-none');
            hotNotice.classList.remove('d-none');
            iceBadge.textContent = 'Served Hot';
            iceBadge.className = 'badge bg-warning-subtle text-dark border small fw-normal';
        } else {
            iceSelect.classList.remove('d-none');
            hotNotice.classList.add('d-none');
            iceSelect.value = 'Regular Ice';
            iceBadge.textContent = 'Chilled';
            iceBadge.className = 'badge bg-info-subtle text-dark border small fw-normal';
        }

        // Uncheck all addons
        document.querySelectorAll('.cust-addon').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.addon-card');
            if (card) card.classList.remove('addon-card-checked');
        });
    }

    currentCustQty = 1;
    document.getElementById('custQtyDisplay').textContent = 1;
    updateCustomPrice();

    const custModal = new bootstrap.Modal(document.getElementById('customizeModal'));
    custModal.show();
}

function changeCustQty(delta) {
    currentCustQty = Math.max(1, currentCustQty + delta);
    document.getElementById('custQtyDisplay').textContent = currentCustQty;
    updateCustomPrice();
}

function updateCustomPrice() {
    const catId = parseInt(document.getElementById('custProdCatId').value || 1);
    const base = parseFloat(document.getElementById('custBasePrice').value || 0);
    
    let sizeAdd = 0;
    let addonSum = 0;

    if (catId !== 4) { // Not pastry
        const sizeRadio = document.querySelector('input[name="custSize"]:checked');
        sizeAdd = parseFloat(sizeRadio ? sizeRadio.dataset.price || 0 : 0);

        document.querySelectorAll('.cust-addon:checked').forEach(cb => {
            addonSum += parseFloat(cb.dataset.price || 0);
        });
    }

    const itemUnit = base + sizeAdd + addonSum;
    const lineTotal = itemUnit * currentCustQty;
    document.getElementById('custTotalDisplay').textContent = formatPeso(lineTotal);
}

function addCustomItemToCart() {
    const prodId = parseInt(document.getElementById('custProdId').value);
    const prodName = document.getElementById('custProdName').value;
    const catId = parseInt(document.getElementById('custProdCatId').value || 1);
    const base = parseFloat(document.getElementById('custBasePrice').value || 0);
    const specialNote = document.getElementById('custSpecialNote').value.trim();

    let sizeName = 'Standard Serving';
    let sizeAdd = 0;
    let sugar = 'Pastry';
    let ice = 'Served As-Is';
    const addons = [];
    let addonSum = 0;

    if (catId === 4) { // Pastry
        sizeName = 'Standard Serving';
        sizeAdd = 0;
        const prepRadio = document.querySelector('input[name="custPastryPrep"]:checked');
        sugar = 'Pastry';
        ice = prepRadio ? prepRadio.value : 'Warm / Lightly Toasted';
    } else { // Beverage
        const sizeRadio = document.querySelector('input[name="custSize"]:checked');
        sizeName = sizeRadio ? sizeRadio.value : 'Regular';
        sizeAdd = parseFloat(sizeRadio ? sizeRadio.dataset.price || 0 : 0);
        sugar = document.getElementById('custSugar').value;
        
        if (catId === 1) {
            ice = 'Served Hot';
        } else {
            ice = document.getElementById('custIce').value;
        }

        document.querySelectorAll('.cust-addon:checked').forEach(cb => {
            addons.push(cb.value);
            addonSum += parseFloat(cb.dataset.price || 0);
        });
    }

    const unitPrice = base + sizeAdd + addonSum;

    preOrderCart.push({
        product_id: prodId,
        product_name: prodName,
        category_id: catId,
        size_name: sizeName,
        sugar_level: sugar,
        ice_level: ice,
        addons: addons,
        special_notes: specialNote,
        quantity: currentCustQty,
        unit_price: unitPrice
    });

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('customizeModal'));
    if (modalInstance) modalInstance.hide();

    renderCart();
    showToast(`Added ${prodName} (${sizeName}) to pre-orders!`, 'success');
}

function removeCartItem(idx) {
    preOrderCart.splice(idx, 1);
    renderCart();
}

function renderCart() {
    const list = document.getElementById('preorderCartList');
    const countBadge = document.getElementById('cartCount');
    const totalEl = document.getElementById('cartTotalDisplay');

    if (countBadge) countBadge.textContent = preOrderCart.length;
    document.querySelectorAll('.modalCartCount, .headerCartCount').forEach(el => el.textContent = preOrderCart.length);

    if (preOrderCart.length === 0) {
        list.innerHTML = `
            <div class="text-center py-3 text-muted small" id="emptyCartText">
                <i class="bi bi-cup-hot text-secondary fs-3 d-block mb-1 text-opacity-50"></i>
                No pre-ordered items yet. Click "Add Items" to browse refreshments ahead!
            </div>
        `;
        totalEl.textContent = '₱0.00';
        document.querySelectorAll('.modalCartTotal').forEach(el => el.textContent = '₱0.00');
        return;
    }

    let html = '';
    let grandTotal = 0;

    preOrderCart.forEach((it, index) => {
        const lineTotal = it.unit_price * it.quantity;
        grandTotal += lineTotal;

        const isPastry = (it.category_id === 4 || it.size_name === 'Standard Serving');
        let details = '';
        if (isPastry) {
            details = `<i class="bi bi-cake2 text-warning me-1"></i>${escapeHtml(it.ice_level)}`;
        } else {
            const addonStr = it.addons && it.addons.length ? ' • ' + it.addons.join(', ') : '';
            details = `${escapeHtml(it.sugar_level)} • ${escapeHtml(it.ice_level)}${escapeHtml(addonStr)}`;
        }
        if (it.special_notes) {
            details += ` <span class="text-navy fst-italic">(${escapeHtml(it.special_notes)})</span>`;
        }

        html += `
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div>
                    <div class="fw-bold small text-navy">
                        ${it.quantity}x ${escapeHtml(it.product_name)} 
                        <span class="badge bg-light text-secondary border ms-1">${escapeHtml(it.size_name)}</span>
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem;">${details}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold text-dark">${formatPeso(lineTotal)}</span>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeCartItem(${index})" title="Remove item"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    });

    list.innerHTML = html;
    totalEl.textContent = formatPeso(grandTotal);
    document.querySelectorAll('.modalCartTotal').forEach(el => el.textContent = formatPeso(grandTotal));
}

function handleClassificationChange() {
    const val = document.getElementById('customer_type').value;
    const idWrapper = document.getElementById('idNumberWrapper');
    const idInput = document.getElementById('id_number');
    if (val === 'Senior/PWD') {
        idWrapper.querySelector('label').innerHTML = 'Senior / PWD ID Card No. <span class="text-danger">*</span>';
        idInput.placeholder = 'e.g. OSCA / PWD ID Number';
        idInput.required = true;
    } else if (val === 'Student') {
        idWrapper.querySelector('label').innerHTML = 'Student ID Number <span class="text-danger">*</span>';
        idInput.placeholder = 'e.g. Valid Student ID Number';
        idInput.required = true;
    } else if (val === 'VIP') {
        idWrapper.querySelector('label').innerHTML = 'VIP / Member ID No. <span class="text-primary">*</span>';
        idInput.placeholder = 'e.g. Member or VIP Card No.';
        idInput.required = false;
    } else {
        idWrapper.querySelector('label').innerHTML = 'ID / Reference <span class="text-muted small fw-normal">(Optional for Regular)</span>';
        idInput.placeholder = 'Optional for regular diners';
        idInput.required = false;
    }
}

// Sync exact arrival_time input when a standard time_slot is chosen
function onTimeSlotChanged() {
    const slotVal = document.getElementById('time_slot').value || '';
    const slotMap = {
        '08:00 AM - 10:00 AM': '08:00',
        '10:00 AM - 12:00 PM': '10:00',
        '01:00 PM - 03:00 PM': '13:00',
        '03:00 PM - 05:00 PM': '15:00',
        '05:00 PM - 07:00 PM': '17:00'
    };
    const arrInput = document.getElementById('arrival_time');
    if (arrInput && slotMap[slotVal]) {
        arrInput.value = slotMap[slotVal];
    }
    checkTableAvailability();
    updateScheduledPrepPreview();
}

function updateScheduledPrepPreview() {
    const arrInput = document.getElementById('arrival_time');
    const previewEl = document.getElementById('scheduledPrepPreviewText');
    if (!arrInput || !previewEl || !arrInput.value) return;

    const parts = arrInput.value.split(':');
    if (parts.length < 2) return;
    const h = parseInt(parts[0], 10);
    const m = parseInt(parts[1], 10);

    const arrDate = new Date();
    arrDate.setHours(h, m, 0, 0);
    const prepDate = new Date(arrDate.getTime() - 15 * 60 * 1000);

    const fmt = (d) => d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    previewEl.textContent = `at ${fmt(prepDate)} (15 minutes before your ${fmt(arrDate)} arrival)`;
}

function handleArrivalChange() {
    updateScheduledPrepPreview();
}

async function lookupExistingCustomerBooking(e) {
    e.preventDefault();
    const q = (document.getElementById('existingBookingLookupInput')?.value || '').trim();
    if (!q) {
        showToast('Please enter your Reservation Number (RES-...), QR Token, or Phone Number.', 'warning');
        return;
    }

    try {
        const res = await fetch(`api/reservations.php?action=lookup_booking&query=${encodeURIComponent(q)}`);
        const data = await res.json();
        if (data.success && data.reservation) {
            window.location.href = `pass?token=${encodeURIComponent(data.reservation.qr_token)}`;
        } else {
            showToast(data.message || 'No booking found with that reference.', 'danger');
        }
    } catch (err) {
        showToast('Network error looking up reservation.', 'danger');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateScheduledPrepPreview();
});

// Submit Reservation
async function submitReservation(e) {
    e.preventDefault();

    if (!selectedTable) {
        showToast('Please select a table from the floor map first!', 'warning');
        return;
    }

    const agreeCheck = document.getElementById('agreeTermsCheck');
    if (agreeCheck && !agreeCheck.checked) {
        showToast('Please agree to the 15-Minute Seating Reservation Policy before confirming.', 'warning');
        agreeCheck.focus();
        return;
    }

    const btn = document.getElementById('btnSubmitRes');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processing Reservation...`;

    const payload = {
        table_id: selectedTable.id,
        customer_name: document.getElementById('customer_name').value.trim(),
        customer_type: document.getElementById('customer_type').value,
        id_number: document.getElementById('id_number').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        email: document.getElementById('email').value.trim(),
        reservation_date: document.getElementById('reservation_date').value,
        time_slot: document.getElementById('time_slot').value,
        arrival_time: document.getElementById('arrival_time')?.value || '',
        guest_count: document.getElementById('guest_count').value,
        special_notes: document.getElementById('special_notes').value.trim(),
        items: preOrderCart
    };

    try {
        const res = await fetch('api/reservations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            // Render QR Code in modal
            const qrContainer = document.getElementById('reservationQrCode');
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: data.qr_token,
                width: 180,
                height: 180,
                colorDark: '#002080',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });

            document.getElementById('modalResNumber').textContent = data.reservation_number;
            document.getElementById('modalGuestName').textContent = data.customer_name;
            document.getElementById('modalTableName').textContent = data.table_name;
            document.getElementById('modalZone').textContent = data.zone;
            document.getElementById('modalTimeSlot').textContent = data.time_slot;
            document.getElementById('modalTotalAmount').textContent = formatPeso(data.total_amount);
            document.getElementById('modalPassLink').href = `pass?token=${encodeURIComponent(data.qr_token)}`;

            const prepInfoEl = document.getElementById('modalScheduledPrepInfo');
            if (prepInfoEl) {
                prepInfoEl.innerHTML = `<span class="text-success"><i class="bi bi-qr-code-scan me-1"></i>Present QR Pass at Cashier Counter to load & customize order</span>`;
            }

            const successModal = new bootstrap.Modal(document.getElementById('qrSuccessModal'));
            successModal.show();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Seating Schedule Conflict',
                    text: data.message || 'The selected table is currently unavailable or booked for this time slot.',
                    icon: 'warning',
                    confirmButtonText: 'Pick an Available Table',
                    customClass: { popup: 'rounded-4 shadow-lg border-0', confirmButton: 'btn btn-navy px-4 py-2 rounded-pill fw-bold' },
                    buttonsStyling: false
                });
            } else {
                showToast(data.message || 'Failed to complete reservation.', 'danger');
            }
            checkTableAvailability();
        }
    } catch (err) {
        showToast('Network error while processing reservation. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-qr-code-scan me-2"></i> Confirm Reservation & Generate QR Pass`;
    }
}

// Stacked modal handler: keep body scroll locked when customizeModal closes back into menuSpecialsModal
document.getElementById('customizeModal')?.addEventListener('hidden.bs.modal', function () {
    const menuModal = document.getElementById('menuSpecialsModal');
    if (menuModal && menuModal.classList.contains('show')) {
        document.body.classList.add('modal-open');
    }
});

// Expose functions globally for event handlers & router
window.openCustomizeModal = openCustomizeModal;
window.changeCustQty = changeCustQty;
window.updateCustomPrice = updateCustomPrice;
window.addCustomItemToCart = addCustomItemToCart;
window.renderCart = renderCart;
window.removeCartItem = removeCartItem;
window.filterZone = filterZone;
window.selectTable = selectTable;
window.checkTableAvailability = checkTableAvailability;
window.handleArrivalChange = handleArrivalChange;
window.handleReservationSubmit = handleReservationSubmit;
window.openMenuSpecialsModal = openMenuSpecialsModal;
window.searchModalMenu = searchModalMenu;
window.clearModalSearch = clearModalSearch;
</script>
