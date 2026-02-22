<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../includes/config.php";
include "../includes/header.php";

$page_title = "Staff Performance Report";

// Get years for filter - FIXED: transDate with capital D
$years_query = "SELECT DISTINCT YEAR(transDate) as year FROM sales ORDER BY year DESC";
$years_result = $conn->query($years_query);
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['year'];
}
if (empty($years)) {
    $years[] = date('Y');
}

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : $years[0];
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$selected_staff = isset($_GET['staff']) ? $_GET['staff'] : '';

// Get all staff who have made sales
$staff_query = "SELECT DISTINCT transBy FROM sales ORDER BY transBy";
$staff_result = $conn->query($staff_query);
$staff_list = [];
while ($row = $staff_result->fetch_assoc()) {
    $staff_list[] = $row['transBy'];
}

// Build WHERE clause - FIXED: transDate with capital D
$where = "WHERE YEAR(s.transDate) = $selected_year";
if ($selected_month > 0) {
    $where .= " AND MONTH(s.transDate) = $selected_month";
}
if (!empty($selected_staff)) {
    $where .= " AND s.transBy = '" . $conn->real_escape_string($selected_staff) . "'";
}

// Staff performance summary - FIXED: transDate with capital D
$staff_query = "SELECT
    s.transBy,
    COUNT(DISTINCT s.sales_id) as transaction_count,
    COALESCE(SUM(s.grand_total), 0) as total_sales,
    COALESCE(SUM(CASE WHEN s.payment_method = 'Cash' THEN s.grand_total ELSE 0 END), 0) as cash_sales,
    COALESCE(SUM(CASE WHEN s.payment_method = 'Mpesa' THEN s.grand_total ELSE 0 END), 0) as mpesa_sales,
    COALESCE(SUM(CASE WHEN s.payment_status = 'Credit' THEN s.grand_total ELSE 0 END), 0) as credit_sales,
    COALESCE(AVG(s.grand_total), 0) as avg_transaction_value,
    COALESCE(SUM(si.profit), 0) as total_profit,
    COALESCE(SUM(si.quantity), 0) as items_sold,
    COUNT(DISTINCT DATE(s.transDate)) as active_days,
    MIN(DATE(s.transDate)) as first_sale,
    MAX(DATE(s.transDate)) as last_sale
FROM sales s
LEFT JOIN sale_items si ON s.sales_id = si.sales_id
$where
GROUP BY s.transBy
ORDER BY total_sales DESC";

$staff_result = $conn->query($staff_query);
$staff_performance = [];
while ($row = $staff_result->fetch_assoc()) {
    $staff_performance[] = $row;
}

// Monthly trend for selected staff or overall - FIXED: transDate with capital D
$trend_where = "WHERE YEAR(s.transDate) = $selected_year";
if (!empty($selected_staff)) {
    $trend_where .= " AND s.transBy = '" . $conn->real_escape_string($selected_staff) . "'";
}

$trend_query = "SELECT
    MONTH(s.transDate) as month,
    COUNT(*) as transactions,
    COALESCE(SUM(s.grand_total), 0) as revenue,
    COALESCE(SUM(si.profit), 0) as profit
FROM sales s
LEFT JOIN sale_items si ON s.sales_id = si.sales_id
$trend_where
GROUP BY MONTH(s.transDate)
ORDER BY month";
$trend_result = $conn->query($trend_query);
$trend_data = [];
while ($row = $trend_result->fetch_assoc()) {
    $trend_data[$row['month']] = $row;
}

// Daily performance for selected month - FIXED: transDate with capital D
$daily_data = [];
if ($selected_month > 0 && !empty($selected_staff)) {
    $daily_query = "SELECT
        DAY(s.transDate) as day,
        COUNT(*) as transactions,
        COALESCE(SUM(s.grand_total), 0) as revenue
    FROM sales s
    WHERE YEAR(s.transDate) = $selected_year
        AND MONTH(s.transDate) = $selected_month
        AND s.transBy = '" . $conn->real_escape_string($selected_staff) . "'
    GROUP BY DAY(s.transDate)
    ORDER BY day";
    $daily_result = $conn->query($daily_query);
    while ($row = $daily_result->fetch_assoc()) {
        $daily_data[$row['day']] = $row;
    }
}

// Overall summary - FIXED: transDate with capital D
$overall_query = "SELECT
    COUNT(DISTINCT s.transBy) as total_staff,
    COUNT(*) as total_transactions,
    COALESCE(SUM(s.grand_total), 0) as total_revenue,
    COALESCE(SUM(si.profit), 0) as total_profit
