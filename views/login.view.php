<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * Unified Authentication & Portal Sign-In View
 * Supports Administrator & All Staff Stations through a Single Universal Entry Point
 * Native PHP 8.x / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

$requestedRedirect = htmlspecialchars($_GET['redirect'] ?? '');
$currentUser = $_SESSION['user'] ?? null;

// Helper to render user role display badge
function renderUserRoleBadge($role) {
    switch ($role) {
        case 'admin':
            return '<span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-shield-lock-fill me-1"></i> Administrator</span>';
        case 'cashier':
            return '<span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-calculator me-1"></i> Cashier & Receptionist</span>';
        case 'barista':
            return '<span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-cup-hot-fill me-1"></i> Barista</span>';
        case 'staff':
        case 'concierge':
            return '<span class="badge bg-info text-dark fw-bold px-3 py-1.5 rounded-pill"><i class="bi bi-qr-code-scan me-1"></i> Staff</span>';
        default:
            return '<span class="badge bg-secondary text-white fw-bold px-3 py-1.5 rounded-pill">' . htmlspecialchars(strtoupper($role)) . '</span>';
    }
}
?>

<div class="login-page-wrapper py-5" style="min-height: calc(100vh - 120px); background: linear-gradient(rgba(255, 246, 246, 0.88), rgba(255, 246, 246, 0.94)), url('assets/images/cafe-bg.jpg') center/cover no-repeat fixed;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-9">
                
                <!-- Unified System Portal Card -->
                <div class="card border-0 shadow-xl rounded-4 overflow-hidden">
                    
                    <!-- Card Header -->
                    <div class="card-header text-white text-center p-4 border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(137, 78, 63, 0.94), rgba(92, 45, 34, 0.96)), url('assets/images/cafe-bg.jpg') center/cover no-repeat;">
                        <div class="position-relative z-1">
                            <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="rounded-circle shadow-md border border-white border-2 mb-2" style="width: 70px; height: 70px; object-fit: cover;">
                            <h4 class="fw-black mb-1 tracking-tight text-white">System Portal Sign-In</h4>
                            <p class="text-white-50 small mb-0">Administrator &amp; Staff Station Access • Raeden’s Bakery and Café</p>
                        </div>
                    </div>

                <!-- Card Body -->
                <div class="card-body p-4 p-md-4.5">
                    
                    <?php if ($requestedRedirect): ?>
                        <div class="alert alert-warning py-2.5 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-shield-lock-fill fs-5 text-warning"></i>
                            <div>
                                <?php if ($requestedRedirect === 'admin'): ?>
                                    Administrator credentials required to access the <strong>Executive Admin Console</strong>.
                                <?php else: ?>
                                    Authentication required to access <strong><?= ucfirst($requestedRedirect) ?></strong> station.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentUser): ?>
                        <!-- Already Logged In Callout -->
                        <div class="bg-light p-3.5 rounded-4 border mb-4 text-center">
                            <div class="small text-muted mb-1">Currently Signed In:</div>
                            <strong class="d-block text-navy fs-5 mb-1"><?= htmlspecialchars($currentUser['name']) ?></strong>
                            <div class="mb-3">
                                <?= renderUserRoleBadge($currentUser['role']) ?>
                            </div>
                            <div class="d-flex gap-2 justify-content-center">
                                <?php
                                    $dashLink = 'booking';
                                    if ($currentUser['role'] === 'admin') $dashLink = 'admin';
                                    elseif ($currentUser['role'] === 'barista') $dashLink = 'kitchen';
                                    elseif ($currentUser['role'] === 'cashier') $dashLink = 'pos';
                                    elseif ($currentUser['role'] === 'staff' || $currentUser['role'] === 'concierge') $dashLink = 'scanner';
                                ?>
                                <a href="<?= $dashLink ?>" class="btn btn-navy btn-sm rounded-pill px-3 py-2 fw-bold shadow-xs">
                                    <i class="bi bi-speedometer2 me-1"></i> Open My Station
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 fw-bold" onclick="handleStaffLogout()">
                                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                                </button>
                            </div>
                        </div>
                        <div class="text-center small text-muted mb-3">— Or Sign In with a Different Account —</div>
                    <?php endif; ?>

                    <!-- Standard Sign-In Form (HTML Best Practice Compliant) -->
                    <form id="systemLoginForm" onsubmit="handleUnifiedLogin(event)" method="POST" autocomplete="on">
                        <input type="hidden" id="redirectTarget" value="<?= $requestedRedirect ?>">
                        
                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label small fw-bold text-secondary">Account Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                                    <i class="bi bi-envelope-fill text-navy"></i>
                                </span>
                                <input type="email" 
                                       id="loginEmail" 
                                       name="email" 
                                       class="form-control border-start-0 rounded-end-pill py-2.5 pe-3" 
                                       placeholder="e.g. admin@raedens.com or staff@raedens.com" 
                                       autocomplete="username" 
                                       spellcheck="false" 
                                       required>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="loginPassword" class="form-label small fw-bold text-secondary mb-0">Password</label>
                                <span class="text-muted" style="font-size: 0.72rem;">Default demo: <code>password</code></span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                                    <i class="bi bi-lock-fill text-navy"></i>
                                </span>
                                <input type="password" 
                                       id="loginPassword" 
                                       name="password" 
                                       class="form-control border-start-0 border-end-0 py-2.5" 
                                       placeholder="Enter your password" 
                                       autocomplete="current-password" 
                                       required>
                                <button type="button" 
                                        class="input-group-text bg-light border-start-0 rounded-end-pill pe-3 text-muted cursor-pointer" 
                                        onclick="togglePasswordVisibility()" 
                                        title="Show/Hide Password"
                                        aria-label="Toggle password visibility">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Sign In Submit Button -->
                        <button type="submit" class="btn btn-navy w-100 py-3 rounded-pill fw-black fs-6 shadow-sm mb-3" id="btnSubmitLogin">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to System
                        </button>
                    </form>

                    <!-- Public Customer Notice -->
                    <div class="text-center pt-2 border-top">
                        <span class="text-muted small">Are you a customer reserving a table?</span>
                        <a href="booking" class="d-block fw-bold text-primary small mt-1 text-decoration-none">
                            <i class="bi bi-arrow-left-circle me-1"></i> Public Online Reservation (/booking)
                        </a>
                    </div>

                </div>

                <!-- 1-Click Fast Role Switcher Tray -->
                <div class="card-footer bg-light p-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Fast Station Sign-In
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 0.65rem;">Active Staff Stations</span>
                    </div>

                    <div class="row g-2">
                        <!-- Admin -->
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 text-start p-2 rounded-3 text-truncate shadow-xs" onclick="quickLoginRole('admin')">
                                <strong class="d-block text-danger small"><i class="bi bi-shield-lock-fill me-1"></i>Administrator</strong>
                                <span class="text-muted" style="font-size: 0.68rem;">Executive Console (/admin)</span>
                            </button>
                        </div>
                        <!-- Cashier -->
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 text-start p-2 rounded-3 text-truncate shadow-xs" onclick="quickLoginRole('cashier')">
                                <strong class="d-block text-primary small"><i class="bi bi-calculator me-1"></i>Cashier & Reception</strong>
                                <span class="text-muted" style="font-size: 0.68rem;">Touch POS & Tables (/pos)</span>
                            </button>
                        </div>
                        <!-- Barista -->
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-warning btn-sm w-100 text-start p-2 rounded-3 text-truncate shadow-xs" onclick="quickLoginRole('barista')">
                                <strong class="d-block text-warning-emphasis small"><i class="bi bi-cup-hot-fill me-1"></i>Barista</strong>
                                <span class="text-muted" style="font-size: 0.68rem;">KDS Brewing (/kitchen)</span>
                            </button>
                        </div>
                        <!-- Staff Only -->
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-info btn-sm w-100 text-start p-2 rounded-3 text-truncate shadow-xs" onclick="quickLoginRole('staff')">
                                <strong class="d-block text-info-emphasis small"><i class="bi bi-qr-code-scan me-1"></i>Staff</strong>
                                <span class="text-muted" style="font-size: 0.68rem;">QR Scanner (/scanner)</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
