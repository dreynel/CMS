<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * Master Front Controller & Dynamic Page Container
 * 
 * Architecture:
 * - 100% Native PHP 8.x + MySQL PDO (Zero External Framework Bloat)
 * - Master HTML Layout Container (<div id="app-content-container">)
 * - Progressive Enhancement: Instant AJAX dynamic view transitions + Fallback Full Page Rendering
 * - Station Backward Compatibility: pos.php, kitchen.php, scanner.php, admin.php route through this container
 */

define('ISATU_APP', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
$pdo = getDB();

// Dynamic Route Definitions & Security Whitelist (Protects against LFI)
$routes = [
    'booking' => [
        'file' => __DIR__ . '/views/booking.view.php',
        'title' => 'Customer Online Reservation & Pre-Order',
        'nav' => 'reservation',
        'bodyClass' => 'bg-light text-dark',
        'auth' => false,
        'showNavbar' => true,
        'showFooter' => true,
    ],
    'reservation' => [
        'file' => __DIR__ . '/views/booking.view.php',
        'title' => 'Customer Online Reservation & Pre-Order',
        'nav' => 'reservation',
        'bodyClass' => 'bg-light text-dark',
        'auth' => false,
        'showNavbar' => true,
        'showFooter' => true,
    ],
    'pass' => [
        'file' => __DIR__ . '/views/pass.view.php',
        'title' => 'Digital QR Boarding Pass',
        'nav' => 'reservation',
        'bodyClass' => 'bg-light text-dark',
        'auth' => false,
        'showNavbar' => true,
        'showFooter' => true,
    ],
    'login' => [
        'file' => __DIR__ . '/views/login.view.php',
        'title' => 'System Portal Sign-In',
        'nav' => 'login',
        'bodyClass' => 'bg-light text-dark',
        'auth' => false,
        'showNavbar' => true,
        'showFooter' => true,
    ],
    'pos' => [
        'file' => __DIR__ . '/views/pos.view.php',
        'title' => 'Cashier Touch POS Terminal',
        'nav' => 'pos',
        'bodyClass' => 'bg-light text-dark',
        'auth' => true,
        'roles' => ['admin', 'cashier'],
        'showNavbar' => false,
        'showFooter' => false,
    ],
    'kitchen' => [
        'file' => __DIR__ . '/views/kitchen.view.php',
        'title' => 'Barista Brewing & KDS Dashboard',
        'nav' => 'kitchen',
        'bodyClass' => 'bg-dark text-light',
        'auth' => true,
        'roles' => ['admin', 'barista'],
        'showNavbar' => false,
        'showFooter' => false,
    ],
    'scanner' => [
        'file' => __DIR__ . '/views/scanner.view.php',
        'title' => 'Staff Concierge & Seating Dashboard',
        'nav' => 'scanner',
        'bodyClass' => 'bg-light text-dark',
        'auth' => true,
        'roles' => ['admin', 'cashier', 'staff', 'concierge'],
        'showNavbar' => false,
        'showFooter' => false,
    ],
];

// Determine requested page / view
$page = isset($_GET['page']) ? strtolower(trim($_GET['page'])) : 'booking';

// Admin Console is strictly treated as its own independent URL
if ($page === 'admin') {
    header('Location: admin.php');
    exit;
}

if (!array_key_exists($page, $routes)) {
    $page = 'booking';
}

$currentRoute = $routes[$page];
$viewFile = $currentRoute['file'];
$pageTitle = $currentRoute['title'];
$activeNav = $currentRoute['nav'];
$bodyClass = $currentRoute['bodyClass'];
$showNavbar = $currentRoute['showNavbar'] ?? true;
$showFooter = $currentRoute['showFooter'] ?? true;

// Dynamic AJAX View Request (PJAX / Content Swap)
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
          || isset($_GET['ajax']);

// Route Guard & Role-Based Access Control (RBAC)
$currentUser = $_SESSION['user'] ?? null;
$authRequired = $currentRoute['auth'] ?? false;
$allowedRoles = $currentRoute['roles'] ?? [];

if ($authRequired) {
    if (!$currentUser) {
        if ($isAjax) {
            header('X-Auth-Required: 1');
            header('X-Redirect-Url: login?redirect=' . urlencode($page));
            echo '<script>window.location.href="login?redirect=' . htmlspecialchars($page) . '";</script>';
            exit;
        }
        header('Location: login?redirect=' . urlencode($page));
        exit;
    }

    // Role check: If current user's role is not allowed and not admin
    if (!empty($allowedRoles) && !in_array($currentUser['role'], $allowedRoles)) {
        if ($currentUser['role'] !== 'admin') {
            $viewFile = __DIR__ . '/views/unauthorized.view.php';
            $pageTitle = 'Access Denied • Unauthorized Role';
        }
    }
}

if ($isAjax) {
    header('X-Page-Title: ' . rawurlencode($pageTitle));
    header('X-Active-Nav: ' . $activeNav);
    header('X-Body-Class: ' . $bodyClass);
    require $viewFile;
    exit;
}

// Full Master HTML Container Rendering
require_once __DIR__ . '/includes/header.php';
?>

<!-- Specific Dynamic Application Content Container -->
<div id="app-content-container" class="dynamic-page-content flex-grow-1" data-current-page="<?= htmlspecialchars($page) ?>">
    <?php require $viewFile; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
