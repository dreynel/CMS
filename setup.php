<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * 1-Click Database Installer & Reset Tool (Native PHP)
 *
 * Runs seamlessly on localhost and Hostinger shared hosting.
 */

require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';
$installed = false;

// If triggered via POST or GET ?install=1
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['install'])) {
    try {
        $pdo = getDB();
        $sqlFile = __DIR__ . '/database.sql';

        if (!file_exists($sqlFile)) {
            throw new Exception("database.sql file not found in " . __DIR__);
        }

        $sql = file_get_contents($sqlFile);

        // Execute queries
        $pdo->exec($sql);
        $installed = true;
        $message = "Database schema and seed data installed successfully! All 11 tables populated.";

        // If AJAX request, return JSON
        if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            jsonResponse(['success' => true, 'message' => $message]);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        if (isset($_GET['ajax'])) {
            jsonResponse(['success' => false, 'message' => $error], 500);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup &amp; Installer • Raeden's Bakery and Café</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --brand-primary: #894E3F;
            --brand-secondary: #C37960;
        }
        body {
            background-color: #FFF6F6;
            color: #2B2B2B;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .brand-header {
            background: linear-gradient(135deg, #6e392c 0%, #894E3F 100%);
            color: white;
        }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 680px;">
        <div class="card shadow border-0 rounded-4 overflow-hidden">
            <!-- Brand Banner -->
            <div class="brand-header p-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 64px; height: 64px; background-color: #C37960; color: #FFF6F6; font-size: 1.8rem; font-weight: 900;">
                    R
                </div>
                <h4 class="fw-bold mb-1">Raeden’s Bakery and Café</h4>
                <p class="small mb-0 fw-semibold text-uppercase tracking-wider" style="color: #EEE1BA;">Raeden's cafe POS with Online Reservation and QR Code</p>
            </div>

            <div class="card-body p-4 p-md-5">
                <?php if ($message): ?>
                    <div class="alert alert-success d-flex align-items-center rounded-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                        <div>
                            <strong>Success!</strong> <?= htmlspecialchars($message) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h5 class="fw-bold text-dark">Database Environment</h5>
                    <ul class="list-group list-group-flush rounded-3 border">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-hdd-network text-primary me-2"></i> Database Host</span>
                            <code class="fw-semibold"><?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?></code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-database text-warning me-2"></i> Database Name</span>
                            <code class="fw-semibold"><?= htmlspecialchars(DB_NAME) ?></code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-badge text-info me-2"></i> Database User</span>
                            <code class="fw-semibold"><?= htmlspecialchars(DB_USER) ?></code>
                        </li>
                    </ul>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-muted text-uppercase small">What this installer does:</h6>
                    <ul class="small text-secondary mb-0">
                        <li>Creates all 11 relational tables (Categories, Products, Tables, Reservations, Orders, Payments, Users).</li>
                        <li>Seeds official bakery signatures, cold brews, breads, pastries, prices, cup sizes, and drink add-ons.</li>
                        <li>Seeds bakery & café dining layout (Lounge, Main Dining, Window Counter, Terrace).</li>
                        <li>Pre-configures demo accounts (Admin, Cashier, Barista, Customer).</li>
                    </ul>
                </div>

                <form method="POST">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm">
                            <i class="bi bi-magic me-2"></i> Initialize / Reset Database (1-Click)
                        </button>
                    </div>
                </form>

                <?php if ($installed || $message): ?>
                    <hr class="my-4">
                    <h6 class="fw-bold text-dark mb-3">Launch System Portals:</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="index.php" class="btn btn-outline-primary w-100 py-2 small fw-semibold">
                                <i class="bi bi-calendar-check me-1"></i> Customer Portal
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="pos.php" class="btn btn-outline-primary w-100 py-2 small fw-semibold">
                                <i class="bi bi-cart3 me-1"></i> Cashier POS
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="kitchen.php" class="btn btn-outline-dark w-100 py-2 small fw-semibold">
                                <i class="bi bi-cup-hot me-1"></i> Barista KDS
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="scanner.php" class="btn btn-outline-secondary w-100 py-2 small fw-semibold">
                                <i class="bi bi-qr-code-scan me-1"></i> QR Scanner
                            </a>
                        </div>
                        <div class="col-12 mt-2">
                            <a href="admin.php" class="btn btn-primary w-100 py-2 fw-semibold" style="background-color: var(--isatu-blue);">
                                <i class="bi bi-speedometer2 me-1"></i> Manager Admin Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top text-center text-muted small">
                    Hostinger Tip: You can also import <code>database.sql</code> directly via Hostinger phpMyAdmin.
                </div>
            </div>
        </div>
    </div>
</body>
</html>

