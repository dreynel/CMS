<?php
/**
 * Raeden’s Bakery and Café — Perpetual Inventory Management & Accounting Ledger Suite
 *
 * Modules included:
 *   1. Master List & Recipes (Raw Materials & Finished Products — new items start at 0 stock)
 *   2. Stock In & Stock Out Module (Draft -> Posted workflow, stock validation on Stock Out)
 *   3. Item Conversion Module (Packs -> Pieces, KG -> G, L -> mL, etc. with stock validation)
 *   4. Brewing & Batch Production Module (-Ingredients, +Finished Product with BOM stock check)
 *   5. Accounting Inventory Ledger (Separate perpetual journal: IN / OUT / Running Balance)
 *   6. Finished Product Yield Calculator (Calculates max product output from ingredient inventory)
 */

if (!defined('ISATU_APP')) {
    exit('Direct access not allowed');
}
?>

<!-- Inventory KPI Summary Banner -->
<div class="row g-3 mb-4" id="invKpiRow">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-primary">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Master List Items</span>
                    <h4 class="fw-black text-navy mb-0 mt-1" id="invKpiTotalItems">0 Items</h4>
                    <span class="badge bg-primary-subtle text-primary border small mt-1" id="invKpiBreakdown">0 Raw • 0 Finished</span>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-success">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Inventory Asset Value</span>
                    <h4 class="fw-black text-navy mb-0 mt-1" id="invKpiValuation">₱0.00</h4>
                    <span class="badge bg-success-subtle text-success border small mt-1">Perpetual Cost Basis</span>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-journal-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-warning">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Low Stock Watch</span>
                    <h4 class="fw-black text-warning mb-0 mt-1" id="invKpiLowStock">0 Items</h4>
                    <span class="badge bg-warning-subtle text-dark border small mt-1">&le; Reorder Threshold</span>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-danger">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Zero Stock Items</span>
                    <h4 class="fw-black text-danger mb-0 mt-1" id="invKpiZeroStock">0 Items</h4>
                    <span class="badge bg-danger-subtle text-danger border small mt-1">Needs Stock-In / Brew</span>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-x-octagon-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module Sub-Navigation Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-2.5 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <ul class="nav nav-pills gap-1 flex-wrap" id="invModuleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold small px-3 py-2" id="inv-subtab-master" data-bs-toggle="pill" data-bs-target="#invPaneMaster" type="button" role="tab">
                    <i class="bi bi-box-seam me-1"></i> 1. Master List & Recipes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold small px-3 py-2" id="inv-subtab-stockio" data-bs-toggle="pill" data-bs-target="#invPaneStockIO" type="button" role="tab" onclick="loadStockTransactions()">
                    <i class="bi bi-arrow-down-up me-1"></i> 2. Stock In & Stock Out
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold small px-3 py-2" id="inv-subtab-conv" data-bs-toggle="pill" data-bs-target="#invPaneConversion" type="button" role="tab" onclick="loadConversions()">
                    <i class="bi bi-arrow-left-right me-1"></i> 3. Item Conversion
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold small px-3 py-2" id="inv-subtab-ledger" data-bs-toggle="pill" data-bs-target="#invPaneLedger" type="button" role="tab" onclick="loadInventoryLedger()">
                    <i class="bi bi-journal-text me-1"></i> 4. Accounting Inventory Ledger
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold small px-3 py-2" id="inv-subtab-yield" data-bs-toggle="pill" data-bs-target="#invPaneYieldCalculator" type="button" role="tab" onclick="loadYieldCalculatorDashboard()">
                    <i class="bi bi-calculator-fill me-1"></i> 5. Product Yield Calculator
                </button>
            </li>
        </ul>

        <div class="d-flex align-items-center gap-1.5">
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="openStockTxnModal('IN')">
                <i class="bi bi-plus-circle me-1"></i> Stock In
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" onclick="openStockTxnModal('OUT')">
                <i class="bi bi-dash-circle me-1"></i> Stock Out
            </button>
        </div>
    </div>
</div>

<div class="tab-content">

    <!-- ================================================================= -->
    <!-- SUB-TAB 1: MASTER LIST & RECIPES (Raw Materials & Finished Goods) -->
    <!-- ================================================================= -->
    <div class="tab-pane fade show active" id="invPaneMaster" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-1">
                        <i class="bi bi-box-seam text-warning me-2"></i>Item Master List (Ingredients & Finished Products)
                    </h5>
                    <p class="text-muted small mb-0">
                        All newly added raw materials and finished products start at <strong>0 stock</strong>. Stock balances only change via posted Ledger transactions.
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-navy active" onclick="filterMasterItems('all', this)">All</button>
                        <button type="button" class="btn btn-outline-navy" onclick="filterMasterItems('raw', this)">Raw Materials (Ingredients)</button>
                        <button type="button" class="btn btn-outline-navy" onclick="filterMasterItems('finished', this)">Finished Products (POS)</button>
                    </div>
                    <input type="search" id="invMasterSearch" class="form-control form-control-sm rounded-pill" style="width: 200px;" placeholder="Search code or name..." oninput="renderMasterItemsTable()">
                    <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="openMenuQrCatalogModal()">
                        <i class="bi bi-qr-code me-1"></i> Generate Menu QR Codes
                    </button>
                    <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold" onclick="openMasterItemModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Master Item (0 Stock)
                    </button>
                </div>
            </div>
            <div class="card-body p-4 overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th>Item Code</th>
                                <th>Item / Product Name</th>
                                <th>Classification</th>
                                <th>Unit</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">POS Price</th>
                                <th class="text-end">Reorder Alert</th>
                                <th class="text-end">Ledger Balance</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invMasterTableBody">
                            <tr><td colspan="10" class="text-center py-4 text-muted small">Loading master items...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- SUB-TAB 2: STOCK IN & STOCK OUT MODULE                            -->
    <!-- ================================================================= -->
    <div class="tab-pane fade" id="invPaneStockIO" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-1">
                        <i class="bi bi-arrow-down-up text-primary me-2"></i>Stock In & Stock Out Vouchers
                    </h5>
                    <p class="text-muted small mb-0">
                        <strong>Stock In (+):</strong> Direct stock replenishment into inventory. <strong>Stock Out (-):</strong> Spoilage, expired batches, or internal withdrawal (requires available stock).
                    </p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-navy active" id="btnFilterStockIn" onclick="switchStockTxnList('IN')">
                            <i class="bi bi-box-arrow-in-down me-1 text-success"></i> Stock In Vouchers (SI)
                        </button>
                        <button type="button" class="btn btn-outline-navy" id="btnFilterStockOut" onclick="switchStockTxnList('OUT')">
                            <i class="bi bi-box-arrow-up me-1 text-danger"></i> Stock Out Vouchers (SO)
                        </button>
                    </div>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="openStockTxnModal('IN')">
                        <i class="bi bi-plus-circle me-1"></i> New Stock In
                    </button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" onclick="openStockTxnModal('OUT')">
                        <i class="bi bi-dash-circle me-1"></i> New Stock Out
                    </button>
                </div>
            </div>
            <div class="card-body p-4 overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th>Voucher No.</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Reason / Remarks</th>
                                <th>Ref No.</th>
                                <th class="text-center">Lines</th>
                                <th class="text-end">Total Cost</th>
                                <th>Status</th>
                                <th>Posted By</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invStockTxnTableBody">
                            <tr><td colspan="10" class="text-center py-4 text-muted small">Loading stock vouchers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- SUB-TAB 3: ITEM CONVERSION MODULE (Packs->Pcs, KG->G, L->mL)      -->
    <!-- ================================================================= -->
    <div class="tab-pane fade" id="invPaneConversion" role="tabpanel">
        <div class="row g-4">
            <!-- Left: Conversion Workstation Form -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-navy text-white px-4 py-3 rounded-top-4" style="background: var(--isatu-navy-gradient);">
                        <h6 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-warning me-2"></i>Convert Bulk Stock to Usable Units</h6>
                        <small class="text-white-50">Deducts (-1) from Source Item and adds (+Factor) to Destination Item</small>
                    </div>
                    <div class="card-body p-4">
                        <form id="invConversionForm" onsubmit="submitConversion(event)" novalidate>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">1. Source Item (Bulk Stocked Item) <span class="text-danger">*</span></label>
                                <input type="text" list="invMasterItemsDatalist" class="form-control rounded-3" id="convSourceItemInput" placeholder="Type code or item name (select from datalist)..." autocomplete="off" required oninput="updateConversionPreview()" onchange="updateConversionPreview()">
                                <input type="hidden" id="convSourceItem" value="">
                                <div class="form-text small" id="convSourceStockHint">Type or pick a stocked item from the datalist.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">2. Destination Item (Target Smaller Unit) <span class="text-danger">*</span></label>
                                <input type="text" list="invMasterItemsDatalist" class="form-control rounded-3" id="convDestItemInput" placeholder="Type code or target item name (select from datalist)..." autocomplete="off" required oninput="updateConversionPreview()" onchange="updateConversionPreview()">
                                <input type="hidden" id="convDestItem" value="">
                                <div class="form-text small" id="convDestStockHint">Type or pick the target item from the datalist.</div>
                            </div>

                            <!-- Quick Conversion Presets -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary d-block">Quick Conversion Presets</label>
                                <div class="d-flex flex-wrap gap-1.5">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-BEAN-KG','RM-BEAN-G',1,1000)">Beans: 1 kg &rarr; 1000 g</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-MILK-PK','RM-MILK-ML',1,1000)">Milk: 1 Pack &rarr; 1000 mL</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-OAT-PK','RM-OAT-ML',1,1000)">Oat Milk: 1 Pack &rarr; 1000 mL</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-CMILK-CN','RM-CMILK-ML',1,300)">Cond. Milk: 1 Can &rarr; 300 mL</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-SUGAR-KG','RM-SUGAR-G',1,1000)">Sugar: 1 kg &rarr; 1000 g</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-CUP12-PK','RM-CUP12-PC',1,50)">12oz Cups: 1 Pack &rarr; 50 pcs</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-CUP16-PK','RM-CUP16-PC',1,50)">16oz Cups: 1 Pack &rarr; 50 pcs</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-STRAW-PK','RM-STRAW-PC',1,100)">Straws: 1 Pack &rarr; 100 pcs</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0.5 px-2.5" style="font-size: 0.72rem;" onclick="applyConvPreset('RM-TOBOX-PK','RM-TOBOX-PC',1,25)">Takeout Box: 1 Pack &rarr; 25 pcs</button>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Qty to Convert <span class="text-danger">*</span></label>
                                    <input type="number" step="0.0001" min="0.0001" class="form-control rounded-3 fw-bold text-navy" id="convSourceQty" value="1" required oninput="updateConversionPreview()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Factor (Dest per 1 Src) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.0001" min="0.0001" class="form-control rounded-3 fw-bold text-navy" id="convFactor" value="1000" required oninput="updateConversionPreview()">
                                </div>
                            </div>

                            <div class="p-3 rounded-3 bg-light border mb-3" id="convPreviewBox">
                                <div class="small fw-bold text-uppercase text-muted mb-1" style="font-size: 0.68rem;">Accounting Ledger Preview</div>
                                <div class="small text-secondary" id="convPreviewText">Select source and destination items above.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Remarks / Batch Note</label>
                                <input type="text" class="form-control rounded-3" id="convRemarks" placeholder="e.g. Opened 1kg espresso bag for bar hopper">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 fw-bold" onclick="document.getElementById('convPostFlag').value='0'">
                                    Save Draft
                                </button>
                                <button type="submit" class="btn btn-navy rounded-pill px-4 fw-bold flex-grow-1" id="btnPostConversion" onclick="document.getElementById('convPostFlag').value='1'">
                                    <i class="bi bi-check2-circle me-1"></i> Convert & Post to Ledger
                                </button>
                            </div>
                            <input type="hidden" id="convPostFlag" value="1">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Conversion History -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-navy mb-1">Item Conversion History</h5>
                            <p class="text-muted small mb-0">Every posted conversion writes a paired <code>CONV_OUT</code> (-) and <code>CONV_IN</code> (+) entry.</p>
                        </div>
                    </div>
                    <div class="card-body p-4 overflow-auto">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted text-uppercase">
                                    <tr>
                                        <th>Conv No.</th>
                                        <th>Date</th>
                                        <th>Source (-Out)</th>
                                        <th>Destination (+In)</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="invConversionsTableBody">
                                    <tr><td colspan="6" class="text-center py-4 text-muted small">No item conversions recorded yet.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- SUB-TAB 4: ACCOUNTING INVENTORY LEDGER (Separate Perpetual Table) -->
    <!-- ================================================================= -->
    <div class="tab-pane fade" id="invPaneLedger" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold text-navy mb-1">
                            <i class="bi bi-journal-text text-info me-2"></i>Perpetual Inventory Ledger (Accounting Style)
                        </h5>
                        <p class="text-muted small mb-0">
                            Immutable accounting journal (<code>inventory_ledger</code>) separate from transaction tables. Records every posted Stock In (+), Stock Out (-), Conversion (-/+), Brew (-/+), and POS Sale (-).
                        </p>
                    </div>
                </div>

                <!-- Ledger Filter Bar -->
                <div class="row g-2 align-items-end bg-light p-3 rounded-3 border">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Item / Stock Card Filter</label>
                        <select class="form-select form-select-sm rounded-3" id="ledgerItemFilter" onchange="loadInventoryLedger()">
                            <option value="0">All Inventory Items (General Journal)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Movement Type</label>
                        <select class="form-select form-select-sm rounded-3" id="ledgerTypeFilter" onchange="loadInventoryLedger()">
                            <option value="">All Posted Movements</option>
                            <option value="STOCK_IN">STOCK_IN (+ Stock In)</option>
                            <option value="STOCK_OUT">STOCK_OUT (- Write-Off / Usage)</option>
                            <option value="CONV_OUT">CONV_OUT (- Conversion Source)</option>
                            <option value="CONV_IN">CONV_IN (+ Conversion Target)</option>
                            <option value="BREW_OUT">BREW_OUT (- Recipe Ingredient)</option>
                            <option value="BREW_IN">BREW_IN (+ Brewed Product)</option>
                            <option value="POS_SALE">POS_SALE (- Cashier Sale)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary mb-1">From Date</label>
                        <input type="date" class="form-control form-control-sm rounded-3" id="ledgerFromDate" value="<?= date('Y-m-01') ?>" onchange="loadInventoryLedger()">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-secondary mb-1">To Date</label>
                        <input type="date" class="form-control form-control-sm rounded-3" id="ledgerToDate" value="<?= date('Y-m-d') ?>" onchange="loadInventoryLedger()">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="button" class="btn btn-navy btn-sm rounded-3 fw-bold" onclick="loadInventoryLedger()">
                            <i class="bi bi-funnel-fill"></i>
                        </button>
                    </div>
                </div>

                <!-- Accounting Summary Strip -->
                <div class="row g-2 mt-2" id="ledgerSummaryBar">
                    <div class="col-sm-3">
                        <div class="p-2 px-3 rounded-3 border bg-white d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Opening Balance:</span>
                            <strong class="fw-mono text-secondary" id="ledgerOpeningVal">—</strong>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="p-2 px-3 rounded-3 border bg-white d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Total Debit / IN (+):</span>
                            <strong class="fw-mono text-success" id="ledgerTotalInVal">+0</strong>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="p-2 px-3 rounded-3 border bg-white d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Total Credit / OUT (-):</span>
                            <strong class="fw-mono text-danger" id="ledgerTotalOutVal">-0</strong>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="p-2 px-3 rounded-3 border bg-white d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Closing Balance:</span>
                            <strong class="fw-mono text-navy" id="ledgerClosingVal">—</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 pt-2 overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 small">
                        <thead class="table-light text-muted text-uppercase">
                            <tr>
                                <th>Entry #</th>
                                <th>Date & Time</th>
                                <th>Ref. Doc No.</th>
                                <th>Movement</th>
                                <th>Item Code & Name</th>
                                <th>Particulars / Memo</th>
                                <th class="text-end text-success">IN (+ Debit)</th>
                                <th class="text-end text-danger">OUT (- Credit)</th>
                                <th class="text-end text-navy">Running Balance</th>
                                <th class="text-end">Unit Cost</th>
                                <th>Posted By</th>
                            </tr>
                        </thead>
                        <tbody id="invLedgerTableBody">
                            <tr><td colspan="11" class="text-center py-4 text-muted">Loading accounting inventory ledger...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- SUB-TAB 5: FINISHED PRODUCT YIELD CALCULATOR                          -->
    <!-- Calculate amount of finished product based on the amount of ingredients-->
    <!-- ===================================================================== -->
    <div class="tab-pane fade" id="invPaneYieldCalculator" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill px-2.5 py-1 text-white" style="background-color: var(--brand-secondary); font-size: 0.72rem;">PRODUCTION CAPACITY ENGINE</span>
                        </div>
                        <h5 class="fw-bold text-navy mb-1 mt-1">
                            <i class="bi bi-calculator-fill text-primary me-2"></i>Finished Product Yield &amp; Recipe Calculator
                        </h5>
                        <p class="text-muted small mb-0">
                            Calculates the maximum quantity of finished bakery &amp; beverage items that can be produced based on available raw ingredient stocks, BOM recipes, and bottleneck analysis.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3.5 py-2 fw-bold" onclick="openInteractiveYieldSimulator()">
                            <i class="bi bi-sliders me-1.5"></i> Batch Simulator
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-bold" onclick="loadYieldCalculatorDashboard()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- KPI Overview Bar -->
                <div class="row g-2.5 mb-3">
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem;">Catalog Recipes</div>
                                <h5 class="fw-black text-navy mb-0" id="yieldKpiTotal">0</h5>
                            </div>
                            <i class="bi bi-journal-bookmark text-primary fs-4 opacity-75"></i>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem;">Ready to Produce</div>
                                <h5 class="fw-black text-success mb-0" id="yieldKpiReady">0</h5>
                            </div>
                            <i class="bi bi-check-circle-fill text-success fs-4 opacity-75"></i>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem;">Zero Stock / Blocked</div>
                                <h5 class="fw-black text-danger mb-0" id="yieldKpiBlocked">0</h5>
                            </div>
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-4 opacity-75"></i>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem;">Max Batch Potential</div>
                                <h5 class="fw-black text-warning mb-0" id="yieldKpiMaxUnits">0 units</h5>
                            </div>
                            <i class="bi bi-boxes text-warning fs-4 opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter & Search Toolbar -->
                <div class="row g-2 align-items-center bg-light p-3 rounded-3 border">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="yieldSearchInput" placeholder="Filter by product name or code..." oninput="filterYieldTable()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="yieldCategoryFilter" onchange="filterYieldTable()">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="yieldStatusFilter" onchange="filterYieldTable()">
                            <option value="all">All Production Statuses</option>
                            <option value="ready">Produceable Now (Yield &gt; 0)</option>
                            <option value="high">High Capacity (&ge; 10 units)</option>
                            <option value="low">Low Capacity (1-9 units)</option>
                            <option value="depleted">Depleted / Zero Yield (0 units)</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-md-end">
                        <span class="small text-muted" id="yieldRecordCount">0 products</span>
                    </div>
                </div>
            </div>

            <!-- Yield Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                            <tr>
                                <th class="ps-4">Finished Product</th>
                                <th>Category</th>
                                <th class="text-center">Recipe (BOM)</th>
                                <th class="text-center">Finished Stock</th>
                                <th class="text-center">Max Yield (From Ingredients)</th>
                                <th>Bottleneck / Limiting Ingredient</th>
                                <th class="text-end">Cost &amp; Price</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="yieldTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    Calculating finished product yields from ingredient inventory...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===================================================================== -->
