<?php
/**
 * Raeden's Resto, Bakery, Cafe - POS with Online Reservation and QR Code
 * Enhanced Universal Top Navigation Header Bar
 * Native PHP / Bootstrap 5.3
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($activeNav)) {
    $activeNav = '';
}
$navUser = $_SESSION['user'] ?? null;
?>
<!-- Top Universal Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-dark isatu-navbar raedens-navbar sticky-top shadow-sm py-2">
    <div class="container-margins-padded d-flex align-items-center justify-content-between">
        
        <!-- Brand / Logo (Points to Customer Booking Landing) -->
        <a class="navbar-brand d-flex align-items-center gap-2.5 text-decoration-none py-1" href="booking" title="Raeden’s Resto, Bakery, Cafe">
            <div class="position-relative">
                <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="brand-logo rounded-circle border border-2 border-white shadow-xs">
            </div>
            <div>
                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                    <span class="badge text-white fw-bold text-uppercase px-2 py-0.5 shadow-xs" style="background-color: var(--brand-secondary); font-size: 0.64rem; letter-spacing: 0.06em;">Raeden's</span>
                    <span class="badge px-2 py-0.5 shadow-xs" style="background-color: var(--brand-surface); color: var(--brand-text-primary); font-size: 0.64rem; font-weight: 700;">
                        <i class="bi bi-shop me-1 text-secondary"></i> Resto &bull; Bakery &bull; Café
                    </span>
                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success border-opacity-25 px-2 py-0.5 d-none d-xxl-inline-flex align-items-center gap-1" style="font-size: 0.65rem;">
                        <span class="nav-pulse-dot"></span> Open Today &bull; 7AM - 9PM
                    </span>
                </div>
                <div class="brand-title fw-black tracking-tight mb-0 text-white">Raeden’s Resto, Bakery, Cafe</div>
            </div>
        </a>

        <!-- Mobile Toggler Button -->
        <button class="navbar-toggler border-0 p-1.5 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#raedensTopNav" aria-controls="raedensTopNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links & Action Utilities -->
        <div class="collapse navbar-collapse" id="raedensTopNav">
            
            <!-- Central Navigation Links -->
            <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-1 my-2 my-lg-0">
                
                <!-- Table Reservation -->
                <li class="nav-item">
                    <a class="nav-link px-3 <?= in_array($activeNav, ['reservation', 'booking']) ? 'active fw-bold' : '' ?>" href="booking" data-page="booking" data-dynamic="true">
                        <i class="bi bi-calendar2-check-fill me-1.5 text-warning"></i> Table Booking
                    </a>
                </li>

                <!-- Menu & Specialties Modal Trigger -->
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-link text-decoration-none px-3 w-100 text-start text-lg-center" onclick="openMenuSpecialsModal(event)">
                        <i class="bi bi-cup-hot-fill me-1.5 text-warning"></i> Menu &amp; Specials
                    </button>
                </li>

                <!-- Track Booking & Scannable QR Pass -->
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-link text-decoration-none px-3 w-100 text-start text-lg-center" data-bs-toggle="modal" data-bs-target="#navbarLookupModal">
                        <i class="bi bi-qr-code-scan me-1.5 text-info"></i> View My QR Pass
                    </button>
                </li>

                <!-- Operations Stations: Quick Access for Logged-In Roles -->
                <?php if ($navUser): ?>
                    <?php if ($navUser['role'] === 'admin'): ?>
                        <li class="nav-item dropdown navbar-station-dropdown ms-lg-1">
                            <a class="nav-link dropdown-toggle px-3 text-warning fw-bold d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-grid-fill me-1.5"></i> Stations
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2">
                                <li><h6 class="dropdown-header text-uppercase text-secondary fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Admin Console</h6></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 <?= $activeNav === 'admin' ? 'active' : '' ?>" href="admin">
                                        <i class="bi bi-speedometer2 text-warning fs-6"></i>
                                        <div>
                                            <div class="fw-bold">Manager Console</div>
                                            <small class="text-secondary" style="font-size: 0.72rem;">Yield calculator, reports, menu &amp; users</small>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1.5"></li>
                                <li><h6 class="dropdown-header text-uppercase text-secondary fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Live Service Stations</h6></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 <?= $activeNav === 'pos' ? 'active' : '' ?>" href="pos">
                                        <i class="bi bi-calculator text-primary fs-6"></i>
                                        <div>
                                            <div class="fw-bold">Cashier Touch POS</div>
                                            <small class="text-secondary" style="font-size: 0.72rem;">Barcode register, pre-orders &amp; billing</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 <?= $activeNav === 'kitchen' ? 'active' : '' ?>" href="kitchen">
                                        <i class="bi bi-cup-hot-fill text-danger fs-6"></i>
                                        <div>
                                            <div class="fw-bold">Barista Kitchen KDS</div>
                                            <small class="text-secondary" style="font-size: 0.72rem;">Ticket queues &amp; atomic stock deduction</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 <?= $activeNav === 'scanner' ? 'active' : '' ?>" href="scanner">
                                        <i class="bi bi-qr-code-scan text-info fs-6"></i>
                                        <div>
                                            <div class="fw-bold">Staff Seating Hub</div>
                                            <small class="text-secondary" style="font-size: 0.72rem;">Guest check-in &amp; 15-min table release</small>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php elseif ($navUser['role'] === 'cashier'): ?>
                        <li class="nav-item">
                            <a class="nav-link px-3 <?= $activeNav === 'pos' ? 'active fw-bold' : '' ?>" href="pos">
                                <i class="bi bi-calculator me-1.5 text-primary"></i> Cashier POS
                            </a>
                        </li>
                    <?php elseif ($navUser['role'] === 'barista'): ?>
                        <li class="nav-item">
                            <a class="nav-link px-3 <?= $activeNav === 'kitchen' ? 'active fw-bold' : '' ?>" href="kitchen">
                                <i class="bi bi-cup-hot-fill me-1.5 text-warning"></i> Barista KDS
                            </a>
                        </li>
                    <?php elseif ($navUser['role'] === 'staff' || $navUser['role'] === 'concierge'): ?>
                        <li class="nav-item">
                            <a class="nav-link px-3 <?= $activeNav === 'scanner' ? 'active fw-bold' : '' ?>" href="scanner">
                                <i class="bi bi-qr-code-scan me-1.5 text-info"></i> Staff Scanner
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

            </ul>

            <!-- Right Utilities Area (Clock, Sign In / Profile) -->
            <div class="d-flex align-items-center gap-2 justify-content-between justify-content-lg-end mt-2 mt-lg-0 pt-2 pt-lg-0 border-top border-lg-0 border-white border-opacity-10">
                
                <!-- Live Standard Digital Clock -->
                <div class="d-none d-lg-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill bg-black bg-opacity-35 border border-white border-opacity-10 text-white font-monospace small shadow-xs" title="Philippine Standard Time (+08:00)">
                    <i class="bi bi-clock-fill text-warning me-1"></i>
                    <span id="navLiveClock" class="fw-bold text-warning" style="letter-spacing: 0.05em;">--:--:--</span>
                </div>

                <?php if (!$navUser): ?>
                    <!-- Staff Portal Sign-In Pill (Visible to Guests / Staff) -->
                    <a href="login" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 text-white shadow-xs" style="background-color: var(--brand-secondary); border: 1.5px solid rgba(255, 255, 255, 0.3);" title="Staff & Operations Login">
                        <i class="bi bi-shield-lock-fill text-warning"></i>
                        <span>Staff Sign In</span>
                    </a>
                <?php else: ?>
                    <!-- Logged-in Staff Profile Pill with Dropdown -->
                    <?php
                    $roleColor = 'bg-warning text-dark';
                    if ($navUser['role'] === 'admin') $roleColor = 'bg-warning text-dark';
                    elseif ($navUser['role'] === 'cashier') $roleColor = 'bg-primary text-white';
                    elseif ($navUser['role'] === 'barista') $roleColor = 'bg-danger text-white';
                    elseif ($navUser['role'] === 'staff' || $navUser['role'] === 'concierge') $roleColor = 'bg-info text-dark';
                    ?>
                    <div class="dropdown navbar-station-dropdown">
                        <button class="btn p-0 border-0 d-flex align-items-center gap-2 navbar-profile-pill py-1 px-3 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="rounded-circle <?= $roleColor ?> fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 28px; height: 28px; font-size: 0.76rem;">
                                <?= strtoupper(substr($navUser['name'], 0, 2)) ?>
                            </span>
                            <div class="text-start d-none d-sm-block" style="line-height: 1.15;">
                                <div class="small fw-bold text-white"><?= htmlspecialchars(explode(' ', $navUser['name'])[0]) ?></div>
                                <span class="badge <?= $roleColor ?> fw-bold py-0 px-1" style="font-size: 0.58rem;"><?= strtoupper(htmlspecialchars($navUser['role'])) ?></span>
                            </div>
                            <i class="bi bi-chevron-down text-white-50 ms-1" style="font-size: 0.7rem;"></i>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" style="min-width: 220px;">
                            <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                                <div class="fw-bold text-white"><?= htmlspecialchars($navUser['name']) ?></div>
                                <small class="text-secondary d-block"><?= htmlspecialchars($navUser['email'] ?? 'staff@raedens.ph') ?></small>
                                <span class="badge <?= $roleColor ?> rounded-pill mt-1" style="font-size: 0.65rem;"><?= strtoupper(htmlspecialchars($navUser['role'])) ?> STATION</span>
                            </li>
                            <?php if ($navUser['role'] === 'admin'): ?>
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="admin"><i class="bi bi-speedometer2 text-warning"></i> Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="booking"><i class="bi bi-grid-3x3-gap text-success"></i> Customer Booking Portal</a></li>
                            <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1"></li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-danger fw-bold d-flex align-items-center gap-2" onclick="globalStaffLogout()">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </button>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
</nav>

<!-- Modal: Universal Booking & Scannable QR Pass Lookup -->
<div class="modal fade" id="navbarLookupModal" tabindex="-1" aria-labelledby="navbarLookupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header text-white px-4 py-3 border-0" style="background: var(--brand-primary-gradient);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs" style="background-color: var(--brand-secondary); width: 38px; height: 38px;">
                        <i class="bi bi-qr-code-scan text-white fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="navbarLookupModalLabel">Track Booking &amp; QR Pass</h6>
                        <small class="text-white-50" style="font-size: 0.75rem;">Raeden’s Resto, Bakery, Cafe</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-toggle="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <p class="text-secondary small mb-3">
                    Enter your <strong>Reservation Code</strong> (e.g. <code>RES-12345</code>), contact <strong>Phone Number</strong>, or <strong>QR Token</strong> to locate your scheduled table and boarding pass:
                </p>

                <form onsubmit="handleNavbarBookingLookup(event)">
                    <div class="input-group mb-3 shadow-xs">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="navbarBookingQuery" class="form-control border-start-0 py-2.5" placeholder="e.g. RES-00123 or 0917-123-4567" required autocomplete="off">
                        <button type="submit" class="btn btn-navy px-3 fw-bold" id="btnNavbarLookupSubmit">
                            Find Pass
                        </button>
                    </div>
                </form>

                <!-- Dynamic Lookup Result Container -->
                <div id="navbarLookupResult" class="mt-2" style="display: none;"></div>
            </div>

            <div class="modal-footer bg-white border-0 py-2.5 px-4 d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-shield-check me-1 text-success"></i> Instant arrival verification</small>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Universal Topbar Client Helpers -->
<script>
// Universal HTML Escaper
window.escapeHtml = window.escapeHtml || function(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
};

// 1. Live Digital Clock in Philippine Standard Time
(function initNavbarClock() {
    function updateClock() {
        const clockEl = document.getElementById('navLiveClock');
        if (!clockEl) return;
        const now = new Date();
        const opts = { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        clockEl.textContent = now.toLocaleTimeString('en-US', opts);
    }
    updateClock();
    setInterval(updateClock, 1000);
})();

// 2. Open Modalized Menu & Specials Dialog
function openMenuSpecialsModal(e) {
    if (e && e.preventDefault) e.preventDefault();

    // Close mobile nav collapse if open
    const navCollapse = document.getElementById('raedensTopNav') || document.getElementById('isatuNav');
    if (navCollapse && navCollapse.classList.contains('show') && typeof bootstrap !== 'undefined') {
        const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
        if (bsCollapse) bsCollapse.hide();
    }

    const modalEl = document.getElementById('menuSpecialsModal') || document.getElementById('preorderModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        return;
    }

    // On another view - navigate to booking with query param to open modal on load
    window.location.href = 'index.php?page=booking&open_menu=1';
}
window.openMenuSpecialsModal = openMenuSpecialsModal;
window.handleNavbarMenuJump = openMenuSpecialsModal;

// 3. Global Staff Logout Handler
async function globalStaffLogout() {
    let confirmLogout = true;
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'Sign Out?',
            text: 'Are you sure you want to sign out of your station?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Yes, Sign Out',
            cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger rounded-pill px-3.5 py-2 fw-bold me-2',
                cancelButton: 'btn btn-secondary rounded-pill px-3.5 py-2 fw-bold'
            }
        });
        confirmLogout = result.isConfirmed;
    }
    if (!confirmLogout) return;

    try {
        const res = await fetch('api/auth.php?action=logout', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            window.location.href = 'login?logged_out=1';
        } else {
            window.location.href = 'login';
        }
    } catch (err) {
        window.location.href = 'login';
    }
}

// 4. Universal Booking & QR Pass Lookup Handler
async function handleNavbarBookingLookup(e) {
    e.preventDefault();
    const queryInput = document.getElementById('navbarBookingQuery');
    const resultBox = document.getElementById('navbarLookupResult');
    const submitBtn = document.getElementById('btnNavbarLookupSubmit');
    const q = (queryInput?.value || '').trim();
    if (!q) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;
    resultBox.style.display = 'block';
    resultBox.innerHTML = `
        <div class="text-center py-3 text-secondary">
            <span class="spinner-border spinner-border-sm text-warning me-2"></span> Searching for active booking...
        </div>
    `;

    try {
        const res = await fetch(`api/reservations.php?action=lookup_booking&query=${encodeURIComponent(q)}`);
        const data = await res.json();

        if (data.success && data.reservation) {
            const r = data.reservation;
            const itemsCount = (data.items || []).length;
            const statusBadgeClass = (r.status === 'Checked-In' || r.status === 'Seated') ? 'bg-success' : 'bg-primary';

            resultBox.innerHTML = `
                <div class="card border border-2 border-primary-subtle rounded-4 p-3 bg-white shadow-xs">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge ${statusBadgeClass} rounded-pill px-2.5 py-1 mb-1 fw-bold">${r.status || 'Confirmed'}</span>
                            <h6 class="fw-bold text-navy mb-0 fs-6">${escapeHtml(r.customer_name)}</h6>
                            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>${escapeHtml(r.reservation_date)} &bull; ${escapeHtml(r.time_slot)}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark border fw-bold px-2 py-1">${escapeHtml(r.table_code || 'Table #' + r.table_id)}</span>
                        </div>
                    </div>
                    <div class="p-2 bg-light rounded-3 small text-secondary mb-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-geo-alt me-1 text-danger"></i>${escapeHtml(r.zone || 'Dining Area')}</span>
                        <span><i class="bi bi-basket2 me-1 text-primary"></i>${itemsCount} Pre-ordered item(s)</span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="pass?token=${encodeURIComponent(r.qr_token || r.reservation_number)}" class="btn btn-navy btn-sm rounded-pill py-2 fw-bold shadow-xs">
                            <i class="bi bi-qr-code me-1"></i> Open Scannable QR Boarding Pass
                        </a>
                    </div>
                </div>
            `;
        } else {
            resultBox.innerHTML = `
                <div class="alert alert-warning border-warning-subtle rounded-4 p-3 small mb-0 text-center">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5 d-block mb-1"></i>
                    <strong>No Active Reservation Found</strong><br>
                    We couldn't find a booking matching "<code>${escapeHtml(q)}</code>". Please check your reference code or phone number.
                </div>
            `;
        }
    } catch (err) {
        resultBox.innerHTML = `
            <div class="alert alert-danger border-danger-subtle rounded-4 p-3 small mb-0 text-center">
                <i class="bi bi-x-circle-fill text-danger fs-5 d-block mb-1"></i>
                An error occurred while connecting to the server. Please try again.
            </div>
        `;
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `Find Pass`;
    }
}

// 5. Global Trigger for Modalized Menu & Specials
if (typeof window.openMenuSpecialsModal !== 'function') {
    window.openMenuSpecialsModal = function(e) {
        if (e && e.preventDefault) e.preventDefault();
        const modalEl = document.getElementById('menuSpecialsModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const navCollapse = document.getElementById('raedensTopNav') || document.getElementById('isatuNav');
            if (navCollapse && navCollapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navCollapse) || new bootstrap.Collapse(navCollapse, { toggle: false });
                bsCollapse.hide();
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            window.location.href = 'booking?open_menu=1#menuSpecialsModal';
        }
    };
}
</script>
