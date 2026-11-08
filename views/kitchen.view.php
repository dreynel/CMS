<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Barista Brewing & Kitchen Display System (KDS) Operations Dashboard (Sidebar Layout)
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

require_once __DIR__ . '/../includes/inventory.php';
inv_ensure_schema($pdo);

// Fetch consumable ingredient levels for the Barista station from inventory_items
$kdsRawItems = $pdo->query("SELECT i.* FROM inventory_items i WHERE i.item_type = 'raw' AND i.is_active = 1 ORDER BY i.name LIMIT 8")->fetchAll();
$kdsFinishedItems = $pdo->query("SELECT i.* FROM inventory_items i WHERE i.item_type = 'finished' AND i.is_active = 1 ORDER BY i.name")->fetchAll();
?>

<div class="dashboard-layout">
    
    <!-- Left Barista Operations Sidebar (Dark Theme) -->
    <aside class="dashboard-sidebar dashboard-sidebar-dark p-3 shadow-sm">
        <div>
            <!-- Station Brand & Header -->
            <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow-xs border border-secondary border-opacity-50" style="width: 38px; height: 38px; object-fit: cover;">
                <div>
                    <div class="fw-black text-white small mb-0">Raeden's Kitchen</div>
                    <span class="badge text-white fw-bold" style="background-color: var(--brand-secondary); font-size: 0.65rem;">BREW &amp; BAKE STATION #02</span>
                </div>
            </div>

            <?php
            $kdsUser = $_SESSION['user'] ?? ['name' => 'Jason Cole', 'role' => 'barista'];
            $kdsInitials = strtoupper(substr($kdsUser['name'], 0, 2));
            ?>
            <!-- Barista Profile Card -->
            <div class="sidebar-profile-card sidebar-profile-card-dark mb-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-warning text-dark fw-black d-flex align-items-center justify-content-center shadow-xs" style="width: 40px; height: 40px; font-size: 0.95rem;">
                        <?= htmlspecialchars($kdsInitials) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-white fs-6" style="line-height: 1.2;"><?= htmlspecialchars($kdsUser['name']) ?></div>
                        <small class="text-white-50 d-block" style="font-size: 0.72rem;">Brew &amp; Bake Specialist • Raeden's</small>
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center text-white-50" style="font-size: 0.72rem;">
                    <span>Station Time:</span>
                    <span id="kdsClock" class="fw-mono text-warning fw-bold">--:--:--</span>
                </div>
            </div>

            <!-- Barista Modules & Queue Navigation -->
            <div class="sidebar-section-title text-white-50">Barista Modules</div>
            <nav class="nav flex-column gap-1 mb-3">
                <a class="sidebar-nav-item sidebar-nav-item-dark d-flex justify-content-between" id="navBaristaBrewing" onclick="switchBaristaModule('brewing', this)">
                    <span class="d-flex align-items-center gap-2"><i class="bi bi-cup-hot-fill text-warning"></i> Brewing & Production</span>
                    <span class="badge bg-warning text-dark rounded-pill fw-bold" id="badgeBrewableNav"><?= count($kdsFinishedItems) ?></span>
                </a>
            </nav>

            <!-- Queue Navigation & Quick Status Filters -->
            <div class="sidebar-section-title text-white-50">Live KDS Ticket Queues</div>
            <nav class="nav flex-column gap-1 mb-3">
                <a class="sidebar-nav-item sidebar-nav-item-dark active" id="navKdsAll" onclick="filterKdsColumn('all', this)">
                    <i class="bi bi-kanban text-warning"></i>
                    <span class="flex-grow-1">All Lanes (4)</span>
                </a>
                <a class="sidebar-nav-item sidebar-nav-item-dark d-flex justify-content-between" onclick="filterKdsColumn('pending', this)">
                    <span class="d-flex align-items-center gap-2"><i class="bi bi-hourglass-split text-warning"></i> 1. Pending</span>
                    <span class="badge bg-warning text-dark rounded-pill fw-bold" id="badgePendingNav">0</span>
                </a>
                <a class="sidebar-nav-item sidebar-nav-item-dark d-flex justify-content-between" onclick="filterKdsColumn('brewing', this)">
                    <span class="d-flex align-items-center gap-2"><i class="bi bi-fire text-info"></i> 2. Brewing</span>
                    <span class="badge bg-primary text-white rounded-pill fw-bold" id="badgeBrewingNav">0</span>
                </a>
                <a class="sidebar-nav-item sidebar-nav-item-dark d-flex justify-content-between" onclick="filterKdsColumn('ready', this)">
                    <span class="d-flex align-items-center gap-2"><i class="bi bi-bell-fill text-success"></i> 3. Ready to Serve</span>
                    <span class="badge bg-success text-white rounded-pill fw-bold" id="badgeReadyNav">0</span>
                </a>
                <a class="sidebar-nav-item sidebar-nav-item-dark d-flex justify-content-between" onclick="filterKdsColumn('completed', this)">
                    <span class="d-flex align-items-center gap-2"><i class="bi bi-check-all text-secondary"></i> 4. Completed</span>
                    <span class="badge bg-secondary text-white rounded-pill fw-bold" id="badgeCompletedNav">0</span>
                </a>
            </nav>

            <!-- Station Consumables Watch -->
            <div class="sidebar-section-title text-white-50">Consumables Watch</div>
            <div class="p-2.5 rounded-3 bg-black bg-opacity-50 border border-secondary border-opacity-25 mb-3 small text-white-50" id="sideConsumablesWatchBox">
                <?php if (empty($kdsRawItems)): ?>
                    <div class="text-white-50 small">No raw ingredients in Master List</div>
                <?php else: ?>
                    <?php foreach ($kdsRawItems as $idx => $ri):
                        $rStock = (float)$ri['current_stock'];
                    ?>
                        <div class="d-flex justify-content-between align-items-center <?= $idx < count($kdsRawItems) - 1 ? 'mb-1.5' : '' ?>">
                            <span class="text-truncate me-2"><i class="bi bi-circle-fill <?= $rStock > 0 ? 'text-success' : 'text-danger' ?> me-1" style="font-size: 0.45rem;"></i><?= htmlspecialchars($ri['name']) ?></span>
                            <strong class="text-white text-nowrap"><?= number_format($rStock, 0) ?> <?= htmlspecialchars($ri['unit']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Station Tools & Quick Actions -->
            <div class="sidebar-section-title text-white-50">Station Tools</div>
            <div class="d-flex flex-column gap-2 mb-3">
                <button type="button" class="btn btn-warning btn-sm rounded-pill w-100 fw-bold text-dark text-start px-3" onclick="switchBaristaModule('brewing', document.getElementById('navBaristaBrewing'))">
                    <i class="bi bi-fire me-1.5"></i> Brewing &amp; Production
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill w-100 fw-bold text-start px-3" onclick="openKitchenYieldCalculatorModal()">
                    <i class="bi bi-calculator-fill me-1.5"></i> Product Yield Calculator
                </button>
                <button type="button" class="btn btn-outline-info btn-sm rounded-pill w-100 fw-bold text-start px-3" data-bs-toggle="modal" data-bs-target="#recipeGuideModal">
                    <i class="bi bi-book-half me-1.5"></i> Drink Formulas
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill w-100 fw-bold text-start px-3" id="btnAudioToggle" onclick="toggleAudio()">
                    <i class="bi bi-volume-up-fill me-1.5" id="audioIcon"></i> Sound Chime ON
                </button>
                <button type="button" class="btn btn-outline-light btn-sm rounded-pill w-100 fw-medium text-start px-3" onclick="loadKdsOrders(); loadBaristaBrewingSuite();">
                    <i class="bi bi-arrow-repeat me-1.5" id="refreshIcon"></i> Force Sync
                </button>
            </div>

            <!-- Station Session -->
            <div class="sidebar-section-title text-white-50">Active Session</div>
            <div class="p-2.5 rounded-3 bg-black bg-opacity-50 border border-secondary border-opacity-25 mb-3 small">
                <div class="d-flex align-items-center gap-2 mb-2 text-white-50">
                    <span class="pulse-warning"></span>
                    <span>Brew Station Active</span>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 fw-bold py-1.5" onclick="globalStaffLogout()">
                    <i class="bi bi-box-arrow-right me-1.5"></i> Sign Out
                </button>
            </div>
        </div>

        <!-- Sidebar Footer Status -->
        <div class="pt-3 border-top border-secondary border-opacity-25 mt-3">
            <div class="d-flex align-items-center justify-content-between text-white-50" style="font-size: 0.7rem;">
                <span class="d-flex align-items-center gap-1.5">
                    <span class="pulse-warning"></span> POS Auto-Sync
                </span>
                <span class="badge bg-dark border border-secondary text-white-50">Every 4s</span>
            </div>
        </div>
    </aside>

    <!-- Main Barista Workspace -->
    <main class="dashboard-main dashboard-main-dark p-3 p-lg-4">
        
        <!-- Workspace Header Banner -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="fw-black text-white mb-0 tracking-tight" id="baristaMainTitle">Barista Operations Dashboard</h3>
                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1" style="font-size: 0.7rem;" id="baristaMainBadge">LIVE KDS</span>
                </div>
                <div class="small text-white-50 mt-1" id="baristaMainSubtitle">
                    Real-time beverage tickets, drink formulations, and fulfillment tracking for Raeden’s Bakery and Café.
                </div>
            </div>

            <!-- Top Module Switcher + Performance Quick Counters -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group btn-group-sm me-lg-2 shadow-sm" role="group">
                    <button type="button" class="btn btn-warning text-dark fw-bold px-3 py-1.5" id="btnTopModeKds" onclick="switchBaristaModule('kds', document.getElementById('navKdsAll'))">
                        <i class="bi bi-kanban me-1"></i> Live KDS Tickets
                    </button>
                    <button type="button" class="btn btn-outline-warning fw-bold px-3 py-1.5" id="btnTopModeBrewing" onclick="switchBaristaModule('brewing', document.getElementById('navBaristaBrewing'))">
                        <i class="bi bi-cup-hot-fill me-1"></i> Brewing & Production
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2 overflow-auto flex-nowrap py-1">
                    <div class="bg-dark bg-opacity-75 border border-warning border-opacity-50 px-3 py-1.5 rounded-3 text-center">
                        <span class="text-white-50 text-uppercase d-block" style="font-size: 0.65rem;">Pending</span>
                        <span id="countPending" class="fw-black text-warning fs-5">0</span>
                    </div>
                    <div class="bg-dark bg-opacity-75 border border-primary border-opacity-50 px-3 py-1.5 rounded-3 text-center">
                        <span class="text-white-50 text-uppercase d-block" style="font-size: 0.65rem;">Brewing</span>
                        <span id="countBrewing" class="fw-black text-info fs-5">0</span>
                    </div>
                    <div class="bg-dark bg-opacity-75 border border-success border-opacity-50 px-3 py-1.5 rounded-3 text-center">
                        <span class="text-white-50 text-uppercase d-block" style="font-size: 0.65rem;">Ready</span>
                        <span id="countReady" class="fw-black text-success fs-5">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- SECTION A: LIVE KDS TICKETS KANBAN BOARD                        -->
        <!-- =============================================================== -->
        <div id="baristaKdsSection">
            <!-- Upcoming Scheduled Pre-Orders Banner (Auto-Fires 15 Minutes Before Booking Arrival) -->
            <div id="kdsScheduledQueueBanner" class="mb-3" style="display: none;"></div>

            <!-- Mobile Lane Switcher Pills (< xl) -->
            <div class="d-xl-none mb-3 overflow-auto flex-nowrap pb-1">
                <div class="btn-group btn-group-sm w-100 shadow-sm" role="group" id="mobileKdsLaneTabs">
                    <button type="button" class="btn btn-dark border-secondary border-opacity-50 active text-warning fw-bold py-1.5" onclick="filterKdsColumn('all', this)">All (4)</button>
                    <button type="button" class="btn btn-dark border-secondary border-opacity-50 text-warning fw-bold py-1.5" onclick="filterKdsColumn('pending', this)">1. Pending</button>
                    <button type="button" class="btn btn-dark border-secondary border-opacity-50 text-info fw-bold py-1.5" onclick="filterKdsColumn('brewing', this)">2. Brewing</button>
                    <button type="button" class="btn btn-dark border-secondary border-opacity-50 text-success fw-bold py-1.5" onclick="filterKdsColumn('ready', this)">3. Ready</button>
                    <button type="button" class="btn btn-dark border-secondary border-opacity-50 text-secondary fw-bold py-1.5" onclick="filterKdsColumn('completed', this)">4. Done</button>
                </div>
            </div>

            <!-- Main KDS Kanban Board (4 Columns) -->
            <div class="row g-3">
                
                <!-- Column 1: Pending / Incoming Tickets -->
                <div class="col-xl-3 col-md-6" id="colWrapperPending">
                    <div class="card bg-black border border-secondary border-opacity-25 rounded-4 shadow-sm h-100 kds-col-new">
                        <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-warning mb-0">
                                <span class="pulse-warning me-2"></span> 1. PENDING TICKETS
                            </h6>
                            <span class="badge bg-warning text-dark rounded-pill fw-bold px-2.5 py-1" id="badgePendingCol">0</span>
                        </div>
                        <div class="card-body p-2 overflow-auto kds-column-cards" style="min-height: 55vh; max-height: calc(100dvh - 270px);" id="columnPending">
                            <div class="text-center py-5 text-white-50 small">
                                <i class="bi bi-hourglass text-secondary fs-3 d-block mb-2"></i>
                                No incoming tickets
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Brewing / In Preparation -->
                <div class="col-xl-3 col-md-6" id="colWrapperBrewing">
                    <div class="card bg-black border border-secondary border-opacity-25 rounded-4 shadow-sm h-100 kds-col-brewing">
                        <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-info mb-0">
                                <i class="bi bi-fire text-info me-2"></i> 2. BREWING / PREP
                            </h6>
                            <span class="badge bg-primary rounded-pill fw-bold px-2.5 py-1" id="badgeBrewingCol">0</span>
                        </div>
                        <div class="card-body p-2 overflow-auto kds-column-cards" style="min-height: 55vh; max-height: calc(100dvh - 270px);" id="columnBrewing">
                            <div class="text-center py-5 text-white-50 small">
                                <i class="bi bi-cup-hot text-secondary fs-3 d-block mb-2"></i>
                                No orders currently brewing
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Ready for Pickup / Serving -->
                <div class="col-xl-3 col-md-6" id="colWrapperReady">
                    <div class="card bg-black border border-secondary border-opacity-25 rounded-4 shadow-sm h-100 kds-col-ready">
                        <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-success mb-0">
                                <i class="bi bi-bell-fill text-success me-2"></i> 3. READY TO SERVE
                            </h6>
                            <span class="badge bg-success rounded-pill fw-bold px-2.5 py-1" id="badgeReadyCol">0</span>
                        </div>
                        <div class="card-body p-2 overflow-auto kds-column-cards" style="min-height: 55vh; max-height: calc(100dvh - 270px);" id="columnReady">
                            <div class="text-center py-5 text-white-50 small">
                                <i class="bi bi-check2-circle text-secondary fs-3 d-block mb-2"></i>
                                No drinks awaiting pickup
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 4: Completed / Served Archive -->
                <div class="col-xl-3 col-md-6" id="colWrapperCompleted">
                    <div class="card bg-black border border-secondary border-opacity-25 rounded-4 shadow-sm h-100 kds-col-completed">
                        <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-secondary mb-0">
                                <i class="bi bi-check-all text-secondary me-2"></i> 4. COMPLETED TODAY
                            </h6>
                            <span class="badge bg-secondary rounded-pill fw-bold px-2.5 py-1" id="badgeCompletedCol">0</span>
                        </div>
                        <div class="card-body p-2 overflow-auto kds-column-cards" style="min-height: 55vh; max-height: calc(100dvh - 270px);" id="columnCompleted">
                            <div class="text-center py-5 text-white-50 small">
                                <i class="bi bi-archive text-secondary fs-3 d-block mb-2"></i>
                                Recent history
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- =============================================================== -->
        <!-- SECTION B: BARISTA BREWING & PRODUCTION MODULE                  -->
        <!-- (-Ingredients BREW_OUT / +Finished Products BREW_IN)            -->
        <!-- =============================================================== -->
        <div id="baristaBrewingSection" class="d-none">
            <div class="row g-4">
                <!-- Left Column: Brew Production Workstation -->
                <div class="col-xl-5">
                    <div class="card bg-black border border-secondary border-opacity-50 rounded-4 shadow-sm mb-4">
                        <div class="card-header bg-dark border-bottom border-secondary border-opacity-25 px-4 py-3 rounded-top-4">
                            <h6 class="fw-bold text-warning mb-0">
                                <i class="bi bi-cup-hot-fill me-2"></i>Brew / Produce Finished Products
                            </h6>
                            <small class="text-white-50">Validates &amp; deducts recipe + add-on raw ingredients (<code>BREW_OUT -</code>) from Inventory Ledger (Finished products are non-stocked)</small>
                        </div>
                        <div class="card-body p-4">
                            <form id="baristaBrewForm" onsubmit="submitBaristaBrewBatch(event)" novalidate>
                                <div class="row g-2 mb-3">
                                    <div class="col-8">
                                        <label class="form-label small fw-bold text-white-50">Finished Product to Brew <span class="text-warning">*</span></label>
                                        <input type="text" list="baristaBrewItemsDatalist" class="form-control bg-dark text-white border-secondary rounded-3" id="brewFinishedItemInput" placeholder="Type code or beverage name (select from list)..." autocomplete="off" required oninput="onBaristaBeverageSelected()" onchange="onBaristaBeverageSelected()">
                                        <input type="hidden" id="brewFinishedItem" value="">
                                        <datalist id="baristaBrewItemsDatalist"></datalist>
                                        <div class="form-text small text-white-50" id="brewFinishedItemHint">Type or pick a beverage from the datalist.</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold text-white-50">Quantity to Brew <span class="text-warning">*</span></label>
                                        <input type="number" step="1" min="1" class="form-control bg-dark text-warning border-secondary rounded-3 fw-bold" id="brewQty" value="10" required oninput="syncBaristaAddonQtysAndCheck()">
                                    </div>
                                </div>

                                <!-- Allowed Add-ons Under Selected Finished Product (Optional Inclusion in Brew) -->
                                <div class="mb-3" id="brewAddonsBox">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                        <span class="small fw-bold text-info text-uppercase" style="font-size: 0.72rem;">
                                            <i class="bi bi-plus-circle-dotted me-1"></i>Allowed Add-ons for Selected Product (Include in Brew)
                                        </span>
                                        <span class="badge bg-dark text-info border border-info border-opacity-50" id="brewAddonsCountBadge">0 add-ons</span>
                                    </div>
                                    <div id="brewAddonsChecklist" class="p-2.5 rounded-3 bg-dark border border-secondary border-opacity-50 small text-white-50">
                                        Select a finished product above to view its allowed add-ons and ingredient formulas.
                                    </div>
                                </div>

                                <!-- Live Ingredient + Add-on Requirement vs Availability Table -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                        <span class="small fw-bold text-white-50 text-uppercase" style="font-size: 0.72rem;">Required Raw Ingredients + Add-ons Check</span>
                                        <span class="badge bg-dark text-warning border border-secondary" id="brewMaxBadge">Select a product</span>
                                    </div>
                                    <div class="table-responsive border border-secondary border-opacity-50 rounded-3">
                                        <table class="table table-dark table-sm align-middle mb-0 small">
                                            <thead class="text-white-50">
                                                <tr>
                                                    <th class="ps-3">Ingredient &amp; Source</th>
                                                    <th class="text-end">Per Unit</th>
                                                    <th class="text-end">Batch Req.</th>
                                                    <th class="text-end">Available</th>
                                                    <th class="text-center pe-3">Check</th>
                                                </tr>
                                            </thead>
                                            <tbody id="brewReqTableBody">
                                                <tr><td colspan="5" class="text-center py-3 text-white-50">Select a finished product above to check ingredient &amp; add-on availability.</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-white-50">Batch Notes</label>
                                    <input type="text" class="form-control bg-dark text-white border-secondary rounded-3" id="brewRemarks" placeholder="e.g. Barista shift batch prep">
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-light rounded-pill px-3 fw-bold" onclick="document.getElementById('brewPostFlag').value='0'">
                                        Save Draft
                                    </button>
                                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold flex-grow-1" id="btnPostBrew" onclick="document.getElementById('brewPostFlag').value='1'">
                                        <i class="bi bi-fire me-1"></i> Brew &amp; Post to Inventory Ledger
                                    </button>
                                </div>
                                <input type="hidden" id="brewPostFlag" value="1">
                            </form>
                        </div>
                    </div>

                    <!-- Raw Ingredients Bar Hoppers & Converted Balances -->
                    <div class="card bg-black border border-secondary border-opacity-50 rounded-4 shadow-sm">
                        <div class="card-header bg-dark border-bottom border-secondary border-opacity-25 px-4 py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-info mb-0"><i class="bi bi-droplet-half me-2"></i>Raw Ingredients Stock (Bar Station)</h6>
                                <small class="text-white-50">Converted usable units available for brewing &amp; add-ons</small>
                            </div>
                        </div>
                        <div class="card-body p-3 overflow-auto" style="max-height: 290px;">
                            <div class="table-responsive">
                                <table class="table table-dark table-sm align-middle mb-0 small">
                                    <thead class="text-white-50">
                                        <tr>
                                            <th>Code</th>
                                            <th>Raw Ingredient</th>
                                            <th class="text-end">Available Stock</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="baristaRawMaterialsBody">
                                        <tr><td colspan="4" class="text-center py-3 text-white-50">Loading raw ingredients...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Finished Beverages Stock Status & Brew Batches History -->
                <div class="col-xl-7">
                    <!-- Finished Beverages Menu & Recipe Card -->
                    <div class="card bg-black border border-secondary border-opacity-50 rounded-4 shadow-sm mb-4">
                        <div class="card-header bg-dark border-bottom border-secondary border-opacity-25 px-4 py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-white mb-0"><i class="bi bi-cup-straw text-warning me-2"></i>Finished Beverages — Recipes &amp; Allowed Add-ons (Made to Order)</h6>
                                <small class="text-white-50">Finished products are non-stocked units; raw material ingredients are deducted when brewed &amp; served</small>
                            </div>
                        </div>
                        <div class="card-body p-3 overflow-auto" style="max-height: 280px;">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0 small">
                                    <thead class="text-white-50 text-uppercase">
                                        <tr>
                                            <th>Code</th>
                                            <th>Beverage / Product</th>
                                            <th class="text-center">Recipe &amp; Add-ons</th>
                                            <th class="text-end">Inventory Mode</th>
                                            <th class="text-end">Quick Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="baristaFinishedItemsBody">
                                        <tr><td colspan="5" class="text-center py-3 text-white-50">Loading finished products...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Brewing & Production Batches History -->
                    <div class="card bg-black border border-secondary border-opacity-50 rounded-4 shadow-sm">
                        <div class="card-header bg-dark border-bottom border-secondary border-opacity-25 px-4 py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-white mb-0"><i class="bi bi-journal-check text-success me-2"></i>Brewing &amp; Ingredient Deduction Log</h6>
                                <small class="text-white-50">Each posted brew deducts raw ingredients + add-ons (<code>BREW_OUT -</code>) from the Inventory Ledger</small>
                            </div>
                        </div>
                        <div class="card-body p-3 overflow-auto" style="max-height: 340px;">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle mb-0 small">
                                    <thead class="text-white-50 text-uppercase">
                                        <tr>
                                            <th>Batch No.</th>
                                            <th>Date</th>
                                            <th>Finished Product &amp; Add-ons</th>
                                            <th class="text-end">Brew Qty</th>
                                            <th>Status</th>
                                            <th>By</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="baristaBrewsTableBody">
                                        <tr><td colspan="7" class="text-center py-4 text-white-50">No brewing batches recorded yet.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Modal: Signature Drink Formulas & Live Master Recipes -->
<div class="modal fade" id="recipeGuideModal" tabindex="-1" aria-labelledby="recipeGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1440px, 96vw);">
        <div class="modal-content bg-dark text-white rounded-4 border border-secondary shadow-lg overflow-hidden">
            <div class="modal-header border-secondary px-4 py-3 bg-black">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-book-half text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="recipeGuideModalLabel">Barista Drink Formulas, Ingredients &amp; Allowed Add-ons</h5>
                        <small class="text-white-50">Standard recipes and add-on raw material specifications per finished product</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-warning mb-0"><i class="bi bi-diagram-3-fill me-1.5"></i>Master List Beverage Recipes &amp; Allowed Add-ons</h6>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3" onclick="loadBaristaRecipeDirectory()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh Recipes
                    </button>
                </div>
                <div id="liveMasterRecipesContainer" class="row g-3 mb-4">
                    <div class="col-12 text-center py-3 text-white-50">Loading master beverage recipes &amp; add-ons...</div>
                </div>

                <hr class="border-secondary my-3">
                <h6 class="fw-bold text-info mb-3"><i class="bi bi-stars me-1.5"></i>Raeden's Signature Formulations &amp; Preparation Notes</h6>
                <div class="row g-3">
                    <!-- Recipe 1 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-black border border-secondary h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-warning mb-0">Raeden's Honey Caramel Latte</h6>
                                <span class="badge bg-warning text-dark fw-bold">Signature</span>
                            </div>
                            <div class="small text-white-50 mb-2">Espresso layered with wild flower honey &amp; caramel.</div>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Espresso:</strong> 2 shots (36g extraction)</li>
                                <li><strong>Honey:</strong> 20ml local Iloilo honey in cup base</li>
                                <li><strong>Milk:</strong> 180ml steamed whole milk (65°C)</li>
                                <li><strong>Garnish:</strong> Golden caramel lattice drizzle</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recipe 2 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-black border border-secondary h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-info mb-0">Blue &amp; Gold Velvet Frappe</h6>
                                <span class="badge bg-primary fw-bold">Cold Special</span>
                            </div>
                            <div class="small text-white-50 mb-2">Butterfly pea vanilla frappe with butterscotch.</div>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Base:</strong> 150ml milk + 2 scoops vanilla frappe powder</li>
                                <li><strong>Color:</strong> 30ml natural butterfly pea blossom infusion</li>
                                <li><strong>Ice:</strong> 1 full scoop cube ice (blended high 25s)</li>
                                <li><strong>Topping:</strong> Golden whipped cream &amp; butterscotch crust</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recipe 3 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-black border border-secondary h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-light mb-0">Classic Spanish Latte</h6>
                                <span class="badge bg-secondary fw-bold">Bestseller</span>
                            </div>
                            <div class="small text-white-50 mb-2">Sweetened condensed milk espresso classic.</div>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Condensed Milk:</strong> 25ml at bottom of glass</li>
                                <li><strong>Espresso:</strong> 2 shots fresh extracted espresso</li>
                                <li><strong>Milk:</strong> 160ml textured milk (hot or over ice)</li>
                                <li><strong>Ice:</strong> 16oz cup filled 75% with clear ice</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recipe 4 -->
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-black border border-secondary h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-warning mb-0">Campustech Dark Mocha</h6>
                                <span class="badge bg-dark border border-warning text-warning fw-bold">Rich Cocoa</span>
                            </div>
                            <div class="small text-white-50 mb-2">Davao dark cocoa paste melted with double shot.</div>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Cocoa Paste:</strong> 30g Davao 70% dark chocolate melt</li>
                                <li><strong>Espresso:</strong> 2 shots espresso stirred thoroughly</li>
                                <li><strong>Milk:</strong> 170ml silky steamed microfoam</li>
                                <li><strong>Finish:</strong> Dutch cocoa powder dusting</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Finished Product Yield Calculator (Kitchen / Barista Workstation) -->
<div class="modal fade" id="kitchenYieldModal" tabindex="-1" aria-labelledby="kitchenYieldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1200px, 96vw);">
        <div class="modal-content bg-dark text-white rounded-4 border border-secondary shadow-lg overflow-hidden">
            <div class="modal-header border-secondary px-4 py-3 bg-black">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px; background-color: var(--brand-secondary); color: #fff;">
                        <i class="bi bi-calculator-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="kitchenYieldModalLabel">Finished Product Yield &amp; Recipe Calculator</h5>
                        <small class="text-white-50">Calculate maximum finished food &amp; beverages produceable from current raw ingredients</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-black">
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-dark border-secondary text-white" id="kitchenYieldSearch" placeholder="Filter finished products by name or code..." oninput="filterKitchenYieldTable()">
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button type="button" class="btn btn-warning rounded-pill fw-bold text-dark px-3" onclick="loadKitchenYieldData()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Stock Calculation
                        </button>
                    </div>
                </div>

                <div class="table-responsive border border-secondary border-opacity-50 rounded-3 mb-4">
                    <table class="table table-dark table-hover align-middle mb-0 small">
                        <thead class="text-white-50 text-uppercase" style="font-size: 0.72rem;">
                            <tr>
                                <th class="ps-3">Product Name</th>
                                <th>Category</th>
                                <th class="text-center">Recipe Ingredients</th>
                                <th class="text-center">Max Yield (From Stock)</th>
                                <th>Bottleneck Ingredient</th>
                                <th class="text-center pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="kitchenYieldTableBody">
                            <tr><td colspan="6" class="text-center py-4 text-white-50">Loading yield calculations...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Interactive Kitchen Batch Simulator -->
                <div class="card bg-dark border border-secondary border-opacity-50 rounded-4 p-4" id="kitchenSimCard" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-50">
                        <div>
                            <h6 class="fw-bold text-warning mb-0" id="kSimProdName">Product Batch Simulator</h6>
                            <small class="text-white-50" id="kSimProdMeta">BOM Ingredient Sufficiency Check</small>
                        </div>
                        <span class="badge bg-black text-warning border border-secondary" id="kSimMaxPossibleBadge">Max 0 units</span>
                    </div>

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-white-50">Target Quantity to Prepare</label>
                            <input type="number" min="1" step="1" id="kSimTargetQty" value="10" class="form-control bg-black text-warning border-secondary fw-bold fs-5 rounded-3" oninput="runKitchenBatchSim()">
                        </div>
                        <div class="col-md-8">
                            <div id="kSimAlertBox" class="alert mb-0 py-2.5 px-3 rounded-3 small">
                                Calculation status
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border border-secondary border-opacity-50 rounded-3 mb-3">
                        <table class="table table-dark table-sm align-middle mb-0 small">
                            <thead class="text-white-50">
                                <tr>
                                    <th class="ps-3">Ingredient</th>
                                    <th class="text-end">Req. Per Unit</th>
                                    <th class="text-end">Batch Needed</th>
                                    <th class="text-end">Available in Bar</th>
                                    <th class="text-center">Check</th>
                                </tr>
                            </thead>
                            <tbody id="kSimIngredientTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-4" onclick="loadKSimIntoBrewWorkstation()">
                            <i class="bi bi-fire me-1"></i> Send to Workstation
                        </button>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-secondary bg-black px-4 py-2.5">
                <button type="button" class="btn btn-outline-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let BARISTA_INV_ITEMS = [];
let CURRENT_BREW_FINISHED_ID = 0;
let CURRENT_BREW_AVAILABLE_ADDONS = [];
let KITCHEN_YIELD_PRODUCTS = [];
let KITCHEN_SIM_SELECTED = null;

function openKitchenYieldCalculatorModal() {
    loadKitchenYieldData();
    const modalEl = document.getElementById('kitchenYieldModal');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

async function loadKitchenYieldData() {
    const tbody = document.getElementById('kitchenYieldTableBody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-white-50"><div class="spinner-border spinner-border-sm text-warning me-2"></div>Calculating finished product yields from ingredient inventory...</td></tr>`;

    try {
        const res = await fetch('api/inventory.php?action=yield_calculator');
        const data = await res.json();
        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">${escapeBaristaHtml(data.message || 'Error loading calculations')}</td></tr>`;
            return;
        }

        KITCHEN_YIELD_PRODUCTS = data.products || [];
        renderKitchenYieldTable(KITCHEN_YIELD_PRODUCTS);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Network error: ${escapeBaristaHtml(e.message)}</td></tr>`;
    }
}

function renderKitchenYieldTable(products) {
    const tbody = document.getElementById('kitchenYieldTableBody');
    if (!tbody) return;

    if (!products.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-white-50">No finished products found.</td></tr>`;
        return;
    }

    tbody.innerHTML = products.map(p => {
        const maxYield = p.max_yield || 0;
        let badge = '';
        if (!p.has_recipe) {
            badge = `<span class="badge bg-secondary">No Recipe</span>`;
        } else if (maxYield >= 10) {
            badge = `<span class="badge bg-success fw-bold fs-6">${maxYield} ${escapeBaristaHtml(p.unit)}</span>`;
        } else if (maxYield > 0) {
            badge = `<span class="badge bg-warning text-dark fw-bold fs-6">${maxYield} ${escapeBaristaHtml(p.unit)}</span>`;
        } else {
            badge = `<span class="badge bg-danger fw-bold fs-6">0 ${escapeBaristaHtml(p.unit)} (Out of Stock)</span>`;
        }

        let bottleneck = '';
        if (p.bottleneck) {
            bottleneck = `<strong class="${p.bottleneck.available_stock <= 0 ? 'text-danger' : 'text-warning'}">${escapeBaristaHtml(p.bottleneck.name)}</strong> <span class="text-white-50">(${p.bottleneck.available_stock} ${p.bottleneck.unit} left • ${p.bottleneck.qty_per_unit}/unit)</span>`;
        } else if (!p.has_recipe) {
            bottleneck = `<span class="text-white-50 fst-italic">Recipe needed</span>`;
        } else {
            bottleneck = `<span class="text-success"><i class="bi bi-check2 me-1"></i>All sufficient</span>`;
        }

        const kitchenPlaceholder = 'assets/images/placeholder-product.svg';
        const kitchenImgSrc = (p.image_url && String(p.image_url).trim() !== '') ? p.image_url : kitchenPlaceholder;

        return `
            <tr>
                <td class="ps-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 overflow-hidden bg-secondary bg-opacity-25 border border-secondary flex-shrink-0" style="width: 32px; height: 32px;">
                            <img src="${escapeBaristaHtml(kitchenImgSrc)}" 
                                 alt="${escapeBaristaHtml(p.name)}" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.onerror=null; this.src='${kitchenPlaceholder}';">
                        </div>
                        <div>
                            <div class="fw-bold text-white">${escapeBaristaHtml(p.name)}</div>
                            <span class="text-white-50 fw-mono small">${escapeBaristaHtml(p.item_code)}</span>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-dark border border-secondary text-white-50">${escapeBaristaHtml(p.category_name || 'Bakery & Café')}</span></td>
                <td class="text-center text-white-50">${p.recipe_count} ingredients</td>
                <td class="text-center">${badge}</td>
                <td>${bottleneck}</td>
                <td class="text-center pe-3">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark" onclick="selectKitchenSimProduct(${p.item_id})">
                        <i class="bi bi-sliders me-1"></i> Simulate
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function filterKitchenYieldTable() {
    const q = (document.getElementById('kitchenYieldSearch')?.value || '').toLowerCase().trim();
    if (!q) {
        renderKitchenYieldTable(KITCHEN_YIELD_PRODUCTS);
        return;
    }
    const filtered = KITCHEN_YIELD_PRODUCTS.filter(p => 
        (p.name || '').toLowerCase().includes(q) || 
        (p.item_code || '').toLowerCase().includes(q)
    );
    renderKitchenYieldTable(filtered);
}

function selectKitchenSimProduct(itemId) {
    const p = KITCHEN_YIELD_PRODUCTS.find(item => item.item_id === itemId);
    if (!p) return;
    KITCHEN_SIM_SELECTED = p;

    document.getElementById('kSimProdName').textContent = p.name;
    document.getElementById('kSimProdMeta').textContent = `${p.item_code} • ${p.category_name || 'Bakery & Café'} • Max Yield: ${p.max_yield} ${p.unit}`;
    document.getElementById('kSimMaxPossibleBadge').textContent = `Max Possible: ${p.max_yield} ${p.unit}`;

    const defaultQty = p.max_yield > 0 ? Math.min(p.max_yield, 10) : 5;
    document.getElementById('kSimTargetQty').value = defaultQty;

    document.getElementById('kitchenSimCard').style.display = 'block';
    runKitchenBatchSim();
}

function runKitchenBatchSim() {
    if (!KITCHEN_SIM_SELECTED) return;
    const target = Math.max(1, parseFloat(document.getElementById('kSimTargetQty')?.value) || 1);
    const ingredients = KITCHEN_SIM_SELECTED.ingredients || [];
    const tbody = document.getElementById('kSimIngredientTableBody');
    const alertBox = document.getElementById('kSimAlertBox');

    let hasShortage = false;

    tbody.innerHTML = ingredients.map(ing => {
        const needed = Math.round(ing.qty_per_unit * target * 10000) / 10000;
        const avail = ing.available_stock;
        const sufficient = (avail + 0.00001 >= needed);
        if (!sufficient) hasShortage = true;

        return `
            <tr class="${!sufficient ? 'table-danger' : ''}">
                <td class="ps-3 fw-bold text-white">${escapeBaristaHtml(ing.name)}</td>
                <td class="text-end text-white-50">${ing.qty_per_unit} ${escapeBaristaHtml(ing.unit)}</td>
                <td class="text-end fw-bold text-warning">${needed} ${escapeBaristaHtml(ing.unit)}</td>
                <td class="text-end ${!sufficient ? 'text-danger fw-bold' : 'text-white'}">${avail} ${escapeBaristaHtml(ing.unit)}</td>
                <td class="text-center">${sufficient ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-danger">Shortage</span>'}</td>
            </tr>
        `;
    }).join('');

    if (!ingredients.length) {
        alertBox.className = 'alert alert-secondary mb-0 py-2.5 px-3 rounded-3 small';
        alertBox.innerHTML = `No recipe formulas found for this product.`;
    } else if (!hasShortage) {
        alertBox.className = 'alert alert-success mb-0 py-2.5 px-3 rounded-3 small';
        alertBox.innerHTML = `<strong>✓ Feasible Batch!</strong> All ingredients are sufficient in stock for ${target} ${KITCHEN_SIM_SELECTED.unit}.`;
    } else {
        alertBox.className = 'alert alert-danger mb-0 py-2.5 px-3 rounded-3 small';
        alertBox.innerHTML = `<strong>⚠ Insufficient Stock!</strong> Cannot produce ${target} ${KITCHEN_SIM_SELECTED.unit}. Maximum possible from current ingredients: <strong>${KITCHEN_SIM_SELECTED.max_yield} ${KITCHEN_SIM_SELECTED.unit}</strong>.`;
    }
}

function loadKSimIntoBrewWorkstation() {
    if (!KITCHEN_SIM_SELECTED) return;
    const target = parseInt(document.getElementById('kSimTargetQty')?.value) || 1;

    // Close modal
    const modalEl = document.getElementById('kitchenYieldModal');
    if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();

    // Switch to brewing module
    switchBaristaModule('brewing', document.getElementById('navBaristaBrewing'));

    // Select the finished item in the workstation
    const input = document.getElementById('brewFinishedItemInput');
    const hidden = document.getElementById('brewFinishedItem');
    const qtyInput = document.getElementById('brewQty');

    if (input) input.value = `${KITCHEN_SIM_SELECTED.name} (${KITCHEN_SIM_SELECTED.item_code})`;
    if (hidden) hidden.value = KITCHEN_SIM_SELECTED.item_id;
    if (qtyInput) qtyInput.value = target;

    onBaristaBeverageSelected();
    showToast(`Loaded ${KITCHEN_SIM_SELECTED.name} (Batch of ${target}) into Workstation!`, 'success');
}

function fmtBrewQty(num) {
    const n = parseFloat(num || 0);
    return Number.isInteger(n) ? n.toLocaleString() : n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function escapeBaristaHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}

function switchBaristaModule(mode, navEl) {
    document.querySelectorAll('.dashboard-sidebar-dark .sidebar-nav-item-dark').forEach(item => {
        item.classList.remove('active');
    });
    if (navEl) navEl.classList.add('active');

    const kdsSec = document.getElementById('baristaKdsSection');
    const brewSec = document.getElementById('baristaBrewingSection');
    const btnKds = document.getElementById('btnTopModeKds');
    const btnBrew = document.getElementById('btnTopModeBrewing');
    const title = document.getElementById('baristaMainTitle');
    const badge = document.getElementById('baristaMainBadge');
    const subtitle = document.getElementById('baristaMainSubtitle');

    if (mode === 'brewing') {
        if (kdsSec) kdsSec.classList.add('d-none');
        if (brewSec) brewSec.classList.remove('d-none');
        if (btnKds) btnKds.className = 'btn btn-outline-warning fw-bold px-3 py-1.5';
        if (btnBrew) btnBrew.className = 'btn btn-warning text-dark fw-bold px-3 py-1.5';
        if (title) title.textContent = 'Barista Brewing & Production Workstation';
        if (badge) badge.textContent = 'BREWING MODULE';
        if (subtitle) subtitle.textContent = 'Brew finished products by deducting recipe raw materials + add-on ingredients (BREW_OUT -) and crediting POS finished stock (BREW_IN +).';
        loadBaristaBrewingSuite();
    } else {
        if (brewSec) brewSec.classList.add('d-none');
        if (kdsSec) kdsSec.classList.remove('d-none');
        if (btnKds) btnKds.className = 'btn btn-warning text-dark fw-bold px-3 py-1.5';
        if (btnBrew) btnBrew.className = 'btn btn-outline-warning fw-bold px-3 py-1.5';
        if (title) title.textContent = 'Barista Operations Dashboard';
        if (badge) badge.textContent = 'LIVE KDS';
        if (subtitle) subtitle.textContent = 'Real-time beverage tickets, drink formulations, and fulfillment tracking for Raeden’s Bakery and Café.';
    }
}

async function loadBaristaBrewingSuite() {
    try {
        const res = await fetch('api/inventory.php?action=items');
        const data = await res.json();
        if (!data.success) return;

        BARISTA_INV_ITEMS = data.items || [];
        const rawItems = BARISTA_INV_ITEMS.filter(i => i.item_type === 'raw');
        const finishedItems = BARISTA_INV_ITEMS.filter(i => i.item_type === 'finished');

        // Update Sidebar Consumables Watch live from actual raw items in Master List
        const sideWatch = document.getElementById('sideConsumablesWatchBox');
        if (sideWatch) {
            if (rawItems.length === 0) {
                sideWatch.innerHTML = `<div class="text-white-50 small">No raw ingredients in Master List</div>`;
            } else {
                const topRaw = rawItems.slice(0, 8);
                sideWatch.innerHTML = topRaw.map((ri, idx) => {
                    const st = parseFloat(ri.current_stock || 0);
                    return `
                        <div class="d-flex justify-content-between align-items-center ${idx < topRaw.length - 1 ? 'mb-1.5' : ''}">
                            <span class="text-truncate me-2"><i class="bi bi-circle-fill ${st > 0 ? 'text-success' : 'text-danger'} me-1" style="font-size: 0.45rem;"></i>${escapeBaristaHtml(ri.name)}</span>
                            <strong class="text-white text-nowrap">${fmtBrewQty(st)} ${escapeBaristaHtml(ri.unit)}</strong>
                        </div>
                    `;
                }).join('');
            }
        }

        // Populate Brew Finished Item Datalist
        const dlist = document.getElementById('baristaBrewItemsDatalist');
        if (dlist) {
            dlist.innerHTML = finishedItems.map(it =>
                `<option value="${escapeBaristaHtml(formatBaristaDatalistLabel(it))}">Made to Order • Recipe: ${it.recipe_lines || 0} ingr. • Add-ons: ${it.addon_lines || 0}</option>`
            ).join('');
        }

        // Render Raw Ingredients Table
        const rawBody = document.getElementById('baristaRawMaterialsBody');
        if (rawBody) {
            rawBody.innerHTML = rawItems.map(r => {
                const st = parseFloat(r.current_stock || 0);
                const badge = st > 0
                    ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Available</span>'
                    : '<span class="badge bg-danger">0 Stock</span>';
                return `
                    <tr>
                        <td class="fw-mono text-white-50">${escapeBaristaHtml(r.item_code)}</td>
                        <td class="fw-semibold text-white">${escapeBaristaHtml(r.name)}</td>
                        <td class="text-end fw-mono fw-bold ${st > 0 ? 'text-warning' : 'text-danger'}">${fmtBrewQty(st)} ${escapeBaristaHtml(r.unit)}</td>
                        <td class="text-center">${badge}</td>
                    </tr>
                `;
            }).join('');
        }

        // Render Finished Beverages Table (Non-Stocked / Made to Order)
        const finBody = document.getElementById('baristaFinishedItemsBody');
        if (finBody) {
            finBody.innerHTML = finishedItems.map(f => {
                const hasRec = parseInt(f.recipe_lines || 0) > 0;
                const addonCnt = parseInt(f.addon_lines || 0);
                return `
                    <tr>
                        <td class="fw-mono text-white-50">${escapeBaristaHtml(f.item_code)}</td>
                        <td class="fw-bold text-white">${escapeBaristaHtml(f.name)}</td>
                        <td class="text-center">
                            ${hasRec
                                ? `<div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                     <span class="badge bg-info-subtle text-info border border-info-subtle">${f.recipe_lines} ingr.</span>
                                     <span class="badge bg-primary-subtle text-primary border border-primary-subtle">${addonCnt} add-ons</span>
                                   </div>`
                                : `<span class="badge bg-secondary">No Recipe</span>`}
                        </td>
                        <td class="text-end"><span class="badge bg-dark text-warning border border-warning border-opacity-50">Made to Order (Non-Stocked)</span></td>
                        <td class="text-end">
                            ${hasRec
                                ? `<button type="button" class="btn btn-warning btn-sm rounded-pill px-2.5 py-0.5 text-dark fw-bold" onclick="selectBaristaDrinkToBrew(${f.id})"><i class="bi bi-fire me-1"></i>Select to Brew</button>`
                                : `<span class="text-white-50 small">Configure Recipe</span>`}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        await checkBaristaBrewIngredients();
        await loadBaristaBrewsHistory();
    } catch (e) {
        console.error('Error loading Barista Brewing Suite:', e);
    }
}

function formatBaristaDatalistLabel(it) {
    if (!it) return '';
    return `${it.item_code} — ${it.name}`;
}

function resolveBaristaBrewItem(rawInput) {
    const val = String(rawInput || '').trim().toLowerCase();
    if (!val) return null;
    return BARISTA_INV_ITEMS.filter(i => i.item_type === 'finished').find(it => {
        const label = formatBaristaDatalistLabel(it).toLowerCase();
        const code = String(it.item_code || '').toLowerCase();
        const name = String(it.name || '').toLowerCase();
        return val === label || val === code || val === name;
    }) || null;
}

function selectBaristaDrinkToBrew(finishedId) {
    const it = BARISTA_INV_ITEMS.find(i => parseInt(i.id) === parseInt(finishedId));
    const inp = document.getElementById('brewFinishedItemInput');
    if (it && inp) {
        CURRENT_BREW_FINISHED_ID = 0; // force reload of allowed add-ons checklist
        inp.value = formatBaristaDatalistLabel(it);
        checkBaristaBrewIngredients();
        inp.focus();
    }
}

function onBaristaBeverageSelected() {
    checkBaristaBrewIngredients();
}

function syncBaristaAddonQtysAndCheck() {
    const batchQty = Math.max(1, parseFloat(document.getElementById('brewQty')?.value || 1));
    document.querySelectorAll('#brewAddonsChecklist .brew-addon-row').forEach(row => {
        const cb = row.querySelector('.brew-addon-cb');
        const qInp = row.querySelector('.brew-addon-qty');
        if (qInp && (!cb || !cb.checked || qInp.dataset.autoSync !== '0')) {
            qInp.value = batchQty;
        }
    });
    checkBaristaBrewIngredients(true);
}

function getSelectedBaristaBrewAddons() {
    const selected = [];
    document.querySelectorAll('#brewAddonsChecklist .brew-addon-row').forEach(row => {
        const cb = row.querySelector('.brew-addon-cb');
        const qInp = row.querySelector('.brew-addon-qty');
        if (cb && cb.checked) {
            const addonId = parseInt(cb.dataset.addonId || 0);
            const qty = Math.max(1, parseFloat(qInp?.value || 1));
            if (addonId > 0) {
                selected.push({ addon_id: addonId, addon_name: cb.dataset.addonName || '', qty });
            }
        }
    });
    return selected;
}

function renderBaristaAllowedAddonsChecklist(addons, batchQty) {
    const container = document.getElementById('brewAddonsChecklist');
    const badge = document.getElementById('brewAddonsCountBadge');
    if (!container) return;

    CURRENT_BREW_AVAILABLE_ADDONS = addons || [];
    if (badge) badge.textContent = `${CURRENT_BREW_AVAILABLE_ADDONS.length} allowed add-on(s)`;

    if (CURRENT_BREW_AVAILABLE_ADDONS.length === 0) {
        container.innerHTML = `<div class="text-white-50 small">No add-ons configured under this finished product in Master List.</div>`;
        return;
    }

    container.innerHTML = `
        <div class="small text-white-50 mb-2">Check any add-ons to include their raw material ingredients in this brew batch:</div>
        <div class="d-flex flex-column gap-1.5">
            ${CURRENT_BREW_AVAILABLE_ADDONS.map(a => `
                <div class="brew-addon-row d-flex align-items-center justify-content-between gap-2 p-1.5 px-2 rounded-2 bg-black border border-secondary border-opacity-50">
                    <label class="form-check-label d-flex align-items-center gap-2 text-white small cursor-pointer mb-0 flex-grow-1">
                        <input type="checkbox" class="form-check-input mt-0 brew-addon-cb" data-addon-id="${a.id}" data-addon-name="${escapeBaristaHtml(a.addon_name)}" onchange="checkBaristaBrewIngredients(true)">
                        <span>
                            <strong class="text-info">+${escapeBaristaHtml(a.addon_name)}</strong>
                            <span class="text-white-50 ms-1">(${escapeBaristaHtml(a.ingredient_name)}: ${fmtBrewQty(a.qty_per_unit)} ${escapeBaristaHtml(a.unit)}/unit)</span>
                        </span>
                    </label>
                    <div class="d-flex align-items-center gap-1" style="width: 115px;">
                        <span class="text-white-50" style="font-size: 0.7rem;">Qty:</span>
                        <input type="number" min="1" step="1" class="form-control form-control-sm bg-dark text-warning border-secondary py-0 px-1.5 text-end fw-bold brew-addon-qty" value="${batchQty}" oninput="this.dataset.autoSync='0'; checkBaristaBrewIngredients(true)">
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

async function checkBaristaBrewIngredients(preserveAddonChecks = false) {
    const inp = document.getElementById('brewFinishedItemInput');
    const hiddenId = document.getElementById('brewFinishedItem');
    const hint = document.getElementById('brewFinishedItemHint');
    const rawVal = (inp?.value || '').trim();
    const matched = resolveBaristaBrewItem(rawVal);

    if (hiddenId) hiddenId.value = matched ? matched.id : '';

    const fid = matched ? parseInt(matched.id) : 0;
    const qty = Math.max(1, parseFloat(document.getElementById('brewQty')?.value || 1));
    const tbody = document.getElementById('brewReqTableBody');
    const maxBadge = document.getElementById('brewMaxBadge');
    const btn = document.getElementById('btnPostBrew');
    const addonsChecklist = document.getElementById('brewAddonsChecklist');
    const addonsBadge = document.getElementById('brewAddonsCountBadge');

    if (!tbody || !maxBadge) return;
    if (!rawVal) {
        CURRENT_BREW_FINISHED_ID = 0;
        inp?.classList.remove('is-valid', 'is-invalid');
        if (hint) hint.innerHTML = 'Type or pick a beverage from the datalist.';
        if (addonsChecklist) addonsChecklist.innerHTML = 'Select a finished product above to view its allowed add-ons and ingredient formulas.';
        if (addonsBadge) addonsBadge.textContent = '0 add-ons';
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-white-50">Select a finished product above to check ingredient &amp; add-on availability.</td></tr>`;
        maxBadge.className = 'badge bg-dark text-warning border border-secondary';
        maxBadge.textContent = 'Select a product';
        if (btn) btn.disabled = false;
        return;
    }

    if (!matched) {
        CURRENT_BREW_FINISHED_ID = 0;
        inp?.classList.remove('is-valid');
        inp?.classList.add('is-invalid');
        if (hint) hint.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Not in Master List — please select a valid item from the datalist.</span>`;
        if (addonsChecklist) addonsChecklist.innerHTML = `<span class="text-danger small">Invalid item entered.</span>`;
        if (addonsBadge) addonsBadge.textContent = '0 add-ons';
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger fw-semibold">Invalid item entered. Please select a valid finished product from the datalist.</td></tr>`;
        maxBadge.className = 'badge bg-danger';
        maxBadge.textContent = 'Invalid Selection';
        if (btn) btn.disabled = true;
        return;
    }

    inp?.classList.remove('is-invalid');
    inp?.classList.add('is-valid');
    if (hint) hint.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Made-to-Order Menu Item (${escapeBaristaHtml(matched.name)})</span>`;

    const isSameProduct = (CURRENT_BREW_FINISHED_ID === fid) && preserveAddonChecks;
    const selectedAddons = isSameProduct ? getSelectedBaristaBrewAddons() : [];
    const addonsParam = encodeURIComponent(JSON.stringify(selectedAddons));

    const res = await fetch(`api/inventory.php?action=brew_check&item_id=${fid}&qty=${qty}&addons=${addonsParam}`);
    const data = await res.json();
    const reqs = data.requirements || [];

    if (!isSameProduct) {
        CURRENT_BREW_FINISHED_ID = fid;
        renderBaristaAllowedAddonsChecklist(data.available_addons || [], qty);
    }

    if (!data.has_recipe || reqs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-warning fw-semibold">No brewing recipe configured for this item (stocked in directly by Admin).</td></tr>`;
        maxBadge.className = 'badge bg-secondary';
        maxBadge.textContent = 'Direct Stock Item';
        if (btn) btn.disabled = true;
        return;
    }

    let allSufficient = true;
    tbody.innerHTML = reqs.map(r => {
        if (!r.sufficient) allSufficient = false;
        const srcLabel = r.source_type || 'Base Recipe';
        const isAddonSource = srcLabel.indexOf('Add-on') !== -1;
        const srcBadge = isAddonSource
            ? `<span class="badge bg-info-subtle text-info border border-info-subtle ms-1" style="font-size:0.65rem;">${escapeBaristaHtml(srcLabel)}</span>`
            : `<span class="badge bg-secondary bg-opacity-50 text-white-50 ms-1" style="font-size:0.65rem;">Base Recipe</span>`;
        return `
            <tr>
                <td class="ps-3">
                    <div class="fw-semibold text-white">${escapeBaristaHtml(r.name)}</div>
                    <div>${srcBadge}</div>
                </td>
                <td class="text-end fw-mono text-white-50">${fmtBrewQty(r.qty_per_unit)} ${escapeBaristaHtml(r.unit)}</td>
                <td class="text-end fw-mono fw-bold text-warning">${fmtBrewQty(r.required)} ${escapeBaristaHtml(r.unit)}</td>
                <td class="text-end fw-mono ${r.sufficient ? 'text-success' : 'text-danger fw-bold'}">${fmtBrewQty(r.available)} ${escapeBaristaHtml(r.unit)}</td>
                <td class="text-center pe-3">
                    ${r.sufficient
                        ? '<span class="badge bg-success">OK</span>'
                        : '<span class="badge bg-danger">Shortage</span>'}
                </td>
            </tr>
        `;
    }).join('');

    maxBadge.className = data.max_brewable > 0 ? 'badge bg-success text-white' : 'badge bg-danger text-white';
    maxBadge.textContent = `Max Brewable Now: ${data.max_brewable} unit(s)`;
    if (btn) btn.disabled = !allSufficient;
}

async function loadBaristaBrewsHistory() {
    const tbody = document.getElementById('baristaBrewsTableBody');
    if (!tbody) return;
    const res = await fetch('api/inventory.php?action=brews');
    const data = await res.json();
    const rows = data.brews || [];
    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-white-50">No brewing batches recorded yet.</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.map(b => {
        const badge = b.status === 'Posted'
            ? '<span class="badge bg-success">Posted</span>'
            : (b.status === 'Draft' ? '<span class="badge bg-warning text-dark">Draft</span>' : '<span class="badge bg-secondary">Cancelled</span>');
        const act = b.status === 'Draft'
            ? `<button class="btn btn-warning btn-sm rounded-pill px-2.5 py-0.5 text-dark fw-bold me-1" onclick="postBaristaBrewDoc(${b.id})">Brew &amp; Post</button>
               <button class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0.5" onclick="cancelBaristaBrewDoc(${b.id})">Cancel</button>`
            : `<span class="text-white-50 small"><i class="bi bi-check2-all text-success"></i> Ledger Posted</span>`;
        let addonsBadges = '';
        if (b.addons_json) {
            try {
                const parsed = JSON.parse(b.addons_json);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    addonsBadges = `<div class="mt-0.5 d-flex flex-wrap gap-1">` + parsed.map(a =>
                        `<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50" style="font-size:0.65rem;">+${escapeBaristaHtml(a.addon_name || ('Add-on #' + a.addon_id))} (${fmtBrewQty(a.qty)}x)</span>`
                    ).join('') + `</div>`;
                }
            } catch (e) {}
        }
        return `
            <tr>
                <td class="fw-mono fw-bold text-warning">${escapeBaristaHtml(b.batch_no)}</td>
                <td class="text-white-50">${escapeBaristaHtml(b.batch_date)}</td>
                <td>
                    <div class="fw-bold text-white">${escapeBaristaHtml(b.item_name)}</div>
                    ${addonsBadges}
                </td>
                <td class="text-end fw-mono fw-bold text-success">+${fmtBrewQty(b.quantity)} ${escapeBaristaHtml(b.unit)}</td>
                <td>${badge}</td>
                <td class="text-white-50">${escapeBaristaHtml(b.posted_by || b.created_by || 'Barista')}</td>
                <td class="text-end">${act}</td>
            </tr>
        `;
    }).join('');
}

async function submitBaristaBrewBatch(e) {
    e.preventDefault();
    const inp = document.getElementById('brewFinishedItemInput');
    const matched = resolveBaristaBrewItem(inp?.value || '');
    if (!matched) {
        if (inp) {
            inp.classList.add('is-invalid');
            inp.focus();
        }
        showToast('Invalid beverage selected! Please select a valid finished product from the datalist.', 'danger');
        return;
    }

    const selectedAddons = getSelectedBaristaBrewAddons();
    const payload = {
        action: 'save_brew',
        finished_item_id: parseInt(matched.id),
        quantity: parseFloat(document.getElementById('brewQty').value || 0),
        addons: selectedAddons,
        remarks: document.getElementById('brewRemarks').value.trim() || 'Barista Station Batch Brew',
        post: document.getElementById('brewPostFlag').value === '1'
    };
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        document.getElementById('brewRemarks').value = '';
        await loadBaristaBrewingSuite();
    } else {
        showToast(data.message || 'Brewing failed', 'danger');
    }
}