<!-- MODAL: DETAILED FINISHED PRODUCT YIELD & RECIPE SIMULATOR              -->
<!-- ===================================================================== -->
<div class="modal fade" id="yieldDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1080px, 96vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--brand-primary-gradient);">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge text-white px-2 py-0.5 rounded-pill" style="background-color: var(--brand-secondary); font-size: 0.68rem;">PRODUCTION CAPACITY ANALYSIS</span>
                    </div>
                    <h5 class="modal-title fw-bold mt-1 mb-0" id="yieldModalTitle">Finished Product Yield Calculator</h5>
                    <small class="text-white-50" id="yieldModalSubtitle">Bill of Materials Ingredient Sufficiency &amp; Batch Simulator</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Product Metric Summary Card -->
                <div class="card border-0 shadow-xs rounded-4 bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5 border-end-md">
                                <span class="badge px-2.5 py-1 rounded-pill small mb-2" id="simProductCatBadge" style="background-color: var(--brand-surface); color: var(--brand-text-primary);">Bakery Item</span>
                                <h4 class="fw-black text-navy mb-1" id="simProductName">Product Name</h4>
                                <div class="text-muted small fw-mono" id="simProductCode">SKU-000</div>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <span class="small text-muted">Finished Stock:</span>
                                    <span class="badge bg-secondary-subtle text-secondary fw-bold" id="simFinishedStock">0 units</span>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="p-2.5 rounded-3 border" style="background-color: #FFF6F6;">
                                            <span class="text-muted small d-block" style="font-size: 0.7rem;">MAX YIELD NOW</span>
                                            <h3 class="fw-black mb-0" style="color: #894E3F;" id="simMaxYieldDisplay">0</h3>
                                            <span class="small text-muted" id="simYieldUnitLabel">units</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2.5 rounded-3 border bg-light">
                                            <span class="text-muted small d-block" style="font-size: 0.7rem;">INGREDIENT COST</span>
                                            <h4 class="fw-bold text-navy mb-0 mt-1" id="simCostPerUnit">₱0.00</h4>
                                            <span class="small text-muted">per 1 unit</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2.5 rounded-3 border bg-light">
                                            <span class="text-muted small d-block" style="font-size: 0.7rem;">PROFIT MARGIN</span>
                                            <h4 class="fw-bold text-success mb-0 mt-1" id="simProfitMargin">0%</h4>
                                            <span class="small text-muted" id="simSellingPriceLabel">₱0.00 POS</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2.5 p-2 rounded-3 border small" id="simBottleneckBanner" style="background-color: #FFF6F6;">
                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                    <strong>Bottleneck Ingredient:</strong> <span id="simBottleneckText">Calculating...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Batch Simulation Controller -->
                <div class="card border-0 shadow-xs rounded-4 bg-white mb-4">
                    <div class="card-header bg-white border-0 pt-3 px-4 pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-navy mb-0">
                                <i class="bi bi-sliders text-warning me-1.5"></i>Batch Production Simulator
                            </h6>
                            <span class="badge bg-light text-muted border small">Real-Time BOM Calculation</span>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4 pt-2">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-secondary mb-1">Target Production Batch Size</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustSimBatch(-5)">-5</button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustSimBatch(-1)">-1</button>
                                    <input type="number" min="1" step="1" id="simBatchTargetQty" value="10" class="form-control text-center fw-bold fs-5 text-navy" oninput="onSimTargetQtyChanged()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustSimBatch(1)">+1</button>
                                    <button class="btn btn-outline-secondary" type="button" onclick="adjustSimBatch(5)">+5</button>
                                    <button class="btn btn-outline-primary fw-bold" type="button" onclick="setSimToMaxYield()">Max (<span id="simBtnMaxVal">0</span>)</button>
                                </div>
                                <div class="form-text small text-muted">Enter how many units you wish to bake or prepare.</div>
                            </div>
                            <div class="col-md-7">
                                <div id="simFeasibilityAlert" class="alert mb-0 p-3 rounded-3 d-flex align-items-center gap-2.5">
                                    <i class="bi bi-check-circle-fill fs-4 flex-shrink-0"></i>
                                    <div>
                                        <div class="fw-bold" id="simAlertTitle">Production Feasible</div>
                                        <div class="small" id="simAlertDesc">Sufficient raw ingredients in stock for this batch.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ingredient Requirement Table for Target Batch -->
                        <div class="table-responsive border rounded-3 overflow-hidden">
                            <table class="table table-sm table-hover align-middle mb-0 small">
                                <thead class="table-light fw-bold text-uppercase" style="font-size: 0.7rem;">
                                    <tr>
                                        <th class="ps-3">Recipe Ingredient</th>
                                        <th class="text-end">Qty Per Unit</th>
                                        <th class="text-end">Batch Needed</th>
                                        <th class="text-end">Available Stock</th>
                                        <th class="text-center">Max From Ingredient</th>
                                        <th class="text-center">Status / Shortage</th>
                                        <th class="text-end pe-3">Line Cost</th>
                                    </tr>
                                </thead>
                                <tbody id="simRecipeTableBody">
                                    <tr><td colspan="7" class="text-center py-3 text-muted">Loading recipe ingredients...</td></tr>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="6" class="text-end ps-3">Total Estimated Ingredient Cost for Batch:</td>
                                        <td class="text-end pe-3 text-navy fw-black fs-6" id="simTotalBatchCost">₱0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Custom "What-If" Ingredient Supply Test (Simulate Extra Stock) -->
                <div class="card border-0 shadow-xs rounded-4 bg-white">
                    <div class="card-header bg-white border-0 pt-3 px-4 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-navy mb-0">
                                <i class="bi bi-lightbulb-fill text-warning me-1.5"></i>"What-If" Stock Level Experimenter
                            </h6>
                            <small class="text-muted">Simulate adding or adjusting ingredients to see how finished product yield changes.</small>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="resetSimCustomStocks()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Actual Stock
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4 pt-2">
                        <div id="simCustomStockRows" class="row g-2">
                            <!-- Populated dynamically with ingredient stock adjusters -->
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i> Calculation verified against perpetual inventory stock balance.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-navy rounded-pill px-4 fw-bold" id="btnTransferToProduction" onclick="transferYieldBatchToProduction()">
                        <i class="bi bi-box-arrow-up-right me-1.5"></i> Open in Brew / Production Workstation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 1: ADD / EDIT MASTER ITEM (Starts at 0 Stock)                   -->
<!-- ===================================================================== -->
<div class="modal fade" id="invMasterItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: min(860px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <h5 class="modal-title fw-bold" id="invMasterModalTitle"><i class="bi bi-box-seam text-warning me-2"></i>Add Master Item (Starts at 0 Stock)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="invMasterItemForm" onsubmit="submitMasterItem(event)">
                    <input type="hidden" id="masterItemId" value="">
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        New items always start at <strong>0 stock</strong>. Use <em>Stock In</em>, <em>Item Conversion</em>, or <em>Brewing</em> to add stock.
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Item Classification <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="masterItemType" onchange="toggleFinishedProductFields()">
                                <option value="raw">Raw Material / Ingredient</option>
                                <option value="finished">Finished Product (POS Sellable)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label small fw-bold text-secondary mb-1">Item / SKU Code <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" style="font-size: 0.72rem;" onclick="autoGenerateMasterSku()">
                                    <i class="bi bi-magic me-0.5"></i>Auto SKU
                                </button>
                            </div>
                            <input type="text" class="form-control rounded-3 fw-mono text-uppercase" id="masterItemCode" placeholder="e.g. SKU-BF-001 or RM-VANILLA-ML" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Item / Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="masterItemName" placeholder="e.g. Burger and Fries" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary">Unit of Measure <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="masterItemUnit" list="invUnitOptions" placeholder="g, ml, pc, pack" required>
                            <datalist id="invUnitOptions">
                                <option value="g"><option value="kg"><option value="ml"><option value="L">
                                <option value="pc"><option value="pack"><option value="box"><option value="can"><option value="cup"><option value="slice"><option value="order">
                            </datalist>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary">Unit Cost (₱)</label>
                            <input type="number" step="0.0001" min="0" class="form-control rounded-3" id="masterItemCost" value="0">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-secondary">Reorder Alert</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="masterItemReorder" value="10">
                        </div>
                    </div>

                    <!-- Image Thumbnail Upload (Optional for both FG and RAW) -->
                    <div class="p-3 rounded-4 bg-light border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label small fw-bold text-secondary mb-0">
                                <i class="bi bi-image me-1 text-primary"></i>Product / Material Thumbnail Image
                                <span class="badge bg-secondary-subtle text-secondary fw-normal ms-1">Optional</span>
                            </label>
                            <span class="text-muted" style="font-size: 0.72rem;">Empty items use brand placeholders</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 overflow-hidden border bg-white shadow-xs d-flex align-items-center justify-content-center flex-shrink-0" style="width: 72px; height: 72px;">
                                <img id="masterItemImagePreview" src="assets/images/placeholder-raw.svg" alt="Thumbnail Preview" class="w-100 h-100 object-fit-cover" onerror="this.src='assets/images/placeholder.svg';">
                            </div>
                            <div class="flex-grow-1">
                                <input type="hidden" id="masterItemImageUrl" value="">
                                <input type="file" id="masterItemImageFile" accept="image/png, image/jpeg, image/webp, image/gif" class="d-none" onchange="handleMasterItemImagePick(event)">
                                <div class="d-flex flex-wrap gap-2 mb-1">
                                    <label for="masterItemImageFile" class="btn btn-sm btn-outline-navy rounded-pill px-3 cursor-pointer mb-0">
                                        <i class="bi bi-upload me-1"></i> Choose Thumbnail...
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" id="masterItemImageRemoveBtn" onclick="removeMasterItemImage()" style="display: none;">
                                        <i class="bi bi-x-circle me-1"></i> Remove
                                    </button>
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    Supports JPG, PNG, WebP, GIF. If blank, the brand theme placeholder will be displayed automatically.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Extra Fields if Finished Product (POS) -->
                    <div id="masterFinishedFields" class="p-3 rounded-4 bg-light border mb-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small fw-bold text-navy"><i class="bi bi-shop me-1"></i>POS Catalog &amp; Menu Settings</div>
                            <span class="badge bg-navy text-warning rounded-pill" style="font-size: 0.68rem;"><i class="bi bi-qr-code-scan me-1"></i>POS QR Enabled</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6" id="masterCategoryWrap">
                                <label class="form-label small fw-bold text-secondary mb-1">POS Category <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" id="masterCategoryId"></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Selling Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control rounded-3 fw-bold" id="masterSellingPrice" value="120">
                            </div>
                            <div class="col-12 mt-2">
                                <div class="alert alert-secondary py-2 px-3 small rounded-3 mb-0">
                                    <i class="bi bi-qr-code text-navy me-1.5"></i><strong>POS QR Code:</strong> Official Printable POS QR Tag will be generated and displayed automatically immediately after saving this product.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-navy rounded-pill px-4 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Save Master Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 2: PRODUCT RECIPE (BOM) & ALLOWED ADD-ONS MANAGER               -->