</div>

<script>
// Toggle Password Visibility
function togglePasswordVisibility() {
    const input = document.getElementById('loginPassword');
    const icon = document.getElementById('togglePasswordIcon');
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Map user roles to titles, styling badges, icons, and station paths
function getRoleMeta(role) {
    const normalized = (role || '').toLowerCase();
    switch (normalized) {
        case 'admin':
            return {
                title: 'Administrator',
                badgeClass: 'bg-danger text-white',
                icon: 'bi-shield-lock-fill',
                stationName: 'Administrator Console (/admin)'
            };
        case 'cashier':
            return {
                title: 'Cashier & Receptionist',
                badgeClass: 'bg-primary text-white',
                icon: 'bi-calculator',
                stationName: 'Cashier Touch POS & Tables (/pos)'
            };
        case 'barista':
            return {
                title: 'Barista',
                badgeClass: 'bg-warning text-dark',
                icon: 'bi-cup-hot-fill',
                stationName: 'Barista Brewing & KDS (/kitchen)'
            };
        case 'staff':
        case 'concierge':
            return {
                title: 'Staff',
                badgeClass: 'bg-info text-dark',
                icon: 'bi-qr-code-scan',
                stationName: 'Staff QR Scanner (/scanner)'
            };
        case 'customer':
            return {
                title: 'Customer',
                badgeClass: 'bg-secondary text-white',
                icon: 'bi-person',
                stationName: 'Customer Online Reservation (/booking)'
            };
        default:
            return {
                title: (role || 'User').toUpperCase(),
                badgeClass: 'bg-dark text-white',
                icon: 'bi-person-check',
                stationName: 'Assigned Station'
            };
    }
}

// Displays explicit match user role popup and redirects
function showRoleMatchModal(user, redirectUrl) {
    const roleMeta = getRoleMeta(user.role);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '<span class="text-navy fw-black fs-4">Access Verified!</span>',
            html: `
                <div class="py-2 text-center">
                    <p class="text-muted small mb-1">Welcome back,</p>
                    <h5 class="fw-black text-dark mb-2">${user.name}</h5>
                    <div class="mb-3">
                        <span class="badge ${roleMeta.badgeClass} px-3 py-2 fs-6 rounded-pill shadow-xs">
                            <i class="bi ${roleMeta.icon} me-1.5"></i> Matched Role: ${roleMeta.title}
                        </span>
                    </div>
                    <div class="p-2.5 bg-light rounded-3 border text-secondary small">
                        <i class="bi bi-box-arrow-up-right me-1 text-primary"></i> Routing directly to <strong>${roleMeta.stationName}</strong>
                    </div>
                </div>
            `,
            timer: 1600,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: { popup: 'rounded-4 shadow-lg border-0' }
        });
    } else {
        showToast(`Authenticated as ${user.name} (${roleMeta.title})`, 'success');
    }

    setTimeout(() => {
        window.location.href = redirectUrl;
    }, 1400);
}

