<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Cashier Touch POS Terminal View
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

require_once __DIR__ . '/../includes/inventory.php';
inv_ensure_schema($pdo);

// Fetch categories, products, dining tables, sizes, addons
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.category_id, p.name")->fetchAll();
$stockByProduct = [];
$alertByProduct = [];
foreach ($products as $p) {
    $stockByProduct[(int)$p['id']] = (int)$p['stock_quantity'];
    $alertByProduct[(int)$p['id']] = (int)$p['min_stock_alert'];
}
$tables = $pdo->query("SELECT * FROM dining_tables ORDER BY zone, table_code")->fetchAll();
$allSizes = $pdo->query("SELECT * FROM product_sizes ORDER BY product_id, price_additional")->fetchAll();
$sizesByProduct = [];
foreach ($allSizes as $s) {
    $sizesByProduct[$s['product_id']][] = [
        'id' => (int)$s['id'],
        'size_name' => $s['size_name'],
        'price_additional' => (float)$s['price_additional']
    ];
}
$addons = $pdo->query("SELECT * FROM product_addons WHERE is_available = 1 ORDER BY id")->fetchAll();
$packagingStockMap = inv_get_packaging_stock_map($pdo);
?>

<div class="container-fluid px-3 px-lg-4 py-3 pos-container">

    <!-- 15-Minute Reservation Urgency Notification Banner -->
    <div id="posUrgentReservationBanner" class="alert alert-warning border border-warning shadow-sm rounded-4 p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3" style="display: none;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning text-dark p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px;">
                <i class="bi bi-bell-fill fs-5"></i>
            </div>
            <div>
                <div class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="fs-6">⚠️ Reservation Attention Needed</span>
                    <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5 small pulse-danger" id="urgentBannerBadge">Starts within 15 min</span>
                </div>
                <div class="small text-secondary" id="urgentBannerDetails">
                    A table reservation is scheduled soon. Please prepare the area and update table status.
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-navy rounded-pill fw-bold px-3 shadow-xs" onclick="openTableManagerModal()">
                <i class="bi bi-grid-3x3-gap-fill me-1 text-warning"></i> Open Table Manager
            </button>
            <button type="button" class="btn btn-sm btn-success rounded-pill fw-bold px-3 shadow-xs" id="urgentQuickOccupyBtn" onclick="quickSeatFromAlert()">
                <i class="bi bi-check2-circle me-1"></i> Mark Table Occupied
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" onclick="dismissUrgentAlert()" title="Dismiss notification">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

        <!-- Mobile Segmented View Switcher (Visible only on < lg screens) -->
    <div class="d-lg-none mb-3" id="posMobileViewSwitcher">
        <div class="bg-white p-1 rounded-pill shadow-xs border d-flex" role="tablist">
            <button type="button" class="btn btn-navy flex-fill rounded-pill py-2 fw-bold small pos-view-btn active" id="btnViewCatalog" onclick="switchPosMobileView('catalog')">
                <i class="bi bi-cup-hot-fill text-warning me-1"></i> Catalog (<span id="mobileCatalogCount"><?= count($products) ?></span>)
            </button>
            <button type="button" class="btn btn-light text-navy flex-fill rounded-pill py-2 fw-bold small pos-view-btn d-flex align-items-center justify-content-center gap-1.5" id="btnViewCart" onclick="switchPosMobileView('cart')">
                <i class="bi bi-cart3"></i> Order Tray
                <span class="badge bg-warning text-dark rounded-pill ms-1" id="mobileCartTabBadge">0</span>
            </button>
        </div>
    </div>

    <div class="row g-3 pos-main-row" id="posMainRow">
        
        <!-- Left Column: Menu Catalog & Optical QR Scanner -->
        <div class="col-xl-8 col-lg-7 d-flex flex-column" id="posCatalogCol">
            <div class="card border-0 shadow-sm rounded-4 d-flex flex-column overflow-hidden">
                
                <?php
                $posUser = $_SESSION['user'] ?? ['name' => 'Cashier Bea', 'role' => 'cashier'];
                $posInitials = strtoupper(substr($posUser['name'], 0, 2));
                ?>
                <!-- Top POS Action Bar -->
                <div class="card-header bg-white border-0 pt-3 px-3 px-xl-4 pb-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow-xs me-1 border border-secondary-subtle" style="width: 34px; height: 34px; object-fit: cover;">
                            <span class="badge bg-navy text-white fw-bold px-3 py-1.5 rounded-pill shadow-xs" style="background-color: var(--brand-primary) !important;">
                                <i class="bi bi-calculator me-1"></i> Raeden's POS Terminal #01
                            </span>
                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-xs text-dark" onclick="openTableManagerModal()" title="Manage Seating & Tables">
                                <i class="bi bi-grid-3x3-gap-fill"></i> <span class="d-none d-sm-inline">Reception & Tables</span><span class="d-inline d-sm-none">Tables</span>
                                <span class="badge bg-danger rounded-pill pulse-danger" id="posUrgentAlertPill" style="display:none;">0 Alert</span>
                            </button>
                            <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-xs" onclick="openPosDailyReportModal()" title="View Daily Sales & Shift Reading">
                                <i class="bi bi-graph-up-arrow text-warning"></i> <span class="d-none d-sm-inline">Daily Sales</span><span class="d-inline d-sm-none">Sales</span>
                            </button>
                            <?php if (($posUser['role'] ?? '') === 'admin'): ?>
                                <a href="admin" class="btn btn-outline-dark btn-sm rounded-pill px-2.5 py-1.5 fw-bold d-none d-md-inline-flex align-items-center gap-1 shadow-xs" title="Return to Admin Console">
                                    <i class="bi bi-speedometer2 text-warning"></i> Admin
                                </a>
                            <?php endif; ?>
                            <a href="booking" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1.5 fw-bold d-none d-xxl-inline-flex align-items-center gap-1 shadow-xs" title="View Customer Booking Portal">
                                <i class="bi bi-box-arrow-up-right"></i> Customer View
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-navy text-warning fw-mono fw-bold px-3 py-1.5 rounded-pill" id="posLiveClock">--:--:--</span>
                            <div class="d-flex align-items-center gap-1.5 bg-light border px-2.5 py-1 rounded-pill shadow-xs">
                                <span class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.68rem;">
                                    <?= htmlspecialchars($posInitials) ?>
                                </span>
                                <span class="small fw-bold text-dark d-none d-sm-inline"><?= htmlspecialchars(explode(' ', $posUser['name'])[0]) ?></span>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill py-0.5 px-2 ms-1 fw-bold" onclick="globalStaffLogout()" title="Sign Out of POS Station" style="font-size: 0.7rem;">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        
                        <!-- Optical Product 2D QR Code Auto-read Input + Camera QR Scanner + Menu QR Sheet -->
                        <div class="col-12 col-md-7 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-navy text-warning border-0"><i class="bi bi-qr-code-scan"></i></span>
                                <input type="text" id="barcodeInput" class="form-control" placeholder="Scan Product QR / Barcode or Customer QR Pass..." autofocus onkeydown="handleBarcodeKey(event)">
                                <button class="btn btn-navy" type="button" onclick="triggerBarcodeLookup()" title="Lookup Product SKU or Booking QR Pass"><i class="bi bi-search"></i></button>
                                <button class="btn btn-warning text-dark fw-bold" type="button" onclick="openPosQrCameraModal()" title="Scan Product Tag or Customer QR Pass using Camera">
                                    <i class="bi bi-camera-fill me-1"></i>Scan QR / Pass
                                </button>
                                <button class="btn btn-outline-navy fw-bold" type="button" onclick="openPosMenuQrModal()" title="View & Print QR Codes for All Existing Menus">
                                    <i class="bi bi-qr-code me-1"></i>Menu QRs
                                </button>
                            </div>
                        </div>

                        <!-- Product Search filter -->
                        <div class="col-12 col-md-5 col-lg-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="Search beverages & snacks..." oninput="filterSearch()">
                            </div>
                        </div>

                        <!-- Hotkey Legend -->
                        <div class="col-lg-3 text-lg-end d-none d-lg-block">
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                <kbd class="bg-light text-dark border">F2</kbd> Scan QR &bull; <kbd class="bg-light text-dark border">F4</kbd> Clear &bull; <kbd class="bg-light text-dark border">F8</kbd> Pay
                            </div>
                        </div>
                    </div>

                    <!-- Category Pills -->
                    <ul class="nav nav-pills mt-3 gap-2 overflow-auto flex-nowrap pb-1" id="posCategoryTabs">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill px-3 py-1.5 small fw-bold text-nowrap" onclick="filterCategory('all', this)">
                                All Items (<?= count($products) ?>)
                            </button>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill px-3 py-1.5 small fw-bold text-nowrap" onclick="filterCategory('<?= $cat['id'] ?>', this)">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Product Catalog Grid -->
                <div class="card-body p-3 p-xl-4 overflow-auto flex-grow-1" id="posProductGrid">
                    <div class="row g-2 g-md-3">
                        <?php foreach ($products as $prod): ?>
                            <div class="col-xl-3 col-lg-4 col-md-4 col-6 pos-product-col" 
                                 data-cat="<?= $prod['category_id'] ?>" 
                                 data-name="<?= strtolower(htmlspecialchars($prod['name'])) ?>"
                                 data-code="<?= strtolower(htmlspecialchars($prod['product_code'])) ?>">
                                
                                <div class="card h-100 border product-card card-lift shadow-xs cursor-pointer"
                                     id="posProdCard_<?= $prod['id'] ?>"
                                     onclick="openPosCustomizer(<?= htmlspecialchars(json_encode($prod)) ?>)">
                                    <div class="card-body p-2.5 p-sm-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-1.5">
                                                <span class="badge bg-light text-secondary border fw-mono" style="font-size: 0.68rem;" title="Click to view Printable Product QR Tag" onclick="event.stopPropagation(); openPosMenuQrModal('<?= htmlspecialchars($prod['product_code']) ?>')">
                                                    <i class="bi bi-qr-code me-1 text-navy"></i><?= htmlspecialchars($prod['product_code']) ?>
                                                </span>
                                                <span id="posProdBadge_<?= $prod['id'] ?>">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Made to Order</span>
                                                </span>
                                            </div>

                                            <!-- Product Thumbnail / Category Fallback Placeholder -->
                                            <div class="text-center my-2 text-navy">
                                                <div class="rounded-3 overflow-hidden bg-light d-inline-flex align-items-center justify-content-center shadow-xs border" style="width: 58px; height: 58px;">
                                                    <img src="<?= !empty($prod['image_url']) ? htmlspecialchars($prod['image_url']) : 'assets/images/placeholder-product.svg' ?>" 
                                                         alt="<?= htmlspecialchars($prod['name']) ?>" 
                                                         class="w-100 h-100 object-fit-cover"
                                                         onerror="this.onerror=null; this.src='assets/images/placeholder-product.svg';">
                                                </div>
                                            </div>

                                            <h6 class="fw-bold text-dark text-truncate mb-1" title="<?= htmlspecialchars($prod['name']) ?>"><?= htmlspecialchars($prod['name']) ?></h6>
                                            <div class="text-muted small text-truncate mb-2" style="font-size: 0.72rem;"><?= htmlspecialchars($prod['category_name']) ?></div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                            <span class="fw-black text-navy fs-6">₱<?= number_format($prod['base_price'], 2) ?></span>
                                            <button type="button" id="posProdBtn_<?= $prod['id'] ?>" class="btn btn-sm btn-outline-navy rounded-circle p-1 d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;">
                                                <i class="bi bi-plus-lg"></i>
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

        <!-- Right Column: Cashier Order Cart Tray -->
        <div class="col-xl-4 col-lg-5 d-flex flex-column" id="posCartCol">
            <div class="card border-0 shadow-sm rounded-4 pos-cart-panel overflow-hidden" id="posCartPanel">
                
                <!-- Cart Header -->
                <div class="card-header bg-navy text-white py-2.5 px-3 px-xl-4" style="background: var(--isatu-navy-gradient);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;">
                                <i class="bi bi-cart3 fs-6"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-white" style="font-size: 1.05rem;">Order Tray</h5>
                                <div class="text-white-50" id="cartTicketInfo" style="font-size: 0.7rem; letter-spacing: 0.02em;">New Order Ticket</div>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-xs" id="cartItemCountBadge">0 Items</span>
                    </div>
                </div>

                <!-- Booking QR Pass Loaded Banner -->
                <div id="posBookingPassBanner" class="d-none bg-warning bg-opacity-10 border-bottom border-warning px-3 py-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-1.5 text-truncate">
                            <span class="badge bg-warning text-dark fw-bold shadow-2xs"><i class="bi bi-qr-code-scan me-1"></i>QR PASS LOADED</span>
                            <span class="small fw-bold text-dark text-truncate" id="posBookingPassLabel">RES-000 • Guest</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" style="font-size: 0.7rem;" onclick="clearLoadedBookingPass()" title="Detach booking">
                            <i class="bi bi-x me-1"></i>Detach
                        </button>
                    </div>
                    <div class="text-secondary" style="font-size: 0.68rem;">
                        <i class="bi bi-pencil-square text-primary me-1"></i><strong>Orders are editable:</strong> Adjust quantities, remove items, or add new items as customer requests.
                    </div>
                </div>

                <!-- Customer Info & Statutory Discounts Section -->
                <div class="px-3 py-2.5 bg-light border-bottom">
                    <!-- Section Title -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="pos-cart-section-title"><i class="bi bi-person-badge me-1 text-primary"></i>Guest & Destination</span>
                        <span class="badge bg-white text-muted border shadow-2xs" style="font-size: 0.65rem;">Counter Service</span>
                    </div>

                    <!-- Customer Name & Destination Selector -->
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" id="cartCustomerName" class="form-control form-control-sm border-start-0" placeholder="Customer Name (Optional)">
                            </div>
                        </div>
                        <div class="col-5">
                            <select id="cartOrderType" class="form-select form-select-sm fw-semibold text-navy" onchange="handleOrderTypeChange()">
                                <option value="Dine-In">Dine-In</option>
                                <option value="Takeout">Takeout</option>
                                <option value="Study Pod">Study Pod</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dining Table Picker (if Dine-In) -->
                    <div class="mb-2" id="tablePickerRow">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-grid-3x3-gap"></i></span>
                            <select id="cartTableSelect" class="form-select form-select-sm border-start-0">
                                <option value="">-- Assign Table / Pod (Optional) --</option>
                                <?php foreach ($tables as $t): 
                                    $tStatus = strtolower($t['status']);
                                    $statusTag = $tStatus === 'available' ? 'Available' : ($tStatus === 'reserved' ? 'Reserved' : ($tStatus === 'occupied' ? 'Occupied' : 'Out of Service'));
                                ?>
                                    <option value="<?= htmlspecialchars($t['table_code']) ?>" data-status="<?= $tStatus ?>">
                                        Table #<?= $t['id'] ?> (<?= htmlspecialchars($t['table_code']) ?>) - <?= htmlspecialchars($t['name']) ?> [<?= $statusTag ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Statutory Discount Section -->
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="pos-cart-section-title"><i class="bi bi-tag-fill me-1 text-warning"></i>Statutory Discount</span>
                            <span class="small text-muted" id="activeDiscountBadge" style="font-size: 0.68rem;">Regular Rate</span>
                        </div>
                        <div class="row g-1.5">
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="discType" id="discNone" value="0" checked onchange="applyDiscount(0, 'None')">
                                <label class="pos-disc-btn" for="discNone">None</label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="discType" id="discSenior" value="0.20" onchange="applyDiscount(0.20, 'Senior/PWD 20%')">
                                <label class="pos-disc-btn" for="discSenior">Senior/PWD</label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="discType" id="discStudent" value="0.10" onchange="applyDiscount(0.10, 'Student 10%')">
                                <label class="pos-disc-btn" for="discStudent">Student</label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="discType" id="discMember" value="0.10" onchange="applyDiscount(0.10, 'VIP / Member 10%')">
                                <label class="pos-disc-btn" for="discMember">VIP/Member</label>
                            </div>
                        </div>
                    </div>

                    <!-- Cups, Straws & Takeout Packaging Stock Bar (Must have stock before ordering; deducted in Kitchen upon Serve) -->
                    <div class="mt-2 pt-2 border-top" id="posPackagingStockBar">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="pos-cart-section-title"><i class="bi bi-cup-straw me-1 text-navy"></i>Cups &amp; Takeout Supplies</span>
                            <span class="text-muted" style="font-size: 0.65rem;" id="posPackagingRuleHint">Deducted upon Kitchen Serve</span>
                        </div>
                        <div class="row g-1" id="posPackagingPills">
                            <!-- Populated dynamically via updatePosPackagingUI() -->
                        </div>
                    </div>
                </div>

                <!-- Line Items Scrollable Table -->
                <div class="flex-grow-1 overflow-auto pos-cart-items" id="posCartItemsList">
                    <div class="text-center py-4 px-3 my-auto text-muted" id="posEmptyCart">
                        <div class="rounded-circle bg-white border d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 52px; height: 52px;">
                            <i class="bi bi-cart-x text-secondary opacity-50 fs-4"></i>
                        </div>
                        <div class="fw-bold text-dark small mb-0.5">Order Tray is Empty</div>
                        <div class="text-secondary" style="font-size: 0.74rem;">Tap catalog items on the left or scan product QR barcode.</div>
                    </div>
                </div>

                <!-- Cart Calculation Summary & Checkout Action (Always Pinned & Visible) -->
                <div class="p-3 bg-white border-top shadow-xs">
                    <div class="d-flex justify-content-between text-secondary mb-1" style="font-size: 0.8rem;">
                        <span>Subtotal:</span>
                        <span class="fw-semibold fw-mono text-dark" id="posSubtotal">₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-danger mb-1" id="posDiscountRow" style="display: none; font-size: 0.8rem;">
                        <span><i class="bi bi-tag-fill me-1"></i>Discount (<span id="posDiscountLabel">None</span>):</span>
                        <span class="fw-bold fw-mono" id="posDiscountAmount">-₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-2" style="font-size: 0.8rem;">
                        <span>12% Philippine VAT (Included):</span>
                        <span class="fw-semibold fw-mono text-dark" id="posVat">₱0.00</span>
                    </div>

                    <!-- Highlighted Net Due Banner -->
                    <div class="pos-due-box rounded-3 p-2.5 px-3 mb-2.5 d-flex justify-content-between align-items-center border">
                        <div>
                            <div class="small fw-bold text-uppercase text-secondary" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Amount Due</div>
                            <div class="text-muted" style="font-size: 0.65rem;">Includes 12% Output VAT</div>
                        </div>
                        <h3 class="fw-black text-navy mb-0 fw-mono" id="posNetTotal">₱0.00</h3>
                    </div>

                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-danger w-100 py-2.5 rounded-pill fw-bold small d-flex align-items-center justify-content-center gap-1 shadow-xs" onclick="clearPosCart()" title="Clear all items (F4)">
                                <i class="bi bi-trash3"></i> Clear
                            </button>
                        </div>
                        <div class="col-8">
                            <button type="button" class="btn btn-warning w-100 py-2.5 rounded-pill fw-black text-dark fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2 pos-charge-btn" id="btnPosCharge" onclick="openCheckoutModal()" title="Proceed to Checkout (F8)">
                                <i class="bi bi-cash-coin fs-5"></i>
                                <span>Process Payment</span>
                                <kbd class="bg-dark text-warning border-0 px-1.5 py-0.5 rounded small ms-auto d-none d-sm-inline" style="font-size: 0.68rem;">F8</kbd>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Mobile Floating Order Summary Bar (Visible only on < lg screens) -->
    <div class="pos-mobile-cart-bar d-lg-none position-fixed bottom-0 start-0 w-100 p-2.5 px-3 z-3 justify-content-between align-items-center shadow-lg" id="posMobileFloatingBar" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-navy text-warning fw-bold px-2.5 py-1.5 rounded-pill shadow-xs" id="posMobileBadge">0 Items</span>
            <div class="small fw-bold text-dark">
                <span>Due: </span><strong class="text-navy fs-6" id="posMobileTotal">₱0.00</strong>
            </div>
        </div>
        <button type="button" class="btn btn-warning btn-sm rounded-pill fw-black px-3.5 py-2 text-dark shadow-xs" onclick="scrollToPosCart()">
            <i class="bi bi-cart-check-fill me-1"></i> Review & Pay
        </button>
    </div>