<!-- ===================================================================== -->
<div class="modal fade" id="invRecipeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1480px, 96vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="recipeModalTitle">Configure Brewing Recipe &amp; Add-ons</h5>
                    <small class="text-white-50">Manage base raw material ingredients and allowed add-ons for this finished product</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="recipeFinishedItemId" value="">
                <datalist id="invRawItemsDatalist"></datalist>
                <datalist id="invAddonNamesPresetList">
                    <option value="Extra Espresso Shot"></option>
                    <option value="Oat Milk Substitute"></option>
                    <option value="Vanilla Syrup Pump"></option>
                    <option value="Golden Caramel Drizzle"></option>
                    <option value="Brown Sugar Pearl Jelly"></option>
                    <option value="Condensed Milk Boost"></option>
                </datalist>

                <div class="row g-4">
                    <!-- Left: Base Recipe Ingredients -->
                    <div class="col-lg-6" id="recipeBaseSection">
                        <div class="p-3 rounded-4 border bg-light h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-list-check text-primary me-1.5"></i>1. Base Recipe Ingredients</h6>
                                    <small class="text-muted">Deducted per 1 unit of finished product brewed</small>
                                </div>
                                <button type="button" class="btn btn-outline-navy btn-sm rounded-pill px-3 fw-bold" onclick="addRecipeLineRow()">
                                    <i class="bi bi-plus-circle me-1"></i> Add Ingredient
                                </button>
                            </div>
                            <div class="table-responsive bg-white rounded-3 border">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light small text-muted">
                                        <tr>
                                            <th class="ps-2" style="width: 62%;">Raw Material (from Datalist)</th>
                                            <th style="width: 28%;">Qty / 1 Unit</th>
                                            <th class="text-end pe-2" style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="recipeLinesBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Allowed Add-ons Under This Finished Product -->
                    <div class="col-lg-6" id="recipeAddonsSection">
                        <div class="p-3 rounded-4 border bg-light h-100">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <h6 class="fw-bold text-navy mb-0"><i class="bi bi-plus-square-dotted text-warning me-1.5"></i>2. Allowed Add-ons for This Product</h6>
                                    <small class="text-muted">Links each add-on to a raw material ingredient &amp; POS price</small>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 fw-semibold" style="font-size: 0.72rem;" onclick="populateDefaultDrinkAddons()">
                                        <i class="bi bi-magic me-1"></i>Default Add-ons
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark" onclick="addProductAddonRow()">
                                        <i class="bi bi-plus-circle me-1"></i> Add Add-on
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive bg-white rounded-3 border">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light small text-muted">
                                        <tr>
                                            <th class="ps-2" style="width: 30%;">Add-on Name</th>
                                            <th style="width: 36%;">Raw Ingredient (Datalist)</th>
                                            <th style="width: 15%;">Qty</th>
                                            <th style="width: 13%;">Price (₱)</th>
                                            <th class="text-end pe-2" style="width: 6%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productAddonsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-navy rounded-pill px-4 fw-bold" onclick="saveRecipeLines()">
                        <i class="bi bi-check2-circle me-1"></i> Save Recipe &amp; Allowed Add-ons
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 3: STOCK IN / STOCK OUT VOUCHER MODAL                           -->
<!-- ===================================================================== -->
<div class="modal fade" id="invStockTxnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1320px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header text-white px-4 py-3" id="stockTxnModalHeader" style="background: var(--isatu-navy-gradient);">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="stockTxnModalTitle">New Stock In</h5>
                    <small class="text-white-50" id="stockTxnModalSubtitle">Replenish raw materials or finished product stock</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="invStockTxnForm" onsubmit="submitStockTxn(event)">
                    <input type="hidden" id="stockTxnType" value="IN">
                    <input type="hidden" id="stockTxnPostFlag" value="1">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm rounded-3" id="stockTxnDate" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-8 d-none" id="stockTxnPartyWrap">
                            <label class="form-label small fw-bold text-secondary" id="stockTxnPartyLabel">Withdrawal / Write-Off Reason <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3" id="stockTxnParty" placeholder="e.g. Expired / Spoiled Batch or Damaged Container">
                        </div>
                        <input type="hidden" id="stockTxnRefNo" value="">
                    </div>

                    <div class="table-responsive border rounded-3 mb-2">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th class="ps-2" style="width: 46%;">Master Item (Raw Material or Finished Product)</th>
                                    <th style="width: 18%;">Quantity</th>
                                    <th style="width: 18%;">Unit Cost (₱)</th>
                                    <th style="width: 12%;">Line Note</th>
                                    <th class="text-end pe-2" style="width: 6%;"></th>
                                </tr>
                            </thead>
                            <tbody id="stockTxnLinesBody"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-outline-navy btn-sm rounded-pill px-3 fw-bold mb-3" onclick="addStockTxnLineRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Item Line
                    </button>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Voucher Remarks</label>
                        <input type="text" class="form-control form-control-sm rounded-3" id="stockTxnRemarks" placeholder="Optional audit remarks">
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 fw-bold" onclick="document.getElementById('stockTxnPostFlag').value='0'">
                            Save as Draft
                        </button>
                        <button type="submit" class="btn btn-navy rounded-pill px-4 fw-bold" id="btnSubmitStockTxn" onclick="document.getElementById('stockTxnPostFlag').value='1'">
                            <i class="bi bi-check2-circle me-1"></i> Post to Inventory Ledger
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 4: SINGLE FINISHED PRODUCT PRINTABLE QR CODE TAG MODAL          -->
<!-- ===================================================================== -->
<div class="modal fade" id="invProductQrTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: min(560px, 95vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Printable Product POS QR Tag</h5>
                        <small class="text-white-50">Tag to physical menu boards, counter stands, or product packaging</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light text-center">
                <div id="singleProductQrTagCard" class="bg-white border border-2 border-dark rounded-4 p-4 mx-auto shadow-sm" style="max-width: 340px;">
                    <div class="text-uppercase fw-black text-navy" style="font-size: 0.72rem; letter-spacing: 0.08em;">Raeden’s Bakery and Café • POS Tag</div>
                    <div class="fw-black text-dark fs-5 mt-1 mb-0.5" id="qrTagProductName">Burger and Fries</div>
                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mb-2" id="qrTagCategoryName">Pastries &amp; Snacks</div>
                    <div class="d-flex justify-content-center my-2">
                        <div id="qrTagCanvasBox" class="p-2.5 bg-white border rounded-3 d-inline-block"></div>
                    </div>
                    <div class="fw-mono fw-black text-navy fs-6 mt-1" id="qrTagSkuCode">SKU-BF-001</div>
                    <div class="fw-black text-warning bg-navy d-inline-block px-3 py-1 rounded-pill mt-1" style="font-size: 1rem;" id="qrTagSellingPrice">₱120.00</div>
                    <div class="text-muted mt-2" style="font-size: 0.68rem;"><i class="bi bi-qr-code-scan me-1"></i>Scan at POS Terminal to add to order</div>
                </div>

                <div class="row g-2 align-items-center justify-content-center mt-3">
                    <div class="col-auto">
                        <label class="small fw-bold text-secondary">Print Copies / Sheet Layout:</label>
                    </div>
                    <div class="col-auto">
                        <select id="qrTagPrintCopies" class="form-select form-select-sm rounded-pill fw-bold">
                            <option value="1">1 Single Tag</option>
                            <option value="4" selected>4 Tags (2×2 Counter Sheet)</option>
                            <option value="8">8 Tags (2×4 Sticker Sheet)</option>
                            <option value="12">12 Tags (3×4 Full Label Sheet)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white px-4 py-3 d-flex flex-wrap justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold" onclick="downloadSingleProductQrPng()" title="Download raw QR Code PNG">
                        <i class="bi bi-download me-1"></i> Download QR PNG
                    </button>
                    <button type="button" class="btn btn-outline-navy btn-sm rounded-pill px-3 fw-bold" onclick="downloadProductTagCardPng()" title="Download complete formatted shelf / counter tag label">
                        <i class="bi bi-card-heading me-1"></i> Download Label Card
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-navy btn-sm rounded-pill px-4 fw-bold" onclick="printSingleProductQrTags()">
                        <i class="bi bi-printer-fill text-warning me-1"></i> Print QR Tag(s)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 5: GENERATE & PRINT QR CODES FOR ALL EXISTING MENUS             -->
<!-- ===================================================================== -->
<div class="modal fade" id="invMenuQrCatalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: min(1360px, 96vw);">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-navy text-white px-4 py-3" style="background: var(--isatu-navy-gradient);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code-scan text-warning fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Menu POS QR Code Generator &amp; Printable Tag Sheet</h5>
                        <small class="text-white-50">Auto-generated 2D QR Codes for all existing Finished Products / Menus — ready to print &amp; scan in POS</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 bg-white p-3 rounded-4 border shadow-xs">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <input type="search" id="menuQrCatalogSearch" class="form-control form-control-sm rounded-pill" style="width: 240px;" placeholder="Filter menu name or SKU..." oninput="renderMenuQrCatalogGrid()">
                        <span class="badge bg-navy text-warning rounded-pill px-3 py-1.5" id="menuQrCatalogCountBadge">0 Menus</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" onclick="toggleSelectAllMenuQr(true)">Select All</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" onclick="toggleSelectAllMenuQr(false)">Clear</button>
                        <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-4 fw-bold shadow-xs" onclick="printSelectedMenuQrCatalog()">
                            <i class="bi bi-printer-fill me-1"></i> Print Selected Menu QR Tags
                        </button>
                    </div>
                </div>
                <div class="row g-3" id="menuQrCatalogGrid"></div>
            </div>
            <div class="modal-footer bg-white px-4 py-3 d-flex justify-content-between">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Every QR code encodes the product's unique POS SKU code for instant optical camera or 2D scanner lookup in the POS Terminal.</small>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Shared Master Items Datalist for Stock In/Out and Item Conversion -->
<datalist id="invMasterItemsDatalist"></datalist>

<script>
// Global HTML Escape Helper for Inventory & Yield Engine
function escapeInvHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}
if (typeof escapeHtml === 'undefined') {
    window.escapeHtml = escapeInvHtml;
}

// =====================================================================
// PERPETUAL INVENTORY SUITE CONTROLLER
// =====================================================================
let INV_ITEMS = [];
let INV_CATEGORIES = [];
let INV_MASTER_FILTER = 'all';
let INV_CURRENT_STOCK_TYPE = 'IN';

function formatDatalistLabel(it) {
    if (!it) return '';
    return `${it.item_code} — ${it.name}`;
}

function resolveDatalistItem(rawInput) {
    const val = String(rawInput || '').trim().toLowerCase();
    if (!val) return null;
    return INV_ITEMS.filter(it => it.item_type === 'raw').find(it => {
        const label = formatDatalistLabel(it).toLowerCase();
        const code = String(it.item_code || '').toLowerCase();
        const name = String(it.name || '').toLowerCase();
        return val === label || val === code || val === name;
    }) || null;
}

function fmtQty(v) {
    const n = parseFloat(v || 0);
    if (Number.isNaN(n)) return '0';
    return parseFloat(n.toFixed(4)).toLocaleString('en-PH', { maximumFractionDigits: 4 });
}

function switchToInvTab(tabBtnId) {
    const btn = document.getElementById(tabBtnId);
    if (btn) {
        new bootstrap.Tab(btn).show();
        btn.click();
    }
}

