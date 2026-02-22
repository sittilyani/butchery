<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/config.php');
include('../includes/header.php');

// Get current year and previous years for filter
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;

// Fetch summary statistics
$stats = [];

// Total Sales - FIXED: transDate with capital D
$sales_query = "SELECT
    COUNT(*) as total_transactions,
    COALESCE(SUM(CASE WHEN payment_status IN ('Paid', 'Credit') THEN grand_total ELSE 0 END), 0) as total_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END), 0) as paid_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'Credit' THEN grand_total ELSE 0 END), 0) as credit_revenue
FROM sales WHERE YEAR(transDate) = ?";
$stmt = $conn->prepare($sales_query);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$stats['sales'] = $stmt->get_result()->fetch_assoc();

// Total Profit - FIXED: transDate with capital D
$profit_query = "SELECT COALESCE(SUM(si.profit), 0) as total_profit
                FROM sale_items si
                JOIN sales s ON si.sales_id = s.sales_id
                WHERE YEAR(s.transDate) = ?";
$stmt = $conn->prepare($profit_query);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$stats['profit'] = $stmt->get_result()->fetch_assoc()['total_profit'];

// Stock Value
$stock_query = "SELECT COALESCE(SUM(p.unit_price * s.stockBalance), 0) as stock_value
                FROM stocks s
                JOIN products p ON s.brandname = p.brandname
                WHERE s.stockID IN (SELECT MAX(stockID) FROM stocks GROUP BY brandname)";
$stock_result = $conn->query($stock_query);
$stats['stock_value'] = $stock_result->fetch_assoc()['stock_value'];

// Low Stock Count
$low_stock_query = "SELECT COUNT(DISTINCT s.brandname) as low_stock_count
                   FROM stocks s
                   JOIN products p ON s.brandname = p.brandname
                   WHERE s.stockID IN (SELECT MAX(stockID) FROM stocks GROUP BY brandname)
                   AND s.stockBalance <= p.reorder_level";
$low_stock_result = $conn->query($low_stock_query);
$stats['low_stock'] = $low_stock_result->fetch_assoc()['low_stock_count'];

// Expiring Soon (next 6 months)
$expiry_query = "SELECT COUNT(*) as expiring_count
                FROM stocks
                WHERE expiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
                AND stockBalance > 0";
$expiry_result = $conn->query($expiry_query);
$stats['expiring'] = $expiry_result->fetch_assoc()['expiring_count'];

// Get available years for filter - FIXED: transDate with capital D
$years_query = "SELECT DISTINCT YEAR(transDate) as year FROM sales
                UNION
                SELECT DISTINCT YEAR(sales_date) as year FROM sale_items
                ORDER BY year DESC";
$years_result = $conn->query($years_query);
$available_years = [];
while ($row = $years_result->fetch_assoc()) {
    $available_years[] = $row['year'];
}

// If no years found, add current year
if (empty($available_years)) {
    $available_years[] = date('Y');
}

// Ensure selected year is valid
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $available_years[0];
if (!in_array($selectedYear, $available_years)) {
    $selectedYear = $available_years[0];
}

// Monthly Sales for Chart - FIXED: transDate with capital D
$monthly_query = "SELECT
    MONTH(transDate) as month,
    COALESCE(SUM(CASE WHEN payment_status IN ('Paid', 'Credit') THEN grand_total ELSE 0 END), 0) as total,
    COALESCE(SUM(CASE WHEN payment_method = 'Cash' AND payment_status IN ('Paid', 'Credit') THEN grand_total ELSE 0 END), 0) as cash,
    COALESCE(SUM(CASE WHEN payment_method = 'Mpesa' AND payment_status IN ('Paid', 'Credit') THEN grand_total ELSE 0 END), 0) as mpesa,
    COALESCE(SUM(CASE WHEN payment_status = 'Credit' THEN grand_total ELSE 0 END), 0) as credit
FROM sales
WHERE YEAR(transDate) = ?
GROUP BY MONTH(transDate)
ORDER BY month";
$stmt = $conn->prepare($monthly_query);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$monthly_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Initialize monthly data array
$months = array_fill(1, 12, ['total' => 0, 'cash' => 0, 'mpesa' => 0, 'credit' => 0]);
foreach ($monthly_data as $data) {
    $months[$data['month']] = [
        'total' => floatval($data['total']),
        'cash' => floatval($data['cash']),
        'mpesa' => floatval($data['mpesa']),
        'credit' => floatval($data['credit'])
    ];
}