</div>

<!-- Modal: POS Item Customizer -->
<div class="modal fade" id="posCustomizerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-warning" id="posCustomModalIcon"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="posItemModalTitle">Customize Item</h5>
                        <small class="text-white-50" id="posItemModalSubtitle">Select modifiers</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="posCustId">
                <input type="hidden" id="posCustName">
                <input type="hidden" id="posCustCatId">
                <input type="hidden" id="posCustBasePrice">

                <!-- Cup Size Selection (Beverages only) -->
                <div class="mb-3" id="posSizeContainer">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Cup Size</label>
                        <span class="badge bg-light text-muted border small fw-normal" id="posSizeBadge">Select Size</span>
                    </div>
                    <div class="row g-2" id="posSizeRadios">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Pastry Serving Notice (Category 4 only) -->
                <div class="mb-3 d-none" id="posPastryNotice">
                    <div class="alert alert-light border border-secondary-subtle rounded-3 p-3 mb-0 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning-subtle text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bi bi-cake2 fs-5 text-warning"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-navy">Fresh Artisan Bakery & Pastry</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Standard single serving portion included at base price. Freshly baked daily at Raeden’s Bakery.</div>
                        </div>
                    </div>
                </div>

                <!-- Sugar Level Selection (Beverages only) -->
                <div class="mb-3" id="posSugarContainer">
                    <label class="form-label small fw-bold text-secondary">Sugar Level</label>
                    <select class="form-select" id="posSugar">
                        <option value="100% Full Sweet">100% Full Sweet</option>
                        <option value="75% Less Sweet">75% Less Sweet</option>
                        <option value="50% Half Sweet" selected>50% Half Sweet</option>
                        <option value="25% Mild Sweet">25% Mild Sweet</option>
                        <option value="0% Unsweetened">0% Unsweetened</option>
                    </select>
                </div>

                <!-- Ice Level Selection (Cold Beverages only) -->
                <div class="mb-3" id="posIceContainer">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Ice Level</label>
                        <span class="badge bg-light text-muted border small fw-normal" id="posIceBadge">Chilled</span>
                    </div>
                    <select class="form-select" id="posIce">
                        <option value="Regular Ice" selected>Regular Ice</option>
                        <option value="Less Ice">Less Ice</option>
                        <option value="Extra Ice">Extra Ice</option>
                        <option value="No Ice">No Ice</option>
                    </select>
                    <div class="alert alert-light border py-2 px-3 mt-2 mb-0 d-none small text-secondary rounded-3" id="posHotNotice">
                        <i class="bi bi-cup-hot-fill text-warning me-1"></i> Freshly brewed & served hot. Ice is not applicable.
                    </div>
                </div>

                <!-- Pastry Serving Preference (Category 4 only) -->
                <div class="mb-3 d-none" id="posPastryPrepContainer">
                    <label class="form-label small fw-bold text-secondary mb-1">Serving Preference</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="posPastryPrep" id="posPrep_warm" value="Warm / Lightly Toasted" checked>
                            <label class="btn btn-outline-navy w-100 rounded-3 text-start p-2.5 h-100 shadow-xs" for="posPrep_warm">
                                <div class="fw-bold small"><i class="bi bi-fire text-warning me-1"></i> Warm / Toasted</div>
                                <div class="small opacity-75" style="font-size: 0.72rem;">Lightly warmed in oven</div>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="posPastryPrep" id="posPrep_asis" value="Serve As-Is (Room Temp)">
                            <label class="btn btn-outline-navy w-100 rounded-3 text-start p-2.5 h-100 shadow-xs" for="posPrep_asis">
                                <div class="fw-bold small"><i class="bi bi-box2 text-info me-1"></i> Serve As-Is</div>
                                <div class="small opacity-75" style="font-size: 0.72rem;">Fresh at room temp</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Addons Selection (Beverages only) -->
                <div class="mb-3" id="posAddonContainer">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-secondary mb-0">Beverage Add-ons</label>
                        <span class="badge bg-light text-muted border small fw-normal">Optional</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($addons as $ad): 
                            $addonLabel = $ad['addon_name'] ?? $ad['name'] ?? 'Add-on';
                        ?>
                            <label class="addon-card shadow-xs" for="posAddon_<?= $ad['id'] ?>">
                                <div class="d-flex align-items-center gap-2.5 overflow-hidden me-2">
                                    <input class="form-check-input pos-addon-cb" type="checkbox" value="<?= htmlspecialchars($addonLabel) ?>" data-price="<?= $ad['price'] ?>" id="posAddon_<?= $ad['id'] ?>" onchange="calcPosCustomPrice()">
                                    <span class="fw-semibold small text-dark text-truncate"><?= htmlspecialchars($addonLabel) ?></span>
                                </div>
                                <span class="badge bg-warning-subtle text-dark border fw-bold flex-shrink-0">+₱<?= number_format($ad['price'], 2) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Special Barista Note -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Barista Kitchen Note</label>
                    <input type="text" id="posSpecialNote" class="form-control form-control-sm rounded-3" placeholder="e.g. Extra hot, oat milk only, slice in two" maxlength="150">
                </div>

                <!-- Live Cup / Straw / Takeout Box Availability Check -->
                <div id="posModalPackagingAlert" class="mb-3"></div>

                <!-- Quantity Stepper -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px;" onclick="changePosQty(-1)"><i class="bi bi-dash"></i></button>
                        <span class="fw-bold px-2 fs-6" id="posQtyDisplay">1</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px;" onclick="changePosQty(1)"><i class="bi bi-plus"></i></button>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Item Line Total</div>
                        <h4 class="fw-black text-navy mb-0" id="posCustomLineTotal">₱0.00</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-warning w-100 py-2.5 rounded-pill fw-bold text-dark shadow-xs" onclick="addCustomizedToCart()">
                    <i class="bi bi-plus-circle me-1"></i> Add to Order Tray
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Payment & Tender Checkout -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: min(780px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-3 px-sm-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cash-coin text-warning fs-5"></i>
                    <h5 class="modal-title fw-bold">Cashier Payment Checkout</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-sm-4">
                
                <!-- Amount Due Banner -->
                <div class="text-center p-2.5 p-sm-3 rounded-4 bg-light border mb-3 shadow-xs">
                    <div class="small text-muted text-uppercase fw-bold" style="letter-spacing: 0.05em;">Total Amount Due</div>
                    <h2 class="fw-black text-navy mb-0" id="modalCheckoutTotal">₱0.00</h2>
                </div>

                <!-- Payment Method Selector (Strictly Cash & GCash) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Payment Tender Method</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payMethod" id="payCash" value="Cash" checked onchange="togglePayMethod('Cash')">
                            <label class="btn btn-outline-navy w-100 fw-bold rounded-3 py-2 text-center" for="payCash">
                                <i class="bi bi-cash d-block fs-5 mb-1"></i>
                                <span class="small">Cash</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="payMethod" id="payGcash" value="GCash" onchange="togglePayMethod('GCash')">
                            <label class="btn btn-outline-navy w-100 fw-bold rounded-3 py-2 text-center" for="payGcash">
                                <i class="bi bi-qr-code d-block fs-5 mb-1"></i>
                                <span class="small">GCash</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Cash Tender Section -->
                <div id="cashTenderSection">
                    <label class="form-label small fw-bold text-secondary">Quick Cash Shortcuts</label>
                    <div class="row g-1.5 g-sm-2 mb-2.5">
                        <div class="col-4"><button type="button" class="btn btn-sm denomination-btn w-100 py-2" onclick="setCashTender('exact')">Exact Due</button></div>
                        <div class="col-4"><button type="button" class="btn btn-sm denomination-btn w-100 py-2" onclick="setCashTender(50)">₱50</button></div>
                        <div class="col-4"><button type="button" class="btn btn-sm denomination-btn w-100 py-2" onclick="setCashTender(100)">₱100</button></div>
                        <div class="col-4"><button type="button" class="btn btn-sm denomination-btn w-100 py-2" onclick="setCashTender(200)">₱200</button></div>
                        <div class="col-4"><button type="button" class="btn btn-sm denomination-btn w-100 py-2" onclick="setCashTender(500)">₱500</button></div>
                        <div class="col-4"><button type="button" class="btn btn-sm denomination-btn w-100 py-2" onclick="setCashTender(1000)">₱1,000</button></div>
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label small fw-bold text-secondary">Cash Tendered (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-navy fw-bold border-end-0">₱</span>
                            <input type="number" id="cashTenderInput" class="form-control form-control-lg border-start-0 fw-bold text-navy" placeholder="0.00" step="0.50" oninput="calcChange()">
                        </div>
                    </div>

                    <!-- Touch Screen Numeric Keypad -->
                    <div class="row g-1.5 g-sm-2 mb-2.5">
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('7')">7</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('8')">8</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('9')">9</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('4')">4</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('5')">5</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('6')">6</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('1')">1</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('2')">2</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('3')">3</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('0')">0</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn w-100" onclick="pressTenderKey('00')">00</button></div>
                        <div class="col-4"><button type="button" class="btn pos-keypad-btn text-danger w-100" onclick="pressTenderKey('backspace')"><i class="bi bi-backspace"></i></button></div>
                    </div>

                    <div class="p-2.5 p-sm-3 bg-light rounded-4 border d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-secondary">Change Due:</span>
                        <h3 class="fw-black text-success mb-0" id="changeDueDisplay">₱0.00</h3>
                    </div>
                </div>

                <!-- GCash Payment Section -->
                <div id="ewalletSection" style="display: none;">
                    <div class="alert alert-primary py-2.5 px-3 small d-flex align-items-center gap-3 rounded-3 mb-3 border-primary-subtle bg-primary-subtle text-primary-emphasis">
                        <i class="bi bi-qr-code-scan fs-3 text-primary"></i>
                        <div>
                            <div class="fw-bold">Official Raeden's GCash Merchant QR</div>
                            <span class="small opacity-75">Customer must scan the counter QR code and provide their Reference ID.</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">GCash Reference Number <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light text-primary fw-bold"><i class="bi bi-receipt"></i></span>
                            <input type="text" id="ewalletRefInput" class="form-control fw-bold text-navy" placeholder="e.g. 1029384756" maxlength="50" autocomplete="off">
                        </div>
                        <div class="form-text text-muted small mt-1"><i class="bi bi-shield-check me-1 text-success"></i>Enter the reference number from the customer's completed GCash transaction screen.</div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light px-3 px-sm-4 py-2.5 py-sm-3">
                <button type="button" class="btn btn-warning w-100 py-2.5 py-sm-3 rounded-pill fw-black text-dark fs-5 shadow-sm" id="btnConfirmPay" onclick="submitPosCheckout()">
                    <i class="bi bi-check2-circle me-2"></i> Complete & Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Transaction Completed & Dispatch Receipt -->
<div class="modal fade" id="receiptSuccessModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(680px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg text-center p-4">
            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-xs" style="width: 72px; height: 72px; font-size: 2.2rem;">
                <i class="bi bi-check-lg"></i>
            </div>
            <h4 class="fw-bold text-navy mb-1">Transaction Completed!</h4>
            <div class="text-muted small mb-3">Order has been dispatched live to the Barista Kitchen Display System.</div>

            <div class="bg-light p-3 rounded-4 border text-start small mb-4">
                <div class="d-flex justify-content-between mb-1.5">
                    <span class="text-muted">Order Number:</span>
                    <strong class="text-navy fw-mono" id="successOrderNum">--</strong>
                </div>
                <div class="d-flex justify-content-between mb-1.5">
                    <span class="text-muted">Total Paid:</span>
                    <strong class="text-navy" id="successTotalPaid">₱0.00</strong>
                </div>
                <div class="d-flex justify-content-between mb-1.5">
                    <span class="text-muted">Change:</span>
                    <strong class="text-success" id="successChange">₱0.00</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Destination:</span>
                    <strong id="successDestination">--</strong>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="#" id="btnPrintReceiptBtn" target="_blank" class="btn btn-navy py-2.5 rounded-pill fw-bold shadow-xs">
                    <i class="bi bi-printer me-2"></i> Print 80mm Thermal Receipt
                </a>
                <button type="button" class="btn btn-outline-secondary py-2 rounded-pill fw-bold" onclick="resetPos()">
                    Next Customer (New Order)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cashier Daily Sales & Shift Reading -->
<div class="modal fade" id="posDailyReportModal" tabindex="-1" aria-labelledby="posDailyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-0.5 text-uppercase" style="font-size: 0.68rem;">SHIFT AUDIT</span>
                        <h5 class="modal-title fw-black mb-0" id="posDailyReportModalLabel">Daily Sales & Cash Reading</h5>
                    </div>
                    <div class="text-white-50 small">Terminal summary, cash drawer balancing, and daily breakdown</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" id="posDailyReportDate" class="form-control form-control-sm rounded-pill border-0 px-3 fw-bold bg-light" value="<?= date('Y-m-d') ?>" onchange="loadPosDailyReport(this.value)">
                    <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-2.5" onclick="loadPosDailyReport()" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 bg-light" id="posDailyReportBody">
                <div id="posDailyReportSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted small mt-2">Computing daily sales and tender totals...</p>
                </div>
                
                <div id="posDailyReportContent" style="display: none;">
                    <!-- Key Financial KPIs -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <div class="bg-white p-3 rounded-4 shadow-xs border h-100">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Gross Sales</span>
                                <h3 class="fw-black text-navy mb-0" id="posDailyGross">₱0.00</h3>
                                <small class="text-muted" id="posDailyOrdersCount">0 orders completed</small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="bg-white p-3 rounded-4 shadow-xs border h-100">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Discounts Given</span>
                                <h3 class="fw-black text-danger mb-0" id="posDailyDiscounts">₱0.00</h3>
                                <small class="text-muted">Student / Senior / PWD</small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="bg-white p-3 rounded-4 shadow-xs border h-100 border-start border-success border-4">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Net Sales Revenue</span>
                                <h3 class="fw-black text-success mb-0" id="posDailyNet">₱0.00</h3>
                                <small class="text-muted">AOV: <span id="posDailyAov" class="fw-bold">₱0.00</span></small>
                            </div>
                        </div>
                    </div>

                    <!-- Tender Breakdown / Cash Balancing -->
                    <div class="card border-0 rounded-4 shadow-xs mb-3">
                        <div class="card-header bg-white border-0 pt-3 px-3 pb-1">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-wallet2 text-warning me-2"></i>Tender Collection Breakdown (Drawer Balancing)</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 text-center" id="posDailyTenderCards">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- VAT & Tax Compliance Summary -->
                    <div class="card border-0 rounded-4 shadow-xs mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                                <span>Vatable Sales Base (12% Output VAT):</span>
                                <strong class="text-dark" id="posDailyVatableBase">₱0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                                <span>12% Output Value Added Tax:</span>
                                <strong class="text-dark" id="posDailyVatAmount">₱0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span>Total Beverage/Food Units Sold:</span>
                                <strong class="text-dark" id="posDailyItemsSold">0 items</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Best Sellers Today -->
                    <div class="card border-0 rounded-4 shadow-xs">
                        <div class="card-header bg-white border-0 pt-3 px-3 pb-1 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-navy mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Moving Items Today</h6>
                            <span class="badge bg-light text-secondary border rounded-pill small" id="posDailyDateBadge">Today</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Item Name</th>
                                            <th>Category</th>
                                            <th class="text-center">Qty Sold</th>
                                            <th class="text-end pe-3">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody id="posDailyTopItemsTable">
                                        <!-- Dynamically loaded -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top px-4 py-2.5 d-flex justify-content-between align-items-center">
                <span class="small text-muted"><i class="bi bi-info-circle text-primary me-1"></i> Shift figures update dynamically as orders complete.</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="printPosDailyReading()">
                        <i class="bi bi-printer me-1"></i> Print Reading
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cashier & Receptionist Table Management Suite -->
<div class="modal fade" id="tableManagerModal" tabindex="-1" aria-labelledby="tableManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-0.5 text-uppercase" style="font-size: 0.68rem;">RECEPTION DESK</span>
                        <h5 class="modal-title fw-black mb-0" id="tableManagerModalLabel">Dining Tables & Seating Management</h5>
                    </div>
                    <div class="text-white-50 small">Monitor floor occupancy, update table statuses, and seat incoming reservations</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="loadTableManagerData()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Floor
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                
                <!-- Quick Metric Strip -->
                <div class="row g-2 mb-3 text-center">
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2.5 rounded-3 border shadow-xs">
                            <span class="text-muted small d-block">Available</span>
                            <h4 class="fw-black text-success mb-0" id="tmAvailableCount">0</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2.5 rounded-3 border shadow-xs">
                            <span class="text-muted small d-block">Reserved</span>
                            <h4 class="fw-black text-warning mb-0" id="tmReservedCount">0</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2.5 rounded-3 border shadow-xs">
                            <span class="text-muted small d-block">Occupied</span>
                            <h4 class="fw-black text-danger mb-0" id="tmOccupiedCount">0</h4>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2.5 rounded-3 border shadow-xs">
                            <span class="text-muted small d-block">Maintenance</span>
                            <h4 class="fw-black text-secondary mb-0" id="tmMaintenanceCount">0</h4>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls & Search -->
                <div class="card border-0 rounded-4 shadow-xs p-3 mb-3 bg-white">
                    <div class="row g-2 align-items-center justify-content-between">
                        <div class="col-md-7">
                            <div class="btn-group btn-group-sm w-100 flex-wrap" role="group" id="tmStatusFilters">
                                <button type="button" class="btn btn-navy active fw-bold px-3" onclick="filterTmStatus('all', this)">All Tables</button>
                                <button type="button" class="btn btn-outline-success fw-bold px-2.5" onclick="filterTmStatus('available', this)">Available</button>
                                <button type="button" class="btn btn-outline-warning text-dark fw-bold px-2.5" onclick="filterTmStatus('reserved', this)">Reserved</button>
                                <button type="button" class="btn btn-outline-danger fw-bold px-2.5" onclick="filterTmStatus('occupied', this)">Occupied</button>
                                <button type="button" class="btn btn-outline-secondary fw-bold px-2.5" onclick="filterTmStatus('maintenance', this)">Maintenance</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="tmSearchInput" class="form-control border-start-0" placeholder="Search Table #, code, zone, or guest..." oninput="filterTmSearch()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Grid Container -->
                <div class="row g-3" id="tmGridContainer">
                    <div class="text-center py-5 text-muted col-12">
                        <span class="spinner-border spinner-border-sm me-2"></span> Loading table statuses...
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-white border-top px-4 py-2.5 d-flex justify-content-between align-items-center">
                <span class="small text-muted"><i class="bi bi-shield-check text-success me-1"></i> Live floor updates sync immediately across POS and Online Customer Booking</span>
                <button type="button" class="btn btn-navy btn-sm rounded-pill px-4 fw-bold shadow-xs" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL: OPTICAL CAMERA & IMAGE PRODUCT QR CODE SCANNER             -->
<!-- ================================================================= -->
<div class="modal fade" id="posQrCameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(680px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code-scan text-warning fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">POS Product QR Code Scanner</h5>
                        <small class="text-white-50">Scan printed menu QR tags via camera, upload QR image, or use USB 2D scanner</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Scan Behavior Controls -->
                <div class="bg-white border rounded-4 p-3 mb-3 shadow-xs">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>When Product QR Code is Scanned:</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="posQrScanBehavior" id="posQrModeDirect" value="direct" checked>
                                <label class="btn btn-outline-navy fw-bold" for="posQrModeDirect">
                                    <i class="bi bi-cart-plus me-1"></i>Instant Add to Tray (1×)
                                </label>
                                <input type="radio" class="btn-check" name="posQrScanBehavior" id="posQrModeCustomize" value="customize">
                                <label class="btn btn-outline-navy fw-bold" for="posQrModeCustomize">
                                    <i class="bi bi-sliders me-1"></i>Open Customizer
                                </label>
                            </div>
                        </div>
                        <div class="col-md-5 ps-md-3">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="posQrKeepOpenSwitch" checked>
                                <label class="form-check-label small fw-semibold text-secondary" for="posQrKeepOpenSwitch">
                                    Continuous Multi-Item Scan
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Camera Viewport -->
                <div class="bg-dark rounded-4 overflow-hidden position-relative mx-auto border border-2 border-navy shadow-sm" style="max-width: 480px; min-height: 290px;">
                    <div id="posQrCameraReader" style="width: 100%;"></div>
                    <div id="posQrCameraPlaceholder" class="text-center text-white-50 py-5 px-3">
                        <i class="bi bi-camera-video fs-1 text-warning d-block mb-2"></i>
                        <div class="fw-bold text-white">Initializing Optical QR Camera...</div>
                        <small>Point your webcam or tablet camera at any printed Finished Product QR Tag</small>
                    </div>
                </div>

                <!-- Last Scanned Feedback Banner -->
                <div id="posQrLastScanBanner" class="alert alert-success py-2 px-3 rounded-3 mt-3 mb-0 d-none small d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-check-circle-fill me-1"></i><strong id="posQrLastScanText">Scanned!</strong></span>
                    <span class="badge bg-success" id="posQrLastScanCode">SKU</span>
                </div>

                <!-- Fallback: Scan from QR Image File or Manual SKU -->
                <div class="row g-2 mt-3 align-items-center">
                    <div class="col-sm-6">
                        <label class="btn btn-outline-navy btn-sm w-100 rounded-pill fw-bold mb-0">
                            <i class="bi bi-image me-1"></i> Scan from QR Image File
                            <input type="file" id="posQrFileInput" accept="image/*" class="d-none" onchange="handlePosQrFileScan(event)">
                        </label>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-warning text-dark btn-sm w-100 rounded-pill fw-bold" onclick="bootstrap.Modal.getInstance(document.getElementById('posQrCameraModal'))?.hide(); openPosMenuQrModal();">
                            <i class="bi bi-qr-code me-1"></i> View All Menu QR Codes
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white px-4 py-2.5 d-flex justify-content-between">
                <small class="text-muted"><i class="bi bi-usb-symbol me-1"></i>Hardware 2D USB Scanners also work anytime via the POS QR input bar.</small>
                <button type="button" class="btn btn-light btn-sm rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close Scanner</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL: EXISTING MENU QR CODES SHEET (PRINT & 1-CLICK SCAN TEST)   -->
<!-- ================================================================= -->
<div class="modal fade" id="posMenuQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1260px, 96vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code text-warning fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Menu Product QR Codes (Print &amp; Quick Scan Sheet)</h5>
                        <small class="text-white-50">Generated 2D QR Tags for all existing Finished Products / Menus</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white p-3 rounded-4 border mb-3 shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <input type="search" id="posMenuQrFilterInput" class="form-control form-control-sm rounded-pill" style="width: 240px;" placeholder="Filter menu name or SKU..." oninput="renderPosMenuQrGrid()">
                        <span class="badge bg-navy text-warning rounded-pill px-3 py-1.5" id="posMenuQrCountBadge"><?= count($products) ?> Menus</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold shadow-xs" onclick="printPosMenuQrSheet()">
                            <i class="bi bi-printer-fill me-1"></i> Print All Menu QR Tags
                        </button>
                    </div>
                </div>
                <div class="row g-3" id="posMenuQrGrid"></div>
            </div>
            <div class="modal-footer bg-white px-4 py-2.5 d-flex justify-content-between">
                <small class="text-muted"><i class="bi bi-lightbulb text-warning me-1"></i>Tip: Click <strong>Simulate Scan</strong> on any card to test POS QR scanning, or click <strong>Print All Menu QR Tags</strong> to print physical labels.</small>
                <button type="button" class="btn btn-light btn-sm rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ==========================================
// POS Mobile Responsiveness & View Switcher
// ==========================================
let currentPosMobileView = 'catalog';

function switchPosMobileView(view) {
    currentPosMobileView = view;
    const catCol = document.getElementById('posCatalogCol');
    const cartCol = document.getElementById('posCartCol');
    const btnCat = document.getElementById('btnViewCatalog');
    const btnCart = document.getElementById('btnViewCart');
    const mobBar = document.getElementById('posMobileFloatingBar');

    if (window.innerWidth < 992) {
        if (view === 'catalog') {
            if (catCol) catCol.classList.remove('d-none');
            if (cartCol) cartCol.classList.add('d-none');
            if (btnCat) {
                btnCat.className = 'btn btn-navy flex-fill rounded-pill py-2 fw-bold small pos-view-btn active';
            }
            if (btnCart) {
                btnCart.className = 'btn btn-light text-navy flex-fill rounded-pill py-2 fw-bold small pos-view-btn d-flex align-items-center justify-content-center gap-1.5';
            }
            if (mobBar && cart.length > 0) {
                mobBar.style.display = 'flex';
            }
        } else {
            if (catCol) catCol.classList.add('d-none');
            if (cartCol) cartCol.classList.remove('d-none');
            if (btnCart) {
                btnCart.className = 'btn btn-navy flex-fill rounded-pill py-2 fw-bold small pos-view-btn active d-flex align-items-center justify-content-center gap-1.5';
            }
            if (btnCat) {
                btnCat.className = 'btn btn-light text-navy flex-fill rounded-pill py-2 fw-bold small pos-view-btn';
            }
            if (mobBar) {
                mobBar.style.display = 'none';
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } else {
        if (catCol) catCol.classList.remove('d-none');
        if (cartCol) cartCol.classList.remove('d-none');
        if (mobBar) mobBar.style.display = 'none';
    }
}

window.addEventListener('resize', () => {
    switchPosMobileView(currentPosMobileView);
});

// Initialize mobile view immediately on load
if (window.innerWidth < 992) {
    switchPosMobileView('catalog');
}

// Update scrollToPosCart to switch to cart view
function scrollToPosCart() {
    switchPosMobileView('cart');
}

let cart = [];
let discountRate = 0;
let discountTypeName = 'None';
let posCustQty = 1;

// Category Filtering
function filterCategory(catId, btn) {
    document.querySelectorAll('#posCategoryTabs .nav-link').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');

    const cols = document.querySelectorAll('.pos-product-col');
    cols.forEach(col => {
        if (catId === 'all' || col.dataset.cat === catId) {
            col.style.display = 'block';
        } else {
            col.style.display = 'none';
        }
    });
}

// Search Filtering
function filterSearch() {
    const term = document.getElementById('searchInput').value.toLowerCase().trim();
    const cols = document.querySelectorAll('.pos-product-col');
    cols.forEach(col => {
        const name = col.dataset.name;
        const code = col.dataset.code;
        if (!term || name.includes(term) || code.includes(term)) {
            col.style.display = 'block';
        } else {
            col.style.display = 'none';
        }
    });
}

const POS_MENU_PRODUCTS = <?= json_encode($products) ?>;
let posQrHtml5Scanner = null;
let posQrLastScanTime = 0;

function playPosScanBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1046.5, ctx.currentTime); // C6 chime
        gain.gain.setValueAtTime(0.12, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.14);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.15);
    } catch (e) {}
}

