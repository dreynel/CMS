<?php
/**
 * Raeden's Resto, Bakery, Cafe - Local PHP Built-In Server Router
 * 
 * Usage:
 *   php -S localhost:8002 router.php
 * or
 *   php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// If the requested physical file or folder exists, let PHP serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Clean URL Route mappings matching Apache .htaccess
$cleanRoutes = [
    '/admin'       => '/admin.php',
    '/login'       => '/login.php',
    '/booking'     => '/booking.php',
    '/reservation' => '/booking.php',
    '/pos'         => '/pos.php',
    '/kitchen'     => '/kitchen.php',
    '/scanner'     => '/scanner.php',
    '/pass'        => '/pass.php',
    '/receipt'     => '/receipt.php',
    '/setup'       => '/setup.php',
];

$trimmed = rtrim($uri, '/');
if (isset($cleanRoutes[$trimmed])) {
    require __DIR__ . $cleanRoutes[$trimmed];
    return true;
}

// Fallback to index.php
require __DIR__ . '/index.php';
