<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * HTML Head & Global Layout Header
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}
if (!isset($pageTitle)) {
    $pageTitle = "Raeden's cafe POS with Online Reservation and QR Code";
}
if (!isset($activeNav)) {
    $activeNav = '';
}
if (!isset($bodyClass)) {
    $bodyClass = 'bg-light text-dark';
}

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$appBasePath = ($scriptDir === '' || $scriptDir === '.') ? '/' : $scriptDir . '/';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim(dirname($scriptName), '/.');
$appBasePath = ($scriptDir === '' || $scriptDir === '/') ? '/' : $scriptDir . '/';
$customCssFile = __DIR__ . '/../assets/css/bootstrap-custom.css';
$customCssVer = is_file($customCssFile) ? (string)filemtime($customCssFile) : '2';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="<?= htmlspecialchars($appBasePath) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="document-page-title"><?= htmlspecialchars($pageTitle) ?> • Raeden's</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo.jpg">
    
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
    
    <!-- Custom Bootstrap Theme CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap-custom.css">
    <!-- Custom Bootstrap Theme CSS (External + Inline Guarantee for Hostinger) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($appBasePath) ?>assets/css/bootstrap-custom.css?v=<?= htmlspecialchars($customCssVer) ?>">
    <?php if (is_file($customCssFile) && is_readable($customCssFile)): ?>
    <style id="isatu-custom-theme-inline">
<?= file_get_contents($customCssFile) ?>
    </style>
    <?php endif; ?>

    <!-- Bootstrap 5.3.3 Bundle with Popper JS CDN (Loaded in head for immediate component availability) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    if (typeof bootstrap === 'undefined') {
        document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"><\/script>');
    }
    </script>

    <!-- Chart.js CDN (Loaded globally for analytics) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- SweetAlert2 11 CDN (Modern Popups, Confirmations & Toast Notifications) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- HTML5-QRCode Scanner Library CDN (Loaded globally for optical scan) -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- Global Utility Helpers -->
    <script>
        window.escapeHtml = function(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
        };
        window.escapeInvHtml = window.escapeHtml;
    </script>

    <style>
        #app-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: #894E3F;
            z-index: 10000;
            transition: width 0.25s ease, opacity 0.25s ease;
            pointer-events: none;
        }
    </style>
</head>
<body id="app-body" class="<?= htmlspecialchars($bodyClass) ?> d-flex flex-column min-vh-100">

    <!-- Dynamic Page Loading Bar -->
    <div id="app-loading-bar"></div>

    <!-- Navigation Bar (Rendered on public pages like Customer Booking) -->
    <?php if ($showNavbar ?? true): ?>
        <?php require_once __DIR__ . '/navbar.php'; ?>
    <?php endif; ?>