// Optical Product QR Code / SKU Auto-read
function handleBarcodeKey(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        triggerBarcodeLookup();
    }
}

async function triggerBarcodeLookup() {
    const input = document.getElementById('barcodeInput');
    const code = input.value.trim();
    if (!code) return;
    input.value = '';
    await processScannedPosQrCode(code, 'input');
}

// Global Active Booking Context for POS Cart
window.posActiveBooking = null;

function updatePosBookingPassBanner() {
    const banner = document.getElementById('posBookingPassBanner');
    const label = document.getElementById('posBookingPassLabel');
    if (!banner) return;

    if (window.posActiveBooking) {
        const b = window.posActiveBooking;
        banner.classList.remove('d-none');
        if (label) {
            label.innerHTML = `<strong>${escapeHtml(b.reservation_number)}</strong> • ${escapeHtml(b.customer_name)} • Table #${b.table_id} (${escapeHtml(b.table_code || b.table_name || '')})`;
        }
    } else {
        banner.classList.add('d-none');
    }
}

function clearLoadedBookingPass() {
    window.posActiveBooking = null;
    updatePosBookingPassBanner();
    showToast('Booking QR pass detached from current order.', 'info');
}

async function loadReservationByIdToCart(resId) {
    if (!resId) return;
    try {
        const res = await fetch(`api/pos.php?action=load_reservation&id=${encodeURIComponent(resId)}`);
        const data = await res.json();
        if (data.success && data.reservation) {
            loadBookingPassToPos(data.reservation);
        } else {
            showToast(data.message || 'Could not load reservation details.', 'warning');
        }
    } catch (err) {
        showToast('Error loading reservation to cart.', 'danger');
    }
}

