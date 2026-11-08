<?php
/**
 * Raeden's Bakery and Café - POS with Online Reservation and QR Code
 * Unauthorized Role Access Guard View
 * Native PHP / Bootstrap 5.3
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

$currentUser = $_SESSION['user'] ?? null;
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 80px; height: 80px; font-size: 2.5rem; margin: 0 auto;">
                    <i class="bi bi-shield-x"></i>
                </div>
                <h3 class="fw-black text-navy mb-2 tracking-tight">Access Restricted</h3>
                <p class="text-secondary small mb-4">
                    Your current account <strong><?= htmlspecialchars($currentUser['name'] ?? 'Staff') ?></strong> with role 
                    <span class="badge bg-navy text-warning text-uppercase"><?= htmlspecialchars($currentUser['role'] ?? 'guest') ?></span> 
                    does not have permission to access this internal station.
                </p>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <?php if ($currentUser && $currentUser['role'] === 'cashier'): ?>
                        <a href="index.php?page=pos" data-page="pos" class="btn btn-navy rounded-pill px-4 py-2.5 fw-bold">
                            <i class="bi bi-calculator me-1"></i> Return to Cashier POS
                        </a>
                    <?php elseif ($currentUser && $currentUser['role'] === 'barista'): ?>
                        <a href="index.php?page=kitchen" data-page="kitchen" class="btn btn-navy rounded-pill px-4 py-2.5 fw-bold">
                            <i class="bi bi-cup-hot-fill me-1"></i> Return to Barista KDS
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=booking" data-page="booking" class="btn btn-navy rounded-pill px-4 py-2.5 fw-bold">
                            <i class="bi bi-house me-1"></i> Return to Home / Booking
                        </a>
                    <?php endif; ?>

                    <a href="index.php?page=login" data-page="login" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-bold">
                        <i class="bi bi-person-switch me-1"></i> Switch Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

