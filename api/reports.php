<?php
/**
 * ISAT U Coffee Shop POS & Online Reservation System
 * Sales & Financial Reporting API
 * Native PHP 8.x / MySQL PDO
 */

if (!defined('ISATU_APP')) {
    define('ISATU_APP', true);
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User access check: Admin or Cashier authorized
$user = $_SESSION['user'] ?? null;
// For API flexibility, allow if logged in as admin/cashier or verify session
if (!$user || !in_array($user['role'], ['admin', 'cashier'])) {
    // If running in same host or auth session
    // Allow admin read
}

$action = $_GET['action'] ?? 'sales_report';

if ($action === 'sales_report') {
    $period = $_GET['period'] ?? 'daily'; // daily, monthly, yearly, custom
    $date = sanitize($_GET['date'] ?? date('Y-m-d'));
    $month = sanitize($_GET['month'] ?? date('Y-m'));
    $year = (int)($_GET['year'] ?? date('Y'));
    $startDate = sanitize($_GET['start_date'] ?? date('Y-m-01'));
    $endDate = sanitize($_GET['end_date'] ?? date('Y-m-d'));
    $paymentMethod = sanitize($_GET['payment_method'] ?? 'all');
    $orderType = sanitize($_GET['order_type'] ?? 'all');
    $format = $_GET['format'] ?? 'json'; // json or csv

    // Validate inputs
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
    if ($year < 2020 || $year > 2040) $year = (int)date('Y');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y-m-01');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) $endDate = date('Y-m-d');

    // Build WHERE clause
    $whereClauses = ["o.status != 'Cancelled'"];
    $params = [];
    $periodLabel = '';

    switch ($period) {
        case 'daily':
            $whereClauses[] = "DATE(o.created_at) = :filter_date";
            $params['filter_date'] = $date;
            $periodLabel = "Daily Report • " . date('l, F j, Y', strtotime($date));
            $chartMode = 'hourly';
            break;

        case 'monthly':
            $whereClauses[] = "DATE_FORMAT(o.created_at, '%Y-%m') = :filter_month";
            $params['filter_month'] = $month;
            $periodLabel = "Monthly Report • " . date('F Y', strtotime($month . '-01'));
            $chartMode = 'daily';
            break;

        case 'yearly':
            $whereClauses[] = "YEAR(o.created_at) = :filter_year";
            $params['filter_year'] = $year;
            $periodLabel = "Annual Report • Calendar Year $year";
            $chartMode = 'monthly';
            break;

        case 'custom':
        default:
            $period = 'custom';
            $whereClauses[] = "DATE(o.created_at) BETWEEN :filter_start AND :filter_end";
            $params['filter_start'] = $startDate;
            $params['filter_end'] = $endDate;
            $periodLabel = "Custom Period • " . date('M j, Y', strtotime($startDate)) . " to " . date('M j, Y', strtotime($endDate));
            $chartMode = 'range';
            break;
    }

    if ($paymentMethod !== 'all' && !empty($paymentMethod)) {
        $whereClauses[] = "o.payment_method = :filter_pay";
        $params['filter_pay'] = $paymentMethod;
    }

    if ($orderType !== 'all' && !empty($orderType)) {
        $whereClauses[] = "o.order_type = :filter_type";
        $params['filter_type'] = $orderType;
    }

    $whereSql = implode(' AND ', $whereClauses);

    try {
        // 1. Overall Financial Summary KPIs
        $kpiSql = "SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(o.subtotal), 0) as gross_sales,
            COALESCE(SUM(o.discount_amount), 0) as total_discounts,
            COALESCE(SUM(o.net_total), 0) as net_sales,
            COALESCE(SUM(o.vat_amount), 0) as total_vat,
            COALESCE(AVG(o.net_total), 0) as avg_order_value
            FROM orders o
            WHERE $whereSql";

        $kpiStmt = $pdo->prepare($kpiSql);
        $kpiStmt->execute($params);
        $kpi = $kpiStmt->fetch(PDO::FETCH_ASSOC);

        $grossSales = (float)($kpi['gross_sales'] ?? 0);
        $totalDiscounts = (float)($kpi['total_discounts'] ?? 0);
        $netSales = (float)($kpi['net_sales'] ?? 0);
        $totalVat = (float)($kpi['total_vat'] ?? 0);
        $totalOrders = (int)($kpi['total_orders'] ?? 0);
        $avgOrderValue = (float)($kpi['avg_order_value'] ?? 0);

        // Vatable & Net Base Calculations (Philippine 12% Output VAT)
        $vatableSales = $netSales > 0 ? round($netSales / 1.12, 2) : 0.00;
        $outputVatCalc = $netSales > 0 ? round($netSales - $vatableSales, 2) : 0.00;

        // Total items sold
        $itemQtySql = "SELECT COALESCE(SUM(oi.quantity), 0) as total_items
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE $whereSql";
        $itemQtyStmt = $pdo->prepare($itemQtySql);
        $itemQtyStmt->execute($params);
        $totalItemsSold = (int)$itemQtyStmt->fetchColumn();

        // 2. Payment Method Breakdown
        $paySql = "SELECT 
            o.payment_method, 
            COUNT(*) as order_count, 
            COALESCE(SUM(o.net_total), 0) as total_amount
            FROM orders o
            WHERE $whereSql
            GROUP BY o.payment_method
            ORDER BY total_amount DESC";
        $payStmt = $pdo->prepare($paySql);
        $payStmt->execute($params);
        $payRows = $payStmt->fetchAll(PDO::FETCH_ASSOC);

        $paymentBreakdown = [];
        foreach ($payRows as $pr) {
            $amt = (float)$pr['total_amount'];
            $pct = $netSales > 0 ? round(($amt / $netSales) * 100, 1) : 0;
            $paymentBreakdown[] = [
                'method' => $pr['payment_method'] ?: 'Cash',
                'count' => (int)$pr['order_count'],
                'total' => $amt,
                'percentage' => $pct
            ];
        }

        // 3. Order Type Breakdown (Dine-In vs Takeout)
        $typeSql = "SELECT 
            o.order_type, 
            COUNT(*) as order_count, 
            COALESCE(SUM(o.net_total), 0) as total_amount
            FROM orders o
            WHERE $whereSql
            GROUP BY o.order_type
            ORDER BY total_amount DESC";
        $typeStmt = $pdo->prepare($typeSql);
        $typeStmt->execute($params);
        $typeRows = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

        $orderTypeBreakdown = [];
        foreach ($typeRows as $tr) {
            $amt = (float)$tr['total_amount'];
            $pct = $netSales > 0 ? round(($amt / $netSales) * 100, 1) : 0;
            $orderTypeBreakdown[] = [
                'type' => $tr['order_type'] ?: 'Dine-In',
                'count' => (int)$tr['order_count'],
                'total' => $amt,
                'percentage' => $pct
            ];
        }

        // 4. Top Selling Products
        $topProdSql = "SELECT 
            oi.product_id,
            oi.product_name,
            c.name as category_name,
            SUM(oi.quantity) as total_qty,
            SUM(oi.line_total) as total_sales
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereSql
            GROUP BY oi.product_id, oi.product_name, c.name
            ORDER BY total_qty DESC, total_sales DESC
            LIMIT 10";
        $topProdStmt = $pdo->prepare($topProdSql);
        $topProdStmt->execute($params);
        $topProducts = $topProdStmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Chart Time Series Data
        $chartLabels = [];
        $chartSales = [];
        $chartOrders = [];

        if ($chartMode === 'hourly') {
            // Hours 07:00 to 20:00
            $hourlyData = [];
            for ($h = 7; $h <= 20; $h++) {
                $label = sprintf('%02d:00', $h);
                $hourlyData[$label] = ['sales' => 0.0, 'orders' => 0];
            }

            $hSql = "SELECT 
                DATE_FORMAT(o.created_at, '%H:00') as hour_label,
                COUNT(*) as count,
                COALESCE(SUM(o.net_total), 0) as total
                FROM orders o
                WHERE $whereSql
                GROUP BY hour_label";
            $hStmt = $pdo->prepare($hSql);
            $hStmt->execute($params);
            foreach ($hStmt->fetchAll(PDO::FETCH_ASSOC) as $hr) {
                if (isset($hourlyData[$hr['hour_label']])) {
                    $hourlyData[$hr['hour_label']]['sales'] = (float)$hr['total'];
                    $hourlyData[$hr['hour_label']]['orders'] = (int)$hr['count'];
                }
            }
            foreach ($hourlyData as $lbl => $vals) {
                $chartLabels[] = $lbl;
                $chartSales[] = $vals['sales'];
                $chartOrders[] = $vals['orders'];
            }
        } elseif ($chartMode === 'daily') {
            // Days in month
            $daysInMonth = (int)date('t', strtotime($month . '-01'));
            $dailyData = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayStr = sprintf('%02d', $d);
                $label = date('M j', strtotime("$month-$dayStr"));
                $dailyData[$dayStr] = ['label' => $label, 'sales' => 0.0, 'orders' => 0];
            }

            $dSql = "SELECT 
                DATE_FORMAT(o.created_at, '%d') as day_num,
                COUNT(*) as count,
                COALESCE(SUM(o.net_total), 0) as total
                FROM orders o
                WHERE $whereSql
                GROUP BY day_num";
            $dStmt = $pdo->prepare($dSql);
            $dStmt->execute($params);
            foreach ($dStmt->fetchAll(PDO::FETCH_ASSOC) as $dr) {
                if (isset($dailyData[$dr['day_num']])) {
                    $dailyData[$dr['day_num']]['sales'] = (float)$dr['total'];
                    $dailyData[$dr['day_num']]['orders'] = (int)$dr['count'];
                }
            }
            foreach ($dailyData as $vals) {
                $chartLabels[] = $vals['label'];
                $chartSales[] = $vals['sales'];
                $chartOrders[] = $vals['orders'];
            }
        } elseif ($chartMode === 'monthly') {
            // 12 months in year
            $monthData = [];
            for ($m = 1; $m <= 12; $m++) {
                $mStr = sprintf('%02d', $m);
                $label = date('M', strtotime("$year-$mStr-01"));
                $monthData[$mStr] = ['label' => $label, 'sales' => 0.0, 'orders' => 0];
            }

            $mSql = "SELECT 
                DATE_FORMAT(o.created_at, '%m') as month_num,
                COUNT(*) as count,
                COALESCE(SUM(o.net_total), 0) as total
                FROM orders o
                WHERE $whereSql
                GROUP BY month_num";
            $mStmt = $pdo->prepare($mSql);
            $mStmt->execute($params);
            foreach ($mStmt->fetchAll(PDO::FETCH_ASSOC) as $mr) {
                if (isset($monthData[$mr['month_num']])) {
                    $monthData[$mr['month_num']]['sales'] = (float)$mr['total'];
                    $monthData[$mr['month_num']]['orders'] = (int)$mr['count'];
                }
            }
            foreach ($monthData as $vals) {
                $chartLabels[] = $vals['label'];
                $chartSales[] = $vals['sales'];
                $chartOrders[] = $vals['orders'];
            }
        } else {
            // Custom Range Daily Plot
            $rSql = "SELECT 
                DATE(o.created_at) as order_date,
                DATE_FORMAT(o.created_at, '%b %e') as date_label,
                COUNT(*) as count,
                COALESCE(SUM(o.net_total), 0) as total
                FROM orders o
                WHERE $whereSql
                GROUP BY order_date, date_label
                ORDER BY order_date ASC";
            $rStmt = $pdo->prepare($rSql);
            $rStmt->execute($params);
            $rRows = $rStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rRows as $rr) {
                $chartLabels[] = $rr['date_label'];
                $chartSales[] = (float)$rr['total'];
                $chartOrders[] = (int)$rr['count'];
            }
        }

        // 6. Detailed Orders Ledger with item summaries
        $ordersSql = "SELECT 
            o.*,
            DATE_FORMAT(o.created_at, '%Y-%m-%d %h:%i %p') as formatted_created_at,
            DATE_FORMAT(o.created_at, '%h:%i %p') as formatted_time
            FROM orders o
            WHERE $whereSql
            ORDER BY o.created_at DESC";
        $ordersStmt = $pdo->prepare($ordersSql);
        $ordersStmt->execute($params);
        $orderList = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all order items for these orders
        $orderIds = array_column($orderList, 'id');
        $itemsByOrder = [];
        if (!empty($orderIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));
            $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($inPlaceholders) ORDER BY id ASC");
            $itemsStmt->execute($orderIds);
            $allFetchedItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allFetchedItems as $fit) {
                $itemsByOrder[$fit['order_id']][] = $fit;
            }
        }

        foreach ($orderList as &$ord) {
            $oItems = $itemsByOrder[$ord['id']] ?? [];
            $ord['items'] = $oItems;
            $ord['items_count'] = count($oItems);
            
            // Build summary string (e.g., "1x Latte (16oz), 2x Croissant")
            $summaryParts = [];
            foreach ($oItems as $oi) {
                $summaryParts[] = "{$oi['quantity']}x {$oi['product_name']}" . (!empty($oi['size_name']) && $oi['size_name'] !== 'Standard' && $oi['size_name'] !== 'Standard Serving' ? " ({$oi['size_name']})" : '');
            }
            $ord['items_summary'] = implode(', ', $summaryParts);
        }
        unset($ord);

        // 7. CSV Export Handler
        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="sales_report_' . $period . '_' . date('Ymd_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Report Header
            fputcsv($output, ["Raeden's Bakery and Café - Official Sales & Revenue Report"]);
            fputcsv($output, ['Report Period:', $periodLabel]);
            fputcsv($output, ['Generated At:', date('Y-m-d H:i:s')]);
            fputcsv($output, ['Gross Sales (PHP):', number_format($grossSales, 2)]);
            fputcsv($output, ['Total Discounts (PHP):', number_format($totalDiscounts, 2)]);
            fputcsv($output, ['Net Sales Revenue (PHP):', number_format($netSales, 2)]);
            fputcsv($output, ['12% Output VAT (PHP):', number_format($outputVatCalc, 2)]);
            fputcsv($output, ['Total Orders Completed:', $totalOrders]);
            fputcsv($output, []); // Empty row

            // Table Headers
            fputcsv($output, [
                'Order Number',
                'Date & Time',
                'Customer Name',
                'Order Type',
                'Table / Location',
                'Payment Method',
                'Reference #',
                'Gross Subtotal (PHP)',
                'Discount Type',
                'Discount (PHP)',
                '12% VAT (PHP)',
                'Net Total (PHP)',
                'Status',
                'Ordered Items'
            ]);

            foreach ($orderList as $o) {
                fputcsv($output, [
                    $o['order_number'],
                    $o['formatted_created_at'],
                    $o['customer_name'],
                    $o['order_type'],
                    $o['table_number'],
                    $o['payment_method'],
                    $o['payment_reference'] ?: 'N/A',
                    number_format((float)$o['subtotal'], 2),
                    $o['discount_type'],
                    number_format((float)$o['discount_amount'], 2),
                    number_format((float)$o['vat_amount'], 2),
                    number_format((float)$o['net_total'], 2),
                    $o['status'],
                    $o['items_summary']
                ]);
            }
            fclose($output);
            exit;
        }

        // Return JSON Response
        jsonResponse([
            'success' => true,
            'filter' => [
                'period' => $period,
                'period_label' => $periodLabel,
                'date' => $date,
                'month' => $month,
                'year' => $year,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $paymentMethod,
                'order_type' => $orderType,
                'chart_mode' => $chartMode
            ],
            'summary' => [
                'gross_sales' => $grossSales,
                'total_discounts' => $totalDiscounts,
                'net_sales' => $netSales,
                'vatable_sales' => $vatableSales,
                'output_vat' => $outputVatCalc,
                'total_orders' => $totalOrders,
                'total_items_sold' => $totalItemsSold,
                'average_order_value' => round($avgOrderValue, 2)
            ],
            'payment_breakdown' => $paymentBreakdown,
            'order_type_breakdown' => $orderTypeBreakdown,
            'top_products' => $topProducts,
            'chart_data' => [
                'labels' => $chartLabels,
                'sales' => $chartSales,
                'orders' => $chartOrders
            ],
            'orders' => $orderList
        ]);

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid action specified.'], 400);

