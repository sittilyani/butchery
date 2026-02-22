<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../includes/config.php";
include "../includes/header.php";

$page_title = "Financial Report";

// Get years for filter
$years_query = "SELECT DISTINCT YEAR(transDate) as year FROM sales ORDER BY year DESC";
$years_result = $conn->query($years_query);
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['year'];
}
if (empty($years)) {
    $years[] = date('Y');
}

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : 0;

// Build WHERE clause
$where = "WHERE YEAR(transDate) = $selected_year";
if ($selected_month > 0) {
    $where .= " AND MONTH(transDate) = $selected_month";
}

// Summary Stats
$summary = [];

// Total Revenue
$revenue_query = "SELECT
    COUNT(*) as transactions,
    COALESCE(SUM(CASE WHEN payment_status IN ('Paid', 'Credit') THEN grand_total ELSE 0 END), 0) as total_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END), 0) as paid_revenue,
    COALESCE(SUM(CASE WHEN payment_status = 'Credit' THEN grand_total ELSE 0 END), 0) as credit_revenue
FROM sales $where";
$summary['revenue'] = $conn->query($revenue_query)->fetch_assoc();

// Total Profit
$profit_query = "SELECT
    COALESCE(SUM(si.profit), 0) as total_profit,
    COALESCE(SUM(si.quantity), 0) as items_sold,
    COALESCE(SUM(si.buying_price_total), 0) as total_cost
FROM sale_items si
JOIN sales s ON si.sales_id = s.sales_id
$where";
$summary['profit'] = $conn->query($profit_query)->fetch_assoc();

// Payment Method Breakdown
$payment_query = "SELECT
    payment_method,
    COALESCE(COUNT(*), 0) as count,
    COALESCE(SUM(grand_total), 0) as total
FROM sales
$where AND payment_status IN ('Paid', 'Credit')
GROUP BY payment_method";
$payment_result = $conn->query($payment_query);
$payment_methods = [];
while ($row = $payment_result->fetch_assoc()) {
    $payment_methods[] = $row;
}

// Monthly Breakdown
$monthly_query = "SELECT
    MONTH(transDate) as month,
    COUNT(*) as transactions,
    COALESCE(SUM(grand_total), 0) as revenue,
    COALESCE(SUM(CASE WHEN payment_method = 'Cash' THEN grand_total ELSE 0 END), 0) as cash,
    COALESCE(SUM(CASE WHEN payment_method = 'Mpesa' THEN grand_total ELSE 0 END), 0) as mpesa,
    COALESCE(SUM(CASE WHEN payment_status = 'Credit' THEN grand_total ELSE 0 END), 0) as credit
FROM sales
WHERE YEAR(transDate) = $selected_year
GROUP BY MONTH(transDate)
ORDER BY month";
$monthly_result = $conn->query($monthly_query);
$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['month']] = $row;
}

// Daily Sales for selected month
$daily_data = [];
if ($selected_month > 0) {
    $daily_query = "SELECT
        DAY(transDate) as day,
        COUNT(*) as transactions,
        COALESCE(SUM(grand_total), 0) as revenue
    FROM sales
    WHERE YEAR(transDate) = $selected_year AND MONTH(transDate) = $selected_month
    GROUP BY DAY(transDate)
    ORDER BY day";
    $daily_result = $conn->query($daily_query);
    while ($row = $daily_result->fetch_assoc()) {
        $daily_data[$row['day']] = $row;
    }
}

// Top Products by Revenue
$top_products_query = "SELECT
    si.brandname,
    SUM(si.quantity) as quantity,
    SUM(si.grand_total) as revenue,
    SUM(si.profit) as profit,
    (SUM(si.profit) / SUM(si.grand_total) * 100) as margin
FROM sale_items si
JOIN sales s ON si.sales_id = s.sales_id
$where
GROUP BY si.brandname
ORDER BY revenue DESC
LIMIT 20";
$top_products = $conn->query($top_products_query)->fetch_all(MYSQLI_ASSOC);