// Top Products - FIXED: transDate with capital D
$top_products_query = "SELECT
    si.brandname,
    SUM(si.quantity) as total_quantity,
    SUM(si.grand_total) as total_revenue,
    SUM(si.profit) as total_profit
FROM sale_items si
JOIN sales s ON si.sales_id = s.sales_id
WHERE YEAR(s.transDate) = ?
GROUP BY si.brandname
ORDER BY total_revenue DESC
LIMIT 10";
$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top Staff - FIXED: transDate with capital D
$top_staff_query = "SELECT
    s.transBy,
    COUNT(*) as transaction_count,
    SUM(s.grand_total) as total_sales,
    COALESCE(SUM(si.profit), 0) as total_profit
FROM sales s
LEFT JOIN sale_items si ON s.sales_id = si.sales_id
WHERE YEAR(s.transDate) = ?
GROUP BY s.transBy
ORDER BY total_sales DESC
LIMIT 10";
$stmt = $conn->prepare($top_staff_query);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$top_staff = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Recent Transactions - FIXED: transDate with capital D
$recent_query = "SELECT receipt_id, grand_total, payment_method, payment_status, transBy, transDate
                FROM sales
                WHERE YEAR(transDate) = ?
                ORDER BY transDate DESC
                LIMIT 10";