function loadBookingPassToPos(res) {
    if (!res) return;
    playPosScanBeep();

    window.posActiveBooking = res;

    // Fill customer name
    const custInput = document.getElementById('cartCustomerName');
    if (custInput && res.customer_name) {
        custInput.value = res.customer_name;
    }

    // Set destination to Dine-In
    const orderTypeSelect = document.getElementById('cartOrderType');
    if (orderTypeSelect) {
        orderTypeSelect.value = 'Dine-In';
        handleOrderTypeChange();
    }

    // Auto-select Table in dropdown
    const tableSelect = document.getElementById('cartTableSelect');
    if (tableSelect) {
        let matched = false;
        for (let i = 0; i < tableSelect.options.length; i++) {
            const opt = tableSelect.options[i];
            if (
                opt.value === res.table_code ||
                opt.value === `T-${res.table_id}` ||
                opt.textContent.includes(`Table #${res.table_id}`) ||
                opt.textContent.includes(res.table_code)
            ) {
                tableSelect.selectedIndex = i;
                matched = true;
                break;
            }
        }
    }

    // Load pre-ordered items into cart (fully editable!)
    const items = res.items || [];
    if (items.length > 0) {
        items.forEach(it => {
            const catId = parseInt(it.category_id || 1);
            const isPastry = (catId === 4);
            const size = it.size_name || (isPastry ? 'Standard Serving' : 'Regular (16oz)');
            const sugar = it.sugar_level || (isPastry ? 'Pastry' : '50% Half Sweet');
            const ice = it.ice_level || (isPastry ? 'Warm / Lightly Toasted' : 'Regular Ice');
            const qty = parseInt(it.quantity || 1);
            const price = parseFloat(it.unit_price || 0);

            cart.push({
                product_id: parseInt(it.product_id),
                product_name: it.product_name,
                category_id: catId,
                size_name: size,
                sugar_level: sugar,
                ice_level: ice,
                addons: Array.isArray(it.addons) ? it.addons : [],
                special_notes: it.special_notes || `Pre-order from QR Pass (${res.reservation_number})`,
                quantity: qty,
                unit_price: price
            });
        });

        renderPosCart();
        refreshPosProductStockUI();
        refreshPosPackagingStock();
    }

    updatePosBookingPassBanner();

    showToast(`🎫 Booking QR Pass loaded! Table #${res.table_id} (${res.table_code || 'Reserved'}) assigned with ${items.length} pre-ordered item(s). You can edit quantities, modify sizes, or add items freely!`, 'success');

    scrollToPosCart();
}

async function processScannedPosQrCode(rawCode, source = 'camera') {
    const code = String(rawCode || '').trim();
    if (!code) return;

    try {
        const res = await fetch(`api/pos.php?action=qr&code=${encodeURIComponent(code)}`);
        const data = await res.json();

        // 1. Handled if scanned code is a Customer Booking QR Pass
        if (data.success && (data.type === 'reservation_pass' || data.reservation)) {
            if (source === 'camera') {
                bootstrap.Modal.getInstance(document.getElementById('posQrCameraModal'))?.hide();
            }
            loadBookingPassToPos(data.reservation);
            return;
        }

        // 2. Handled if scanned code is a Menu Product
        if (data.success && data.product) {
            playPosScanBeep();
            const prod = data.product;
            const scanMode = document.querySelector('input[name="posQrScanBehavior"]:checked')?.value || 'direct';
            const keepOpen = document.getElementById('posQrKeepOpenSwitch')?.checked;

            const banner = document.getElementById('posQrLastScanBanner');
            const bannerTxt = document.getElementById('posQrLastScanText');
            const bannerCode = document.getElementById('posQrLastScanCode');
            if (banner && bannerTxt && bannerCode) {
                banner.classList.remove('d-none');
                bannerTxt.textContent = `${prod.name} (₱${parseFloat(prod.base_price || prod.price || 0).toFixed(2)})`;
                bannerCode.textContent = prod.product_code;
            }

            if (source === 'camera') {
                if (scanMode === 'direct') {
                    quickAddProductToPosCart(prod);
                    if (!keepOpen) {
                        bootstrap.Modal.getInstance(document.getElementById('posQrCameraModal'))?.hide();
                    }
                } else {
                    bootstrap.Modal.getInstance(document.getElementById('posQrCameraModal'))?.hide();
                    setTimeout(() => openPosCustomizer(prod), 220);
                }
            } else {
                // Triggered from #barcodeInput or Simulate Scan
                if (scanMode === 'direct') {
                    quickAddProductToPosCart(prod);
                } else {
                    openPosCustomizer(prod);
                    showToast(`QR Found: ${prod.name}`, 'success');
                }
            }
        } else {
            showToast(data.message || `No menu item or booking pass found for: ${code}`, 'warning');
        }
    } catch (err) {
        showToast('Error looking up scanned QR code', 'danger');
    }
}

function quickAddProductToPosCart(prod) {
    const catId = parseInt(prod.category_id || 1);
    const base = parseFloat(prod.base_price || prod.price || 0);
    const isPastry = (catId === 4);
    const sizeName = isPastry ? 'Standard Serving' : (catId === 1 ? 'Regular (12oz)' : 'Regular (16oz)');
    const sugar = isPastry ? 'Pastry' : '50% Half Sweet';
    const ice = isPastry ? 'Warm / Lightly Toasted' : (catId === 1 ? 'Served Hot' : 'Regular Ice');

    const newItem = {
        product_id: parseInt(prod.id),
        product_name: prod.name,
        category_id: catId,
        size_name: sizeName,
        sugar_level: sugar,
        ice_level: ice,
        addons: [],
        special_notes: 'Scanned via POS QR',
        quantity: 1,
        unit_price: base
    };

    const orderType = document.getElementById('cartOrderType')?.value || 'Dine-In';
    const projectedNeeds = calcCartPackagingNeeds([...cart, newItem], orderType);
    const shortages = validatePackagingNeeds(projectedNeeds);
    if (shortages.length > 0) {
        showToast(`Cannot add ${prod.name} — Insufficient packaging stock: ${shortages.join('; ')}`, 'danger');
        return false;
    }

    // Merge if identical default item already in cart
    const existingIdx = cart.findIndex(it =>
        parseInt(it.product_id) === newItem.product_id &&
        it.size_name === newItem.size_name &&
        it.sugar_level === newItem.sugar_level &&
        it.ice_level === newItem.ice_level &&
        (!it.addons || it.addons.length === 0)
    );
    if (existingIdx >= 0) {
        cart[existingIdx].quantity += 1;
    } else {
        cart.push(newItem);
    }

    renderPosCart();
    showToast(`QR Scanned: +1 ${prod.name} (${prod.product_code}) added to Order Tray!`, 'success');
    return true;
}

async function openPosQrCameraModal() {
    const modalEl = document.getElementById('posQrCameraModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    modalEl.addEventListener('hidden.bs.modal', () => {
        stopPosQrCamera();
    }, { once: true });

    setTimeout(async () => {
        if (typeof Html5Qrcode === 'undefined') {
            showToast('Camera QR library is loading, please try again or use image upload.', 'warning');
            return;
        }
        try {
            if (posQrHtml5Scanner) {
                await stopPosQrCamera();
            }
            document.getElementById('posQrCameraPlaceholder')?.classList.add('d-none');
            posQrHtml5Scanner = new Html5Qrcode('posQrCameraReader');
            await posQrHtml5Scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => {
                    const now = Date.now();
                    if (now - posQrLastScanTime < 1400) return; // debounce rapid duplicate frames
                    posQrLastScanTime = now;
                    processScannedPosQrCode(decodedText, 'camera');
                },
                () => {}
            );
        } catch (err) {
            const ph = document.getElementById('posQrCameraPlaceholder');
            if (ph) {
                ph.classList.remove('d-none');
                ph.innerHTML = `
                    <i class="bi bi-exclamation-triangle text-warning fs-2 d-block mb-2"></i>
                    <div class="fw-bold text-white">Camera Access Unavailable</div>
                    <small class="text-white-50">Use "Scan from QR Image File" below, or use the USB 2D QR Scanner input bar.</small>
                `;
            }
        }
    }, 300);
}

async function stopPosQrCamera() {
    if (posQrHtml5Scanner) {
        try {
            if (posQrHtml5Scanner.isScanning) {
                await posQrHtml5Scanner.stop();
            }
            posQrHtml5Scanner.clear();
        } catch (e) {}
        posQrHtml5Scanner = null;
    }
}

async function handlePosQrFileScan(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    if (typeof Html5Qrcode === 'undefined') {
        showToast('QR reader library not loaded.', 'danger');
        return;
    }
    try {
        await stopPosQrCamera();
        const fileScanner = new Html5Qrcode('posQrCameraReader');
        const decodedText = await fileScanner.scanFile(file, true);
        await processScannedPosQrCode(decodedText, 'camera');
    } catch (err) {
        showToast('Could not detect a valid QR Code in that image.', 'warning');
    } finally {
        event.target.value = '';
    }
}

function openPosMenuQrModal(filterCode = '') {
    const searchInput = document.getElementById('posMenuQrFilterInput');
    if (searchInput) searchInput.value = filterCode || '';
    renderPosMenuQrGrid();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('posMenuQrModal')).show();
}