async function postBaristaBrewDoc(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'post_brew', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        await loadBaristaBrewingSuite();
    } else {
        showToast(data.message || 'Failed to post brew batch', 'danger');
    }
}

async function cancelBaristaBrewDoc(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'cancel_brew', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'info');
        await loadBaristaBrewsHistory();
    } else {
        showToast(data.message || 'Failed to cancel draft', 'danger');
    }
}

async function loadBaristaRecipeDirectory() {
    const container = document.getElementById('liveMasterRecipesContainer');
    if (!container) return;
    try {
        const res = await fetch('api/inventory.php?action=items');
        const data = await res.json();
        if (!data.success) return;
        const finished = (data.items || []).filter(i => i.item_type === 'finished' && parseInt(i.recipe_lines || 0) > 0);
        if (finished.length === 0) {
            container.innerHTML = `<div class="col-12 text-center py-3 text-white-50">No master beverage recipes configured yet.</div>`;
            return;
        }
        const cards = await Promise.all(finished.map(async (f) => {
            const rRes = await fetch(`api/inventory.php?action=recipe&item_id=${f.id}`);
            const rData = await rRes.json();
            const lines = rData.lines || [];
            const addons = rData.addons || [];
            return `
                <div class="col-md-6">
                    <div class="p-3 rounded-4 bg-black border border-secondary h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold text-warning mb-0">${escapeBaristaHtml(f.name)}</h6>
                                    <small class="text-white-50 fw-mono">${escapeBaristaHtml(f.item_code)}</small>
                                </div>
                                <span class="badge ${parseFloat(f.current_stock) > 0 ? 'bg-success' : 'bg-danger'}">Brewed: ${fmtBrewQty(f.current_stock)} ${escapeBaristaHtml(f.unit)}</span>
                            </div>
                            <div class="small fw-bold text-white-50 text-uppercase mb-1" style="font-size:0.7rem;">Base Recipe Ingredients (per 1 ${escapeBaristaHtml(f.unit)}):</div>
                            <ul class="list-unstyled small mb-2 ps-2 border-start border-warning border-2">
                                ${lines.map(l => `<li class="text-light"><strong>${escapeBaristaHtml(l.name)}:</strong> <span class="text-warning fw-mono">${fmtBrewQty(l.qty_per_unit)} ${escapeBaristaHtml(l.unit)}</span></li>`).join('')}
                            </ul>
                            <div class="small fw-bold text-info text-uppercase mb-1" style="font-size:0.7rem;">Allowed Add-ons &amp; Raw Materials:</div>
                            ${addons.length > 0 ? `
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    ${addons.map(a => `<span class="badge bg-dark border border-info border-opacity-50 text-info">+${escapeBaristaHtml(a.addon_name)}: ${escapeBaristaHtml(a.ingredient_name)} (${fmtBrewQty(a.qty_per_unit)} ${escapeBaristaHtml(a.unit)})</span>`).join('')}
                                </div>
                            ` : `<div class="small text-white-50">No add-ons assigned.</div>`}
                        </div>
                        <div class="mt-2 pt-2 border-top border-secondary border-opacity-25 text-end">
                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-bold" data-bs-dismiss="modal" onclick="switchBaristaModule('brewing'); setTimeout(() => selectBaristaDrinkToBrew(${f.id}), 150);">
                                <i class="bi bi-fire me-1"></i>Brew This Product
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }));
        container.innerHTML = cards.join('');
    } catch (e) {
        console.error('Failed to load master recipe directory:', e);
    }
}

document.getElementById('recipeGuideModal')?.addEventListener('show.bs.modal', loadBaristaRecipeDirectory);

function quickBrewOrderedItem(finishedItemId, qty, event) {
    if (event) event.stopPropagation();
    switchBaristaModule('brewing');
    setTimeout(() => {
        const qEl = document.getElementById('brewQty');
        if (qEl && qty > 0) qEl.value = Math.max(1, qty);
        selectBaristaDrinkToBrew(finishedItemId);
    }, 150);
}

let isAudioEnabled = true;
let previousPendingCount = 0;
let kdsTimer = null;
let clockTimer = null;

// Dual-Tone Web Audio Chime (Zero external audio file dependency)
function playBeep() {
    if (!isAudioEnabled) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
        osc.frequency.setValueAtTime(880.00, audioCtx.currentTime + 0.12); // A5

        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);

        osc.start(audioCtx.currentTime);
        osc.stop(audioCtx.currentTime + 0.4);
    } catch (e) {
        console.warn('Web Audio chime not permitted:', e);
    }
}

function toggleAudio() {
    isAudioEnabled = !isAudioEnabled;
    const btn = document.getElementById('btnAudioToggle');
    if (isAudioEnabled) {
        btn.className = 'btn btn-outline-warning btn-sm rounded-pill w-100 fw-bold text-start px-3';
        btn.innerHTML = `<i class="bi bi-volume-up-fill me-1.5" id="audioIcon"></i> Sound Chime ON`;
        playBeep();
    } else {
        btn.className = 'btn btn-outline-secondary btn-sm rounded-pill w-100 fw-medium text-start px-3';
        btn.innerHTML = `<i class="bi bi-volume-mute-fill me-1.5" id="audioIcon"></i> Muted`;
    }
}

// Live Digital Clock
function updateKdsClock() {
    const el = document.getElementById('kdsClock');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', { hour12: true });
}
clockTimer = setInterval(updateKdsClock, 1000);
updateKdsClock();

function getElapsedBadge(dateStr) {
    const created = new Date(dateStr.replace(' ', 'T'));
    const now = new Date();
    const diffSec = Math.floor((now - created) / 1000);
    const min = Math.floor(diffSec / 60);

    let text = diffSec < 60 ? `${diffSec}s ago` : `${min}m ago`;
    let badgeClass = 'bg-success';
    if (min >= 7) {
        badgeClass = 'bg-danger pulse-warning';
    } else if (min >= 3) {
        badgeClass = 'bg-warning text-dark';
    }
    return { text, badgeClass };
}

// Filter Columns from Sidebar or Mobile Tabs
function filterKdsColumn(lane, el) {
    switchBaristaModule('kds', el);
    document.querySelectorAll('.dashboard-sidebar-dark .sidebar-nav-item-dark').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelectorAll('#mobileKdsLaneTabs .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    if (el) el.classList.add('active');

    const colPending = document.getElementById('colWrapperPending');
    const colBrewing = document.getElementById('colWrapperBrewing');
    const colReady = document.getElementById('colWrapperReady');
    const colCompleted = document.getElementById('colWrapperCompleted');

    if (!colPending || !colBrewing || !colReady || !colCompleted) return;

    if (lane === 'all') {
        colPending.style.display = '';
        colBrewing.style.display = '';
        colReady.style.display = '';
        colCompleted.style.display = '';
        [colPending, colBrewing, colReady, colCompleted].forEach(c => {
            c.className = 'col-xl-3 col-md-6';
        });
    } else {
        colPending.style.display = lane === 'pending' ? '' : 'none';
        colBrewing.style.display = lane === 'brewing' ? '' : 'none';
        colReady.style.display = lane === 'ready' ? '' : 'none';
        colCompleted.style.display = lane === 'completed' ? '' : 'none';
        
        const activeCol = lane === 'pending' ? colPending : (lane === 'brewing' ? colBrewing : (lane === 'ready' ? colReady : colCompleted));
        if (activeCol) activeCol.className = 'col-12';
    }
}

// Load KDS Orders from Server
async function loadKdsOrders() {
    const refreshIcon = document.getElementById('refreshIcon');
    if (refreshIcon) refreshIcon.classList.add('spin-animation');

    try {
        const res = await fetch('api/kitchen.php?action=orders');
        const data = await res.json();

        if (data.success) {
            // Update counts in top cards
            document.getElementById('countPending').textContent = data.counts.pending;
            document.getElementById('countBrewing').textContent = data.counts.brewing;
            document.getElementById('countReady').textContent = data.counts.ready;

            // Update column badges
            document.getElementById('badgePendingCol').textContent = data.counts.pending;
            document.getElementById('badgeBrewingCol').textContent = data.counts.brewing;
            document.getElementById('badgeReadyCol').textContent = data.counts.ready;
            document.getElementById('badgeCompletedCol').textContent = data.counts.completed;

            // Update sidebar navigation badges
            const bpNav = document.getElementById('badgePendingNav'); if (bpNav) bpNav.textContent = data.counts.pending;
            const bbNav = document.getElementById('badgeBrewingNav'); if (bbNav) bbNav.textContent = data.counts.brewing;
            const brNav = document.getElementById('badgeReadyNav'); if (brNav) brNav.textContent = data.counts.ready;
            const bcNav = document.getElementById('badgeCompletedNav'); if (bcNav) bcNav.textContent = data.counts.completed;

            // Audio Alert if new pending orders arrived
            if (data.counts.pending > previousPendingCount && previousPendingCount !== 0) {
                playBeep();
                showToast(`🔔 New beverage ticket arrived! ${data.counts.pending} pending.`, 'warning');
            }
            previousPendingCount = data.counts.pending;

            // Render columns
            renderColumn('columnPending', data.orders.filter(o => o.status === 'Pending'), 'Pending');
            renderColumn('columnBrewing', data.orders.filter(o => o.status === 'Brewing'), 'Brewing');
            renderColumn('columnReady', data.orders.filter(o => o.status === 'Ready'), 'Ready');
            renderColumn('columnCompleted', data.orders.filter(o => o.status === 'Completed').slice(0, 10), 'Completed');
        }
    } catch (err) {
        console.error('KDS load error:', err);
    } finally {
        if (refreshIcon) refreshIcon.classList.remove('spin-animation');
    }
}

function renderScheduledQueueBanner(upcomingList) {
    const banner = document.getElementById('kdsScheduledQueueBanner');
    if (banner) {
        banner.style.display = 'none';
        banner.innerHTML = '';
    }
}

async function dispatchScheduledToKdsNow(reservationId) {
    try {
        const res = await fetch('api/scanner.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'dispatch_order', reservation_id: reservationId })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Scheduled pre-order pulled into KDS!', 'success');
            loadKdsOrders();
        } else {
            showToast(data.message || 'Failed to dispatch scheduled order.', 'danger');
        }
    } catch (e) {
        showToast('Network error dispatching scheduled order.', 'danger');
    }
}

function renderColumn(colId, orders, status) {
    const container = document.getElementById(colId);
    if (!orders || orders.length === 0) {
        container.innerHTML = `<div class="text-center py-5 text-white-50 small"><i class="bi bi-inbox fs-3 d-block mb-1 text-opacity-25"></i>No ${status.toLowerCase()} orders</div>`;
        return;
    }

    let html = '';
    orders.forEach(ord => {
        const elapsed = getElapsedBadge(ord.created_at);
        
        let headerColor = 'bg-secondary';
        let actionBtn = '';

        if (status === 'Pending') {
            headerColor = 'bg-warning text-dark';
            actionBtn = `
                <button type="button" class="btn btn-warning w-100 py-2.5 fw-black text-dark rounded-pill shadow-xs" onclick="updateOrderStatus(${ord.id}, 'Brewing')">
                    <i class="bi bi-play-circle-fill me-1"></i> Start Brewing
                </button>
            `;
        } else if (status === 'Brewing') {
            headerColor = 'bg-primary text-white';
            actionBtn = `
                <div class="d-grid gap-1.5">
                    <button type="button" class="btn btn-info w-100 py-2 fw-bold text-dark rounded-pill shadow-xs" onclick="updateOrderStatus(${ord.id}, 'Ready')">
                        <i class="bi bi-bell-fill me-1"></i> Mark Ready for Pickup
                    </button>
                    <button type="button" class="btn btn-success w-100 py-2 fw-black text-white rounded-pill shadow-xs" onclick="updateOrderStatus(${ord.id}, 'Completed')">
                        <i class="bi bi-check2-circle me-1"></i> Brewed &amp; Serve (Deduct Ingredients)
                    </button>
                </div>
            `;
        } else if (status === 'Ready') {
            headerColor = 'bg-success text-white';
            actionBtn = `
                <button type="button" class="btn btn-warning w-100 py-2.5 fw-black text-dark rounded-pill shadow-xs" onclick="updateOrderStatus(${ord.id}, 'Completed')">
                    <i class="bi bi-box-arrow-right me-1"></i> Brewed &amp; Serve (Deduct Ingredients)
                </button>
            `;
        } else {
            actionBtn = `<span class="badge bg-success-subtle text-success border border-success-subtle w-100 py-2"><i class="bi bi-check2-all me-1"></i>Brewed &amp; Served — Ingredients Deducted (BREW_OUT -)</span>`;
        }

        html += `
            <div class="card kds-card mb-2 shadow-sm">
                <!-- Ticket Header -->
                <div class="p-2.5 px-3 ${headerColor} rounded-top-3 d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="fs-6 fw-mono">${ord.order_number}</strong>
                        <span class="badge bg-black bg-opacity-25 ms-1">${ord.table_number || ord.order_type}</span>
                    </div>
                    <span class="badge ${elapsed.badgeClass} text-white fw-mono small">${elapsed.text}</span>
                </div>

                <!-- Ticket Body -->
                <div class="card-body p-3">
                    <div class="text-white-50 small mb-2 d-flex justify-content-between align-items-center">
                        <span>Customer: <strong class="text-light">${ord.customer_name}</strong></span>
                        ${ord.order_type === 'Scheduled Pre-order'
                            ? `<span class="badge bg-warning text-dark fw-bold"><i class="bi bi-alarm-fill me-1"></i>SCHEDULED (T-15m)</span>`
                            : `<span class="badge bg-secondary-subtle text-light border border-secondary">${ord.order_type}</span>`
                        }
                    </div>
                    ${(ord.order_type === 'Scheduled Pre-order' && ord.payment_reference) ? `
                        <div class="mb-2 px-2 py-1 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25 text-warning" style="font-size: 0.7rem;">
                            <i class="bi bi-calendar-check me-1"></i>${escapeBaristaHtml(ord.payment_reference)}
                        </div>
                    ` : ''}

                    <!-- Items List with Touch Strike-Through Check-off & Ordered Product Recipe Breakdown -->
                    <div class="border-top border-secondary border-opacity-50 pt-2">
                        ${ord.items.map(it => {
                            const recLines = it.recipe_lines || [];
                            const addonRecLines = it.addon_recipe_lines || [];
                            const hasFormula = recLines.length > 0 || addonRecLines.length > 0;
                            const showExpandedByDefault = (status === 'Pending' || status === 'Brewing');
                            return `
                            <div class="mb-2 pb-2 border-bottom border-secondary border-opacity-25 kds-item-row">
                                <div class="d-flex justify-content-between align-items-start cursor-pointer" onclick="this.parentElement.classList.toggle('opacity-40'); this.querySelector('.kds-check')?.classList.toggle('bi-check-circle-fill'); this.querySelector('.kds-check')?.classList.toggle('text-success');" title="Tap to check off as brewed">
                                    <span class="fw-bold text-white fs-6">
                                        <i class="bi bi-circle me-1.5 kds-check text-secondary" style="font-size: 0.8rem;"></i>
                                        <span class="text-warning">${it.quantity}x</span> ${escapeBaristaHtml(it.product_name)}
                                    </span>
                                    <span class="badge bg-light text-dark fw-bold">${escapeBaristaHtml(it.size_name)}</span>
                                </div>
                                <div class="small mt-1 d-flex flex-wrap align-items-center gap-1 ps-3">
                                    ${(it.sugar_level === 'Pastry' || it.size_name === 'Standard Serving') ? `
                                        <span class="badge bg-warning-subtle text-dark border border-warning"><i class="bi bi-cake2 me-1"></i>${escapeBaristaHtml(it.ice_level)}</span>
                                    ` : `
                                        <span class="badge bg-dark border border-secondary text-warning">${escapeBaristaHtml(it.sugar_level)}</span>
                                        <span class="badge bg-dark border border-secondary text-info">${escapeBaristaHtml(it.ice_level)}</span>
                                        ${it.addons.length ? it.addons.map(a => `<span class="badge bg-primary bg-opacity-25 border border-primary text-info">+${escapeBaristaHtml(a)}</span>`).join(' ') : ''}
                                    `}
                                    ${hasFormula ? `
                                        <button type="button" class="btn btn-outline-warning btn-sm py-0 px-1.5 rounded-pill ms-auto" style="font-size: 0.68rem;" onclick="event.stopPropagation(); this.closest('.kds-item-row').querySelector('.kds-recipe-panel')?.classList.toggle('d-none');">
                                            <i class="bi bi-journal-text me-1"></i>Recipe
                                        </button>
                                    ` : ''}
                                </div>

                                ${hasFormula ? `
                                    <div class="kds-recipe-panel ${showExpandedByDefault ? '' : 'd-none'} mt-2 ms-3 p-2 rounded-3 bg-black border border-secondary border-opacity-75 small">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-warning" style="font-size: 0.7rem;">
                                                <i class="bi bi-cup-hot-fill me-1"></i>ORDERED PRODUCT RECIPE (${it.quantity}x)
                                            </span>
                                            ${it.finished_item_id ? `
                                                <button type="button" class="btn btn-link btn-sm p-0 text-info text-decoration-none" style="font-size: 0.68rem;" onclick="quickBrewOrderedItem(${it.finished_item_id}, ${it.quantity}, event)">
                                                    <i class="bi bi-fire"></i> Brew Batch
                                                </button>
                                            ` : ''}
                                        </div>
                                        ${recLines.length > 0 ? `
                                            <div class="d-flex flex-column gap-0.5">
                                                ${recLines.map(r => `
                                                    <div class="d-flex justify-content-between align-items-center text-white-50" style="font-size: 0.73rem;">
                                                        <span>• ${escapeBaristaHtml(r.ingredient_name)}</span>
                                                        <span class="fw-mono ${r.sufficient !== false ? 'text-light' : 'text-danger fw-bold'}">
                                                            ${fmtBrewQty(r.total_qty)} ${escapeBaristaHtml(r.unit)}
                                                            ${!ord.ingredients_deducted ? (r.sufficient !== false
                                                                ? `<span class="text-success ms-1" title="Available: ${fmtBrewQty(r.available_stock)} ${escapeBaristaHtml(r.unit)}">✓</span>`
                                                                : `<span class="badge bg-danger ms-1" style="font-size:0.6rem;">Avail: ${fmtBrewQty(r.available_stock)}</span>`) : ''}
                                                        </span>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                        ${addonRecLines.length > 0 ? `
                                            <div class="mt-1 pt-1 border-top border-secondary border-opacity-50">
                                                <div class="fw-bold text-info" style="font-size: 0.68rem;">ORDERED ADD-ONS INGREDIENTS:</div>
                                                ${addonRecLines.map(ar => `
                                                    <div class="d-flex justify-content-between align-items-center text-info" style="font-size: 0.72rem;">
                                                        <span>+ ${escapeBaristaHtml(ar.addon_name)} (${escapeBaristaHtml(ar.ingredient_name)})</span>
                                                        <span class="fw-mono fw-bold ${ar.sufficient !== false ? 'text-info' : 'text-danger'}">
                                                            ${fmtBrewQty(ar.total_qty)} ${escapeBaristaHtml(ar.unit)}
                                                            ${!ord.ingredients_deducted ? (ar.sufficient !== false
                                                                ? `<span class="text-success ms-1" title="Available: ${fmtBrewQty(ar.available_stock)} ${escapeBaristaHtml(ar.unit)}">✓</span>`
                                                                : `<span class="badge bg-danger ms-1" style="font-size:0.6rem;">Avail: ${fmtBrewQty(ar.available_stock)}</span>`) : ''}
                                                        </span>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                ` : ''}

                                ${it.special_notes ? `<div class="alert alert-warning py-1 px-2 small mt-1 mb-0 border-0 fw-bold"><i class="bi bi-chat-left-dots-fill me-1"></i>Note: ${escapeBaristaHtml(it.special_notes)}</div>` : ''}
                            </div>
                        `}).join('')}
                    </div>

                    <!-- Cups, Straws & Takeout Supplies Breakdown (Deducted upon Brew & Serve) -->
                    ${(ord.packaging_lines && ord.packaging_lines.length > 0) ? `
                        <div class="mt-2 p-2 rounded-3 bg-black border border-secondary border-opacity-75 small">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-info" style="font-size: 0.7rem;">
                                    <i class="bi bi-box-seam me-1"></i>CUPS &amp; ${String(ord.order_type || '').toLowerCase() === 'takeout' ? 'TAKEOUT PACKAGING' : 'SERVING SUPPLIES'}
                                </span>
                                <span class="badge ${ord.ingredients_deducted ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-dark text-white-50 border border-secondary'}" style="font-size: 0.62rem;">
                                    ${ord.ingredients_deducted ? 'Deducted' : 'Deducts on Serve'}
                                </span>
                            </div>
                            <div class="d-flex flex-column gap-0.5">
                                ${ord.packaging_lines.map(pk => `
                                    <div class="d-flex justify-content-between align-items-center text-white-50" style="font-size: 0.73rem;">
                                        <span>• ${escapeBaristaHtml(pk.name)} <small class="text-secondary">[${escapeBaristaHtml(pk.item_code)}]</small></span>
                                        <span class="fw-mono ${pk.sufficient !== false ? 'text-warning fw-bold' : 'text-danger fw-bold'}">
                                            ${fmtBrewQty(pk.required)} ${escapeBaristaHtml(pk.unit)}
                                            ${!ord.ingredients_deducted ? (pk.sufficient !== false
                                                ? `<span class="text-success ms-1" title="Available: ${fmtBrewQty(pk.available_stock)} ${escapeBaristaHtml(pk.unit)}">✓</span>`
                                                : `<span class="badge bg-danger ms-1" style="font-size:0.6rem;">Avail: ${fmtBrewQty(pk.available_stock)}</span>`) : ''}
                                        </span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}

                    <!-- Action Button -->
                    <div class="mt-3">
                        ${actionBtn}
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Update Order Status
async function updateOrderStatus(orderId, nextStatus) {
    try {
        const res = await fetch('api/kitchen.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: nextStatus })
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            loadKdsOrders();
            loadBaristaBrewingSuite();
        } else {
            showToast(data.message || 'Failed to update order', 'danger');
        }
    } catch (err) {
        showToast('Network error updating order', 'danger');
    }
}

// Initial Load & 4-second Polling Loop
loadKdsOrders();
loadBaristaBrewingSuite();
kdsTimer = setInterval(loadKdsOrders, 4000);

// Cleanup on Dynamic View Unload
window.addEventListener('pageUnload', () => {
    if (kdsTimer) clearInterval(kdsTimer);
    if (clockTimer) clearInterval(clockTimer);
}, { once: true });
</script>