async function loadInventorySuite() {
    try {
        const res = await fetch('api/inventory.php?action=items');
        const data = await res.json();
        if (!data.success) return;

        INV_ITEMS = data.items || [];
        INV_CATEGORIES = data.categories || [];
        const s = data.summary || {};

        document.getElementById('invKpiTotalItems').textContent = `${(s.raw || 0) + (s.finished || 0)} Items`;
        document.getElementById('invKpiBreakdown').textContent = `${s.raw || 0} Raw Materials • ${s.finished || 0} Finished`;
        document.getElementById('invKpiValuation').textContent = '₱' + Number(s.value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('invKpiLowStock').textContent = `${s.low || 0} Items`;
        document.getElementById('invKpiZeroStock').textContent = `${s.zero || 0} Items`;

        renderMasterItemsTable();
        populateInventoryDropdowns();
        loadYieldCalculatorDashboard();
    } catch (e) {
        console.error('Error loading inventory suite:', e);
    }
}

function filterMasterItems(type, btn) {
    INV_MASTER_FILTER = type;
    if (btn && btn.parentElement) {
        btn.parentElement.querySelectorAll('.btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    renderMasterItemsTable();
}

function renderMasterItemsTable() {
    const tbody = document.getElementById('invMasterTableBody');
    if (!tbody) return;
    const q = (document.getElementById('invMasterSearch')?.value || '').trim().toLowerCase();

    const filtered = INV_ITEMS.filter(it => {
        if (INV_MASTER_FILTER !== 'all' && it.item_type !== INV_MASTER_FILTER) return false;
        if (q && !`${it.item_code} ${it.name} ${it.category_name || ''}`.toLowerCase().includes(q)) return false;
        return true;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted small">No matching master items found.</td></tr>`;
        return;
    }

    tbody.innerHTML = filtered.map(it => {
        const stock = parseFloat(it.current_stock || 0);
        const reorder = parseFloat(it.reorder_level || 0);
        const isRaw = it.item_type === 'raw';

        let statusBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle">In Stock</span>`;
        if (!isRaw) {
            statusBadge = `<span class="badge bg-warning-subtle text-dark border border-warning-subtle">Made to Order</span>`;
        } else if (stock <= 0) {
            statusBadge = `<span class="badge bg-danger">0 Stock</span>`;
        } else if (stock <= reorder) {
            statusBadge = `<span class="badge bg-warning text-dark">Low (&le;${fmtQty(reorder)})</span>`;
        }

        const typeBadge = isRaw
            ? `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><i class="bi bi-droplet-half me-1"></i>Raw Material</span>`
            : `<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-cup-hot me-1"></i>Menu Product (${escapeInvHtml(it.category_name || 'POS')})</span>`;

        const recipeBtn = !isRaw
            ? `<button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-2.5 py-0.5 fw-bold shadow-xs" onclick='openProductQrTagModal(${JSON.stringify(it)})' title="View & Download Product POS QR Tag">
                   <i class="bi bi-qr-code me-1"></i>View QR
               </button>
               <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5" onclick="openRecipeModal(${it.id}, 'recipe')" title="Configure Brewing Ingredients">
                   <i class="bi bi-list-check me-1"></i>Recipe (${it.recipe_lines || 0})
               </button>
               <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-2.5 py-0.5" onclick="openRecipeModal(${it.id}, 'addons')" title="Manage Allowed Add-ons for this Finished Product">
                   <i class="bi bi-plus-square-dotted text-warning me-1"></i>Add-ons (${it.addon_lines || 0})
               </button>`
            : '';

        const quickActionBtn = isRaw
            ? `<button type="button" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-0.5" onclick="openStockTxnModal('IN', ${it.id})">+ Stock In</button>`
            : `<span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" title="Brewed & Served in Kitchen (Non-Stocked)"><i class="bi bi-cup-hot-fill text-warning me-1"></i>Kitchen Prep</span>`;

        const stockCellHtml = isRaw
            ? `<span class="fw-mono fw-black ${stock <= 0 ? 'text-danger' : 'text-navy'} fs-6">${fmtQty(stock)} <small class="fw-normal text-muted">${escapeInvHtml(it.unit)}</small></span>`
            : `<span class="badge bg-light text-muted border fw-normal">Non-Stocked Unit</span>`;

        const placeholderImg = isRaw ? 'assets/images/placeholder-raw.svg' : 'assets/images/placeholder-product.svg';
        const itemImgSrc = (it.image_url && String(it.image_url).trim() !== '') ? it.image_url : placeholderImg;

        return `
            <tr>
                <td class="fw-mono fw-bold text-navy small">
                    ${escapeInvHtml(it.item_code)}
                    ${!isRaw ? `<button type="button" class="btn btn-link btn-sm p-0 ms-1 text-navy" onclick='openProductQrTagModal(${JSON.stringify(it)})' title="Print POS QR Code"><i class="bi bi-qr-code"></i></button>` : ''}
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 overflow-hidden bg-light border shadow-xs flex-shrink-0" style="width: 38px; height: 38px;">
                            <img src="${escapeInvHtml(itemImgSrc)}" 
                                 alt="${escapeInvHtml(it.name)}" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.onerror=null; this.src='${placeholderImg}';">
                        </div>
                        <div>
                            <div class="fw-bold text-dark">${escapeInvHtml(it.name)}</div>
                        </div>
                    </div>
                </td>
                <td>${typeBadge}</td>
                <td><span class="badge bg-light text-secondary border fw-mono">${escapeInvHtml(it.unit)}</span></td>
                <td class="text-end fw-mono small">₱${Number(it.unit_cost || 0).toFixed(2)}</td>
                <td class="text-end fw-mono small">${it.selling_price ? '₱' + Number(it.selling_price).toFixed(2) : '<span class="text-muted">—</span>'}</td>
                <td class="text-end fw-mono small text-muted">${isRaw ? fmtQty(reorder) + ' ' + escapeInvHtml(it.unit) : '—'}</td>
                <td class="text-end">${stockCellHtml}</td>
                <td>${statusBadge}</td>
                <td class="text-end">
                    <div class="d-inline-flex gap-1 align-items-center">
                        ${recipeBtn}
                        ${quickActionBtn}
                        <button type="button" class="btn btn-light btn-sm border rounded-circle" onclick='openMasterItemModal(${JSON.stringify(it)})' title="Edit Item"><i class="bi bi-pencil"></i></button>
                        ${isRaw ? `<button type="button" class="btn btn-light btn-sm border rounded-circle" onclick="viewItemStockCard(${it.id})" title="View Accounting Ledger"><i class="bi bi-journal-text"></i></button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function populateInventoryDropdowns() {
    // Categories in Add Item modal
    const catSel = document.getElementById('masterCategoryId');
    if (catSel) {
        catSel.innerHTML = INV_CATEGORIES.map(c => `<option value="${c.id}">${escapeInvHtml(c.name)}</option>`).join('');
    }

    // Master Items Datalist (used by Stock In, Stock Out, and Item Conversion — Raw Materials only)
    const dlist = document.getElementById('invMasterItemsDatalist');
    if (dlist) {
        dlist.innerHTML = INV_ITEMS.filter(it => it.item_type === 'raw').map(it =>
            `<option value="${escapeInvHtml(formatDatalistLabel(it))}">[RAW INGREDIENT] Avail: ${fmtQty(it.current_stock)} ${escapeInvHtml(it.unit)} • Cost: ₱${Number(it.unit_cost || 0).toFixed(2)}</option>`
        ).join('');
    }

    // Raw Materials Datalist (used by Recipe & Add-ons Manager)
    const rawDlist = document.getElementById('invRawItemsDatalist');
    if (rawDlist) {
        rawDlist.innerHTML = INV_ITEMS.filter(it => it.item_type === 'raw').map(it =>
            `<option value="${escapeInvHtml(formatDatalistLabel(it))}">Unit: ${escapeInvHtml(it.unit)} • Avail: ${fmtQty(it.current_stock)} ${escapeInvHtml(it.unit)}</option>`
        ).join('');
    }

    // Ledger filter dropdown
    const ledSel = document.getElementById('ledgerItemFilter');
    if (ledSel) {
        const curLed = ledSel.value;
        ledSel.innerHTML = '<option value="0">All Inventory Items (General Journal)</option>' +
            INV_ITEMS.map(it => `<option value="${it.id}">${escapeInvHtml(it.item_code)} — ${escapeInvHtml(it.name)} (${escapeInvHtml(it.unit)})</option>`).join('');
        if (curLed) ledSel.value = curLed;
    }
}

// ---------------------------------------------------------------------
// 1. MASTER ITEM, QR CODE GENERATOR & RECIPE HANDLERS
// ---------------------------------------------------------------------
let CURRENT_SINGLE_QR_ITEM = null;

function autoGenerateMasterSku() {
    const type = document.getElementById('masterItemType')?.value || 'raw';
    const name = (document.getElementById('masterItemName')?.value || '').trim();
    const prefix = type === 'finished' ? 'SKU' : 'RM';
    let slugPart = name
        .toUpperCase()
        .replace(/[^A-Z0-9\s]/g, '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(w => w.substring(0, 4))
        .join('-');
    if (!slugPart) {
        slugPart = String(Math.floor(100 + Math.random() * 900));
    }
    const existingCount = INV_ITEMS.filter(i => i.item_type === type).length + 1;
    const numSuffix = String(existingCount).padStart(3, '0');
    const codeInput = document.getElementById('masterItemCode');
    if (codeInput) {
        codeInput.value = `${prefix}-${slugPart}-${numSuffix}`;
    }
}

function updateMasterItemQrPreview() {
    // Official QR is generated only after adding the item to the database
}

let CURRENT_ITEM_IMAGE_BASE64 = null;

function handleMasterItemImagePick(e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    if (file.size > 3 * 1024 * 1024) {
        showToast('Image file size must be less than 3MB.', 'warning');
        e.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(evt) {
        CURRENT_ITEM_IMAGE_BASE64 = evt.target.result;
        const preview = document.getElementById('masterItemImagePreview');
        if (preview) {
            preview.src = CURRENT_ITEM_IMAGE_BASE64;
        }
        const rmBtn = document.getElementById('masterItemImageRemoveBtn');
        if (rmBtn) rmBtn.style.display = 'inline-block';
    };
    reader.readAsDataURL(file);
}

function removeMasterItemImage() {
    CURRENT_ITEM_IMAGE_BASE64 = null;
    const fileInput = document.getElementById('masterItemImageFile');
    if (fileInput) fileInput.value = '';
    const urlInput = document.getElementById('masterItemImageUrl');
    if (urlInput) urlInput.value = '';
    const rmBtn = document.getElementById('masterItemImageRemoveBtn');
    if (rmBtn) rmBtn.style.display = 'none';

    const type = document.getElementById('masterItemType')?.value || 'raw';
    const preview = document.getElementById('masterItemImagePreview');
    if (preview) {
        preview.src = type === 'finished' ? 'assets/images/placeholder-product.svg' : 'assets/images/placeholder-raw.svg';
    }
}

function toggleFinishedProductFields() {
    const type = document.getElementById('masterItemType').value;
    const wrap = document.getElementById('masterFinishedFields');

    // Update thumbnail preview placeholder if no custom image is selected
    if (!CURRENT_ITEM_IMAGE_BASE64 && !document.getElementById('masterItemImageUrl')?.value) {
        const preview = document.getElementById('masterItemImagePreview');
        if (preview) {
            preview.src = type === 'finished' ? 'assets/images/placeholder-product.svg' : 'assets/images/placeholder-raw.svg';
        }
    }

    if (type === 'finished') {
        wrap.classList.remove('d-none');
        if (!document.getElementById('masterItemId').value && !document.getElementById('masterItemCode').value.trim()) {
            autoGenerateMasterSku();
        }
        if (!document.getElementById('masterItemId').value && document.getElementById('masterItemUnit').value === 'g') {
            document.getElementById('masterItemUnit').value = 'order';
        }
    } else {
        wrap.classList.add('d-none');
    }
}

function openMasterItemModal(item = null) {
    document.getElementById('invMasterItemForm').reset();
    CURRENT_ITEM_IMAGE_BASE64 = null;
    const fileInput = document.getElementById('masterItemImageFile');
    if (fileInput) fileInput.value = '';

    const urlInput = document.getElementById('masterItemImageUrl');
    const preview = document.getElementById('masterItemImagePreview');
    const rmBtn = document.getElementById('masterItemImageRemoveBtn');

    if (item && item.image_url && String(item.image_url).trim() !== '') {
        if (urlInput) urlInput.value = item.image_url;
        if (preview) preview.src = item.image_url;
        if (rmBtn) rmBtn.style.display = 'inline-block';
    } else {
        if (urlInput) urlInput.value = '';
        const isFinished = (item && item.item_type === 'finished');
        if (preview) preview.src = isFinished ? 'assets/images/placeholder-product.svg' : 'assets/images/placeholder-raw.svg';
        if (rmBtn) rmBtn.style.display = 'none';
    }

    document.getElementById('masterItemId').value = item ? item.id : '';
    document.getElementById('masterItemType').value = item ? item.item_type : 'raw';
    document.getElementById('masterItemType').disabled = !!item;
    document.getElementById('masterItemCode').value = item ? item.item_code : '';
    document.getElementById('masterItemName').value = item ? item.name : '';
    document.getElementById('masterItemUnit').value = item ? item.unit : 'g';
    document.getElementById('masterItemCost').value = item ? parseFloat(item.unit_cost || 0) : 0;
    document.getElementById('masterItemReorder').value = item ? parseFloat(item.reorder_level || 0) : 10;
    if (item && item.selling_price) {
        document.getElementById('masterSellingPrice').value = parseFloat(item.selling_price);
    }
    document.getElementById('masterCategoryWrap').style.display = item ? 'none' : 'block';
    document.getElementById('invMasterModalTitle').innerHTML = item
        ? `<i class="bi bi-pencil-square text-warning me-2"></i>Edit Master Item: ${escapeInvHtml(item.name)}`
        : `<i class="bi bi-box-seam text-warning me-2"></i>Add Master Item (Starts at 0 Stock)`;
    toggleFinishedProductFields();
    new bootstrap.Modal(document.getElementById('invMasterItemModal')).show();
}

async function submitMasterItem(e) {
    e.preventDefault();
    const payload = {
        action: 'save_item',
        id: document.getElementById('masterItemId').value || 0,
        item_type: document.getElementById('masterItemType').value,
        item_code: document.getElementById('masterItemCode').value.trim().toUpperCase(),
        name: document.getElementById('masterItemName').value.trim(),
        unit: document.getElementById('masterItemUnit').value.trim(),
        unit_cost: parseFloat(document.getElementById('masterItemCost').value || 0),
        reorder_level: parseFloat(document.getElementById('masterItemReorder').value || 0),
        category_id: parseInt(document.getElementById('masterCategoryId').value || 1),
        selling_price: parseFloat(document.getElementById('masterSellingPrice').value || 0),
        image_url: document.getElementById('masterItemImageUrl').value || '',
        image_base64: CURRENT_ITEM_IMAGE_BASE64 || '',
        has_customizations: 1
    };

    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('invMasterItemModal'))?.hide();
        await loadInventorySuite();

        // Generate and show official POS QR tag immediately after adding
        if (payload.item_type === 'finished') {
            const catObj = INV_CATEGORIES.find(c => parseInt(c.id) === parseInt(payload.category_id));
            const savedItem = (data.item && data.item.item_code) ? {
                ...data.item,
                category_name: catObj ? catObj.name : 'POS Menu'
            } : (INV_ITEMS.find(i => String(i.item_code).toUpperCase() === payload.item_code) || {
                item_code: payload.item_code,
                name: payload.name,
                selling_price: payload.selling_price,
                category_name: catObj ? catObj.name : 'POS Menu'
            });
            setTimeout(() => openProductQrTagModal(savedItem), 200);
        }
    } else {
        showToast(data.message || 'Failed to save item', 'danger');
    }
}

function ensureQrCodeLib() {
    return new Promise((resolve) => {
        if (typeof QRCode !== 'undefined') return resolve(true);
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        s.onload = () => resolve(true);
        s.onerror = () => {
            const s2 = document.createElement('script');
            s2.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
            s2.onload = () => resolve(true);
            s2.onerror = () => resolve(false);
            document.head.appendChild(s2);
        };
        document.head.appendChild(s);
    });
}

async function openProductQrTagModal(item) {
    if (!item) return;
    if (typeof item === 'number' || typeof item === 'string') {
        const found = INV_ITEMS.find(i => i.id == item || String(i.item_code).toUpperCase() === String(item).toUpperCase());
        if (found) item = found;
    }
    CURRENT_SINGLE_QR_ITEM = item;
    const code = String(item.item_code || item.product_code || '').trim().toUpperCase();
    const name = item.name || 'Menu Product';
    const cat = item.category_name || 'POS Menu';
    const price = parseFloat(item.selling_price || item.price || 0);

    document.getElementById('qrTagProductName').textContent = name;
    document.getElementById('qrTagCategoryName').textContent = cat;
    document.getElementById('qrTagSkuCode').textContent = code;
    document.getElementById('qrTagSellingPrice').textContent = '₱' + price.toFixed(2);

    const box = document.getElementById('qrTagCanvasBox');
    if (box) {
        box.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="width:170px; height:170px;"><div class="spinner-border text-primary spinner-border-sm me-2"></div><span class="small text-muted">Rendering QR...</span></div>';
        await ensureQrCodeLib();
        box.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(box, {
                text: code,
                width: 170,
                height: 170,
                colorDark: '#0c2340',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } else {
            const img = document.createElement('img');
            img.src = `https://api.qrserver.com/v1/create-qr-code/?size=170x170&data=${encodeURIComponent(code)}`;
            img.alt = code;
            img.style.width = '170px';
            img.style.height = '170px';
            box.appendChild(img);
        }
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('invProductQrTagModal')).show();
}

async function downloadSingleProductQrPng() {
    if (!CURRENT_SINGLE_QR_ITEM) return;
    const code = String(CURRENT_SINGLE_QR_ITEM.item_code || CURRENT_SINGLE_QR_ITEM.product_code || 'PRODUCT').trim().toUpperCase();
    const box = document.getElementById('qrTagCanvasBox');

    await ensureQrCodeLib();

    let canvas = box ? box.querySelector('canvas') : null;
    let img = box ? box.querySelector('img') : null;
    let dataUrl = '';

    if (canvas && canvas.width > 0) {
        try {
            dataUrl = canvas.toDataURL('image/png');
        } catch (e) {}
    }
    if (!dataUrl && img && img.src && img.src.startsWith('data:image')) {
        dataUrl = img.src;
    }

    if (!dataUrl && typeof QRCode !== 'undefined') {
        const offscreen = document.createElement('div');
        offscreen.style.position = 'fixed';
        offscreen.style.left = '-9999px';
        document.body.appendChild(offscreen);
        new QRCode(offscreen, {
            text: code,
            width: 300,
            height: 300,
            colorDark: '#0c2340',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        const offCanvas = offscreen.querySelector('canvas');
        if (offCanvas) {
            dataUrl = offCanvas.toDataURL('image/png');
        }
        offscreen.remove();
    }

    if (!dataUrl) {
        const fallbackUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(code)}`;
        try {
            const res = await fetch(fallbackUrl);
            const blob = await res.blob();
            const blobUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = `QR-${code}.png`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
            showToast(`Downloaded QR Code for ${code}`, 'success');
            return;
        } catch (err) {
            window.open(fallbackUrl, '_blank');
            return;
        }
    }

    const a = document.createElement('a');
    a.href = dataUrl;
    a.download = `QR-${code}.png`;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => a.remove(), 100);
    showToast(`Downloaded QR Code for ${code}`, 'success');
}

async function downloadProductTagCardPng() {
    if (!CURRENT_SINGLE_QR_ITEM) return;
    const code = String(CURRENT_SINGLE_QR_ITEM.item_code || CURRENT_SINGLE_QR_ITEM.product_code || 'PRODUCT').trim().toUpperCase();
    const name = CURRENT_SINGLE_QR_ITEM.name || 'Menu Product';
    const cat = CURRENT_SINGLE_QR_ITEM.category_name || 'POS Menu';
    const price = parseFloat(CURRENT_SINGLE_QR_ITEM.selling_price || CURRENT_SINGLE_QR_ITEM.price || 0);

    const box = document.getElementById('qrTagCanvasBox');
    let qrCanvas = box?.querySelector('canvas');
    let qrImg = box?.querySelector('img');

    // Create high-res 680x880 canvas
    const card = document.createElement('canvas');
    card.width = 680;
    card.height = 880;
    const ctx = card.getContext('2d');

    // White background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, card.width, card.height);

    // Outer border
    ctx.strokeStyle = '#0c2340';
    ctx.lineWidth = 8;
    ctx.strokeRect(10, 10, card.width - 20, card.height - 20);

    // Inner border
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 2;
    ctx.strokeRect(20, 20, card.width - 40, card.height - 40);

    // Header branding
    ctx.fillStyle = '#894E3F';
    ctx.font = 'bold 22px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('RAEDEN’S BAKERY AND CAFÉ', card.width / 2, 70);

    ctx.fillStyle = '#64748b';
    ctx.font = 'bold 16px sans-serif';
    ctx.fillText('OFFICIAL POS MENU QR TAG', card.width / 2, 98);

    // Product Title
    ctx.fillStyle = '#1e293b';
    ctx.font = '900 36px sans-serif';
    const displayTitle = name.length > 25 ? name.substring(0, 23) + '...' : name;
    ctx.fillText(displayTitle, card.width / 2, 160);

    // Category Pill
    ctx.fillStyle = '#EEE1BA';
    ctx.beginPath();
    ctx.roundRect ? ctx.roundRect((card.width - 260) / 2, 185, 260, 36, 18) : ctx.rect((card.width - 260) / 2, 185, 260, 36);
    ctx.fill();
    ctx.fillStyle = '#894E3F';
    ctx.font = 'bold 18px sans-serif';
    ctx.fillText(cat, card.width / 2, 210);

    // QR Code Container Box
    const qrSize = 340;
    const qrX = (card.width - qrSize) / 2;
    const qrY = 250;
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(qrX - 10, qrY - 10, qrSize + 20, qrSize + 20);
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 2;
    ctx.strokeRect(qrX - 10, qrY - 10, qrSize + 20, qrSize + 20);

    if (qrCanvas && qrCanvas.width > 0) {
        ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);
    } else if (qrImg && qrImg.complete && qrImg.naturalWidth > 0) {
        ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);
    }

    // SKU Code
    ctx.fillStyle = '#0c2340';
    ctx.font = 'bold 30px monospace';
    ctx.fillText(code, card.width / 2, 650);

    // Price Pill
    ctx.fillStyle = '#0c2340';
    ctx.beginPath();
    ctx.roundRect ? ctx.roundRect((card.width - 240) / 2, 680, 240, 56, 28) : ctx.rect((card.width - 240) / 2, 680, 240, 56);
    ctx.fill();
    ctx.fillStyle = '#F59E0B';
    ctx.font = '900 32px sans-serif';
    ctx.fillText('₱' + price.toFixed(2), card.width / 2, 720);

    // Footer note
    ctx.fillStyle = '#64748b';
    ctx.font = '16px sans-serif';
    ctx.fillText('Scan at POS Touch Terminal or Online Ordering', card.width / 2, 785);

    // Download PNG
    const cardDataUrl = card.toDataURL('image/png');
    const a = document.createElement('a');
    a.href = cardDataUrl;
    a.download = `LABEL-${code}.png`;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => a.remove(), 100);
    showToast(`Downloaded Label Card for ${code}`, 'success');
}

function printSingleProductQrTags() {
    if (!CURRENT_SINGLE_QR_ITEM) return;
    const copies = Math.max(1, parseInt(document.getElementById('qrTagPrintCopies')?.value || 4));
    const itemsToPrint = [];
    for (let i = 0; i < copies; i++) {
        itemsToPrint.push(CURRENT_SINGLE_QR_ITEM);
    }
    openPrintableQrSheetWindow(itemsToPrint, `${CURRENT_SINGLE_QR_ITEM.name} (${CURRENT_SINGLE_QR_ITEM.item_code})`);
}

function openMenuQrCatalogModal() {
    const searchEl = document.getElementById('menuQrCatalogSearch');
    if (searchEl) searchEl.value = '';
    renderMenuQrCatalogGrid();
    new bootstrap.Modal(document.getElementById('invMenuQrCatalogModal')).show();
}

function renderMenuQrCatalogGrid() {
    const grid = document.getElementById('menuQrCatalogGrid');
    if (!grid) return;
    const q = (document.getElementById('menuQrCatalogSearch')?.value || '').trim().toLowerCase();
    const finishedItems = INV_ITEMS.filter(it => it.item_type === 'finished' && (
        !q || `${it.item_code} ${it.name} ${it.category_name || ''}`.toLowerCase().includes(q)
    ));

    const badge = document.getElementById('menuQrCatalogCountBadge');
    if (badge) badge.textContent = `${finishedItems.length} Menu Product${finishedItems.length === 1 ? '' : 's'}`;

    if (finishedItems.length === 0) {
        grid.innerHTML = `<div class="col-12 text-center py-5 text-muted">No Finished Products / Menus found. Add a Finished Product in the Master List to generate its QR Code.</div>`;
        return;
    }

    grid.innerHTML = finishedItems.map((it, idx) => {
        const price = parseFloat(it.selling_price || 0).toFixed(2);
        return `
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 border rounded-4 shadow-xs bg-white overflow-hidden">
                    <div class="card-body p-3 text-center d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="form-check mb-0">
                                <input class="form-check-input menu-qr-select-chk" type="checkbox" value="${escapeInvHtml(it.item_code)}" id="menuQrChk_${idx}" checked>
                                <label class="form-check-label small text-muted fw-semibold" for="menuQrChk_${idx}">Select</label>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.65rem;">${escapeInvHtml(it.category_name || 'POS Menu')}</span>
                        </div>
                        <div class="fw-black text-navy text-truncate mt-1" title="${escapeInvHtml(it.name)}">${escapeInvHtml(it.name)}</div>
                        <div class="d-flex justify-content-center my-2">
                            <div class="p-2 bg-white border rounded-3 d-inline-block menu-qr-render-box" data-qr-code="${escapeInvHtml(it.item_code)}"></div>
                        </div>
                        <div class="fw-mono fw-bold text-dark small">${escapeInvHtml(it.item_code)}</div>
                        <div class="fw-black text-warning bg-navy d-inline-block mx-auto px-3 py-0.5 rounded-pill mt-1 small">₱${price}</div>
                        <div class="mt-auto pt-2 d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-outline-navy btn-sm rounded-pill px-3 fw-bold w-100" onclick='openProductQrTagModal(${JSON.stringify(it)})'>
                                <i class="bi bi-qr-code me-1"></i>View QR Tag
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    ensureQrCodeLib().then(() => {
        grid.querySelectorAll('.menu-qr-render-box').forEach(box => {
            const code = box.getAttribute('data-qr-code') || '';
            box.innerHTML = '';
            if (code) {
                if (typeof QRCode !== 'undefined') {
                    new QRCode(box, {
                        text: code,
                        width: 120,
                        height: 120,
                        colorDark: '#0c2340',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                } else {
                    const img = document.createElement('img');
                    img.src = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(code)}`;
                    img.style.width = '120px';
                    img.style.height = '120px';
                    box.appendChild(img);
                }
            }
        });
    });
}

function toggleSelectAllMenuQr(checked) {
    document.querySelectorAll('.menu-qr-select-chk').forEach(chk => {
        chk.checked = !!checked;
    });
}

function printSelectedMenuQrCatalog() {
    const selectedCodes = Array.from(document.querySelectorAll('.menu-qr-select-chk:checked')).map(c => c.value);
    if (selectedCodes.length === 0) {
        showToast('Please select at least one Menu QR Tag to print.', 'warning');
        return;
    }
    const itemsToPrint = INV_ITEMS.filter(it => it.item_type === 'finished' && selectedCodes.includes(it.item_code));
    openPrintableQrSheetWindow(itemsToPrint, 'Raeden’s Bakery and Café — Master Menu POS QR Tags');
}

function openPrintableQrSheetWindow(items, sheetTitle = 'Product POS QR Tags') {
    const w = window.open('', '_blank', 'width=980,height=760');
    if (!w) {
        showToast('Pop-up blocked! Please allow pop-ups to print QR code tags.', 'danger');
        return;
    }
    const cardsHtml = items.map((it, i) => {
        const code = String(it.item_code || it.product_code || '').toUpperCase();
        const name = escapeInvHtml(it.name || 'Menu Item');
        const cat = escapeInvHtml(it.category_name || 'Bakery & Café');
        const price = Number(it.selling_price || it.price || 0).toFixed(2);
        return `
            <div class="qr-tag-card">
                <div class="brand">RAEDEN'S BAKERY AND CAFÉ</div>
                <div class="prod-name">${name}</div>
                <div class="cat-badge">${cat}</div>
                <div class="qr-holder" id="printQr_${i}" data-code="${escapeInvHtml(code)}"></div>
                <div class="sku-code">${escapeInvHtml(code)}</div>
                <div class="price-pill">₱${price}</div>
                <div class="hint">SCAN AT POS TERMINAL</div>
            </div>
        `;
    }).join('');

    w.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>${escapeInvHtml(sheetTitle)}</title>
    <style>
        * { box-sizing: border-box; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body { margin: 0; padding: 20px; background: #f8fafc; color: #0f172a; }
        .no-print-bar { display: flex; justify-content: space-between; align-items: center; background: #0c2340; color: #fff; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; }
        .no-print-bar button { background: #ffc72c; color: #0c2340; border: 0; padding: 8px 18px; border-radius: 999px; font-weight: 800; cursor: pointer; }
        .tag-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 14px; }
        .qr-tag-card { background: #fff; border: 2px dashed #0c2340; border-radius: 14px; padding: 14px 10px; text-align: center; page-break-inside: avoid; }
        .brand { font-size: 9px; font-weight: 900; letter-spacing: 0.08em; color: #0c2340; }
        .prod-name { font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cat-badge { font-size: 10px; color: #475569; margin-bottom: 6px; }
        .qr-holder { display: inline-flex; padding: 6px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; margin: 4px auto; }
        .sku-code { font-family: monospace; font-size: 13px; font-weight: 800; color: #0c2340; margin-top: 4px; }
        .price-pill { display: inline-block; background: #0c2340; color: #ffc72c; font-weight: 900; font-size: 13px; padding: 2px 12px; border-radius: 999px; margin-top: 4px; }
        .hint { font-size: 8px; color: #64748b; margin-top: 5px; letter-spacing: 0.06em; font-weight: 700; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print-bar { display: none !important; }
            .tag-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
</head>
<body>
    <div class="no-print-bar">
        <div>
            <strong>${escapeInvHtml(sheetTitle)}</strong>
            <div style="font-size:12px;opacity:0.8;">Cut along dashed lines to tag finished products, menu boards, or POS binder</div>
        </div>
        <button onclick="window.print()">Print QR Tags Now</button>
    </div>
    <div class="tag-grid">${cardsHtml}</div>
    <script>
        window.onload = function() {
            document.querySelectorAll('.qr-holder').forEach(function(el) {
                var code = el.getAttribute('data-code');
                if (code && typeof QRCode !== 'undefined') {
                    new QRCode(el, {
                        text: code,
                        width: 128,
                        height: 128,
                        colorDark: '#0c2340',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            });
            setTimeout(function() { window.print(); }, 450);
        };
    <\/script>
</body>
</html>`);
    w.document.close();
}

function resolveRawDatalistItem(rawInput) {
    const val = String(rawInput || '').trim().toLowerCase();
    if (!val) return null;
    return INV_ITEMS.filter(i => i.item_type === 'raw').find(it => {
        const label = formatDatalistLabel(it).toLowerCase();
        const code = String(it.item_code || '').toLowerCase();
        const name = String(it.name || '').toLowerCase();
        return val === label || val === code || val === name;
    }) || null;
}

async function openRecipeModal(finishedItemId, focusSection = 'recipe') {
    const fg = INV_ITEMS.find(i => parseInt(i.id) === parseInt(finishedItemId));
    document.getElementById('recipeFinishedItemId').value = finishedItemId;
    document.getElementById('recipeModalTitle').textContent = `Recipe & Allowed Add-ons: ${fg ? fg.name : 'Finished Product'}`;
    const tbody = document.getElementById('recipeLinesBody');
    const addonsBody = document.getElementById('productAddonsBody');
    tbody.innerHTML = '';
    if (addonsBody) addonsBody.innerHTML = '';

    const res = await fetch(`api/inventory.php?action=recipe&item_id=${finishedItemId}`);
    const data = await res.json();
    const lines = data.lines || [];
    const addons = data.addons || [];

    if (lines.length === 0) {
        addRecipeLineRow();
    } else {
        lines.forEach(ln => addRecipeLineRow(ln.ingredient_item_id, ln.qty_per_unit));
    }

    if (addons.length === 0) {
        addProductAddonRow();
    } else {
        addons.forEach(ad => addProductAddonRow(ad.addon_name, ad.ingredient_item_id, ad.qty_per_unit, ad.price));
    }

    const modalEl = document.getElementById('invRecipeModal');
    new bootstrap.Modal(modalEl).show();
    setTimeout(() => {
        const target = document.getElementById(focusSection === 'addons' ? 'recipeAddonsSection' : 'recipeBaseSection');
        target?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 250);
}

function addRecipeLineRow(ingId = '', qty = 1) {
    const tbody = document.getElementById('recipeLinesBody');
    const preRaw = ingId ? INV_ITEMS.find(i => parseInt(i.id) === parseInt(ingId)) : null;
    const preLabel = preRaw ? formatDatalistLabel(preRaw) : '';
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="ps-2">
            <input type="text" list="invRawItemsDatalist" class="form-control form-control-sm rounded-3 recipe-ing-input" placeholder="Type code or raw ingredient..." value="${escapeInvHtml(preLabel)}" autocomplete="off" oninput="validateRawDatalistInput(this)">
        </td>
        <td>
            <input type="number" step="0.0001" min="0.0001" class="form-control form-control-sm rounded-3 fw-bold recipe-qty-input" value="${qty}">
        </td>
        <td class="text-end pe-2">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
}

function addProductAddonRow(addonName = '', ingId = '', qty = 15, price = 20) {
    const tbody = document.getElementById('productAddonsBody');
    if (!tbody) return;
    const preRaw = ingId ? INV_ITEMS.find(i => parseInt(i.id) === parseInt(ingId)) : null;
    const preLabel = preRaw ? formatDatalistLabel(preRaw) : '';
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="ps-2">
            <input type="text" list="invAddonNamesPresetList" class="form-control form-control-sm rounded-3 addon-name-input" placeholder="e.g. Extra Espresso Shot" value="${escapeInvHtml(addonName)}" autocomplete="off">
        </td>
        <td>
            <input type="text" list="invRawItemsDatalist" class="form-control form-control-sm rounded-3 addon-ing-input" placeholder="Select raw ingredient..." value="${escapeInvHtml(preLabel)}" autocomplete="off" oninput="validateRawDatalistInput(this)">
        </td>
        <td>
            <input type="number" step="0.0001" min="0.0001" class="form-control form-control-sm rounded-3 fw-bold addon-qty-input" value="${qty}">
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm rounded-3 addon-price-input" value="${price}">
        </td>
        <td class="text-end pe-2">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
}

function populateDefaultDrinkAddons() {
    const tbody = document.getElementById('productAddonsBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const defaults = [
        ['Extra Espresso Shot', 'RM-BEAN-G', 18, 25],
        ['Oat Milk Substitute', 'RM-OAT-ML', 150, 30],
        ['Vanilla Syrup Pump', 'RM-VANILLA-ML', 15, 20],
        ['Golden Caramel Drizzle', 'RM-CARAMEL-ML', 15, 15],
        ['Brown Sugar Pearl Jelly', 'RM-PEARL-G', 30, 15],
    ];
    defaults.forEach(([name, code, q, p]) => {
        const raw = INV_ITEMS.find(i => i.item_code === code);
        addProductAddonRow(name, raw ? raw.id : '', q, p);
    });
}

function validateRawDatalistInput(inputEl) {
    const val = (inputEl?.value || '').trim();
    if (!val) {
        inputEl.classList.remove('is-valid', 'is-invalid');
        return;
    }
    const matched = resolveRawDatalistItem(val);
    inputEl.classList.toggle('is-valid', !!matched);
    inputEl.classList.toggle('is-invalid', !matched);
}

async function saveRecipeLines() {
    const fid = parseInt(document.getElementById('recipeFinishedItemId').value);
    const lines = [];
    const recRows = Array.from(document.querySelectorAll('#recipeLinesBody tr'));
    for (let i = 0; i < recRows.length; i++) {
        const tr = recRows[i];
        const inp = tr.querySelector('.recipe-ing-input');
        const rawVal = (inp?.value || '').trim();
        const qty = parseFloat(tr.querySelector('.recipe-qty-input')?.value || 0);
        if (!rawVal) continue;
        const matched = resolveRawDatalistItem(rawVal);
        if (!matched) {
            inp?.classList.add('is-invalid');
            inp?.focus();
            showToast(`Recipe Line #${i + 1}: "${rawVal}" is not a valid Raw Material from the datalist.`, 'danger');
            return;
        }
        if (qty > 0) lines.push({ ingredient_item_id: parseInt(matched.id), qty_per_unit: qty });
    }

    const addons = [];
    const addonRows = Array.from(document.querySelectorAll('#productAddonsBody tr'));
    for (let i = 0; i < addonRows.length; i++) {
        const tr = addonRows[i];
        const addonName = (tr.querySelector('.addon-name-input')?.value || '').trim();
        const ingInp = tr.querySelector('.addon-ing-input');
        const rawVal = (ingInp?.value || '').trim();
        const qty = parseFloat(tr.querySelector('.addon-qty-input')?.value || 0);
        const price = parseFloat(tr.querySelector('.addon-price-input')?.value || 0);

        if (!addonName && !rawVal) continue;
        if (!addonName) {
            tr.querySelector('.addon-name-input')?.focus();
            showToast(`Add-on Line #${i + 1}: Please enter an Add-on Name.`, 'danger');
            return;
        }
        const matched = resolveRawDatalistItem(rawVal);
        if (!matched) {
            ingInp?.classList.add('is-invalid');
            ingInp?.focus();
            showToast(`Add-on "${addonName}": "${rawVal || 'Empty'}" is not a valid Raw Material from the datalist.`, 'danger');
            return;
        }
        if (qty > 0) {
            addons.push({
                addon_name: addonName,
                ingredient_item_id: parseInt(matched.id),
                qty_per_unit: qty,
                price: price
            });
        }
    }

    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'save_recipe', finished_item_id: fid, lines, addons })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('invRecipeModal'))?.hide();
        loadInventorySuite();
    } else {
        showToast(data.message || 'Error saving recipe & add-ons', 'danger');
    }
}

// ---------------------------------------------------------------------
// 2. STOCK IN & STOCK OUT MODULE
// ---------------------------------------------------------------------
function switchStockTxnList(type) {
    INV_CURRENT_STOCK_TYPE = type;
    document.getElementById('btnFilterStockIn')?.classList.toggle('active', type === 'IN');
    document.getElementById('btnFilterStockOut')?.classList.toggle('active', type === 'OUT');
    loadStockTransactions();
}

async function loadStockTransactions() {
    const tbody = document.getElementById('invStockTxnTableBody');
    if (!tbody) return;
    const res = await fetch(`api/inventory.php?action=stock_txns&type=${INV_CURRENT_STOCK_TYPE}`);
    const data = await res.json();
    const txns = data.transactions || [];
    if (txns.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted small">No Stock ${INV_CURRENT_STOCK_TYPE} vouchers recorded yet.</td></tr>`;
        return;
    }
    tbody.innerHTML = txns.map(t => {
        const badge = t.status === 'Posted'
            ? '<span class="badge bg-success">Posted</span>'
            : (t.status === 'Draft' ? '<span class="badge bg-warning text-dark">Draft</span>' : '<span class="badge bg-secondary">Cancelled</span>');
        const actions = t.status === 'Draft'
            ? `<button class="btn btn-success btn-sm rounded-pill px-2.5 py-0.5 fw-bold me-1" onclick="postStockVoucher(${t.id})"><i class="bi bi-check2-circle me-1"></i>Post</button>
               <button class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0.5" onclick="cancelStockVoucher(${t.id})">Cancel</button>`
            : `<span class="text-muted small"><i class="bi bi-lock-fill me-1"></i>Ledger Posted</span>`;

        return `
            <tr>
                <td class="fw-mono fw-bold text-navy">${escapeInvHtml(t.txn_no)}</td>
                <td class="small">${escapeInvHtml(t.txn_date)}</td>
                <td><span class="badge ${t.txn_type === 'IN' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} border">STOCK ${t.txn_type}</span></td>
                <td class="fw-semibold">${escapeInvHtml(t.party || '—')}</td>
                <td class="fw-mono small text-muted">${escapeInvHtml(t.reference_no || '—')}</td>
                <td class="text-center"><span class="badge bg-light text-dark border">${t.line_count}</span></td>
                <td class="text-end fw-mono">₱${Number(t.total_cost || 0).toFixed(2)}</td>
                <td>${badge}</td>
                <td class="small text-secondary">${escapeInvHtml(t.posted_by || t.created_by || '—')}</td>
                <td class="text-end">${actions}</td>
            </tr>
        `;
    }).join('');
}

function openStockTxnModal(type = 'IN', preselectItemId = '') {
    document.getElementById('invStockTxnForm').reset();
    document.getElementById('stockTxnType').value = type;
    document.getElementById('stockTxnDate').value = new Date().toISOString().slice(0, 10);
    const isOut = type === 'OUT';
    document.getElementById('stockTxnModalTitle').innerHTML = isOut
        ? '<i class="bi bi-box-arrow-up text-warning me-2"></i>New Stock Out Voucher (-1 Ledger)'
        : '<i class="bi bi-box-arrow-in-down text-warning me-2"></i>New Stock In (+1 Ledger)';
    document.getElementById('stockTxnModalSubtitle').textContent = isOut
        ? 'Deduct spoilage, expired items, or internal withdrawals (validates available stock)'
        : 'Direct stock replenishment into raw materials or finished product stock';

    const partyWrap = document.getElementById('stockTxnPartyWrap');
    const partyInput = document.getElementById('stockTxnParty');
    const refNoInput = document.getElementById('stockTxnRefNo');

    if (refNoInput) refNoInput.value = '';

    if (isOut) {
        if (partyWrap) partyWrap.classList.remove('d-none');
        if (partyInput) {
            partyInput.required = true;
            partyInput.value = '';
            partyInput.placeholder = 'e.g. Expired / Spoiled Batch or Damaged Container';
        }
    } else {
        if (partyWrap) partyWrap.classList.add('d-none');
        if (partyInput) {
            partyInput.required = false;
            partyInput.value = '';
        }
    }

    const tbody = document.getElementById('stockTxnLinesBody');
    tbody.innerHTML = '';
    addStockTxnLineRow(preselectItemId);
    new bootstrap.Modal(document.getElementById('invStockTxnModal')).show();
}

function addStockTxnLineRow(preselectId = '') {
    const tbody = document.getElementById('stockTxnLinesBody');
    const preItem = preselectId ? INV_ITEMS.find(i => parseInt(i.id) === parseInt(preselectId)) : null;
    const preLabel = preItem ? formatDatalistLabel(preItem) : '';
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="ps-2">
            <input type="text" list="invMasterItemsDatalist" class="form-control form-control-sm rounded-3 stxn-item-input" placeholder="Type code or item name (select from list)..." value="${escapeInvHtml(preLabel)}" autocomplete="off" required oninput="onStockTxnItemChange(this)" onchange="onStockTxnItemChange(this)">
            <input type="hidden" class="stxn-item" value="${preItem ? preItem.id : ''}">
            <div class="small mt-0.5 stxn-item-hint text-muted" style="font-size: 0.7rem;">Pick from Master List datalist</div>
        </td>
        <td><input type="number" step="0.0001" min="0.0001" class="form-control form-control-sm rounded-3 fw-bold stxn-qty" value="1" required></td>
        <td><input type="number" step="0.0001" min="0" class="form-control form-control-sm rounded-3 stxn-cost" value="0"></td>
        <td><input type="text" class="form-control form-control-sm rounded-3 stxn-note" placeholder="Note"></td>
        <td class="text-end pe-2">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="if(document.querySelectorAll('#stockTxnLinesBody tr').length>1)this.closest('tr').remove()"><i class="bi bi-trash"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    if (preItem) onStockTxnItemChange(tr.querySelector('.stxn-item-input'));
}

function onStockTxnItemChange(inputEl) {
    if (!inputEl) return;
    const tr = inputEl.closest('tr');
    const hiddenId = tr.querySelector('.stxn-item');
    const costInput = tr.querySelector('.stxn-cost');
    const hintEl = tr.querySelector('.stxn-item-hint');
    const rawVal = inputEl.value.trim();

    if (!rawVal) {
        if (hiddenId) hiddenId.value = '';
        inputEl.classList.remove('is-valid', 'is-invalid');
        if (hintEl) hintEl.innerHTML = `<span class="text-muted">Pick from Master List datalist</span>`;
        return;
    }

    const matched = resolveDatalistItem(rawVal);
    if (matched) {
        const prevId = hiddenId ? hiddenId.value : '';
        if (hiddenId) hiddenId.value = matched.id;
        inputEl.classList.remove('is-invalid');
        inputEl.classList.add('is-valid');
        if (costInput && String(prevId) !== String(matched.id)) {
            costInput.value = parseFloat(matched.unit_cost || 0);
        }
        if (hintEl) {
            hintEl.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>[${matched.item_type.toUpperCase()}] Avail: ${fmtQty(matched.current_stock)} ${escapeInvHtml(matched.unit)}</span>`;
        }
    } else {
        if (hiddenId) hiddenId.value = '';
        inputEl.classList.remove('is-valid');
        inputEl.classList.add('is-invalid');
        if (hintEl) {
            hintEl.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Invalid item — select a valid item from the datalist</span>`;
        }
    }
}

async function submitStockTxn(e) {
    e.preventDefault();
    const type = document.getElementById('stockTxnType').value;
    const isPost = document.getElementById('stockTxnPostFlag').value === '1';
    const rows = Array.from(document.querySelectorAll('#stockTxnLinesBody tr'));
    const lines = [];

    for (let idx = 0; idx < rows.length; idx++) {
        const tr = rows[idx];
        const inputEl = tr.querySelector('.stxn-item-input');
        const rawVal = (inputEl?.value || '').trim();
        const matched = resolveDatalistItem(rawVal);

        // Strict datalist validation upon submission
        if (!matched) {
            if (inputEl) {
                inputEl.classList.add('is-invalid');
                inputEl.focus();
            }
            showToast(`Line #${idx + 1}: "${rawVal || 'Empty'}" is not a valid Master List item. Please select an item from the datalist.`, 'danger');
            return;
        }

        const quantity = parseFloat(tr.querySelector('.stxn-qty')?.value || 0);
        const unit_cost = parseFloat(tr.querySelector('.stxn-cost')?.value || 0);
        const remarks = tr.querySelector('.stxn-note')?.value || '';

        if (quantity <= 0) {
            showToast(`Line #${idx + 1}: Quantity for "${matched.name}" must be greater than zero.`, 'danger');
            return;
        }

        if (type === 'OUT' && isPost && parseFloat(matched.current_stock || 0) < quantity) {
            showToast(`Line #${idx + 1}: Insufficient stock for "${matched.name}". Available: ${fmtQty(matched.current_stock)} ${matched.unit} (Need ${fmtQty(quantity)}).`, 'danger');
            return;
        }

        lines.push({ item_id: parseInt(matched.id), quantity, unit_cost, remarks });
    }

    const payload = {
        action: 'save_stock_txn',
        txn_type: type,
        txn_date: document.getElementById('stockTxnDate').value,
        party: document.getElementById('stockTxnParty').value.trim(),
        reference_no: document.getElementById('stockTxnRefNo').value.trim(),
        remarks: document.getElementById('stockTxnRemarks').value.trim(),
        post: isPost,
        lines
    };

    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('invStockTxnModal'))?.hide();
        await loadInventorySuite();
        switchStockTxnList(type);
    } else {
        showToast(data.message || 'Stock transaction failed', 'danger');
    }
}

async function postStockVoucher(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'post_stock_txn', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        await loadInventorySuite();
        loadStockTransactions();
    } else {
        showToast(data.message || 'Failed to post voucher', 'danger');
    }
}

async function cancelStockVoucher(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'cancel_stock_txn', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'info');
        loadStockTransactions();
    } else {
        showToast(data.message, 'danger');
    }
}

// ---------------------------------------------------------------------
// 3. ITEM CONVERSION MODULE (Packs -> Pieces, KG -> G, etc.)
// ---------------------------------------------------------------------
function applyConvPreset(srcCode, dstCode, srcQty, factor) {
    const srcItem = INV_ITEMS.find(i => i.item_code === srcCode);
    const dstItem = INV_ITEMS.find(i => i.item_code === dstCode);
    const srcInput = document.getElementById('convSourceItemInput');
    const dstInput = document.getElementById('convDestItemInput');
    if (srcItem && srcInput) srcInput.value = formatDatalistLabel(srcItem);
    if (dstItem && dstInput) dstInput.value = formatDatalistLabel(dstItem);
    document.getElementById('convSourceQty').value = srcQty;
    document.getElementById('convFactor').value = factor;
    updateConversionPreview();
}

function updateConversionPreview() {
    const srcInput = document.getElementById('convSourceItemInput');
    const dstInput = document.getElementById('convDestItemInput');
    const srcHidden = document.getElementById('convSourceItem');
    const dstHidden = document.getElementById('convDestItem');
    const sq = parseFloat(document.getElementById('convSourceQty')?.value || 0);
    const factor = parseFloat(document.getElementById('convFactor')?.value || 0);
    const preview = document.getElementById('convPreviewText');
    const hint = document.getElementById('convSourceStockHint');
    const dstHint = document.getElementById('convDestStockHint');
    const btn = document.getElementById('btnPostConversion');

    const srcVal = (srcInput?.value || '').trim();
    const dstVal = (dstInput?.value || '').trim();
    const src = resolveDatalistItem(srcVal);
    const dst = resolveDatalistItem(dstVal);

    if (srcHidden) srcHidden.value = src ? src.id : '';
    if (dstHidden) dstHidden.value = dst ? dst.id : '';

    // Validate Source Input against Datalist
    if (!srcVal) {
        srcInput?.classList.remove('is-valid', 'is-invalid');
        if (hint) hint.innerHTML = 'Type or pick a stocked item from the datalist.';
    } else if (!src) {
        srcInput?.classList.remove('is-valid');
        srcInput?.classList.add('is-invalid');
        if (hint) hint.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Not in Master List — please select a valid Source Item from the datalist.</span>`;
    } else {
        srcInput?.classList.remove('is-invalid');
        srcInput?.classList.add('is-valid');
    }

    // Validate Destination Input against Datalist
    if (!dstVal) {
        dstInput?.classList.remove('is-valid', 'is-invalid');
        if (dstHint) dstHint.innerHTML = 'Type or pick the target item from the datalist.';
    } else if (!dst) {
        dstInput?.classList.remove('is-valid');
        dstInput?.classList.add('is-invalid');
        if (dstHint) dstHint.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Not in Master List — please select a valid Target Item from the datalist.</span>`;
    } else {
        dstInput?.classList.remove('is-invalid');
        dstInput?.classList.add('is-valid');
        if (dstHint) dstHint.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Current Target Stock: ${fmtQty(dst.current_stock)} ${escapeInvHtml(dst.unit)}</span>`;
    }

    if (!src) {
        if (preview) preview.innerHTML = 'Select valid source and destination items from the datalist above.';
        if (btn) btn.disabled = !!srcVal;
        return;
    }

    const avail = parseFloat(src.current_stock || 0);
    const enough = avail >= sq && sq > 0;
    if (hint) {
        hint.innerHTML = enough
            ? `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Available: ${fmtQty(avail)} ${escapeInvHtml(src.unit)}</span>`
            : `<span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i>Insufficient stock! Available: ${fmtQty(avail)} ${escapeInvHtml(src.unit)} (Need ${fmtQty(sq)})</span>`;
    }

    const validPair = !!(src && dst && parseInt(src.id) !== parseInt(dst.id));
    if (btn) btn.disabled = !(enough && (!dstVal || validPair));

    if (src && dst && sq > 0 && factor > 0) {
        const outQty = sq * factor;
        if (preview) {
            preview.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-danger fw-bold fw-mono">-${fmtQty(sq)} ${escapeInvHtml(src.unit)} (${escapeInvHtml(src.name)})</span>
                    <i class="bi bi-arrow-right-circle-fill text-navy fs-5"></i>
                    <span class="text-success fw-bold fw-mono">+${fmtQty(outQty)} ${escapeInvHtml(dst.unit)} (${escapeInvHtml(dst.name)})</span>
                </div>
            `;
        }
    }
}

async function loadConversions() {
    updateConversionPreview();
    const tbody = document.getElementById('invConversionsTableBody');
    if (!tbody) return;
    const res = await fetch('api/inventory.php?action=conversions');
    const data = await res.json();
    const rows = data.conversions || [];
    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted small">No item conversions recorded yet.</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.map(c => {
        const badge = c.status === 'Posted'
            ? '<span class="badge bg-success">Posted</span>'
            : (c.status === 'Draft' ? '<span class="badge bg-warning text-dark">Draft</span>' : '<span class="badge bg-secondary">Cancelled</span>');
        const act = c.status === 'Draft'
            ? `<button class="btn btn-success btn-sm rounded-pill px-2.5 py-0.5 fw-bold me-1" onclick="postConversionDoc(${c.id})">Post</button>
               <button class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0.5" onclick="cancelConversionDoc(${c.id})">Cancel</button>`
            : `<span class="text-muted small"><i class="bi bi-check2-all"></i> Posted</span>`;
        return `
            <tr>
                <td class="fw-mono fw-bold text-navy small">${escapeInvHtml(c.conv_no)}</td>
                <td class="small">${escapeInvHtml(c.conv_date)}</td>
                <td class="text-danger fw-mono small">-${fmtQty(c.source_qty)} ${escapeInvHtml(c.source_unit)} <span class="text-dark fw-semibold">${escapeInvHtml(c.source_name)}</span></td>
                <td class="text-success fw-mono small">+${fmtQty(c.dest_qty)} ${escapeInvHtml(c.dest_unit)} <span class="text-dark fw-semibold">${escapeInvHtml(c.dest_name)}</span></td>
                <td>${badge}</td>
                <td class="text-end">${act}</td>
            </tr>
        `;
    }).join('');
}

async function submitConversion(e) {
    e.preventDefault();
    const srcInput = document.getElementById('convSourceItemInput');
    const dstInput = document.getElementById('convDestItemInput');
    const src = resolveDatalistItem(srcInput?.value || '');
    const dst = resolveDatalistItem(dstInput?.value || '');

    // Strict datalist validation upon submission
    if (!src) {
        if (srcInput) {
            srcInput.classList.add('is-invalid');
            srcInput.focus();
        }
        showToast('Invalid Source Item! Please select a valid item from the Master List datalist.', 'danger');
        return;
    }

    if (!dst) {
        if (dstInput) {
            dstInput.classList.add('is-invalid');
            dstInput.focus();
        }
        showToast('Invalid Destination Item! Please select a valid target item from the Master List datalist.', 'danger');
        return;
    }

    if (parseInt(src.id) === parseInt(dst.id)) {
        if (dstInput) {
            dstInput.classList.add('is-invalid');
            dstInput.focus();
        }
        showToast('Source and Destination items must be two different items from the datalist.', 'danger');
        return;
    }

    const sq = parseFloat(document.getElementById('convSourceQty').value || 0);
    const factor = parseFloat(document.getElementById('convFactor').value || 0);
    const isPost = document.getElementById('convPostFlag').value === '1';

    if (isPost && parseFloat(src.current_stock || 0) < sq) {
        showToast(`Insufficient stock for "${src.name}". Available: ${fmtQty(src.current_stock)} ${src.unit} (Need ${fmtQty(sq)}).`, 'danger');
        return;
    }

    const payload = {
        action: 'save_conversion',
        source_item_id: parseInt(src.id),
        dest_item_id: parseInt(dst.id),
        source_qty: sq,
        factor: factor,
        remarks: document.getElementById('convRemarks').value.trim(),
        post: isPost
    };
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        if (srcInput) { srcInput.value = ''; srcInput.classList.remove('is-valid', 'is-invalid'); }
        if (dstInput) { dstInput.value = ''; dstInput.classList.remove('is-valid', 'is-invalid'); }
        document.getElementById('convRemarks').value = '';
        await loadInventorySuite();
        loadConversions();
    } else {
        showToast(data.message || 'Conversion failed', 'danger');
    }
}

async function postConversionDoc(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'post_conversion', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        await loadInventorySuite();
        loadConversions();
    } else {
        showToast(data.message, 'danger');
    }
}

async function cancelConversionDoc(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'cancel_conversion', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'info');
        loadConversions();
    } else {
        showToast(data.message, 'danger');
    }
}

// ---------------------------------------------------------------------
// 4. BREWING / PRODUCTION MODULE (-Ingredients, +Finished Product)
// ---------------------------------------------------------------------
function quickSelectBrew(finishedItemId) {
    switchToInvTab('inv-subtab-brew');
    const sel = document.getElementById('brewFinishedItem');
    if (sel) {
        sel.value = finishedItemId;
        checkBrewIngredients();
    }
}

async function checkBrewIngredients() {
    const fid = parseInt(document.getElementById('brewFinishedItem')?.value || 0);
    const qty = parseFloat(document.getElementById('brewQty')?.value || 1);
    const tbody = document.getElementById('brewReqTableBody');
    const maxBadge = document.getElementById('brewMaxBadge');
    const btn = document.getElementById('btnPostBrew');

    if (!fid) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-muted">Select a finished product above to check ingredient availability.</td></tr>`;
        maxBadge.textContent = 'Select a product';
        if (btn) btn.disabled = false;
        return;
    }

    const res = await fetch(`api/inventory.php?action=brew_check&item_id=${fid}&qty=${qty}`);
    const data = await res.json();
    const reqs = data.requirements || [];

    if (!data.has_recipe || reqs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-3 text-danger fw-semibold">No recipe configured for this item. Configure its recipe in Tab 1 or use Stock In for ready-made items.</td></tr>`;
        maxBadge.className = 'badge bg-danger';
        maxBadge.textContent = 'No Recipe Defined';
        if (btn) btn.disabled = true;
        return;
    }

    let allSufficient = true;
    tbody.innerHTML = reqs.map(r => {
        if (!r.sufficient) allSufficient = false;
        return `
            <tr>
                <td class="ps-3 fw-semibold text-dark">${escapeInvHtml(r.name)}</td>
                <td class="text-end fw-mono text-muted">${fmtQty(r.qty_per_unit)} ${escapeInvHtml(r.unit)}</td>
                <td class="text-end fw-mono fw-bold text-navy">${fmtQty(r.required)} ${escapeInvHtml(r.unit)}</td>
                <td class="text-end fw-mono ${r.sufficient ? 'text-success' : 'text-danger fw-bold'}">${fmtQty(r.available)} ${escapeInvHtml(r.unit)}</td>
                <td class="text-center pe-3">
                    ${r.sufficient
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle">OK</span>'
                        : '<span class="badge bg-danger">Shortage</span>'}
                </td>
            </tr>
        `;
    }).join('');

    maxBadge.className = data.max_brewable > 0 ? 'badge bg-success-subtle text-success border' : 'badge bg-danger';
    maxBadge.textContent = `Max Brewable Now: ${data.max_brewable} unit(s)`;
    if (btn) btn.disabled = !allSufficient;
}