// Expenses (if you have expenses table)
// For now, calculate estimated expenses (rent, salaries, etc.)
$estimated_expenses = $summary['revenue']['total_revenue'] * 0.15; // 15% estimated expenses
$net_profit = $summary['profit']['total_profit'] - $estimated_expenses;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $selected_year; ?></title>
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
        }

        body {
            background: #f4f7fc;
            font-family: 'Inter', -apple-system, sans-serif;
        }

        .main-content {
            padding: 20px 25px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h2 {
            font-size: 28px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .filters {
            display: flex;
            gap: 10px;
            background: white;
            padding: 10px 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .filters select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            outline: none;
        }

        .filters button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filters button:hover {
            background: var(--secondary);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card-icon.revenue { background: #e6f0ff; color: #4361ee; }
        .card-icon.profit { background: #e6f7e6; color: #2ecc71; }
        .card-icon.cost { background: #ffe6e6; color: #f72585; }
        .card-icon.net { background: #fff4e6; color: #f39c12; }

        .card-title {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            margin: 0;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .card-sub {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
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

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .metric-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
        }

        .metric-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
        }

        .metric-trend {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .table-responsive {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px 10px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 12px 10px;
            font-size: 13px;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .profit-positive {
            color: #059669;
            font-weight: 600;
        }

        .profit-negative {
            color: #b91c1c;
            font-weight: 600;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .export-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="page-header">
        <h2><i class="bi bi-graph-up" style="margin-right: 10px; color: var(--primary);"></i> Financial Report</h2>
        <div class="filters">
            <select id="year-select">
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $selected_year ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="month-select">
                <option value="0">All Months</option>
                <option value="1" <?php echo $selected_month == 1 ? 'selected' : ''; ?>>January</option>
                <option value="2" <?php echo $selected_month == 2 ? 'selected' : ''; ?>>February</option>
                <option value="3" <?php echo $selected_month == 3 ? 'selected' : ''; ?>>March</option>
                <option value="4" <?php echo $selected_month == 4 ? 'selected' : ''; ?>>April</option>
                <option value="5" <?php echo $selected_month == 5 ? 'selected' : ''; ?>>May</option>
                <option value="6" <?php echo $selected_month == 6 ? 'selected' : ''; ?>>June</option>
                <option value="7" <?php echo $selected_month == 7 ? 'selected' : ''; ?>>July</option>
                <option value="8" <?php echo $selected_month == 8 ? 'selected' : ''; ?>>August</option>
                <option value="9" <?php echo $selected_month == 9 ? 'selected' : ''; ?>>September</option>
                <option value="10" <?php echo $selected_month == 10 ? 'selected' : ''; ?>>October</option>
                <option value="11" <?php echo $selected_month == 11 ? 'selected' : ''; ?>>November</option>
                <option value="12" <?php echo $selected_month == 12 ? 'selected' : ''; ?>>December</option>
            </select>
            <button onclick="applyFilters()">Apply</button>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="export-buttons">
        <button class="export-btn" onclick="exportToCSV()">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export to CSV
        </button>
        <button class="export-btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="card-header">
                <div class="card-icon revenue">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="card-title">Total Revenue</div>
                    <div class="card-value">KES <?php echo number_format($summary['revenue']['total_revenue'], 2); ?></div>
                </div>
            </div>
            <div class="card-sub">
                <?php echo $summary['revenue']['transactions']; ?> transactions
            </div>
            <div class="metrics-grid" style="margin-top: 15px;">
                <div class="metric-item">
                    <div class="metric-label">Cash & M-Pesa</div>
                    <div class="metric-value">KES <?php echo number_format($summary['revenue']['paid_revenue'], 2); ?></div>
                </div>
                <div class="metric-item">
                    <div class="metric-label">Credit Sales</div>
                    <div class="metric-value">KES <?php echo number_format($summary['revenue']['credit_revenue'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon profit">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="card-title">Gross Profit</div>
                    <div class="card-value">KES <?php echo number_format($summary['profit']['total_profit'], 2); ?></div>
                </div>
            </div>
            <div class="card-sub">
                <?php echo number_format($summary['profit']['items_sold']); ?> items sold
            </div>
            <div class="metric-item" style="margin-top: 15px;">
                <div class="metric-label">Profit Margin</div>
                <div class="metric-value">
                    <?php
                    $margin = $summary['revenue']['total_revenue'] > 0
                        ? ($summary['profit']['total_profit'] / $summary['revenue']['total_revenue'] * 100)
                        : 0;
                    echo number_format($margin, 1) . '%';
                    ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon cost">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="card-title">Cost of Goods</div>
                    <div class="card-value">KES <?php echo number_format($summary['profit']['total_cost'], 2); ?></div>
                </div>
            </div>
            <div class="card-sub">
                Total buying price
            </div>
            <div class="metric-item" style="margin-top: 15px;">
                <div class="metric-label">Avg Cost per Item</div>
                <div class="metric-value">
                    KES <?php
                    $avg_cost = $summary['profit']['items_sold'] > 0
                        ? $summary['profit']['total_cost'] / $summary['profit']['items_sold']
                        : 0;
                    echo number_format($avg_cost, 2);
                    ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon net">
                    <i class="bi bi-piggy-bank"></i>
                </div>
                <div>
                    <div class="card-title">Net Profit (Est.)</div>
                    <div class="card-value">KES <?php echo number_format($net_profit, 2); ?></div>
                </div>
            </div>
            <div class="card-sub">
                After estimated expenses
            </div>
            <div class="metric-item" style="margin-top: 15px;">
                <div class="metric-label">Est. Expenses</div>
                <div class="metric-value">KES <?php echo number_format($estimated_expenses, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Monthly Revenue</h3>
                <span class="badge bg-light"><?php echo $selected_year; ?></span>
            </div>
            <div class="chart-wrapper">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3>Payment Methods</h3>
                <span class="badge bg-light">Breakdown</span>
            </div>
            <div class="chart-wrapper" style="height: 250px;">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Daily Sales (if month selected) -->
    <?php if ($selected_month > 0): ?>
    <div class="chart-container" style="margin-bottom: 30px;">
        <div class="chart-header">
            <h3>Daily Sales - <?php echo date('F', mktime(0, 0, 0, $selected_month, 1)); ?> <?php echo $selected_year; ?></h3>
        </div>
        <div class="chart-wrapper" style="height: 250px;">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Methods Breakdown -->
    <div class="table-responsive" style="margin-bottom: 30px;">
        <h4 style="margin-bottom: 20px;">Payment Method Breakdown</h4>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Transactions</th>
                    <th>Total Amount</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_payments = array_sum(array_column($payment_methods, 'total'));
                foreach ($payment_methods as $method):
                ?>
                <tr>
                    <td>
                        <?php if ($method['payment_method'] == 'Cash'): ?>
                            <i class="bi bi-cash" style="color: #2ecc71; margin-right: 8px;"></i>
                        <?php elseif ($method['payment_method'] == 'Mpesa'): ?>
                            <i class="bi bi-phone" style="color: #e84393; margin-right: 8px;"></i>
                        <?php else: ?>
                            <i class="bi bi-credit-card" style="color: #f39c12; margin-right: 8px;"></i>
                        <?php endif; ?>
                        <?php echo $method['payment_method'] ?: 'Other'; ?>
                    </td>
                    <td><?php echo $method['count']; ?></td>
                    <td>KES <?php echo number_format($method['total'], 2); ?></td>
                    <td>
                        <?php echo $total_payments > 0 ? number_format(($method['total'] / $total_payments * 100), 1) : 0; ?>%
                        <div class="progress" style="width: 100px; height: 6px; margin-top: 5px;">
                            <div class="progress-bar" style="width: <?php echo $total_payments > 0 ? ($method['total'] / $total_payments * 100) : 0; ?>%; background: <?php
                                echo $method['payment_method'] == 'Cash' ? '#2ecc71' : ($method['payment_method'] == 'Mpesa' ? '#e84393' : '#f39c12');
                            ?>;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payment_methods)): ?>
                <tr><td colspan="4" style="text-align: center;">No payment data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Products Table -->
    <div class="table-responsive">
        <h4 style="margin-bottom: 20px;">Top Products by Revenue</h4>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity Sold</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $index => $product): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($product['brandname']); ?></strong></td>
                    <td><?php echo number_format($product['quantity']); ?></td>
                    <td>KES <?php echo number_format($product['revenue'], 2); ?></td>
                    <td class="<?php echo $product['profit'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                        KES <?php echo number_format($product['profit'], 2); ?>
                    </td>
                    <td>
                        <?php echo number_format($product['margin'], 1); ?>%
                        <div class="progress" style="width: 80px; height: 4px; margin-top: 5px;">
                            <div class="progress-bar" style="width: <?php echo min($product['margin'], 100); ?>%; background: <?php echo $product['margin'] >= 30 ? '#059669' : ($product['margin'] >= 15 ? '#f39c12' : '#b91c1c'); ?>;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($top_products)): ?>
                <tr><td colspan="6" style="text-align: center;">No product data available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const year = document.getElementById('year-select').value;
    const month = document.getElementById('month-select').value;
    window.location.href = `financial_report.php?year=${year}&month=${month}`;
}

function exportToCSV() {
    // Collect data for CSV
    const rows = [
        ['Financial Report - <?php echo $selected_year; ?>' + (<?php echo $selected_month; ?> > 0 ? ' - <?php echo date('F', mktime(0, 0, 0, $selected_month, 1)); ?>' : '')],
        [],
        ['Summary'],
        ['Total Revenue', 'KES <?php echo number_format($summary['revenue']['total_revenue'], 2); ?>'],
        ['Gross Profit', 'KES <?php echo number_format($summary['profit']['total_profit'], 2); ?>'],
        ['Cost of Goods', 'KES <?php echo number_format($summary['profit']['total_cost'], 2); ?>'],
        ['Transactions', '<?php echo $summary['revenue']['transactions']; ?>'],
        ['Items Sold', '<?php echo number_format($summary['profit']['items_sold']); ?>'],
        [],
        ['Payment Methods'],
        ['Method', 'Transactions', 'Amount']
    ];

    <?php foreach ($payment_methods as $method): ?>
    rows.push(['<?php echo $method['payment_method']; ?>', '<?php echo $method['count']; ?>', 'KES <?php echo number_format($method['total'], 2); ?>']);
    <?php endforeach; ?>

    rows.push([]);
    rows.push(['Top Products']);
    rows.push(['Product', 'Quantity', 'Revenue', 'Profit', 'Margin %']);

    <?php foreach ($top_products as $product): ?>
    rows.push(['<?php echo addslashes($product['brandname']); ?>', '<?php echo $product['quantity']; ?>', 'KES <?php echo number_format($product['revenue'], 2); ?>', 'KES <?php echo number_format($product['profit'], 2); ?>', '<?php echo number_format($product['margin'], 1); ?>']);
    <?php endforeach; ?>

    // Convert to CSV
    let csv = rows.map(row => row.join(',')).join('\n');

    // Download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'financial_report_<?php echo $selected_year; ?>_<?php echo $selected_month > 0 ? $selected_month : 'all'; ?>.csv';
    a.click();
}

$(document).ready(function() {
    // Monthly Chart
    const monthlyData = <?php
        $chart_data = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart_data[] = isset($monthly_data[$m]) ? $monthly_data[$m]['revenue'] : 0;
        }
        echo json_encode($chart_data);
    ?>;

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue',
                data: monthlyData,
                backgroundColor: '#4361ee',
                borderRadius: 6
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
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Payment Chart
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: <?php
                $labels = array_column($payment_methods, 'payment_method');
                echo json_encode($labels);
            ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($payment_methods, 'total')); ?>,
                backgroundColor: ['#2ecc71', '#e84393', '#f39c12', '#3498db'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': KES ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    <?php if ($selected_month > 0): ?>
    // Daily Chart
    const daysInMonth = new Date(<?php echo $selected_year; ?>, <?php echo $selected_month; ?>, 0).getDate();
    const dailyLabels = [];
    const dailyValues = [];

    for (let d = 1; d <= daysInMonth; d++) {
        dailyLabels.push(d);
        dailyValues.push(<?php echo isset($daily_data[d]) ? $daily_data[d]['revenue'] : 0; ?>);
    }

    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Daily Sales',
                data: dailyValues,
                borderColor: '#f72585',
                backgroundColor: 'rgba(247, 37, 133, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
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
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>
</body>
</html>
<?php $conn->close(); ?>