// Handle Universal Form Login Submission
async function handleUnifiedLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitLogin');
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const redirect = document.getElementById('redirectTarget').value;

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Verifying Credentials...`;

    try {
        const res = await fetch('api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password, redirect })
        });
        const data = await res.json();

        if (data.success && data.user) {
            showRoleMatchModal(data.user, data.redirect);
        } else {
            showToast(data.message || 'Authentication failed. Please verify credentials.', 'danger');
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-box-arrow-in-right me-2"></i> Sign In to System`;
        }
    } catch (err) {
        showToast('Network error while connecting to authentication service.', 'danger');
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-box-arrow-in-right me-2"></i> Sign In to System`;
    }
}

// Quick 1-Click Demo Account Login
async function quickLoginRole(role) {
    const redirect = document.getElementById('redirectTarget').value;
    showToast(`Authenticating demo ${role.toUpperCase()} account...`, 'info');

    try {
        const res = await fetch('api/auth.php?action=quick_login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role, redirect })
        });
        const data = await res.json();

        if (data.success && data.user) {
            showRoleMatchModal(data.user, data.redirect);
        } else {
            showToast(data.message || 'Quick login failed.', 'danger');
        }
    } catch (err) {
        showToast('Network error during quick authentication.', 'danger');
    }
}

// Handle Sign Out from Login View
async function handleStaffLogout() {
    try {
        const res = await fetch('api/auth.php?action=logout', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            showToast('Signed out successfully.', 'info');
        }
        setTimeout(() => {
            window.location.href = 'login';
        }, 400);
    } catch (err) {
        console.warn('Sign out error, redirecting:', err);
        window.location.href = 'login';
    }
}
</script>