$stmt = $conn->prepare($recent_query);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get available years for filter
$years_query = "SELECT DISTINCT YEAR(transDate) as year FROM sales ORDER BY year DESC";
$years_result = $conn->query($years_query);
$available_years = [];
while ($row = $years_result->fetch_assoc()) {
    $available_years[] = $row['year'];
}
if (empty($available_years)) {
    $available_years[] = $currentYear;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $selectedYear; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --purple: #7209b7;
            --green: #4ad9a3;
            --orange: #fb8b24;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f4f7fc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .main-content {
            padding: 20px 25px;
        }

        /* Header Section */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .header-title p {
            color: #64748b;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .year-selector {
            background: white;
            padding: 8px 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .year-selector label {
            font-weight: 500;
            color: #475569;
            margin: 0;
        }

        .year-selector select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            outline: none;
            transition: all 0.2s;
        }

        .year-selector select:hover {
            border-color: var(--primary);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--stat-color, var(--primary));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        .stat-info h3 {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            margin: 0 0 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: #94a3b8;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(var(--stat-color-rgb), 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--stat-color);
        }

        .stat-card.revenue { --stat-color: #4361ee; --stat-color-rgb: 67, 97, 238; }
        .stat-card.profit { --stat-color: #4ad9a3; --stat-color-rgb: 74, 217, 163; }
        .stat-card.transactions { --stat-color: #f72585; --stat-color-rgb: 247, 37, 133; }
        .stat-card.credit { --stat-color: #fb8b24; --stat-color-rgb: 251, 139, 36; }
        .stat-card.stock { --stat-color: #7209b7; --stat-color-rgb: 114, 9, 183; }
        .stat-card.lowstock { --stat-color: #f8961e; --stat-color-rgb: 248, 150, 30; }

        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .chart-header .badge {
            background: #f1f5f9;
            color: #475569;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Payment Methods Chart */
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .payment-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .payment-icon.cash { background: #e6f7e6; color: #2ecc71; }
        .payment-icon.mpesa { background: #ffe6f0; color: #e84393; }
        .payment-icon.credit { background: #fff4e6; color: #f39c12; }

        .payment-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 3px;
        }

        .payment-details span {
            font-size: 12px;
            color: #64748b;
        }

        .payment-amount {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Tables Grid */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .table-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .table-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .table-header a {
            color: var(--primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 10px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 10px;
            font-size: 13px;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge-success {
            background: #d1fae5;
            color: #059669;
            padding: 4px 8px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-warning {
            background: #fed7aa;
            color: #b45309;
            padding: 4px 8px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
            padding: 4px 8px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 500;
        }

        .progress {
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            margin: 5px 0;
        }

        .progress-bar {
            height: 100%;
            border-radius: 10px;
            background: var(--primary);
        }

        .rank {
            width: 24px;
            height: 24px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .rank-1 { background: #fef9c3; color: #854d0e; }
        .rank-2 { background: #e5e5e5; color: #404040; }
        .rank-3 { background: #fed7aa; color: #92400e; }

        @media (max-width: 1024px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .tables-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <!-- Header with Year Selector -->
    <div class="dashboard-header">
        <div class="header-title">
            <h1>Dashboard Overview</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! Here's what's happening with your business.</p>
        </div>
        <div class="year-selector">
            <label for="year-select"><i class="bi bi-calendar3"></i> Select Year:</label>
            <select id="year-select" onchange="window.location.href='?year=' + this.value">
                <?php foreach ($available_years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card revenue">
            <div class="stat-info">
                <h3>Total Revenue</h3>
                <div class="stat-value">KES <?php echo number_format($stats['sales']['total_revenue'], 0); ?></div>
                <div class="stat-label"><?php echo $stats['sales']['total_transactions']; ?> transactions</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
        </div>

        <div class="stat-card profit">
            <div class="stat-info">
                <h3>Gross Profit</h3>
                <div class="stat-value">KES <?php echo number_format($stats['profit'], 0); ?></div>
                <div class="stat-label"><?php echo number_format($stats['sales']['total_revenue'] > 0 ? ($stats['profit'] / $stats['sales']['total_revenue'] * 100) : 0, 1); ?>% margin</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
        </div>

        <div class="stat-card transactions">
            <div class="stat-info">
                <h3>Paid Sales</h3>
                <div class="stat-value">KES <?php echo number_format($stats['sales']['paid_revenue'], 0); ?></div>
                <div class="stat-label">Cash & M-Pesa</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>

        <div class="stat-card credit">
            <div class="stat-info">
                <h3>Credit Sales</h3>
                <div class="stat-value">KES <?php echo number_format($stats['sales']['credit_revenue'], 0); ?></div>
                <div class="stat-label">Outstanding payments</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-credit-card"></i>
            </div>
        </div>

        <div class="stat-card stock">
            <div class="stat-info">
                <h3>Stock Value</h3>
                <div class="stat-value">KES <?php echo number_format($stats['stock_value'], 0); ?></div>
                <div class="stat-label">Current inventory</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>

        <div class="stat-card lowstock">
            <div class="stat-info">
                <h3>Alerts</h3>
                <div class="stat-value"><?php echo $stats['low_stock'] + $stats['expiring']; ?></div>
                <div class="stat-label"><?php echo $stats['low_stock']; ?> low stock, <?php echo $stats['expiring']; ?> expiring</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Monthly Sales Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="bi bi-bar-chart-line" style="margin-right: 8px; color: var(--primary);"></i> Monthly Sales Trend (<?php echo $selectedYear; ?>)</h3>
                <span class="badge">Revenue by month</span>
            </div>
            <div class="chart-container">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>

        <!-- Payment Methods Summary -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="bi bi-pie-chart" style="margin-right: 8px; color: var(--primary);"></i> Payment Methods</h3>
                <span class="badge">Year to date</span>
            </div>
            <div class="payment-methods">
                <?php
                $total_cash = array_sum(array_column($months, 'cash'));
                $total_mpesa = array_sum(array_column($months, 'mpesa'));
                $total_credit = array_sum(array_column($months, 'credit'));
                $grand_total = $total_cash + $total_mpesa + $total_credit;
                ?>
                <div class="payment-item">
                    <div class="payment-info">
                        <div class="payment-icon cash">
                            <i class="bi bi-cash"></i>
                        </div>
                        <div class="payment-details">
                            <h4>Cash</h4>
                            <span><?php echo $grand_total > 0 ? number_format(($total_cash / $grand_total * 100), 1) : 0; ?>% of total</span>
                        </div>
                    </div>
                    <div class="payment-amount">KES <?php echo number_format($total_cash, 0); ?></div>
                </div>

                <div class="payment-item">
                    <div class="payment-info">
                        <div class="payment-icon mpesa">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="payment-details">
                            <h4>M-Pesa</h4>
                            <span><?php echo $grand_total > 0 ? number_format(($total_mpesa / $grand_total * 100), 1) : 0; ?>% of total</span>
                        </div>
                    </div>
                    <div class="payment-amount">KES <?php echo number_format($total_mpesa, 0); ?></div>
                </div>

                <div class="payment-item">
                    <div class="payment-info">
                        <div class="payment-icon credit">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="payment-details">
                            <h4>Credit</h4>
                            <span><?php echo $grand_total > 0 ? number_format(($total_credit / $grand_total * 100), 1) : 0; ?>% of total</span>
                        </div>
                    </div>
                    <div class="payment-amount">KES <?php echo number_format($total_credit, 0); ?></div>
                </div>
            </div>

            <!-- Mini Donut Chart -->
            <div style="margin-top: 20px; height: 120px;">
                <canvas id="paymentDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="tables-grid">
        <!-- Top Products -->
        <div class="table-card">
            <div class="table-header">
                <h3><i class="bi bi-trophy" style="margin-right: 8px; color: #fbbf24;"></i> Top Products (<?php echo $selectedYear; ?>)</h3>
                <a href="../reports/top_products.php?year=<?php echo $selectedYear; ?>">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty Sold</th>
                            <th>Revenue</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $index => $product): ?>
                        <tr>
                            <td>
                                <div class="rank <?php echo $index == 0 ? 'rank-1' : ($index == 1 ? 'rank-2' : ($index == 2 ? 'rank-3' : '')); ?>">
                                    <?php echo $index + 1; ?>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars(substr($product['brandname'], 0, 30)) . (strlen($product['brandname']) > 30 ? '...' : ''); ?></strong></td>
                            <td><?php echo number_format($product['total_quantity']); ?></td>
                            <td>KES <?php echo number_format($product['total_revenue'], 0); ?></td>
                            <td>KES <?php echo number_format($product['total_profit'], 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($top_products)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #94a3b8;">No sales data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Staff -->
        <div class="table-card">
            <div class="table-header">
                <h3><i class="bi bi-people" style="margin-right: 8px; color: #4cc9f0;"></i> Top Performers (<?php echo $selectedYear; ?>)</h3>
                <a href="../reports/staff_performance.php?year=<?php echo $selectedYear; ?>">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Transactions</th>
                            <th>Sales</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_staff as $index => $staff): ?>
                        <tr>
                            <td>
                                <div class="rank <?php echo $index == 0 ? 'rank-1' : ($index == 1 ? 'rank-2' : ($index == 2 ? 'rank-3' : '')); ?>">
                                    <?php echo $index + 1; ?>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars($staff['transBy']); ?></strong></td>
                            <td><?php echo $staff['transaction_count']; ?></td>
                            <td>KES <?php echo number_format($staff['total_sales'], 0); ?></td>
                            <td>KES <?php echo number_format($staff['total_profit'], 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($top_staff)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #94a3b8;">No staff data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="table-card" style="margin-bottom: 30px;">
        <div class="table-header">
            <h3><i class="bi bi-clock-history" style="margin-right: 8px; color: #fb8b24;"></i> Recent Transactions</h3>
            <a href="../sales/view_order.php">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Receipt ID</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $trans): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($trans['receipt_id']); ?></strong></td>
                        <td><?php echo date('d M Y, H:i', strtotime($trans['transDate'])); ?></td>
                        <td><?php echo htmlspecialchars($trans['transBy']); ?></td>
                        <td>KES <?php echo number_format($trans['grand_total'], 0); ?></td>
                        <td><?php echo $trans['payment_method']; ?></td>
                        <td>
                            <?php if ($trans['payment_status'] == 'Paid'): ?>
                                <span class="badge-success">Paid</span>
                            <?php elseif ($trans['payment_status'] == 'Credit'): ?>
                                <span class="badge-warning">Credit</span>
                            <?php else: ?>
                                <span class="badge-danger"><?php echo $trans['payment_status']; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_transactions)): ?>
                    <tr><td colspan="6" style="text-align: center; color: #94a3b8;">No recent transactions</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Monthly Sales Chart
    const months = <?php echo json_encode(array_values($months)); ?>;
    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    new Chart(document.getElementById('monthlySalesChart'), {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Total Sales',
                data: months.map(m => m.total),
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#4361ee',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'KES ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e2e8f0'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Payment Methods Donut Chart
    new Chart(document.getElementById('paymentDonutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'M-Pesa', 'Credit'],
            datasets: [{
                data: [<?php echo $total_cash; ?>, <?php echo $total_mpesa; ?>, <?php echo $total_credit; ?>],
                backgroundColor: ['#2ecc71', '#e84393', '#f39c12'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': KES ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>