async function loadBrews() {
    checkBrewIngredients();
    const tbody = document.getElementById('invBrewsTableBody');
    if (!tbody) return;
    const res = await fetch('api/inventory.php?action=brews');
    const data = await res.json();
    const rows = data.brews || [];
    if (rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted small">No brewing batches recorded yet.</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.map(b => {
        const badge = b.status === 'Posted'
            ? '<span class="badge bg-success">Posted</span>'
            : (b.status === 'Draft' ? '<span class="badge bg-warning text-dark">Draft</span>' : '<span class="badge bg-secondary">Cancelled</span>');
        const act = b.status === 'Draft'
            ? `<button class="btn btn-success btn-sm rounded-pill px-2.5 py-0.5 fw-bold me-1" onclick="postBrewDoc(${b.id})">Brew & Post</button>
               <button class="btn btn-outline-danger btn-sm rounded-pill px-2 py-0.5" onclick="cancelBrewDoc(${b.id})">Cancel</button>`
            : `<span class="text-muted small"><i class="bi bi-check2-all"></i> Posted</span>`;
        return `
            <tr>
                <td class="fw-mono fw-bold text-navy small">${escapeInvHtml(b.batch_no)}</td>
                <td class="small">${escapeInvHtml(b.batch_date)}</td>
                <td class="fw-bold text-dark">${escapeInvHtml(b.item_name)}</td>
                <td class="text-end fw-mono fw-bold text-success">+${fmtQty(b.quantity)} ${escapeInvHtml(b.unit)}</td>
                <td>${badge}</td>
                <td class="text-end">${act}</td>
            </tr>
        `;
    }).join('');
}