FROM sales s
LEFT JOIN sale_items si ON s.sales_id = si.sales_id
WHERE YEAR(s.transDate) = $selected_year";
$overall = $conn->query($overall_query)->fetch_assoc();
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
            padding: 15px 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 500;
            color: #475569;
            font-size: 14px;
        }

        .filter-group select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            outline: none;
            min-width: 150px;
        }

        .filter-group button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-group button:hover {
            background: var(--secondary);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .card-icon.staff { background: #e6f0ff; color: #4361ee; }
        .card-icon.sales { background: #e6f7e6; color: #2ecc71; }
        .card-icon.profit { background: #fff4e6; color: #f39c12; }
        .card-icon.transactions { background: #f3e8ff; color: #7209b7; }

        .card-title {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            margin: 0;
        }

        .card-value {
            font-size: 24px;
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
            grid-template-columns: 1fr 1fr;
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

        .table-responsive {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h4 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
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
            border-bottom: 2px solid #e2e8f0;
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

        .staff-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .staff-avatar {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: #e6f0ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .staff-name {
            font-weight: 600;
            color: #1e293b;
        }

        .badge-success {
            background: #d1fae5;
            color: #059669;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-warning {
            background: #fed7aa;
            color: #b45309;
            padding: 4px 10px;
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

        .rank-1 { background: #fef9c3; color: #854d0e; }
        .rank-2 { background: #e5e5e5; color: #404040; }
        .rank-3 { background: #fed7aa; color: #92400e; }

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

            .filters {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select {
                flex: 1;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="page-header">
        <h2><i class="bi bi-people" style="margin-right: 10px; color: var(--primary);"></i> Staff Performance Report</h2>
        <button class="export-btn" onclick="exportToCSV()">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export to CSV
        </button>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-group">
            <label><i class="bi bi-calendar"></i> Year:</label>
            <select id="year-select">
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $selected_year ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="bi bi-calendar-month"></i> Month:</label>
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
        </div>
        <div class="filter-group">
            <label><i class="bi bi-person"></i> Staff:</label>
            <select id="staff-select">
                <option value="">All Staff</option>
                <?php foreach ($staff_list as $staff): ?>
                    <option value="<?php echo htmlspecialchars($staff); ?>" <?php echo $staff == $selected_staff ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($staff); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <button onclick="applyFilters()">Apply Filters</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="card-header">
                <div class="card-icon staff">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="card-title">Active Staff</div>
                    <div class="card-value"><?php echo $overall['total_staff'] ?: 0; ?></div>
                </div>
            </div>
            <div class="card-sub">with sales in <?php echo $selected_year; ?></div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-icon transactions">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div>
                    <div class="card-title">Total Transactions</div>
                    <div class="card-value"><?php echo number_format($overall['total_transactions'] ?: 0); ?></div>
                </div>
            </div>
            <div class="card-sub">across all staff</div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-icon sales">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="card-title">Total Revenue</div>
                    <div class="card-value">KES <?php echo number_format($overall['total_revenue'] ?: 0, 2); ?></div>
                </div>
            </div>
            <div class="card-sub">generated by staff</div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-icon profit">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="card-title">Total Profit</div>
                    <div class="card-value">KES <?php echo number_format($overall['total_profit'] ?: 0, 2); ?></div>
                </div>
            </div>
            <div class="card-sub">
                Margin: <?php echo $overall['total_revenue'] > 0 ? number_format(($overall['total_profit'] / $overall['total_revenue'] * 100), 1) : 0; ?>%
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Staff Performance Comparison</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="staffComparisonChart"></canvas>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-header">
                <h3><?php echo !empty($selected_staff) ? htmlspecialchars($selected_staff) : 'Staff'; ?> Monthly Trend</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Staff Performance Table -->
    <div class="table-responsive">
        <div class="table-header">
            <h4>Staff Performance Details</h4>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Staff</th>
                    <th>Transactions</th>
                    <th>Items Sold</th>
                    <th>Active Days</th>
                    <th>Avg Transaction</th>
                    <th>Cash Sales</th>
                    <th>M-Pesa Sales</th>
                    <th>Credit Sales</th>
                    <th>Total Revenue</th>
                    <th>Total Profit</th>
                    <th>Performance</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $max_sales = !empty($staff_performance) ? max(array_column($staff_performance, 'total_sales')) : 0;
                foreach ($staff_performance as $index => $staff):
                ?>
                <tr>
                    <td>
                        <div class="staff-avatar" style="background: <?php
                            if ($index == 0) echo '#fef9c3';
                            elseif ($index == 1) echo '#e5e5e5';
                            elseif ($index == 2) echo '#fed7aa';
                            else echo '#e6f0ff';
                        ?>; color: <?php
                            if ($index == 0) echo '#854d0e';
                            elseif ($index == 1) echo '#404040';
                            elseif ($index == 2) echo '#92400e';
                            else echo '#4361ee';
                        ?>;">
                            <?php echo $index + 1; ?>
                        </div>
                    </td>
                    <td>
                        <div class="staff-info">
                            <span class="staff-name"><?php echo htmlspecialchars($staff['transBy']); ?></span>
                        </div>
                    </td>
                    <td><strong><?php echo $staff['transaction_count']; ?></strong></td>
                    <td><?php echo number_format($staff['items_sold']); ?></td>
                    <td><?php echo $staff['active_days']; ?> days</td>
                    <td>KES <?php echo number_format($staff['avg_transaction_value'], 2); ?></td>
                    <td>KES <?php echo number_format($staff['cash_sales'], 2); ?></td>
                    <td>KES <?php echo number_format($staff['mpesa_sales'], 2); ?></td>
                    <td>KES <?php echo number_format($staff['credit_sales'], 2); ?></td>
                    <td><strong>KES <?php echo number_format($staff['total_sales'], 2); ?></strong></td>
                    <td class="<?php echo $staff['total_profit'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                        KES <?php echo number_format($staff['total_profit'], 2); ?>
                    </td>
                    <td>
                        <div class="progress" style="width: 100px;">
                            <div class="progress-bar" style="width: <?php echo $max_sales > 0 ? ($staff['total_sales'] / $max_sales * 100) : 0; ?>%;"></div>
                        </div>
                        <small><?php echo $max_sales > 0 ? number_format(($staff['total_sales'] / $max_sales * 100), 1) : 0; ?>% of top</small>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($staff_performance)): ?>
                <tr><td colspan="12" style="text-align: center; padding: 40px; color: #94a3b8;">No staff performance data available for selected filters</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const year = document.getElementById('year-select').value;
    const month = document.getElementById('month-select').value;
    const staff = document.getElementById('staff-select').value;
    window.location.href = `staff_performance.php?year=${year}&month=${month}&staff=${encodeURIComponent(staff)}`;
}

function exportToCSV() {
    const rows = [
        ['Staff Performance Report - <?php echo $selected_year; ?>' + (<?php echo $selected_month; ?> > 0 ? ' - Month <?php echo $selected_month; ?>' : '')],
        [],
        ['Summary'],
        ['Active Staff', '<?php echo $overall['total_staff'] ?: 0; ?>'],
        ['Total Transactions', '<?php echo number_format($overall['total_transactions'] ?: 0); ?>'],
        ['Total Revenue', 'KES <?php echo number_format($overall['total_revenue'] ?: 0, 2); ?>'],
        ['Total Profit', 'KES <?php echo number_format($overall['total_profit'] ?: 0, 2); ?>'],
        [],
        ['Staff Performance'],
        ['Staff', 'Transactions', 'Items Sold', 'Active Days', 'Avg Transaction', 'Cash Sales', 'M-Pesa Sales', 'Credit Sales', 'Total Revenue', 'Total Profit']
    ];

    <?php foreach ($staff_performance as $staff): ?>
    rows.push([
        '<?php echo addslashes($staff['transBy']); ?>',
        '<?php echo $staff['transaction_count']; ?>',
        '<?php echo $staff['items_sold']; ?>',
        '<?php echo $staff['active_days']; ?>',
        'KES <?php echo number_format($staff['avg_transaction_value'], 2); ?>',
        'KES <?php echo number_format($staff['cash_sales'], 2); ?>',
        'KES <?php echo number_format($staff['mpesa_sales'], 2); ?>',
        'KES <?php echo number_format($staff['credit_sales'], 2); ?>',
        'KES <?php echo number_format($staff['total_sales'], 2); ?>',
        'KES <?php echo number_format($staff['total_profit'], 2); ?>'
    ]);
    <?php endforeach; ?>

    let csv = rows.map(row => row.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'staff_performance_<?php echo $selected_year; ?>_<?php echo $selected_month; ?>.csv';
    a.click();
}

$(document).ready(function() {
    // Staff Comparison Chart
    const staffLabels = <?php
        $labels = array_column($staff_performance, 'transBy');
        $sales = array_column($staff_performance, 'total_sales');
        $profits = array_column($staff_performance, 'total_profit');
        echo json_encode(['labels' => $labels, 'sales' => $sales, 'profits' => $profits]);
    ?>;

    if (staffLabels.labels.length > 0) {
        new Chart(document.getElementById('staffComparisonChart'), {
            type: 'bar',
            data: {
                labels: staffLabels.labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: staffLabels.sales,
                        backgroundColor: '#4361ee',
                        borderRadius: 6
                    },
                    {
                        label: 'Profit',
                        data: staffLabels.profits,
                        backgroundColor: '#4cc9f0',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': KES ' + context.raw.toLocaleString();
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
    } else {
        document.getElementById('staffComparisonChart').parentElement.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 50px;">No staff data available</div>';
    }

    // Monthly Trend Chart
    const monthlyData = <?php
        $trend_values = [];
        for ($m = 1; $m <= 12; $m++) {
            $trend_values[] = isset($trend_data[$m]) ? $trend_data[$m]['revenue'] : 0;
        }
        echo json_encode($trend_values);
    ?>;

    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Revenue',
                data: monthlyData,
                borderColor: '#f72585',
                backgroundColor: 'rgba(247, 37, 133, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#f72585',
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
});
</script>
</body>
</html>
<?php $conn->close(); ?>