function renderPosMenuQrGrid() {
    const grid = document.getElementById('posMenuQrGrid');
    if (!grid) return;
    const q = (document.getElementById('posMenuQrFilterInput')?.value || '').trim().toLowerCase();
    const list = (POS_MENU_PRODUCTS || []).filter(p =>
        !q || `${p.product_code} ${p.name} ${p.category_name || ''}`.toLowerCase().includes(q)
    );

    const badge = document.getElementById('posMenuQrCountBadge');
    if (badge) badge.textContent = `${list.length} Menu${list.length === 1 ? '' : 's'}`;

    if (list.length === 0) {
        grid.innerHTML = `<div class="col-12 text-center py-5 text-muted">No matching menu products found.</div>`;
        return;
    }

    grid.innerHTML = list.map(p => {
        const price = parseFloat(p.base_price || p.price || 0).toFixed(2);
        return `
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 border rounded-4 shadow-xs bg-white">
                    <div class="card-body p-3 text-center d-flex flex-column">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mx-auto mb-1" style="font-size: 0.65rem;">${escapeHtml(p.category_name || 'POS Menu')}</span>
                        <div class="fw-black text-navy text-truncate" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</div>
                        <div class="d-flex justify-content-center my-2">
                            <div class="p-2 bg-white border rounded-3 d-inline-block pos-menu-qr-box" data-code="${escapeHtml(p.product_code)}"></div>
                        </div>
                        <div class="fw-mono fw-bold text-dark small">${escapeHtml(p.product_code)}</div>
                        <div class="fw-black text-warning bg-navy d-inline-block mx-auto px-3 py-0.5 rounded-pill mt-1 small">₱${price}</div>
                        <div class="mt-auto pt-2.5 d-flex gap-1">
                            <button type="button" class="btn btn-navy btn-sm rounded-pill flex-fill fw-bold" onclick="simulatePosQrScan('${escapeHtml(p.product_code)}')">
                                <i class="bi bi-qr-code-scan text-warning me-1"></i>Scan to Tray
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" onclick="printPosMenuQrSheet('${escapeHtml(p.product_code)}')" title="Print this QR Tag">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    if (typeof QRCode !== 'undefined') {
        grid.querySelectorAll('.pos-menu-qr-box').forEach(box => {
            const code = box.getAttribute('data-code') || '';
            box.innerHTML = '';
            if (code) {
                new QRCode(box, {
                    text: code,
                    width: 118,
                    height: 118,
                    colorDark: '#0c2340',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        });
    }
}

async function simulatePosQrScan(code) {
    bootstrap.Modal.getInstance(document.getElementById('posMenuQrModal'))?.hide();
    await processScannedPosQrCode(code, 'input');
}

function printPosMenuQrSheet(singleCode = '') {
    const items = singleCode
        ? (POS_MENU_PRODUCTS || []).filter(p => String(p.product_code).toUpperCase() === String(singleCode).toUpperCase())
        : (POS_MENU_PRODUCTS || []);
    if (items.length === 0) {
        showToast('No menu QR tags to print.', 'warning');
        return;
    }
    const printList = singleCode ? [items[0], items[0], items[0], items[0]] : items;
    const w = window.open('', '_blank', 'width=980,height=760');
    if (!w) {
        showToast('Please allow pop-ups to print Menu QR Tags.', 'danger');
        return;
    }
    const cardsHtml = printList.map((it, idx) => `
        <div class="qr-card">
            <div class="brand">RAEDEN'S BAKERY AND CAFÉ</div>
            <div class="name">${escapeHtml(it.name)}</div>
            <div class="cat">${escapeHtml(it.category_name || 'Bakery & Café')}</div>
            <div class="qr-box" data-code="${escapeHtml(it.product_code)}"></div>
            <div class="sku">${escapeHtml(it.product_code)}</div>
            <div class="price">₱${parseFloat(it.base_price || it.price || 0).toFixed(2)}</div>
        </div>
    `).join('');

    w.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Raeden’s Bakery and Café — POS Menu QR Code Tags</title>
    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { margin: 0; padding: 20px; background: #FFF6F6; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; background: #894E3F; color: #fff; padding: 12px 20px; border-radius: 12px; margin-bottom: 18px; }
        .top-bar button { background: #C37960; color: #fff; border: 0; padding: 8px 18px; border-radius: 999px; font-weight: 800; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 14px; }
        .qr-card { background: #fff; border: 2px dashed #894E3F; border-radius: 14px; padding: 14px 10px; text-align: center; page-break-inside: avoid; }
        .brand { font-size: 9px; font-weight: 900; letter-spacing: 0.08em; color: #894E3F; }
        .name { font-size: 14px; font-weight: 800; color: #2B2B2B; margin-top: 3px; }
        .cat { font-size: 10px; color: #64748b; margin-bottom: 6px; }
        .qr-box { display: inline-flex; padding: 6px; border: 1px solid #d5c5b5; border-radius: 8px; background: #fff; margin: 4px auto; }
        .sku { font-family: monospace; font-size: 13px; font-weight: 800; color: #894E3F; margin-top: 4px; }
        .price { display: inline-block; background: #894E3F; color: #EEE1BA; font-weight: 900; font-size: 13px; padding: 2px 12px; border-radius: 999px; margin-top: 4px; }
        @media print { .top-bar { display: none !important; } body { background: #fff; padding: 0; } .grid { grid-template-columns: repeat(3, 1fr); } }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
</head>
<body>
    <div class="top-bar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <img src="assets/images/logo.jpg" alt="Logo" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff;">
            <strong>Raeden’s Bakery and Café — Printable Menu POS QR Tags</strong>
        </div>
        <button onclick="window.print()">Print QR Tags</button>
    </div>
    <div class="grid">${cardsHtml}</div>
    <script>
        window.onload = function() {
            document.querySelectorAll('.qr-box').forEach(function(el) {
                new QRCode(el, { text: el.getAttribute('data-code'), width: 128, height: 128, colorDark: '#0c2340', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });
            });
            setTimeout(function() { window.print(); }, 450);
        };
    <\/script>
</body>
</html>`);
    w.document.close();
}

const POS_PRODUCT_SIZES_MAP = <?= json_encode($sizesByProduct) ?>;
const POS_PRODUCT_STOCK_MAP = <?= json_encode($stockByProduct) ?>;
const POS_PRODUCT_ALERT_MAP = <?= json_encode($alertByProduct) ?>;
let POS_PACKAGING_STOCK = <?= json_encode($packagingStockMap) ?>;

function getPackagingAvail(code) {
    return parseFloat((POS_PACKAGING_STOCK && POS_PACKAGING_STOCK[code]) ? POS_PACKAGING_STOCK[code].current_stock : 0);
}

function getPackagingName(code) {
    return (POS_PACKAGING_STOCK && POS_PACKAGING_STOCK[code]) ? POS_PACKAGING_STOCK[code].name : code;
}

/**
 * Compute required Cups (`RM-CUP12-PC`, `RM-CUP16-PC`), Straws (`RM-STRAW-PC`),
 * and Takeout Boxes (`RM-TOBOX-PC`) for a list of cart items and orderType.
 */
function calcCartPackagingNeeds(itemsList, orderType) {
    const isTakeout = String(orderType || '').toLowerCase() === 'takeout';
    const needs = {
        'RM-CUP12-PC': 0,
        'RM-CUP16-PC': 0,
        'RM-STRAW-PC': 0,
        'RM-TOBOX-PC': 0
    };
    let totalItems = 0;
    let pastryItems = 0;

    (itemsList || []).forEach(it => {
        const catId = parseInt(it.category_id || 1);
        const qty = Math.max(1, parseInt(it.quantity || it.qty || 1));
        const sizeName = String(it.size_name || '');
        totalItems += qty;

        if ([1, 2, 3].includes(catId)) {
            const is16oz = /16oz|22oz|large/i.test(sizeName) || [2, 3].includes(catId);
            const cupCode = is16oz ? 'RM-CUP16-PC' : 'RM-CUP12-PC';
            needs[cupCode] += qty;

            if ([2, 3].includes(catId) || isTakeout) {
                needs['RM-STRAW-PC'] += qty;
            }
        } else {
            pastryItems += qty;
            if (isTakeout) {
                needs['RM-TOBOX-PC'] += qty;
            }
        }
    });

    if (isTakeout && totalItems > 0 && pastryItems <= 0) {
        needs['RM-TOBOX-PC'] += 1;
    }

    return needs;
}

function hasPackagingItem(code) {
    return Boolean(POS_PACKAGING_STOCK && !Array.isArray(POS_PACKAGING_STOCK) && POS_PACKAGING_STOCK[code]);
}

function validatePackagingNeeds(needs) {
    const shortages = [];
    Object.keys(needs).forEach(code => {
        if (!hasPackagingItem(code)) return;
        const req = needs[code] || 0;
        if (req <= 0) return;
        const avail = getPackagingAvail(code);
        if (avail < req) {
            shortages.push(`${getPackagingName(code)} (Available: ${avail} pc, Required: ${req} pc)`);
        }
    });
    return shortages;
}

function updatePosPackagingUI() {
    const bar = document.getElementById('posPackagingStockBar');
    const container = document.getElementById('posPackagingPills');
    if (!container) return;
    const orderType = document.getElementById('cartOrderType')?.value || 'Dine-In';
    const needs = calcCartPackagingNeeds(cart, orderType);

    const items = [
        { code: 'RM-CUP12-PC', label: '12oz Cup', icon: 'bi-cup-hot' },
        { code: 'RM-CUP16-PC', label: '16oz Cup', icon: 'bi-cup-straw' },
        { code: 'RM-STRAW-PC', label: 'Straws',   icon: 'bi-slash-lg' },
        { code: 'RM-TOBOX-PC', label: 'T/O Box',  icon: 'bi-box-seam' }
    ].filter(meta => hasPackagingItem(meta.code));

    if (items.length === 0) {
        if (bar) bar.classList.add('d-none');
        container.innerHTML = '';
        return;
    }
    if (bar) bar.classList.remove('d-none');

    container.innerHTML = items.map(meta => {
        const avail = getPackagingAvail(meta.code);
        const req = needs[meta.code] || 0;
        const isShort = req > avail;
        const isZero = avail <= 0;
        const borderClass = isShort || isZero ? 'border-danger bg-danger-subtle text-danger' : (req > 0 ? 'border-primary bg-primary-subtle text-navy' : 'border bg-white text-secondary');
        return `
            <div class="col-6">
                <div class="rounded-2 px-2 py-1 d-flex justify-content-between align-items-center ${borderClass}" style="font-size: 0.68rem;">
                    <span class="fw-bold text-truncate"><i class="bi ${meta.icon} me-1"></i>${meta.label}</span>
                    <span class="fw-mono fw-bold ms-1">${req > 0 ? `${req}/` : ''}${avail}</span>
                </div>
            </div>
        `;
    }).join('');
}

function updateModalPackagingNotice() {
    const alertEl = document.getElementById('posModalPackagingAlert');
    if (!alertEl) return;
    const catId = parseInt(document.getElementById('posCustCatId')?.value || 1);
    const sizeRadio = document.querySelector('input[name="posSize"]:checked');
    const sizeName = (catId === 4) ? 'Standard Serving' : (sizeRadio ? sizeRadio.value : 'Regular (12oz)');
    const orderType = document.getElementById('cartOrderType')?.value || 'Dine-In';

    const draftItem = {
        category_id: catId,
        size_name: sizeName,
        quantity: posCustQty
    };
    const projectedNeeds = calcCartPackagingNeeds([...cart, draftItem], orderType);
    const itemOnlyNeeds = calcCartPackagingNeeds([draftItem], orderType);
    const shortages = validatePackagingNeeds(projectedNeeds);

    const reqParts = [];
    Object.keys(itemOnlyNeeds).forEach(code => {
        if (hasPackagingItem(code) && itemOnlyNeeds[code] > 0) {
            reqParts.push(`${itemOnlyNeeds[code]}× ${getPackagingName(code)} (Stock: ${getPackagingAvail(code)} pc)`);
        }
    });

    if (shortages.length > 0) {
        alertEl.innerHTML = `
            <div class="alert alert-danger py-2 px-3 mb-0 small rounded-3 border-danger">
                <div class="fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Insufficient Cup / Packaging Stock</div>
                <div style="font-size:0.76rem;">${shortages.join(' &bull; ')}</div>
            </div>
        `;
    } else if (reqParts.length > 0) {
        alertEl.innerHTML = `
            <div class="alert alert-light border py-2 px-3 mb-0 small rounded-3 text-secondary d-flex justify-content-between align-items-center flex-wrap gap-1">
                <span><i class="bi bi-box-seam text-navy me-1"></i><strong>Packaging Required:</strong> ${reqParts.join(', ')}</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Stock OK</span>
            </div>
        `;
    } else {
        alertEl.innerHTML = '';
    }
}

function getInCartQtyForProduct(productId) {
    return cart.reduce((sum, it) => sum + (parseInt(it.product_id) === parseInt(productId) ? parseInt(it.quantity) : 0), 0);
}

function getRemainingProductStock(productId) {
    const totalStock = parseInt(POS_PRODUCT_STOCK_MAP[productId] || 0);
    return Math.max(0, totalStock - getInCartQtyForProduct(productId));
}

function refreshPosProductStockUI(productId) {
    const stock = parseInt(POS_PRODUCT_STOCK_MAP[productId] || 0);
    const alertLvl = parseInt(POS_PRODUCT_ALERT_MAP[productId] || 10);
    const card = document.getElementById(`posProdCard_${productId}`);
    const badgeWrap = document.getElementById(`posProdBadge_${productId}`);
    const btn = document.getElementById(`posProdBtn_${productId}`);

    if (card) {
        if (stock <= 0) {
            card.classList.add('opacity-50');
            card.classList.remove('cursor-pointer');
        } else {
            card.classList.remove('opacity-50');
            card.classList.add('cursor-pointer');
        }
    }
    if (btn) btn.disabled = (stock <= 0);
    if (badgeWrap) {
        if (stock <= 0) {
            badgeWrap.innerHTML = '<span class="badge bg-danger">No Brewed Stock</span>';
        } else if (stock <= alertLvl) {
            badgeWrap.innerHTML = `<span class="badge bg-warning text-dark">${stock} left</span>`;
        } else {
            badgeWrap.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle">${stock} in stock</span>`;
        }
    }
}

const POS_CATEGORY_DEFAULT_SIZES = {
    1: [ // Hot Coffee & Classics
        { id: 'pos_c1_reg', size_name: 'Regular (12oz)', price_additional: 0 },
        { id: 'pos_c1_lrg', size_name: 'Large (16oz)', price_additional: 20 }
    ],
    2: [ // Iced Espresso & Signatures
        { id: 'pos_c2_reg', size_name: 'Regular (16oz)', price_additional: 0 },
        { id: 'pos_c2_lrg', size_name: 'Large (22oz)', price_additional: 25 }
    ],
    3: [ // Non-Coffee & Frappes
        { id: 'pos_c3_reg', size_name: 'Regular (16oz)', price_additional: 0 },
        { id: 'pos_c3_lrg', size_name: 'Large (22oz)', price_additional: 25 }
    ],
    4: [] // Pastries & Snacks: No cup sizes
};

// Open Item Customizer (Finished products are Made-to-Order; no stock trapping on POS)
function openPosCustomizer(prod) {
    const catId = parseInt(prod.category_id || 1);
    document.getElementById('posCustId').value = prod.id;
    document.getElementById('posCustName').value = prod.name;
    document.getElementById('posCustCatId').value = catId;
    document.getElementById('posCustBasePrice').value = prod.base_price;
    document.getElementById('posSpecialNote').value = '';

    const isPastry = (catId === 4);
    const isHotDrink = (catId === 1);

    const modalIcon = document.getElementById('posCustomModalIcon');
    const modalTitle = document.getElementById('posItemModalTitle');
    const modalSubtitle = document.getElementById('posItemModalSubtitle');

    if (isPastry) {
        modalIcon.className = 'bi bi-cake2 text-warning';
        modalTitle.textContent = `Customize ${prod.name}`;
        modalSubtitle.textContent = 'Bakery & Pastry Item';
    } else if (isHotDrink) {
        modalIcon.className = 'bi bi-cup-hot text-warning';
        modalTitle.textContent = `Customize ${prod.name}`;
        modalSubtitle.textContent = 'Hot Coffee Beverage';
    } else if (catId === 2) {
        modalIcon.className = 'bi bi-cup-straw text-primary';
        modalTitle.textContent = `Customize ${prod.name}`;
        modalSubtitle.textContent = 'Iced Espresso Drink';
    } else {
        modalIcon.className = 'bi bi-snow text-info';
        modalTitle.textContent = `Customize ${prod.name}`;
        modalSubtitle.textContent = 'Frappe / Cold Drink';
    }

    const sizeContainer = document.getElementById('posSizeContainer');
    const pastryNotice = document.getElementById('posPastryNotice');
    const sugarContainer = document.getElementById('posSugarContainer');
    const iceContainer = document.getElementById('posIceContainer');
    const pastryPrepContainer = document.getElementById('posPastryPrepContainer');
    const addonContainer = document.getElementById('posAddonContainer');

    if (isPastry) {
        sizeContainer.classList.add('d-none');
        pastryNotice.classList.remove('d-none');
        sugarContainer.classList.add('d-none');
        iceContainer.classList.add('d-none');
        pastryPrepContainer.classList.remove('d-none');
        addonContainer.classList.add('d-none');

        const warmRadio = document.getElementById('posPrep_warm');
        if (warmRadio) warmRadio.checked = true;
    } else {
        sizeContainer.classList.remove('d-none');
        pastryNotice.classList.add('d-none');
        sugarContainer.classList.remove('d-none');
        iceContainer.classList.remove('d-none');
        pastryPrepContainer.classList.add('d-none');
        addonContainer.classList.remove('d-none');

        const sizes = (POS_PRODUCT_SIZES_MAP && POS_PRODUCT_SIZES_MAP[prod.id] && POS_PRODUCT_SIZES_MAP[prod.id].length > 0)
            ? POS_PRODUCT_SIZES_MAP[prod.id]
            : (POS_CATEGORY_DEFAULT_SIZES[catId] || [
                { id: 'def_reg', size_name: 'Regular', price_additional: 0 },
                { id: 'def_lrg', size_name: 'Large', price_additional: 20 }
            ]);

        let sizeHtml = '';
        sizes.forEach((s, idx) => {
            const isChecked = (idx === 0) ? 'checked' : '';
            const priceAdd = parseFloat(s.price_additional || 0);
            const priceBadge = priceAdd > 0 ? `+₱${priceAdd.toFixed(2)}` : 'Base Price';
            const badgeClass = priceAdd > 0 ? 'bg-warning-subtle text-dark border' : 'bg-light text-muted border';
            const radioId = `posSize_${prod.id}_${idx}`;

            sizeHtml += `
                <div class="col-6">
                    <input type="radio" class="btn-check" name="posSize" id="${radioId}" value="${escapeHtml(s.size_name)}" data-price="${priceAdd}" ${isChecked} onchange="calcPosCustomPrice()">
                    <label class="btn btn-outline-navy w-100 rounded-3 text-start p-2.5 h-100 shadow-xs" for="${radioId}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">${escapeHtml(s.size_name)}</span>
                            <span class="badge ${badgeClass} small">${priceBadge}</span>
                        </div>
                    </label>
                </div>
            `;
        });
        document.getElementById('posSizeRadios').innerHTML = sizeHtml;

        document.getElementById('posSugar').value = '50% Half Sweet';

        const iceSelect = document.getElementById('posIce');
        const hotNotice = document.getElementById('posHotNotice');
        const iceBadge = document.getElementById('posIceBadge');

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

        document.querySelectorAll('.pos-addon-cb').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.addon-card');
            if (card) card.classList.remove('addon-card-checked');
        });
    }

    posCustQty = 1;
    document.getElementById('posQtyDisplay').textContent = 1;
    calcPosCustomPrice();

    const m = new bootstrap.Modal(document.getElementById('posCustomizerModal'));
    m.show();
}