async function submitBrewBatch(e) {
    e.preventDefault();
    const payload = {
        action: 'save_brew',
        finished_item_id: parseInt(document.getElementById('brewFinishedItem').value || 0),
        quantity: parseFloat(document.getElementById('brewQty').value || 0),
        remarks: document.getElementById('brewRemarks').value.trim(),
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
        await loadInventorySuite();
        loadBrews();
    } else {
        showToast(data.message || 'Brewing failed', 'danger');
    }
}

async function postBrewDoc(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'post_brew', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        await loadInventorySuite();
        loadBrews();
    } else {
        showToast(data.message, 'danger');
    }
}

async function cancelBrewDoc(id) {
    const res = await fetch('api/inventory.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'cancel_brew', id })
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'info');
        loadBrews();
    } else {
        showToast(data.message, 'danger');
    }
}

// ---------------------------------------------------------------------
// 5. ACCOUNTING INVENTORY LEDGER (Separate Perpetual Table)
// ---------------------------------------------------------------------
function viewItemStockCard(itemId) {
    switchToInvTab('inv-subtab-ledger');
    const sel = document.getElementById('ledgerItemFilter');
    if (sel) {
        sel.value = itemId;
        loadInventoryLedger();
    }
}

async function loadInventoryLedger() {
    const tbody = document.getElementById('invLedgerTableBody');
    if (!tbody) return;
    const itemId = document.getElementById('ledgerItemFilter')?.value || 0;
    const type = document.getElementById('ledgerTypeFilter')?.value || '';
    const from = document.getElementById('ledgerFromDate')?.value || '';
    const to = document.getElementById('ledgerToDate')?.value || '';

    const res = await fetch(`api/inventory.php?action=ledger&item_id=${encodeURIComponent(itemId)}&txn_type=${encodeURIComponent(type)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
    const data = await res.json();
    const entries = data.entries || [];

    document.getElementById('ledgerOpeningVal').textContent = data.opening !== null && data.opening !== undefined ? fmtQty(data.opening) : '— (Select 1 Item)';
    document.getElementById('ledgerTotalInVal').textContent = '+' + fmtQty(data.total_in || 0);
    document.getElementById('ledgerTotalOutVal').textContent = '-' + fmtQty(data.total_out || 0);
    document.getElementById('ledgerClosingVal').textContent = data.closing !== null && data.closing !== undefined ? fmtQty(data.closing) : '— (Select 1 Item)';

    if (entries.length === 0) {
        tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-muted">No posted ledger entries match the selected filter criteria.</td></tr>`;
        return;
    }

    const badgeMap = {
        'STOCK_IN':  'bg-success',
        'STOCK_OUT': 'bg-danger',
        'CONV_OUT':  'bg-warning text-dark',
        'CONV_IN':   'bg-info text-dark',
        'BREW_OUT':  'bg-secondary',
        'BREW_IN':   'bg-primary',
        'POS_SALE':  'bg-dark',
        'RES_SALE':  'bg-dark'
    };

    tbody.innerHTML = entries.map(e => {
        const qIn = parseFloat(e.qty_in || 0);
        const qOut = parseFloat(e.qty_out || 0);
        const bal = parseFloat(e.balance_after || 0);
        const bCls = badgeMap[e.txn_type] || 'bg-secondary';

        return `
            <tr>
                <td class="fw-mono text-muted">#${e.id}</td>
                <td class="fw-mono">${escapeInvHtml(e.entry_date)}</td>
                <td class="fw-mono fw-bold text-navy">${escapeInvHtml(e.ref_no)}</td>
                <td><span class="badge ${bCls}">${escapeInvHtml(e.txn_type)}</span></td>
                <td>
                    <strong class="text-dark">${escapeInvHtml(e.item_name)}</strong>
                    <span class="text-muted fw-mono ms-1">(${escapeInvHtml(e.item_code)})</span>
                </td>
                <td class="text-secondary">${escapeInvHtml(e.particulars || '—')}</td>
                <td class="text-end fw-mono fw-bold text-success">${qIn > 0 ? '+' + fmtQty(qIn) + ' ' + escapeInvHtml(e.unit) : '—'}</td>
                <td class="text-end fw-mono fw-bold text-danger">${qOut > 0 ? '-' + fmtQty(qOut) + ' ' + escapeInvHtml(e.unit) : '—'}</td>
                <td class="text-end fw-mono fw-black text-navy bg-light">${fmtQty(bal)} ${escapeInvHtml(e.unit)}</td>
                <td class="text-end fw-mono text-muted">₱${Number(e.unit_cost || 0).toFixed(2)}</td>
                <td class="text-secondary">${escapeInvHtml(e.posted_by || 'System')}</td>
            </tr>
        `;
    }).join('');
}

