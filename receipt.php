<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * 80mm ESC/POS Printable Thermal Receipt
 * Native PHP
 */

require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$orderId = (int)($_GET['order_id'] ?? 0);
$orderNumber = trim($_GET['order_number'] ?? '');
$order = null;
$items = [];
$payment = null;

if ($orderId > 0 || !empty($orderNumber)) {
    try {
        if ($orderId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $stmt->execute([$orderId]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
            $stmt->execute([$orderNumber]);
        }
        $order = $stmt->fetch();

        if ($order) {
            $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll();

            $payStmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
            $payStmt->execute([$order['id']]);
            $payment = $payStmt->fetch();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= htmlspecialchars($order['order_number'] ?? 'Not Found') ?> • Raeden's</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- QRCode.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f3f4f6;
            font-family: 'Space Mono', monospace, Courier, sans-serif;
            font-size: 12px;
            color: #000;
            padding: 20px;
        }
        .receipt-container {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 15px 12px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        .d-flex {
            display: flex;
            justify-content: space-between;
        }
        .item-row {
            margin-bottom: 6px;
        }
        .modifiers {
            font-size: 10px;
            color: #333;
            padding-left: 10px;
        }
        .action-bar {
            width: 80mm;
            max-width: 100%;
            margin: 15px auto 0;
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            font-family: sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            border: none;
        }
        .btn-print { background: #002080; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                padding: 0;
                width: 76mm;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<?php if (!$order): ?>
    <div class="receipt-container text-center">
        <h3>Receipt Not Found</h3>
        <p>No valid order ID was provided.</p>
        <div class="action-bar">
            <a href="pos.php" class="btn btn-back">Return to POS</a>
        </div>
    </div>
<?php else: ?>

    <div class="receipt-container" id="printable">
        
        <!-- Header -->
        <div class="text-center">
            <div class="fw-bold" style="font-size: 15px; letter-spacing: 0.5px; color: #894E3F;">RAEDEN'S BAKERY AND CAFÉ</div>
            <div>Artisanal Bakes &amp; Specialty Coffee</div>
            <div>Raeden's cafe POS with Online Reservation and QR Code</div>
            <div>VAT REG TIN: 450-890-123-000</div>
            <div>SN: RDN-2026-001 • POS Terminal #01</div>
        </div>

        <div class="divider"></div>

        <!-- Order Metadata -->
        <div>
            <div class="d-flex">
                <span>ORDER #:</span>
                <span class="fw-bold"><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="d-flex">
                <span>DATE/TIME:</span>
                <span><?= date('m/d/Y h:i A', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="d-flex">
                <span>CASHIER/BARISTA:</span>
                <span>Dual-Station #1</span>
            </div>
            <div class="d-flex">
                <span>TYPE / TABLE:</span>
                <span class="fw-bold"><?= htmlspecialchars($order['order_type']) ?> (<?= htmlspecialchars($order['table_number']) ?>)</span>
            </div>
            <div class="d-flex">
                <span>CUSTOMER:</span>
                <span><?= htmlspecialchars($order['customer_name']) ?></span>
            </div>
        </div>

        <div class="double-divider"></div>

        <!-- Items Table -->
        <div class="d-flex fw-bold" style="margin-bottom: 4px;">
            <span style="flex: 2;">ITEM</span>
            <span style="flex: 0.6;" class="text-center">QTY</span>
            <span style="flex: 1;" class="text-end">AMOUNT</span>
        </div>
        <div class="divider"></div>

        <?php foreach ($items as $it): 
            $addonsArr = !empty($it['addons_json']) ? json_decode($it['addons_json'], true) : [];
        ?>
            <div class="item-row">
                <div class="d-flex">
                    <span style="flex: 2;" class="fw-bold"><?= htmlspecialchars($it['product_name']) ?></span>
                    <span style="flex: 0.6;" class="text-center"><?= $it['quantity'] ?></span>
                    <span style="flex: 1;" class="text-end">₱<?= number_format($it['line_total'], 2) ?></span>
                </div>
                <div class="modifiers">
                    <?php if ($it['sugar_level'] === 'Pastry' || $it['size_name'] === 'Standard Serving'): ?>
                        Serving: <?= htmlspecialchars($it['size_name']) ?> | <?= htmlspecialchars($it['ice_level']) ?>
                    <?php else: ?>
                        Size: <?= htmlspecialchars($it['size_name']) ?> | <?= htmlspecialchars($it['sugar_level']) ?> | <?= htmlspecialchars($it['ice_level']) ?>
                    <?php endif; ?>
                    <?php if (!empty($addonsArr)): ?>
                        <br>Addons: <?= implode(', ', array_map('htmlspecialchars', $addonsArr)) ?>
                    <?php endif; ?>
                    <?php if (!empty($it['special_notes'])): ?>
                        <br>Note: <?= htmlspecialchars($it['special_notes']) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>

        <!-- Totals & Discounts -->
        <div>
            <div class="d-flex">
                <span>SUBTOTAL:</span>
                <span>₱<?= number_format($order['subtotal'], 2) ?></span>
            </div>
            <?php if ($order['discount_amount'] > 0): ?>
                <div class="d-flex fw-bold">
                    <span>DISCOUNT (<?= htmlspecialchars($order['discount_type']) ?>):</span>
                    <span>-₱<?= number_format($order['discount_amount'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="d-flex fw-bold" style="font-size: 14px; margin: 4px 0;">
                <span>TOTAL DUE:</span>
                <span>₱<?= number_format($order['net_total'], 2) ?></span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Payment Breakdown -->
        <div>
            <div class="d-flex">
                <span>PAYMENT METHOD:</span>
                <span class="fw-bold"><?= htmlspecialchars($order['payment_method']) ?></span>
            </div>
            <?php if ($payment): ?>
                <div class="d-flex">
                    <span>AMOUNT TENDERED:</span>
                    <span>₱<?= number_format($payment['amount_tendered'], 2) ?></span>
                </div>
                <div class="d-flex fw-bold">
                    <span>CHANGE:</span>
                    <span>₱<?= number_format($payment['amount_change'], 2) ?></span>
                </div>
                <?php if (!empty($payment['payment_reference'])): ?>
                    <div class="d-flex">
                        <span>REF #:</span>
                        <span><?= htmlspecialchars($payment['payment_reference']) ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <!-- Philippine 12% VAT Computation -->
        <?php 
            $vatable = $order['net_total'] / 1.12;
            $vat = $order['net_total'] - $vatable;
        ?>
        <div style="font-size: 10px;">
            <div class="d-flex">
                <span>VATable Sales:</span>
                <span>₱<?= number_format($vatable, 2) ?></span>
            </div>
            <div class="d-flex">
                <span>VAT Amount (12%):</span>
                <span>₱<?= number_format($vat, 2) ?></span>
            </div>
            <div class="d-flex">
                <span>VAT-Exempt Sales:</span>
                <span>₱0.00</span>
            </div>
            <div class="d-flex">
                <span>Zero Rated Sales:</span>
                <span>₱0.00</span>
            </div>
        </div>

        <div class="double-divider"></div>

        <!-- Verification QR Code -->
        <div class="text-center" style="margin: 10px 0;">
            <div id="receiptQr" style="display: inline-block;"></div>
            <div style="font-size: 9px; margin-top: 4px;">VERIFY TRANSACTION • RAEDEN'S POS</div>
        </div>

        <div class="text-center" style="font-size: 10px; margin-top: 6px;">
            <div>THANK YOU FOR VISITING</div>
            <div class="fw-bold">RAEDEN'S BAKERY AND CAFÉ</div>
            <div style="font-size: 8px; margin-top: 4px; color: #555;">
                Freshly Baked Daily • Handcrafted Coffee<br>
                This serves as an Official Sales Slip.
            </div>
        </div>

    </div>

    <!-- Action Bar for Screen -->
    <div class="action-bar">
        <button class="btn btn-print" style="background: #894E3F; color: #fff;" onclick="window.print()">Print Receipt</button>
        <a href="pos" class="btn btn-back">Back to POS</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qrEl = document.getElementById('receiptQr');
            if (qrEl) {
                new QRCode(qrEl, {
                    text: 'RAEDEN-RECEIPT-<?= $order['order_number'] ?>-<?= $order['id'] ?>',
                    width: 100,
                    height: 100,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            }

            // Auto-trigger print if ?print=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 400);
            }
        });
    </script>
<?php endif; ?>

</body>
</html>