function changePosQty(delta) {
    const nextQty = posCustQty + delta;
    posCustQty = Math.max(1, nextQty);
    document.getElementById('posQtyDisplay').textContent = posCustQty;
    calcPosCustomPrice();
}

function calcPosCustomPrice() {
    const catId = parseInt(document.getElementById('posCustCatId').value || 1);
    const base = parseFloat(document.getElementById('posCustBasePrice').value || 0);
    
    let sizeAdd = 0;
    let addonSum = 0;

    if (catId !== 4) {
        const sizeRadio = document.querySelector('input[name="posSize"]:checked');
        sizeAdd = parseFloat(sizeRadio ? sizeRadio.dataset.price || 0 : 0);

        document.querySelectorAll('.pos-addon-cb:checked').forEach(cb => {
            addonSum += parseFloat(cb.dataset.price || 0);
        });
    }

    const itemUnit = base + sizeAdd + addonSum;
    const lineTotal = itemUnit * posCustQty;
    document.getElementById('posCustomLineTotal').textContent = formatPeso(lineTotal);
    updateModalPackagingNotice();
}

function addCustomizedToCart() {
    const prodId = parseInt(document.getElementById('posCustId').value);
    const prodName = document.getElementById('posCustName').value;
    const catId = parseInt(document.getElementById('posCustCatId').value || 1);
    const base = parseFloat(document.getElementById('posCustBasePrice').value || 0);
    const notes = document.getElementById('posSpecialNote').value.trim();

    let sizeName = 'Standard Serving';
    let sizeAdd = 0;
    let sugar = 'Pastry';
    let ice = 'Served As-Is';
    const addons = [];
    let addonSum = 0;

    if (catId === 4) { // Pastry
        sizeName = 'Standard Serving';
        sizeAdd = 0;
        const prepRadio = document.querySelector('input[name="posPastryPrep"]:checked');
        sugar = 'Pastry';
        ice = prepRadio ? prepRadio.value : 'Warm / Lightly Toasted';
    } else { // Beverage
        const sizeRadio = document.querySelector('input[name="posSize"]:checked');
        sizeName = sizeRadio ? sizeRadio.value : 'Regular';
        sizeAdd = parseFloat(sizeRadio ? sizeRadio.dataset.price || 0 : 0);
        sugar = document.getElementById('posSugar').value;
        
        if (catId === 1) {
            ice = 'Served Hot';
        } else {
            ice = document.getElementById('posIce').value;
        }

        document.querySelectorAll('.pos-addon-cb:checked').forEach(cb => {
            addons.push(cb.value);
            addonSum += parseFloat(cb.dataset.price || 0);
        });
    }

    const unitPrice = base + sizeAdd + addonSum;
    const newItem = {
        product_id: prodId,
        product_name: prodName,
        category_id: catId,
        size_name: sizeName,
        sugar_level: sugar,
        ice_level: ice,
        addons: addons,
        special_notes: notes,
        quantity: posCustQty,
        unit_price: unitPrice
    };

    // Validate Cup, Straw, and Takeout Box stock BEFORE allowing item into the order cart
    const orderType = document.getElementById('cartOrderType')?.value || 'Dine-In';
    const projectedNeeds = calcCartPackagingNeeds([...cart, newItem], orderType);
    const shortages = validatePackagingNeeds(projectedNeeds);
    if (shortages.length > 0) {
        showToast(`Cannot add item — Insufficient packaging stock: ${shortages.join('; ')}`, 'danger');
        updateModalPackagingNotice();
        return;
    }

    cart.push(newItem);

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('posCustomizerModal'));
    if (modalInstance) modalInstance.hide();
    
    renderPosCart();
    showToast(`Added ${prodName} (${sizeName}) to cart`, 'success');
}

function removePosCartItem(idx) {
    cart.splice(idx, 1);
    renderPosCart();
}

function updateCartQty(idx, delta) {
    if (delta > 0) {
        const testCart = cart.map((item, i) => i === idx ? { ...item, quantity: item.quantity + delta } : item);
        const orderType = document.getElementById('cartOrderType')?.value || 'Dine-In';
        const shortages = validatePackagingNeeds(calcCartPackagingNeeds(testCart, orderType));
        if (shortages.length > 0) {
            showToast(`Cannot increase quantity — Insufficient packaging stock: ${shortages.join('; ')}`, 'danger');
            return;
        }
    }
    cart[idx].quantity += delta;
    if (cart[idx].quantity <= 0) {
        cart.splice(idx, 1);
    }
    renderPosCart();
}

async function clearPosCart() {
    if (cart.length === 0) return;
    
    const result = await showConfirm({
        title: 'Clear Current Cart?',
        text: 'Are you sure you want to remove all items from this order transaction?',
        icon: 'warning',
        confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Yes, Clear Cart',
        cancelButtonText: 'Keep Items',
        isDestructive: true
    });

    if (result.isConfirmed) {
        cart = [];
        window.posActiveBooking = null;
        updatePosBookingPassBanner();
        discountRate = 0;
        discountTypeName = 'None';
        const dNone = document.getElementById('discNone');
        if (dNone) dNone.checked = true;
        const badge = document.getElementById('activeDiscountBadge');
        if (badge) {
            badge.textContent = 'Regular Rate';
            badge.className = 'small text-muted';
        }
        document.getElementById('cartCustomerName').value = '';
        renderPosCart();
        showToast('Cart has been cleared.', 'info');
    }
}

function applyDiscount(rate, label) {
    discountRate = rate;
    discountTypeName = label;
    const badge = document.getElementById('activeDiscountBadge');
    if (badge) {
        if (rate > 0) {
            badge.textContent = `${label} Applied`;
            badge.className = 'badge bg-warning text-dark fw-bold px-2 py-0.5';
        } else {
            badge.textContent = 'Regular Rate';
            badge.className = 'small text-muted';
        }
    }
    renderPosCart();
}

function handleOrderTypeChange() {
    const type = document.getElementById('cartOrderType').value;
    const tableRow = document.getElementById('tablePickerRow');
    if (type === 'Takeout') {
        tableRow.style.display = 'none';
    } else {
        tableRow.style.display = 'flex';
    }
    updatePosPackagingUI();
    if (cart.length > 0) {
        const shortages = validatePackagingNeeds(calcCartPackagingNeeds(cart, type));
        if (shortages.length > 0) {
            showToast(`Packaging stock alert for ${type}: ${shortages.join('; ')}`, 'warning');
        }
    }
}

function renderPosCart() {
    updatePosPackagingUI();
    const container = document.getElementById('posCartItemsList');
    const countBadge = document.getElementById('cartItemCountBadge');
    countBadge.textContent = `${cart.length} ${cart.length === 1 ? 'Item' : 'Items'}`;

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 px-3 my-auto text-muted" id="posEmptyCart">
                <div class="rounded-circle bg-white border d-inline-flex align-items-center justify-content-center mb-2 shadow-xs" style="width: 52px; height: 52px;">
                    <i class="bi bi-cart-x text-secondary opacity-50 fs-4"></i>
                </div>
                <div class="fw-bold text-dark small mb-0.5">Order Tray is Empty</div>
                <div class="text-secondary" style="font-size: 0.74rem;">Tap catalog items on the left or scan product QR barcode.</div>
            </div>
        `;
        document.getElementById('posSubtotal').textContent = '₱0.00';
        document.getElementById('posDiscountAmount').textContent = '-₱0.00';
        document.getElementById('posDiscountRow').style.display = 'none';
        document.getElementById('posVat').textContent = '₱0.00';
        document.getElementById('posNetTotal').textContent = '₱0.00';
        const mobBar = document.getElementById('posMobileFloatingBar');
        if (mobBar) mobBar.style.display = 'none';
        return;
    }

    let subtotal = 0;
    let html = '';

    cart.forEach((it, idx) => {
        const lineTotal = it.unit_price * it.quantity;
        subtotal += lineTotal;
        const isPastry = (it.category_id === 4 || it.size_name === 'Standard Serving');

        let modifierBadges = '';
        if (isPastry) {
            modifierBadges += `<span class="badge bg-light text-secondary border me-1">${escapeHtml(it.size_name)}</span>`;
            if (it.ice_level && it.ice_level !== 'Standard') {
                modifierBadges += `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle me-1"><i class="bi bi-cake2 me-1"></i>${escapeHtml(it.ice_level)}</span>`;
            }
        } else {
            modifierBadges += `<span class="badge bg-light text-secondary border me-1">${escapeHtml(it.size_name)}</span>`;
            if (it.sugar_level) {
                modifierBadges += `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle me-1">${escapeHtml(it.sugar_level)}</span>`;
            }
            if (it.ice_level) {
                modifierBadges += `<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle me-1">${escapeHtml(it.ice_level)}</span>`;
            }
        }
        if (it.addons && it.addons.length) {
            it.addons.forEach(a => {
                modifierBadges += `<span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">+${escapeHtml(a)}</span>`;
            });
        }

        html += `
            <div class="pos-cart-item-card d-flex justify-content-between align-items-center">
                <div class="overflow-hidden pe-2">
                    <div class="fw-bold small text-navy text-truncate" title="${escapeHtml(it.product_name)}">${escapeHtml(it.product_name)}</div>
                    <div class="d-flex flex-wrap gap-1 mt-1 align-items-center" style="font-size: 0.7rem;">
                        ${modifierBadges}
                    </div>
                    ${it.special_notes ? `<div class="text-danger mt-1 small" style="font-size: 0.7rem;"><i class="bi bi-chat-square-text me-1"></i>${escapeHtml(it.special_notes)}</div>` : ''}
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="btn-group btn-group-sm border rounded-pill overflow-hidden bg-light shadow-2xs" style="height: 28px;">
                        <button type="button" class="btn btn-light text-navy px-2 py-0 fw-bold border-0 d-flex align-items-center justify-content-center" style="width: 26px;" onclick="updateCartQty(${idx}, -1)" aria-label="Decrease quantity"><i class="bi bi-dash" style="font-size: 0.85rem;"></i></button>
                        <span class="px-2 fw-bold small d-flex align-items-center justify-content-center bg-white text-navy" style="min-width: 24px; font-size: 0.8rem;">${it.quantity}</span>
                        <button type="button" class="btn btn-light text-navy px-2 py-0 fw-bold border-0 d-flex align-items-center justify-content-center" style="width: 26px;" onclick="updateCartQty(${idx}, 1)" aria-label="Increase quantity"><i class="bi bi-plus" style="font-size: 0.85rem;"></i></button>
                    </div>
                    <div class="text-end" style="min-width: 62px;">
                        <span class="fw-bold text-navy fw-mono small">${formatPeso(lineTotal)}</span>
                        <div class="text-muted" style="font-size: 0.65rem;">@ ${formatPeso(it.unit_price)}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-0 d-flex align-items-center justify-content-center ms-0.5" style="width: 26px; height: 26px;" onclick="removePosCartItem(${idx})" title="Remove item"><i class="bi bi-trash3" style="font-size: 0.8rem;"></i></button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Financial Calculation
    const discountAmount = subtotal * discountRate;
    const netTotal = Math.max(0, subtotal - discountAmount);
    const vat = netTotal - (netTotal / 1.12);

    document.getElementById('posSubtotal').textContent = formatPeso(subtotal);
    if (discountRate > 0) {
        document.getElementById('posDiscountRow').style.display = 'flex';
        document.getElementById('posDiscountLabel').textContent = discountTypeName;
        document.getElementById('posDiscountAmount').textContent = `-${formatPeso(discountAmount)}`;
    } else {
        document.getElementById('posDiscountRow').style.display = 'none';
    }
    document.getElementById('posVat').textContent = formatPeso(vat);
    document.getElementById('posNetTotal').textContent = formatPeso(netTotal);

    const mobBar = document.getElementById('posMobileFloatingBar');
    if (mobBar) {
        mobBar.style.display = 'flex';
        const mobBadge = document.getElementById('posMobileBadge');
        if (mobBadge) mobBadge.textContent = `${cart.length} ${cart.length === 1 ? 'Item' : 'Items'}`;
        const mobTotal = document.getElementById('posMobileTotal');
        if (mobTotal) mobTotal.textContent = formatPeso(netTotal);
    }
}

function scrollToPosCart() {
    const cartEl = document.getElementById('posCartPanel');
    if (cartEl) {
        cartEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Checkout Modal
function openCheckoutModal() {
    if (cart.length === 0) {
        showToast('Cart is empty. Add items before charging.', 'warning');
        return;
    }

    const orderType = document.getElementById('cartOrderType')?.value || 'Dine-In';
    const shortages = validatePackagingNeeds(calcCartPackagingNeeds(cart, orderType));
    if (shortages.length > 0) {
        showToast(`Cannot proceed to payment — Insufficient Cup / Takeout Supply stock: ${shortages.join('; ')}`, 'danger');
        return;
    }

    const subtotal = cart.reduce((acc, it) => acc + (it.unit_price * it.quantity), 0);
    const net = subtotal - (subtotal * discountRate);

    document.getElementById('modalCheckoutTotal').textContent = formatPeso(net);
    document.getElementById('cashTenderInput').value = '';
    const refInput = document.getElementById('ewalletRefInput');
    if (refInput) refInput.value = '';
    document.getElementById('changeDueDisplay').textContent = '₱0.00';
    
    // Ensure Cash is selected by default
    const cashRadio = document.getElementById('payCash');
    if (cashRadio) cashRadio.checked = true;
    togglePayMethod('Cash');

    const m = new bootstrap.Modal(document.getElementById('checkoutModal'));
    m.show();
}

function togglePayMethod(method) {
    const cashSec = document.getElementById('cashTenderSection');
    const ewalletSec = document.getElementById('ewalletSection');
    if (method === 'Cash') {
        cashSec.style.display = 'block';
        ewalletSec.style.display = 'none';
        calcChange();
    } else {
        cashSec.style.display = 'none';
        ewalletSec.style.display = 'block';
        const refInput = document.getElementById('ewalletRefInput');
        if (refInput) setTimeout(() => refInput.focus(), 150);
    }
}

function setCashTender(amount) {
    const subtotal = cart.reduce((acc, it) => acc + (it.unit_price * it.quantity), 0);
    const net = subtotal - (subtotal * discountRate);

    if (amount === 'exact') {
        document.getElementById('cashTenderInput').value = net.toFixed(2);
    } else {
        document.getElementById('cashTenderInput').value = amount;
    }
    calcChange();
}

function calcChange() {
    const subtotal = cart.reduce((acc, it) => acc + (it.unit_price * it.quantity), 0);
    const net = subtotal - (subtotal * discountRate);
    const tendered = parseFloat(document.getElementById('cashTenderInput').value || 0);

    const change = Math.max(0, tendered - net);
    document.getElementById('changeDueDisplay').textContent = formatPeso(change);
}

// Complete Checkout & Send to API
async function submitPosCheckout() {
    const subtotal = cart.reduce((acc, it) => acc + (it.unit_price * it.quantity), 0);
    const discountAmount = subtotal * discountRate;
    const net = Math.max(0, subtotal - discountAmount);
    const payMethod = document.querySelector('input[name="payMethod"]:checked')?.value || 'Cash';
    const tendered = parseFloat(document.getElementById('cashTenderInput').value || 0);
    const ewalletRef = document.getElementById('ewalletRefInput').value.trim();

    if (payMethod === 'Cash' && tendered < net) {
        showToast('Cash tendered is less than amount due!', 'danger');
        document.getElementById('cashTenderInput').focus();
        return;
    }

    if (payMethod === 'GCash' && !ewalletRef) {
        showToast('Please enter the GCash Reference Number from the customer transaction.', 'warning');
        document.getElementById('ewalletRefInput').focus();
        return;
    }

    const btn = document.getElementById('btnConfirmPay');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processing Order...`;

    const payload = {
        reservation_id: window.posActiveBooking ? window.posActiveBooking.id : null,
        order_type: document.getElementById('cartOrderType').value,
        table_number: document.getElementById('cartTableSelect').value || 'Counter',
        customer_name: document.getElementById('cartCustomerName').value.trim() || 'Walk-In Customer',
        customer_type: discountTypeName === 'None' ? 'Regular' : discountTypeName,
        discount_type: discountTypeName,
        discount_amount: discountAmount,
        payment_method: payMethod,
        amount_tendered: payMethod === 'Cash' ? tendered : net,
        cash_tendered: payMethod === 'Cash' ? tendered : net,
        payment_reference: payMethod === 'GCash' ? ewalletRef : '',
        ewallet_ref: payMethod === 'GCash' ? ewalletRef : '',
        items: cart
    };

    try {
        const res = await fetch('api/pos.php?action=checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            window.posActiveBooking = null;
            updatePosBookingPassBanner();
            if (data.packaging_stock) {
                POS_PACKAGING_STOCK = data.packaging_stock;
                updatePosPackagingUI();
            }
            bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

            document.getElementById('successOrderNum').textContent = data.order_number;
            document.getElementById('successTotalPaid').textContent = formatPeso(data.net_total);
            document.getElementById('successChange').textContent = formatPeso(data.amount_change);
            document.getElementById('successDestination').textContent = `${data.order_type} (${data.table_number})`;
            document.getElementById('btnPrintReceiptBtn').href = `receipt?order_id=${data.order_id}&print=1`;

            const successM = new bootstrap.Modal(document.getElementById('receiptSuccessModal'));
            successM.show();
        } else {
            showToast(data.message || 'Checkout failed', 'danger');
        }
    } catch (err) {
        showToast('Network error during checkout', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-check2-circle me-2"></i> Complete & Process Payment`;
    }
}