// =====================================================================
// FINISHED PRODUCT YIELD CALCULATOR JAVASCRIPT ENGINE
// =====================================================================
let CURRENT_YIELD_PRODUCTS = [];
let SELECTED_YIELD_ITEM = null;
let CUSTOM_SIM_STOCKS = {};

async function loadYieldCalculatorDashboard() {
    const tbody = document.getElementById('yieldTableBody');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Calculating finished product yields from ingredient inventory...
            </td>
        </tr>
    `;

    try {
        const res = await fetch('api/inventory.php?action=yield_calculator');
        const data = await res.json();
        if (!data.success) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${escapeInvHtml(data.message || 'Error loading yield calculations')}</td></tr>`;
            return;
        }

        CURRENT_YIELD_PRODUCTS = data.products || [];

        // Update KPIs
        const total = CURRENT_YIELD_PRODUCTS.length;
        const ready = CURRENT_YIELD_PRODUCTS.filter(p => p.has_recipe && p.max_yield > 0).length;
        const blocked = CURRENT_YIELD_PRODUCTS.filter(p => !p.has_recipe || p.max_yield === 0).length;
        const totalMaxUnits = CURRENT_YIELD_PRODUCTS.reduce((acc, p) => acc + (p.max_yield || 0), 0);

        if (document.getElementById('yieldKpiTotal')) document.getElementById('yieldKpiTotal').textContent = total;
        if (document.getElementById('yieldKpiReady')) document.getElementById('yieldKpiReady').textContent = ready;
        if (document.getElementById('yieldKpiBlocked')) document.getElementById('yieldKpiBlocked').textContent = blocked;
        if (document.getElementById('yieldKpiMaxUnits')) document.getElementById('yieldKpiMaxUnits').textContent = totalMaxUnits + ' units';

        // Populate Category Filter
        const catSelect = document.getElementById('yieldCategoryFilter');
        if (catSelect) {
            const categories = [...new Set(CURRENT_YIELD_PRODUCTS.map(p => p.category_name).filter(Boolean))];
            const currentCat = catSelect.value;
            catSelect.innerHTML = '<option value="">All Categories</option>' + 
                categories.map(c => `<option value="${escapeInvHtml(c)}"${c === currentCat ? ' selected' : ''}>${escapeInvHtml(c)}</option>`).join('');
        }

        renderYieldTable(CURRENT_YIELD_PRODUCTS);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Network error connecting to yield engine: ${escapeInvHtml(err.message)}</td></tr>`;
    }
}

function renderYieldTable(products) {
    const tbody = document.getElementById('yieldTableBody');
    const countEl = document.getElementById('yieldRecordCount');
    if (!tbody) return;

    if (countEl) countEl.textContent = `${products.length} product${products.length === 1 ? '' : 's'}`;

    if (!products.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 text-secondary d-block mb-2"></i>
                    No finished products found matching the criteria.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = products.map(p => {
        const hasRecipe = p.has_recipe && p.recipe_count > 0;
        const maxYield = p.max_yield || 0;

        let yieldBadge = '';
        if (!hasRecipe) {
            yieldBadge = `<span class="badge bg-secondary-subtle text-secondary border small">No Recipe Set</span>`;
        } else if (maxYield >= 10) {
            yieldBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold fs-6">${maxYield} ${escapeInvHtml(p.unit)}</span>`;
        } else if (maxYield > 0) {
            yieldBadge = `<span class="badge text-white px-2.5 py-1 fw-bold fs-6" style="background-color: var(--brand-secondary);">${maxYield} ${escapeInvHtml(p.unit)}</span>`;
        } else {
            yieldBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-bold fs-6"><i class="bi bi-x-circle me-1"></i>0 ${escapeInvHtml(p.unit)}</span>`;
        }

        let bottleneckHtml = '';
        if (p.bottleneck) {
            const b = p.bottleneck;
            const isZero = (b.available_stock <= 0);
            bottleneckHtml = `
                <div class="d-flex align-items-center gap-1.5">
                    <i class="bi bi-circle-fill ${isZero ? 'text-danger' : 'text-warning'}" style="font-size: 0.5rem;"></i>
                    <strong class="${isZero ? 'text-danger' : 'text-dark'}">${escapeInvHtml(b.name)}</strong>
                </div>
                <div class="text-muted" style="font-size: 0.72rem;">
                    Stock: <span class="fw-bold">${b.available_stock} ${escapeInvHtml(b.unit)}</span> • Req: ${b.qty_per_unit} / unit
                </div>
            `;
        } else if (!hasRecipe) {
            bottleneckHtml = `<span class="text-muted fst-italic">BOM formula required</span>`;
        } else {
            bottleneckHtml = `<span class="text-success"><i class="bi bi-check2-all me-1"></i>Sufficient stock</span>`;
        }

        const cost = Number(p.cost_per_unit || 0).toFixed(2);
        const price = Number(p.selling_price || 0).toFixed(2);
        const margin = p.profit_margin_pct || 0;

        const yieldPlaceholder = 'assets/images/placeholder-product.svg';
        const yieldImgSrc = (p.image_url && String(p.image_url).trim() !== '') ? p.image_url : yieldPlaceholder;

        return `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 overflow-hidden bg-light border shadow-xs flex-shrink-0" style="width: 38px; height: 38px;">
                            <img src="${escapeInvHtml(yieldImgSrc)}" 
                                 alt="${escapeInvHtml(p.name)}" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.onerror=null; this.src='${yieldPlaceholder}';">
                        </div>
                        <div>
                            <div class="fw-bold text-dark">${escapeInvHtml(p.name)}</div>
                            <span class="text-muted fw-mono" style="font-size: 0.72rem;">${escapeInvHtml(p.item_code)}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge rounded-pill px-2 py-1" style="background-color: var(--brand-surface); color: var(--brand-text-primary); font-size: 0.72rem;">
                        ${escapeInvHtml(p.category_name || 'Bakery & Café')}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">
                        ${p.recipe_count} ingredient${p.recipe_count === 1 ? '' : 's'}
                    </span>
                </td>
                <td class="text-center fw-mono">
                    ${Number(p.current_finished_stock || 0).toFixed(0)} <span class="text-muted small">${escapeInvHtml(p.unit)}</span>
                </td>
                <td class="text-center">
                    ${yieldBadge}
                </td>
                <td>
                    ${bottleneckHtml}
                </td>
                <td class="text-end">
                    <div class="fw-bold text-navy">₱${price}</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Cost: ₱${cost} (${margin}%)</div>
                </td>
                <td class="text-center pe-4">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold shadow-xs" onclick="openYieldProductDetail(${p.item_id})" title="Analyze Recipe BOM & Simulate Yield">
                            <i class="bi bi-sliders me-1"></i> Simulate
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function filterYieldTable() {
    const q = (document.getElementById('yieldSearchInput')?.value || '').toLowerCase().trim();
    const cat = document.getElementById('yieldCategoryFilter')?.value || '';
    const status = document.getElementById('yieldStatusFilter')?.value || 'all';

    let filtered = CURRENT_YIELD_PRODUCTS.slice();

    if (q) {
        filtered = filtered.filter(p => 
            (p.name || '').toLowerCase().includes(q) || 
            (p.item_code || '').toLowerCase().includes(q)
        );
    }

    if (cat) {
        filtered = filtered.filter(p => (p.category_name || '') === cat);
    }

    if (status === 'ready') {
        filtered = filtered.filter(p => p.has_recipe && p.max_yield > 0);
    } else if (status === 'high') {
        filtered = filtered.filter(p => p.has_recipe && p.max_yield >= 10);
    } else if (status === 'low') {
        filtered = filtered.filter(p => p.has_recipe && p.max_yield > 0 && p.max_yield < 10);
    } else if (status === 'depleted') {
        filtered = filtered.filter(p => !p.has_recipe || p.max_yield === 0);
    }

    renderYieldTable(filtered);
}

function openInteractiveYieldSimulator() {
    if (!CURRENT_YIELD_PRODUCTS.length) {
        showToast('Please wait for product yield calculations to load.', 'info');
        return;
    }
    // Open the first product by default
    openYieldProductDetail(CURRENT_YIELD_PRODUCTS[0].item_id);
}

async function openYieldProductDetail(itemId, customBatchQty = null) {
    let product = CURRENT_YIELD_PRODUCTS.find(p => p.item_id === itemId);
    if (!product) {
        try {
            const res = await fetch(`api/inventory.php?action=yield_calculator&item_id=${itemId}`);
            const data = await res.json();
            if (data.success && data.product) {
                product = data.product;
            }
        } catch (e) {
            showToast('Failed to load item yield data', 'danger');
            return;
        }
    }
    if (!product) {
        showToast('Finished product yield data not found', 'danger');
        return;
    }

    SELECTED_YIELD_ITEM = product;

    // Reset Custom Stocks to actual available stock
    CUSTOM_SIM_STOCKS = {};
    (product.ingredients || []).forEach(ing => {
        CUSTOM_SIM_STOCKS[ing.ingredient_id] = ing.available_stock;
    });

    // Populate Product Information Header
    document.getElementById('simProductName').textContent = product.name;
    document.getElementById('simProductCode').textContent = product.item_code;
    document.getElementById('simProductCatBadge').textContent = product.category_name || 'Bakery & Café';
    document.getElementById('simFinishedStock').textContent = `${Number(product.current_finished_stock || 0).toFixed(0)} ${product.unit}`;
    document.getElementById('simCostPerUnit').textContent = `₱${Number(product.cost_per_unit || 0).toFixed(2)}`;
    document.getElementById('simSellingPriceLabel').textContent = `₱${Number(product.selling_price || 0).toFixed(2)} POS`;
    document.getElementById('simProfitMargin').textContent = `${product.profit_margin_pct || 0}%`;
    document.getElementById('simYieldUnitLabel').textContent = product.unit;

    // Set batch target input
    const defaultBatch = customBatchQty || (product.max_yield > 0 ? Math.min(product.max_yield, 20) : 10);
    document.getElementById('simBatchTargetQty').value = defaultBatch;

    renderSimCustomStockInputs();
    runYieldBatchSimulation();

    const modalEl = document.getElementById('yieldDetailModal');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function renderSimCustomStockInputs() {
    const container = document.getElementById('simCustomStockRows');
    if (!container || !SELECTED_YIELD_ITEM) return;

    const ingredients = SELECTED_YIELD_ITEM.ingredients || [];
    if (!ingredients.length) {
        container.innerHTML = `<div class="col-12 text-muted small">No ingredient recipes linked to this product. Go to Tab 1 to set up the BOM.</div>`;
        return;
    }

    container.innerHTML = ingredients.map(ing => {
        const currentSimVal = CUSTOM_SIM_STOCKS[ing.ingredient_id] !== undefined ? CUSTOM_SIM_STOCKS[ing.ingredient_id] : ing.available_stock;
        return `
            <div class="col-md-4 col-sm-6">
                <div class="p-2 border rounded-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-dark text-truncate me-1" title="${escapeInvHtml(ing.name)}">${escapeInvHtml(ing.name)}</span>
                        <span class="badge bg-secondary-subtle text-secondary small">${escapeInvHtml(ing.unit)}</span>
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="number" min="0" step="any" class="form-control fw-bold text-navy" 
                            id="customStock_${ing.ingredient_id}" 
                            value="${currentSimVal}" 
                            oninput="onSimCustomStockInput(${ing.ingredient_id}, this.value)">
                        <span class="input-group-text small text-muted" style="font-size: 0.68rem;">simulated</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.68rem;">
                        <span>Actual: ${ing.available_stock}</span>
                        <span>Req: ${ing.qty_per_unit}/unit</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function onSimCustomStockInput(ingId, val) {
    CUSTOM_SIM_STOCKS[ingId] = Math.max(0, parseFloat(val) || 0);
    runYieldBatchSimulation();
}

