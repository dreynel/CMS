<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Standalone Administrator Operations Console & Executive Workspace
 * Native PHP 8.x / MySQL PDO
 */

define('ISATU_APP', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$adminUser = $_SESSION['user'] ?? null;
$isAdmin = ($adminUser && ($adminUser['role'] === 'admin'));

// Single Unified Login Guard: If not authenticated as administrator, redirect to /login
if (!$isAdmin) {
    header('Location: login?redirect=admin');
    exit;
}

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$appBasePath = ($scriptDir === '' || $scriptDir === '.') ? '/' : $scriptDir . '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptName), '/.');
$appBasePath = ($scriptDir === '' || $scriptDir === '/') ? '/' : $scriptDir . '/';
$customCssFile = __DIR__ . '/assets/css/bootstrap-custom.css';
$customCssVer = is_file($customCssFile) ? (string)filemtime($customCssFile) : '2';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="<?= htmlspecialchars($appBasePath) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Console • Raeden's Bakery and Café</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap 5.3.3 CSS CDN (Primary + Fallback for Hostinger/ISP reliability) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons 1.11.3 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap-custom.css">
    <!-- Custom Theme CSS (External + Inline Guarantee for Hostinger) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($appBasePath) ?>assets/css/bootstrap-custom.css?v=<?= htmlspecialchars($customCssVer) ?>">
    <?php if (is_file($customCssFile) && is_readable($customCssFile)): ?>
    <style id="isatu-custom-theme-inline">
<?= file_get_contents($customCssFile) ?>
    </style>
    <?php endif; ?>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- QRCode.js CDN for Dynamic POS QR Generation & Tags -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    if (typeof QRCode === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>');
    }
    </script>

    <!-- SweetAlert2 11 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap 5.3.3 Bundle with Popper JS CDN (Loaded in head for immediate component availability) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    if (typeof bootstrap === 'undefined') {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"><\/script>');
    }
    </script>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: var(--brand-canvas); color: var(--brand-text-primary);">

    <!-- Dedicated Admin Top Header -->
    <header class="navbar navbar-dark isatu-navbar raedens-navbar sticky-top shadow-sm py-2 px-3 px-lg-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2.5">
            <a href="admin.php" class="text-decoration-none d-flex align-items-center gap-2.5">
                <img src="assets/images/logo.jpg" alt="Raeden's Logo" class="brand-logo rounded-circle border border-2 border-white shadow-xs">
                <div>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="badge text-white fw-bold text-uppercase px-2 py-0.5 shadow-xs" style="background-color: var(--brand-secondary); font-size: 0.64rem; letter-spacing: 0.06em;">Raeden's</span>
                        <span class="badge px-2 py-0.5 shadow-xs" style="background-color: var(--brand-surface); color: var(--brand-text-primary); font-size: 0.64rem; font-weight: 700;">
                            <i class="bi bi-shield-lock-fill me-1 text-warning"></i> Admin Operations Console
                        </span>
                    </div>
                    <div class="brand-title fw-black tracking-tight mb-0 text-white">Raeden’s Resto, Bakery, Cafe</div>
                </div>
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Live Digital Clock -->
            <div class="d-none d-lg-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill bg-black bg-opacity-35 border border-white border-opacity-10 text-white font-monospace small shadow-xs" title="Philippine Standard Time">
                <i class="bi bi-clock-fill text-warning me-1"></i>
                <span id="adminTopLiveClock" class="fw-bold text-warning" style="letter-spacing: 0.05em;">--:--:--</span>
            </div>

            <!-- Quick Station Switcher Dropdown -->
            <div class="dropdown navbar-station-dropdown d-none d-md-inline-block">
                <button class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 fw-bold dropdown-toggle d-flex align-items-center gap-1.5 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-grid-fill text-warning"></i> Stations
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2">
                    <li><h6 class="dropdown-header text-uppercase text-secondary fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Live Service Terminals</h6></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="pos" target="_blank">
                            <i class="bi bi-calculator text-primary fs-6"></i>
                            <div>
                                <div class="fw-bold">Cashier Touch POS</div>
                                <small class="text-secondary" style="font-size: 0.72rem;">Barcode register, pre-orders &amp; billing</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="kitchen" target="_blank">
                            <i class="bi bi-cup-hot-fill text-danger fs-6"></i>
                            <div>
                                <div class="fw-bold">Barista Kitchen KDS</div>
                                <small class="text-secondary" style="font-size: 0.72rem;">Live ticket queues &amp; stock deduction</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="scanner" target="_blank">
                            <i class="bi bi-qr-code-scan text-info fs-6"></i>
                            <div>
                                <div class="fw-bold">Staff Seating Hub</div>
                                <small class="text-secondary" style="font-size: 0.72rem;">Guest check-in &amp; 15-min table release</small>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary border-opacity-25 my-1.5"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="booking" target="_blank">
                            <i class="bi bi-box-arrow-up-right text-success fs-6"></i>
                            <div>
                                <div class="fw-bold">Customer Booking Portal</div>
                                <small class="text-secondary" style="font-size: 0.72rem;">Public reservation &amp; pre-order screen</small>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Booking Portal Direct Link -->
            <a href="booking" target="_blank" class="btn btn-navy btn-sm rounded-pill px-3 py-1.5 fw-bold d-none d-xl-inline-flex align-items-center gap-1.5 shadow-xs" style="background-color: var(--brand-secondary) !important; border: 1px solid rgba(255,255,255,0.25);" title="Open Customer Booking Website in New Tab">
                <i class="bi bi-box-arrow-up-right me-1"></i> Customer View
            </a>

            <!-- Admin Profile Pill -->
            <div class="dropdown navbar-station-dropdown">
                <button class="btn p-0 border-0 d-flex align-items-center gap-2 navbar-profile-pill py-1 px-3 rounded-pill shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 28px; height: 28px; font-size: 0.76rem;">
                        <?= strtoupper(substr($adminUser['name'], 0, 2)) ?>
                    </span>
                    <div class="text-start d-none d-sm-block" style="line-height: 1.15;">
                        <div class="small fw-bold text-white"><?= htmlspecialchars(explode(' ', $adminUser['name'])[0]) ?></div>
                        <span class="badge bg-warning text-dark fw-bold py-0 px-1" style="font-size: 0.58rem;">ADMIN</span>
                    </div>
                    <i class="bi bi-chevron-down text-white-50 ms-1" style="font-size: 0.7rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" style="min-width: 200px;">
                    <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                        <div class="fw-bold text-white"><?= htmlspecialchars($adminUser['name']) ?></div>
                        <small class="text-secondary d-block"><?= htmlspecialchars($adminUser['email'] ?? 'admin@raedens.ph') ?></small>
                        <span class="badge bg-warning text-dark rounded-pill mt-1" style="font-size: 0.65rem;">EXECUTIVE ADMIN</span>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item py-2 text-danger fw-bold d-flex align-items-center gap-2" onclick="logoutAdmin()">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Render Full Administrator View -->
    <div class="flex-grow-1">
        <?php require_once __DIR__ . '/views/admin.view.php'; ?>
    </div>

    <!-- Universal Admin Script Helpers -->
    <script>
    // SweetAlert2 Toast Mixin
    const isatuSwalToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: { popup: 'isatu-swal-toast rounded-4 shadow border' }
    });

    function showToast(message, type = 'info') {
        let swalIcon = 'info';
        if (type === 'success') swalIcon = 'success';
        else if (type === 'danger' || type === 'error') swalIcon = 'error';
        else if (type === 'warning') swalIcon = 'warning';
        isatuSwalToast.fire({ icon: swalIcon, title: message });
    }

    async function showConfirm({ title = 'Are you sure?', text = '', icon = 'warning', confirmButtonText = 'Yes, Proceed', cancelButtonText = 'Cancel', isDestructive = false } = {}) {
        return Swal.fire({
            title: title,
            html: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-4 shadow-lg border-0 p-4',
                title: 'fw-black text-navy fs-4',
                confirmButton: isDestructive ? 'btn btn-danger px-4 py-2 rounded-pill fw-bold' : 'btn btn-navy px-4 py-2 rounded-pill fw-bold',
                cancelButton: 'btn btn-outline-secondary px-4 py-2 rounded-pill fw-bold ms-2'
            }
        });
    }

    async function logoutAdmin() {
        const confirmRes = await showConfirm({
            title: 'Sign Out of Admin Console?',
            text: 'Are you sure you want to exit the management workspace?',
            icon: 'question',
            confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Sign Out',
            cancelButtonText: 'Stay in Console'
        });
        if (!confirmRes.isConfirmed) return;

        try {
            await fetch('api/auth.php?action=logout', { method: 'POST', headers: { 'Content-Type': 'application/json' } });
            window.location.href = 'login';
        } catch (e) {
            window.location.href = 'login';
        }
    }

    // Live Digital Clock in Philippine Standard Time
    (function initAdminTopClock() {
        function updateClock() {
            const clockEl = document.getElementById('adminTopLiveClock');
            if (!clockEl) return;
            const now = new Date();
            const opts = { timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            clockEl.textContent = now.toLocaleTimeString('en-US', opts);
        }
        updateClock();
        setInterval(updateClock, 1000);
    })();
    </script>

    <!-- Initialize All Bootstrap Components -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el) {
                try { bootstrap.Dropdown.getOrCreateInstance(el); } catch(e) {}
            });
        }
    });
    </script>

</body>
</html>