function resetPos() {
    bootstrap.Modal.getInstance(document.getElementById('receiptSuccessModal')).hide();
    cart = [];
    window.posActiveBooking = null;
    updatePosBookingPassBanner();
    discountRate = 0;
    discountTypeName = 'None';
    document.getElementById('discNone').checked = true;
    const badge = document.getElementById('activeDiscountBadge');
    if (badge) {
        badge.textContent = 'Regular Rate';
        badge.className = 'small text-muted';
    }
    document.getElementById('cartCustomerName').value = '';
    renderPosCart();
    if (window.innerWidth < 992) switchPosMobileView('catalog');
    const bInput = document.getElementById('barcodeInput'); if (bInput) bInput.focus();
}

function pressTenderKey(key) {
    const input = document.getElementById('cashTenderInput');
    if (!input) return;
    if (key === 'backspace') {
        input.value = input.value.slice(0, -1);
    } else {
        input.value = (input.value || '') + key;
    }
    calcChange();
}

// Live Clock for POS Cashier Station
(function initPosClock() {
    function updateClock() {
        const el = document.getElementById('posLiveClock');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleTimeString('en-US', { hour12: true });
        }
    }
    updateClock();
    const clockInterval = setInterval(updateClock, 1000);
    window.addEventListener('pageUnload', () => clearInterval(clockInterval), { once: true });
})();

// =========================================================================
// CASHIER & RECEPTIONIST: TABLE MANAGEMENT & 15-MINUTE RESERVATION ALERTS
// =========================================================================
let cachedTmTables = [];
let cachedTmAlerts = [];
let currentTmStatus = 'all';
let notifiedAlertResIds = new Set();
let tmPollingInterval = null;

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function openTableManagerModal() {
    loadTableManagerData();
    const modalEl = document.getElementById('tableManagerModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function playAlertChime() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.12);
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.35);
    } catch (e) {
        // Silently continue if autoplay policy blocks audio
    }
}

async function loadTableManagerData(silent = false) {
    try {
        const res = await fetch('api/pos.php?action=get_tables_and_alerts');
        const data = await res.json();

        if (data.success) {
            cachedTmTables = data.tables || [];
            cachedTmAlerts = data.urgent_alerts || [];

            // Update Counts
            let avail = 0, resCount = 0, occ = 0, maint = 0;
            cachedTmTables.forEach(t => {
                if (t.status === 'available') avail++;
                else if (t.status === 'reserved') resCount++;
                else if (t.status === 'occupied') occ++;
                else if (t.status === 'maintenance') maint++;
            });

            const elAvail = document.getElementById('tmAvailableCount');
            const elRes = document.getElementById('tmReservedCount');
            const elOcc = document.getElementById('tmOccupiedCount');
            const elMaint = document.getElementById('tmMaintenanceCount');
            if (elAvail) elAvail.textContent = avail;
            if (elRes) elRes.textContent = resCount;
            if (elOcc) elOcc.textContent = occ;
            if (elMaint) elMaint.textContent = maint;

            renderTableManagerGrid();
            updateUrgentAlertSystem(silent);
            updateCartTableSelectDropdown();
        }
    } catch (err) {
        if (!silent) console.error('Error fetching table manager data:', err);
    }
}

function updateUrgentAlertSystem(silent = false) {
    const banner = document.getElementById('posUrgentReservationBanner');
    const pill = document.getElementById('posUrgentAlertPill');
    const badge = document.getElementById('urgentBannerBadge');
    const details = document.getElementById('urgentBannerDetails');

    if (cachedTmAlerts.length > 0) {
        // Update header pill
        if (pill) {
            pill.style.display = 'inline-block';
            pill.textContent = `${cachedTmAlerts.length} Attention`;
        }

        // Update top banner
        const topAlert = cachedTmAlerts[0];
        if (banner && badge && details) {
            badge.textContent = topAlert.display_time;
            details.innerHTML = `<strong>Table #${topAlert.table_id} (${topAlert.table_code} - ${escapeHtml(topAlert.table_name)})</strong> is reserved for <strong>${escapeHtml(topAlert.customer_name)}</strong> (${topAlert.guest_count} guests) at <strong>${topAlert.time_slot}</strong>. Current table status: <span class="badge bg-warning text-dark text-capitalize">${topAlert.table_status}</span>. Please seat guest or prepare the table.`;
            banner.style.display = 'flex';
        }

        // Trigger notification if not yet alerted for this reservation
        cachedTmAlerts.forEach(alert => {
            if (!notifiedAlertResIds.has(alert.reservation_id)) {
                notifiedAlertResIds.add(alert.reservation_id);
                playAlertChime();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '⚠️ 15-Minute Reservation Alert',
                        html: `<div class="text-start small">
                            <p class="mb-1.5">A table reservation is scheduled <strong>${alert.display_time}</strong>!</p>
                            <div class="bg-light p-2.5 rounded-3 border mb-2">
                                <div><strong>Table:</strong> Table #${alert.table_id} (${alert.table_code} - ${escapeHtml(alert.table_name)})</div>
                                <div><strong>Guest:</strong> ${escapeHtml(alert.customer_name)} (${alert.phone || 'No phone'})</div>
                                <div><strong>Time Slot:</strong> ${alert.time_slot}</div>
                                <div><strong>Current Status:</strong> <span class="badge bg-warning text-dark text-capitalize">${alert.table_status}</span></div>
                            </div>
                            <div class="text-muted">Please update table status or seat the customer upon arrival.</div>
                        </div>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Seat & Mark Occupied',
                        cancelButtonText: 'Review in Table Manager',
                        customClass: {
                            popup: 'rounded-4 shadow-lg border-0',
                            confirmButton: 'btn btn-success px-4 py-2 rounded-pill fw-bold',
                            cancelButton: 'btn btn-outline-secondary px-4 py-2 rounded-pill fw-bold ms-2'
                        },
                        buttonsStyling: false
                    }).then(res => {
                        if (res.isConfirmed) {
                            quickSeatGuest(alert.reservation_id);
                        } else if (res.dismiss === Swal.DismissReason.cancel) {
                            openTableManagerModal();
                        }
                    });
                } else {
                    showToast(`Table #${alert.table_id} reservation for ${alert.customer_name} starts in ${alert.minutes_diff} min! Please update table status.`, 'warning');
                }
            }
        });
    } else {
        if (pill) pill.style.display = 'none';
        if (banner) banner.style.display = 'none';
    }
}

function dismissUrgentAlert() {
    const banner = document.getElementById('posUrgentReservationBanner');
    if (banner) banner.style.display = 'none';
}

function quickSeatFromAlert() {
    if (cachedTmAlerts.length > 0) {
        quickSeatGuest(cachedTmAlerts[0].reservation_id);
    } else {
        openTableManagerModal();
    }
}

function updateCartTableSelectDropdown() {
    const sel = document.getElementById('cartTableSelect');
    if (!sel || !cachedTmTables.length) return;
    const currentVal = sel.value;

    let html = `<option value="">-- Assign Table / Pod (Optional) --</option>`;
    cachedTmTables.forEach(t => {
        const s = t.status.toLowerCase();
        const tag = s === 'available' ? 'Available' : (s === 'reserved' ? 'Reserved' : (s === 'occupied' ? 'Occupied' : 'Maintenance'));
        html += `<option value="${escapeHtml(t.table_code)}" data-status="${s}" ${currentVal === t.table_code ? 'selected' : ''}>
            Table #${t.id} (${escapeHtml(t.table_code)}) - ${escapeHtml(t.name)} [${tag}]
        </option>`;
    });
    sel.innerHTML = html;
}