function resetSimCustomStocks() {
    if (!SELECTED_YIELD_ITEM) return;
    (SELECTED_YIELD_ITEM.ingredients || []).forEach(ing => {
        CUSTOM_SIM_STOCKS[ing.ingredient_id] = ing.available_stock;
        const el = document.getElementById(`customStock_${ing.ingredient_id}`);
        if (el) el.value = ing.available_stock;
    });
    runYieldBatchSimulation();
}

function adjustSimBatch(delta) {
    const input = document.getElementById('simBatchTargetQty');
    if (!input) return;
    let val = Math.max(1, (parseInt(input.value) || 0) + delta);
    input.value = val;
    runYieldBatchSimulation();
}

function setSimToMaxYield() {
    if (!SELECTED_YIELD_ITEM) return;
    const input = document.getElementById('simBatchTargetQty');
    if (!input) return;
    input.value = Math.max(1, SELECTED_YIELD_ITEM.max_yield || 1);
    runYieldBatchSimulation();
}

function onSimTargetQtyChanged() {
    runYieldBatchSimulation();
}

function runYieldBatchSimulation() {
    if (!SELECTED_YIELD_ITEM) return;

    const targetQty = Math.max(0.01, parseFloat(document.getElementById('simBatchTargetQty')?.value) || 1);
    const ingredients = SELECTED_YIELD_ITEM.ingredients || [];
    const tbody = document.getElementById('simRecipeTableBody');

    let minYield = null;
    let bottleneck = null;
    let totalBatchCost = 0.0;
    let hasShortage = false;

    const linesData = ingredients.map(ing => {
        const stock = (CUSTOM_SIM_STOCKS[ing.ingredient_id] !== undefined) ? CUSTOM_SIM_STOCKS[ing.ingredient_id] : ing.available_stock;
        const qtyPerUnit = ing.qty_per_unit || 0;
        const needed = round4(qtyPerUnit * targetQty);
        const maxFromIng = qtyPerUnit > 0 ? Math.floor((stock + 0.00001) / qtyPerUnit) : 0;
        const shortage = Math.max(0, round4(needed - stock));
        const isSufficient = stock + 0.00001 >= needed;

        if (!isSufficient) hasShortage = true;

        if (minYield === null || maxFromIng < minYield) {
            minYield = maxFromIng;
            bottleneck = { ...ing, available_stock: stock, max_units: maxFromIng };
        }

        const lineCost = round2(needed * (ing.unit_cost || 0));
        totalBatchCost += lineCost;

        return {
            ...ing,
            stock,
            needed,
            maxFromIng,
            shortage,
            isSufficient,
            lineCost
        };
    });

    const calculatedMaxYield = (minYield !== null && ingredients.length > 0) ? minYield : 0;

    // Update Max Yield Display
    document.getElementById('simMaxYieldDisplay').textContent = calculatedMaxYield;
    document.getElementById('simBtnMaxVal').textContent = calculatedMaxYield;

    // Update Bottleneck Banner
    const bottleneckBanner = document.getElementById('simBottleneckBanner');
    const bottleneckText = document.getElementById('simBottleneckText');
    if (bottleneck) {
        bottleneckText.innerHTML = `<strong>${escapeInvHtml(bottleneck.name)}</strong> (${bottleneck.available_stock} ${escapeInvHtml(bottleneck.unit)} in stock • allows max <strong>${bottleneck.max_units}</strong> units)`;
    } else {
        bottleneckText.textContent = ingredients.length ? 'None - All ingredients sufficient' : 'No ingredients defined in recipe';
    }

    // Update Feasibility Alert Banner
    const alertBox = document.getElementById('simFeasibilityAlert');
    const alertTitle = document.getElementById('simAlertTitle');
    const alertDesc = document.getElementById('simAlertDesc');

    if (!ingredients.length) {
        alertBox.className = 'alert alert-secondary mb-0 p-3 rounded-3 d-flex align-items-center gap-2.5';
        alertTitle.textContent = 'No Recipe Configured';
        alertDesc.textContent = 'Please configure recipe Bill of Materials (BOM) in Tab 1 before simulating.';
    } else if (!hasShortage) {
        alertBox.className = 'alert alert-success mb-0 p-3 rounded-3 d-flex align-items-center gap-2.5';
        alertTitle.textContent = `✓ Batch of ${targetQty} ${SELECTED_YIELD_ITEM.unit} Feasible!`;
        alertDesc.textContent = `All raw ingredients are in stock. Ready to produce and post to perpetual ledger.`;
    } else {
        alertBox.className = 'alert alert-danger mb-0 p-3 rounded-3 d-flex align-items-center gap-2.5';
        alertTitle.textContent = `⚠ Ingredient Deficit for ${targetQty} ${SELECTED_YIELD_ITEM.unit}`;
        alertDesc.textContent = `Shortage detected in 1 or more ingredients. Maximum batch possible right now is ${calculatedMaxYield} ${SELECTED_YIELD_ITEM.unit}.`;
    }

    // Render Table Rows
    if (!ingredients.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-3 text-muted">No ingredients defined for this product.</td></tr>`;
    } else {
        tbody.innerHTML = linesData.map(l => {
            let statusBadge = '';
            if (l.isSufficient) {
                const surplus = round4(l.stock - l.needed);
                statusBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check me-1"></i>Sufficient (+${surplus})</span>`;
            } else {
                statusBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-exclamation-triangle me-1"></i>Deficit (-${l.shortage})</span>`;
            }

            return `
                <tr class="${!l.isSufficient ? 'table-danger' : ''}">
                    <td class="ps-3">
                        <strong class="text-dark">${escapeInvHtml(l.name)}</strong>
                        <span class="text-muted fw-mono ms-1">(${escapeInvHtml(l.code)})</span>
                    </td>
                    <td class="text-end fw-mono">${l.qty_per_unit} ${escapeInvHtml(l.unit)}</td>
                    <td class="text-end fw-mono fw-bold">${l.needed} ${escapeInvHtml(l.unit)}</td>
                    <td class="text-end fw-mono ${!l.isSufficient ? 'text-danger fw-bold' : ''}">${l.stock} ${escapeInvHtml(l.unit)}</td>
                    <td class="text-center fw-mono fw-bold text-navy">${l.maxFromIng} units</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-end pe-3 fw-mono">₱${l.lineCost.toFixed(2)}</td>
                </tr>
            `;
        }).join('');
    }

    // Total Cost
    document.getElementById('simTotalBatchCost').textContent = `₱${totalBatchCost.toFixed(2)}`;
}

function transferYieldBatchToProduction() {
    if (!SELECTED_YIELD_ITEM) return;
    const targetQty = parseInt(document.getElementById('simBatchTargetQty')?.value) || 1;
    
    // Hide simulator modal
    const modalEl = document.getElementById('yieldDetailModal');
    if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();

    // Switch to Barista / Kitchen module or notify
    showToast(`Batch of ${targetQty} ${SELECTED_YIELD_ITEM.name} queued for production preparation.`, 'success');
}

function round4(val) {
    return Math.round((parseFloat(val) + Number.EPSILON) * 10000) / 10000;
}

function round2(val) {
    return Math.round((parseFloat(val) + Number.EPSILON) * 100) / 100;
}


// Backward compatibility alias
if (typeof escapeHtml === 'undefined') {
    window.escapeHtml = escapeInvHtml;
}

document.addEventListener('DOMContentLoaded', () => {
    loadInventorySuite();
});
</script>