function renderTableManagerGrid() {
    const container = document.getElementById('tmGridContainer');
    if (!container) return;

    const searchTerm = (document.getElementById('tmSearchInput')?.value || '').toLowerCase().trim();

    const filtered = cachedTmTables.filter(t => {
        // Status filter
        if (currentTmStatus !== 'all' && t.status !== currentTmStatus) return false;

        // Search term filter
        if (searchTerm) {
            const matchesCode = t.table_code.toLowerCase().includes(searchTerm);
            const matchesName = t.name.toLowerCase().includes(searchTerm);
            const matchesZone = t.zone.toLowerCase().includes(searchTerm);
            const matchesId = String(t.id).includes(searchTerm);
            let matchesGuest = false;
            if (t.active_reservation && t.active_reservation.customer_name) {
                matchesGuest = t.active_reservation.customer_name.toLowerCase().includes(searchTerm);
            }
            if (!matchesCode && !matchesName && !matchesZone && !matchesId && !matchesGuest) {
                return false;
            }
        }
        return true;
    });

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-grid-3x3 fs-2 d-block mb-2 text-opacity-50 text-secondary"></i>
                No tables match the current filter or search criteria.
            </div>
        `;
        return;
    }

    let html = '';
    filtered.forEach(t => {
        const status = t.status.toLowerCase();
        let statusBadgeClass = 'bg-success';
        let statusLabel = 'Available';
        let borderClass = 'border-success';

        if (status === 'reserved') {
            statusBadgeClass = 'bg-warning text-dark';
            statusLabel = 'Reserved';
            borderClass = 'border-warning';
        } else if (status === 'occupied') {
            statusBadgeClass = 'bg-danger';
            statusLabel = 'Occupied';
            borderClass = 'border-danger';
        } else if (status === 'maintenance') {
            statusBadgeClass = 'bg-dark text-white';
            statusLabel = 'Maintenance';
            borderClass = 'border-secondary';
        }

        const res = t.active_reservation;
        const isUrgent = res && res.is_urgent;

        html += `
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 rounded-4 shadow-xs border-2 ${borderClass} overflow-hidden ${isUrgent ? 'pulse-danger' : ''}">
                    <!-- Card Header -->
                    <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="badge bg-warning text-dark fw-bold px-2 py-0.5"><i class="bi bi-hash"></i> Table #${t.id}</span>
                            <span class="badge bg-navy text-white fw-bold px-2 py-0.5">${escapeHtml(t.table_code)}</span>
                        </div>
                        <span class="badge ${statusBadgeClass} text-uppercase fw-bold px-2.5 py-1">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.4rem;"></i> ${statusLabel}
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-3 pt-1 d-flex flex-column">
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="${escapeHtml(t.name)}">
                            ${escapeHtml(t.name)}
                        </h6>
                        <div class="d-flex align-items-center gap-2 text-muted small mb-2" style="font-size: 0.75rem;">
                            <span><i class="bi ${escapeHtml(t.zone_icon || 'bi-geo-alt')} text-primary me-1"></i>${escapeHtml(t.zone)}</span>
                            <span>&bull;</span>
                            <span><i class="bi bi-person me-1"></i>Cap: ${t.capacity}</span>
                            ${t.has_power_outlet ? `<span>&bull;</span><span class="text-warning"><i class="bi bi-plug-fill"></i> Outlet</span>` : ''}
                        </div>

                        ${isUrgent ? `
                            <div class="alert alert-danger py-1.5 px-2 small rounded-3 mb-2 d-flex align-items-center gap-1.5 fw-bold" style="font-size: 0.72rem;">
                                <i class="bi bi-alarm-fill fs-6"></i>
                                <span>Reservation starts soon! Please update status.</span>
                            </div>
                        ` : ''}

                        ${res ? `
                            <div class="bg-light p-2.5 rounded-3 border mb-2 small text-secondary">
                                <div class="fw-bold text-navy text-truncate mb-0.5"><i class="bi bi-person-badge text-warning me-1"></i> ${escapeHtml(res.customer_name)}</div>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span><i class="bi bi-clock me-1"></i> ${escapeHtml(res.time_slot)}</span>
                                    <span class="badge bg-light text-dark border">${escapeHtml(res.status)}</span>
                                </div>
                            </div>
                        ` : `
                            <div class="text-muted small py-2 mb-2 fst-italic" style="font-size: 0.72rem;">
                                No active booking assigned to this table today.
                            </div>
                        `}

                        <!-- Quick Seat & Load Order Actions for Incoming Reservation -->
                        ${res ? `
                            <div class="d-grid gap-1.5 mb-2">
                                <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill fw-bold py-1 shadow-xs" onclick="bootstrap.Modal.getInstance(document.getElementById('tableManagerModal'))?.hide(); loadReservationByIdToCart(${res.reservation_id});">
                                    <i class="bi bi-cart-plus-fill me-1"></i> Load Pre-Order to Cart
                                </button>
                                ${status !== 'occupied' ? `
                                    <button type="button" class="btn btn-sm btn-outline-navy rounded-pill fw-bold py-1 shadow-xs" onclick="quickSeatGuest(${res.reservation_id})">
                                        <i class="bi bi-person-check-fill me-1 text-warning"></i> Check-In & Seat Guest
                                    </button>
                                ` : ''}
                            </div>
                        ` : ''}

                        <!-- Status Toggle Action Buttons -->
                        <div class="mt-auto pt-2 border-top">
                            <label class="form-label text-muted small fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.04em;">SET TABLE STATUS:</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <button type="button" class="btn ${status === 'available' ? 'btn-success fw-bold' : 'btn-outline-success'}" 
                                        onclick="setTableStatus(${t.id}, 'available')" title="Mark Available (Clear table)">
                                    Avail
                                </button>
                                <button type="button" class="btn ${status === 'reserved' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark'}" 
                                        onclick="setTableStatus(${t.id}, 'reserved')" title="Mark Reserved (Hold table)">
                                    Resv
                                </button>
                                <button type="button" class="btn ${status === 'occupied' ? 'btn-danger fw-bold' : 'btn-outline-danger'}" 
                                        onclick="setTableStatus(${t.id}, 'occupied')" title="Mark Occupied (Seated)">
                                    Occ
                                </button>
                                <button type="button" class="btn ${status === 'maintenance' ? 'btn-dark fw-bold' : 'btn-outline-secondary'}" 
                                        onclick="setTableStatus(${t.id}, 'maintenance')" title="Mark Out of Order">
                                    Maint
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function filterTmStatus(status, btn) {
    currentTmStatus = status;
    document.querySelectorAll('#tmStatusFilters .btn').forEach(b => {
        b.classList.remove('active', 'btn-navy');
        if (!b.className.includes('btn-outline-')) {
            // Restore outline class based on status
            if (b.textContent.includes('Available')) b.className = 'btn btn-outline-success fw-bold px-2.5';
            else if (b.textContent.includes('Reserved')) b.className = 'btn btn-outline-warning text-dark fw-bold px-2.5';
            else if (b.textContent.includes('Occupied')) b.className = 'btn btn-outline-danger fw-bold px-2.5';
            else if (b.textContent.includes('Maintenance')) b.className = 'btn btn-outline-secondary fw-bold px-2.5';
            else b.className = 'btn btn-outline-navy fw-bold px-3';
        }
    });
    btn.classList.add('active', 'btn-navy');
    btn.classList.remove('btn-outline-navy', 'btn-outline-success', 'btn-outline-warning', 'btn-outline-danger', 'btn-outline-secondary');
    renderTableManagerGrid();
}

function filterTmSearch() {
    renderTableManagerGrid();
}

async function setTableStatus(tableId, status) {
    try {
        const res = await fetch('api/pos.php?action=update_table_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ table_id: tableId, status: status })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            loadTableManagerData(true);
        } else {
            showToast(data.message || 'Failed to update table status.', 'danger');
        }
    } catch (err) {
        showToast('Network error updating table status.', 'danger');
    }
}

async function quickSeatGuest(resId) {
    try {
        const res = await fetch('api/pos.php?action=quick_seat_reservation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reservation_id: resId })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            loadTableManagerData(true);
        } else {
            showToast(data.message || 'Failed to check in reservation.', 'danger');
        }
    } catch (err) {
        showToast('Network error during guest check-in.', 'danger');
    }
}

// Initialize Reception & Table Management Polling (Every 25 seconds)
document.addEventListener('DOMContentLoaded', () => {
    loadTableManagerData(true);
    tmPollingInterval = setInterval(() => {
        loadTableManagerData(true);
    }, 25000);
});

window.addEventListener('pageUnload', () => {
    if (tmPollingInterval) clearInterval(tmPollingInterval);
}, { once: true });

// ==========================================
// Cashier Daily Sales & Shift Reading Modal
// ==========================================
let currentPosDailyData = null;

function openPosDailyReportModal() {
    const modalEl = document.getElementById('posDailyReportModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    loadPosDailyReport();
}

async function loadPosDailyReport(dateVal) {
    const dateInput = document.getElementById('posDailyReportDate');
    const selectedDate = dateVal || (dateInput ? dateInput.value : new Date().toISOString().split('T')[0]);
    if (dateInput && !dateVal) dateInput.value = selectedDate;

    const spinner = document.getElementById('posDailyReportSpinner');
    const content = document.getElementById('posDailyReportContent');
    if (spinner) spinner.style.display = 'block';
    if (content) content.style.display = 'none';

    try {
        const res = await fetch(`api/reports.php?action=sales_report&period=daily&date=${selectedDate}`);
        const data = await res.json();

        if (spinner) spinner.style.display = 'none';
        if (content) content.style.display = 'block';

        if (data.success) {
            currentPosDailyData = data;
            const s = data.summary || {};
            const filter = data.filter || {};

            document.getElementById('posDailyGross').textContent = '₱' + Number(s.gross_sales || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('posDailyDiscounts').textContent = '₱' + Number(s.total_discounts || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('posDailyNet').textContent = '₱' + Number(s.net_sales || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('posDailyOrdersCount').textContent = `${s.total_orders || 0} orders completed`;
            document.getElementById('posDailyAov').textContent = '₱' + Number(s.average_order_value || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('posDailyVatableBase').textContent = '₱' + Number(s.vatable_sales || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('posDailyVatAmount').textContent = '₱' + Number(s.vat_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('posDailyItemsSold').textContent = `${s.total_items_sold || 0} items`;
            document.getElementById('posDailyDateBadge').textContent = filter.period_label || selectedDate;

            // Render Payment Tender breakdown
            const tenders = data.payment_breakdown || [];
            const tenderIcons = {
                'Cash': { icon: 'bi-cash-coin', color: 'success' },
                'GCash': { icon: 'bi-phone', color: 'primary' },
                'Maya': { icon: 'bi-credit-card', color: 'info' }
            };
            let tenderHtml = '';
            tenders.forEach(pr => {
                const method = pr.method || 'Cash';
                const amount = pr.total || 0;
                const conf = tenderIcons[method] || { icon: 'bi-wallet', color: 'secondary' };
                tenderHtml += `
                    <div class="col-4">
                        <div class="border rounded-3 p-2 bg-light">
                            <span class="d-block small text-muted"><i class="bi ${conf.icon} text-${conf.color} me-1"></i>${escapeHtml(method)}</span>
                            <span class="fw-bold fs-6 text-dark">₱${Number(amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                    </div>
                `;
            });
            if (!tenderHtml || tenders.length === 0) {
                tenderHtml = `<div class="col-12 text-muted small py-2">No payment transactions recorded for this date.</div>`;
            }
            document.getElementById('posDailyTenderCards').innerHTML = tenderHtml;

            // Render Top Items
            const items = data.top_products || [];
            let itemsHtml = '';
            if (items.length === 0) {
                itemsHtml = `<tr><td colspan="4" class="text-center text-muted py-3">No products sold on this date yet.</td></tr>`;
            } else {
                items.slice(0, 5).forEach((p, idx) => {
                    itemsHtml += `
                        <tr>
                            <td class="ps-3 fw-bold text-dark">${idx + 1}. ${escapeHtml(p.product_name)}</td>
                            <td><span class="badge bg-light text-secondary border">${escapeHtml(p.category_name || 'Beverage')}</span></td>
                            <td class="text-center fw-bold">${p.total_qty || p.units_sold || 0}</td>
                            <td class="text-end pe-3 fw-bold text-success">₱${Number(p.total_sales || p.revenue || 0).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('posDailyTopItemsTable').innerHTML = itemsHtml;
        } else {
            showToast(data.message || 'Failed to load daily report.', 'danger');
        }
    } catch (e) {
        if (spinner) spinner.style.display = 'none';
        showToast('Error fetching daily sales data.', 'danger');
    }
}

function printPosDailyReading() {
    if (!currentPosDailyData || !currentPosDailyData.success) {
        showToast('Please wait for the daily report to load before printing.', 'warning');
        return;
    }
    const s = currentPosDailyData.summary || {};
    const filter = currentPosDailyData.filter || {};
    const dateStr = filter.period_label || 'Today';
    const printWindow = window.open('', '_blank', 'width=450,height=700');
    if (!printWindow) {
        window.print();
        return;
    }
    
    let tendersRows = '';
    (currentPosDailyData.payment_breakdown || []).forEach(pr => {
        tendersRows += `<tr><td style="padding: 3px 0;">${escapeHtml(pr.method)}:</td><td style="text-align: right; font-weight: bold;">₱${Number(pr.total).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>`;
    });

    let topItemsRows = '';
    (currentPosDailyData.top_products || []).slice(0, 10).forEach(p => {
        const qty = p.total_qty || p.units_sold || 0;
        const rev = p.total_sales || p.revenue || 0;
        topItemsRows += `<tr><td style="padding: 2px 0;">${escapeHtml(p.product_name)} (x${qty})</td><td style="text-align: right;">₱${Number(rev).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>`;
    });

    const receiptHtml = `<!DOCTYPE html>
    <html>
    <head>
        <title>Daily Reading - ${dateStr}</title>
        <style>
            body { font-family: 'Courier New', monospace; font-size: 13px; color: #000; margin: 20px; line-height: 1.3; }
            .text-center { text-align: center; }
            .divider { border-top: 1px dashed #000; margin: 8px 0; }
            table { width: 100%; border-collapse: collapse; }
            .fw-bold { font-weight: bold; }
            @media print { body { margin: 5px; } }
        </style>
    </head>
    <body onload="window.print(); window.close();">
        <div class="text-center">
            <h3 style="margin: 0; font-size: 16px;">RAEDEN'S BAKERY AND CAFÉ</h3>
            <div>Artisanal Bakes &amp; Specialty Coffee</div>
            <div>POS Terminal #01 • Daily Sales Report</div>
            <div class="divider"></div>
            <div class="fw-bold">DAILY SALES / SHIFT READING (X-READING)</div>
            <div>Date: ${dateStr}</div>
            <div>Generated: ${new Date().toLocaleString('en-PH')}</div>
        </div>
        <div class="divider"></div>
        <table>
            <tr><td style="padding: 3px 0;">Gross Sales:</td><td style="text-align: right; font-weight: bold;">₱${Number(s.gross_sales||0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>
            <tr><td style="padding: 3px 0;">Discounts:</td><td style="text-align: right; color: red;">-₱${Number(s.total_discounts||0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>
            <tr><td style="padding: 3px 0; font-weight: bold;">NET SALES:</td><td style="text-align: right; font-weight: bold; font-size: 15px;">₱${Number(s.net_sales||0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>
            <tr><td style="padding: 3px 0;">Total Completed Orders:</td><td style="text-align: right;">${s.total_orders||0}</td></tr>
            <tr><td style="padding: 3px 0;">Average Order Value:</td><td style="text-align: right;">₱${Number(s.average_order_value||0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>
            <tr><td style="padding: 3px 0;">Total Units Sold:</td><td style="text-align: right;">${s.total_items_sold||0}</td></tr>
        </table>
        <div class="divider"></div>
        <div class="fw-bold mb-1">VAT BREAKDOWN (12%):</div>
        <table>
            <tr><td style="padding: 2px 0;">Vatable Sales Base:</td><td style="text-align: right;">₱${Number(s.vatable_sales||0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>
            <tr><td style="padding: 2px 0;">12% Output VAT:</td><td style="text-align: right;">₱${Number(s.vat_amount||0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td></tr>
        </table>
        <div class="divider"></div>
        <div class="fw-bold mb-1">PAYMENT TENDER BREAKDOWN:</div>
        <table>${tendersRows || '<tr><td>No records</td></tr>'}</table>
        <div class="divider"></div>
        <div class="fw-bold mb-1">TOP SELLING PRODUCTS:</div>
        <table>${topItemsRows || '<tr><td>No records</td></tr>'}</table>
        <div class="divider"></div>
        <br>
        <div>Certified By:</div>
        <div style="margin-top: 35px; border-top: 1px solid #000; text-align: center; font-weight: bold;">
            Cashier on Duty / Terminal Operator
        </div>
    </body>
    </html>`;
    
    printWindow.document.write(receiptHtml);
    printWindow.document.close();
}

async function refreshPosPackagingStock() {
    try {
        const res = await fetch('api/pos.php');
        const data = await res.json();
        if (data && data.success && data.packaging_stock) {
            POS_PACKAGING_STOCK = data.packaging_stock;
            updatePosPackagingUI();
        }
    } catch (e) {}
}

updatePosPackagingUI();
setInterval(refreshPosPackagingStock, 10000);
</script>
