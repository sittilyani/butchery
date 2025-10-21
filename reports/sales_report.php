<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
        session_start();
}
include "../includes/config.php";
include "../includes/header.php";

$page_title = "Sales Report";

// Get current date and month for top sellers
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m');

// Get top sellers for current date
$sql_today = "SELECT transBy, SUM(grand_total) as daily_total, COUNT(*) as transactions_count
                            FROM sales
                            WHERE DATE(transDate) = '$currentDate'
                            AND payment_status = 'paid'
                            GROUP BY transBy
                            ORDER BY daily_total DESC
                            LIMIT 5";

$result_today = $conn->query($sql_today);
$top_sellers_today = [];
if ($result_today) {
        while ($row = $result_today->fetch_assoc()) {
                $top_sellers_today[] = $row;
        }
}

// Get top sellers for current month
$sql_month = "SELECT transBy, SUM(grand_total) as monthly_total, COUNT(*) as transactions_count
                            FROM sales
                            WHERE DATE_FORMAT(transDate, '%Y-%m') = '$currentMonth'
                            AND payment_status = 'paid'
                            GROUP BY transBy
                            ORDER BY monthly_total DESC
                            LIMIT 5";

$result_month = $conn->query($sql_month);
$top_sellers_month = [];
if ($result_month) {
        while ($row = $result_month->fetch_assoc()) {
                $top_sellers_month[] = $row;
        }
}

// Get cumulative profits for today
$sql_profit_today = "SELECT SUM(sale_items.profit) as daily_profit
                     FROM sale_items
                     JOIN sales ON sale_items.sales_id = sales.sales_id
                     WHERE DATE(sales.transDate) = '$currentDate'";
$result_profit_today = $conn->query($sql_profit_today);
$profit_today = $result_profit_today ? ($result_profit_today->fetch_assoc()['daily_profit'] ?? 0) : 0;

// Get cumulative profits for current month
$sql_profit_month = "SELECT SUM(sale_items.profit) as monthly_profit
                     FROM sale_items
                     JOIN sales ON sale_items.sales_id = sales.sales_id
                     WHERE DATE_FORMAT(sales.transDate, '%Y-%m') = '$currentMonth'";
$result_profit_month = $conn->query($sql_profit_month);
$profit_month = $result_profit_month ? ($result_profit_month->fetch_assoc()['monthly_profit'] ?? 0) : 0;

// Get cumulative profits for current year
$currentYear = date('Y');
$sql_profit_year = "SELECT SUM(sale_items.profit) as yearly_profit
                    FROM sale_items
                    JOIN sales ON sale_items.sales_id = sales.sales_id
                    WHERE YEAR(sales.transDate) = '$currentYear'";
$result_profit_year = $conn->query($sql_profit_year);
$profit_year = $result_profit_year ? ($result_profit_year->fetch_assoc()['yearly_profit'] ?? 0) : 0;

// Get top 10 brands by profit for current month
$sql_top_brands = "SELECT sale_items.brandname, SUM(sale_items.profit) as total_profit
                   FROM sale_items
                   JOIN sales ON sale_items.sales_id = sales.sales_id
                   WHERE DATE_FORMAT(sales.transDate, '%Y-%m') = '$currentMonth'
                   GROUP BY sale_items.brandname
                   ORDER BY total_profit DESC
                   LIMIT 10";
$result_top_brands = $conn->query($sql_top_brands);
$top_brands = [];
if ($result_top_brands) {
        while ($row = $result_top_brands->fetch_assoc()) {
                $top_brands[] = $row;
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($page_title); ?></title>
        <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
        <style>
                
                .graph-container {
                        margin-bottom: 30px;
                }
                .canvas {
                        max-height: 300px;
                }
                #monthly-sales-container {
                        height: 900px;
                }
                #monthly-sales-canvas {
                        max-height: 900px;
                }

                /* Top Sellers Styling */
                .top-sellers-section {
                        background: white;
                        border-radius: 8px;
                        padding: 20px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .seller-item {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 10px 0;
                        border-bottom: 1px solid #eee;
                }

                .seller-item:last-child {
                        border-bottom: none;
                }

                .seller-rank {
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        color: white;
                        margin-right: 10px;
                }

                .rank-1 { background: #FFD700; color: #333; }
                .rank-2 { background: #C0C0C0; color: #333; }
                .rank-3 { background: #CD7F32; color: white; }
                .rank-other { background: #6c757d; }

                .seller-info {
                        flex-grow: 1;
                        margin-left: 10px;
                }

                .seller-name {
                        font-weight: 600;
                        color: #2c3e50;
                }

                .seller-transactions {
                        font-size: 0.85em;
                        color: #6c757d;
                }

                .seller-amount {
                        font-weight: bold;
                        color: #27ae60;
                        font-size: 1.1em;
                }

                .no-data {
                        text-align: center;
                        color: #6c757d;
                        font-style: italic;
                        padding: 20px;
                }

                /* Profit Summary Cards */
                .profit-card {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border-radius: 10px;
                        padding: 20px;
                        text-align: center;
                        margin-bottom: 20px;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }

                .profit-amount {
                        font-size: 2em;
                        font-weight: bold;
                        margin-bottom: 5px;
                }

                .profit-label {
                        font-size: 0.9em;
                        opacity: 0.9;
                }

                /* Top Brands Section */
                .top-brands-section {
                        background: white;
                        border-radius: 8px;
                        padding: 20px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .brand-item {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 10px 0;
                        border-bottom: 1px solid #eee;
                }

                .brand-item:last-child {
                        border-bottom: none;
                }

                .brand-rank {
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        color: white;
                        margin-right: 10px;
                        background: #28a745;
                }

                .brand-info {
                        flex-grow: 1;
                        margin-left: 10px;
                }

                .brand-name {
                        font-weight: 600;
                        color: #2c3e50;
                }

                .brand-profit {
                        font-weight: bold;
                        color: #28a745;
                        font-size: 1.1em;
                }
        </style>
</head>
<body>
<div class="main-content">
        <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?></h2>

        <!-- Profit Summary Cards -->
        <div class="row mb-4">
                <div class="col-md-4">
                        <div class="profit-card">
                                <div class="profit-amount">KES <?php echo number_format($profit_today, 2); ?></div>
                                <div class="profit-label">Profit Today<br>(<?php echo date('M d, Y'); ?>)</div>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="profit-card">
                                <div class="profit-amount">KES <?php echo number_format($profit_month, 2); ?></div>
                                <div class="profit-label">Profit This Month<br>(<?php echo date('F Y'); ?>)</div>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="profit-card">
                                <div class="profit-amount">KES <?php echo number_format($profit_year, 2); ?></div>
                                <div class="profit-label">Profit This Year<br>(<?php echo date('Y'); ?>)</div>
                        </div>
                </div>
        </div>

        <!-- Top Sellers Section -->
        <div class="row mb-4">
                <div class="col-md-6">
                        <div class="top-sellers-section">
                                <h5 class="mb-3">🏆 Top Sellers Today (<?php echo date('M d, Y'); ?>)</h5>
                                <?php if (count($top_sellers_today) > 0): ?>
                                        <?php foreach ($top_sellers_today as $index => $seller): ?>
                                        <div class="seller-item">
                                                <div class="d-flex align-items-center">
                                                        <div class="seller-rank <?php
                                                                if ($index == 0) echo 'rank-1';
                                                                elseif ($index == 1) echo 'rank-2';
                                                                elseif ($index == 2) echo 'rank-3';
                                                                else echo 'rank-other';
                                                        ?>">
                                                                <?php echo ($index + 1); ?>
                                                        </div>
                                                        <div class="seller-info">
                                                                <div class="seller-name"><?php echo htmlspecialchars($seller['transBy']); ?></div>
                                                                <div class="seller-transactions"><?php echo $seller['transactions_count']; ?> transactions</div>
                                                        </div>
                                                </div>
                                                <div class="seller-amount">KES <?php echo number_format($seller['daily_total'], 2); ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                <?php else: ?>
                                        <div class="no-data">No sales recorded for today</div>
                                <?php endif; ?>
                        </div>
                </div>

                <div class="col-md-6">
                        <div class="top-sellers-section">
                                <h5 class="mb-3">🏅 Top Sellers This Month (<?php echo date('F Y'); ?>)</h5>
                                <?php if (count($top_sellers_month) > 0): ?>
                                        <?php foreach ($top_sellers_month as $index => $seller): ?>
                                        <div class="seller-item">
                                                <div class="d-flex align-items-center">
                                                        <div class="seller-rank <?php
                                                                if ($index == 0) echo 'rank-1';
                                                                elseif ($index == 1) echo 'rank-2';
                                                                elseif ($index == 2) echo 'rank-3';
                                                                else echo 'rank-other';
                                                        ?>">
                                                                <?php echo ($index + 1); ?>
                                                        </div>
                                                        <div class="seller-info">
                                                                <div class="seller-name"><?php echo $seller['transBy']; ?></div>
                                                                <div class="seller-transactions"><?php echo $seller['transactions_count']; ?> transactions</div>
                                                        </div>
                                                </div>
                                                <div class="seller-amount">KES <?php echo number_format($seller['monthly_total'], 2); ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                <?php else: ?>
                                        <div class="no-data">No sales recorded for this month</div>
                                <?php endif; ?>
                        </div>
                </div>
        </div>

        <!-- Top Brands by Profit Section -->
        <div class="row mb-4">
                <div class="col-md-12">
                        <div class="top-brands-section">
                                <h5 class="mb-3">💰 Top 10 Brands by Profit This Month (<?php echo date('F Y'); ?>)</h5>
                                <?php if (count($top_brands) > 0): ?>
                                        <?php foreach ($top_brands as $index => $brand): ?>
                                        <div class="brand-item">
                                                <div class="d-flex align-items-center">
                                                        <div class="brand-rank">
                                                                <?php echo ($index + 1); ?>
                                                        </div>
                                                        <div class="brand-info">
                                                                <div class="brand-name"><?php echo htmlspecialchars($brand['brandname']); ?></div>
                                                        </div>
                                                </div>
                                                <div class="brand-profit">KES <?php echo number_format($brand['total_profit'] ?? 0.0, 2); ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                <?php else: ?>
                                        <div class="no-data">No profits recorded for this month</div>
                                <?php endif; ?>
                        </div>
                </div>
        </div>

        <!-- Original Charts Section -->
        <div class="row mb-3">
                <div class="col-md-4">
                        <label for="month-select" class="form-label">Select Month</label>
                        <select id="month-select" class="form-select">
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                        </select>
                </div>
        </div>

        <div class="row">
                <div class="col-md-8">
                        <div class="graph-container">
                                <h4>Daily Sales</h4>
                                <canvas id="daily-sales-chart" class="canvas"></canvas>
                        </div>
                        <div class="graph-container">
                                <h4>Sales by Payment Method</h4>
                                <canvas id="payment-sales-chart" class="canvas"></canvas>
                        </div>
                </div>
                <div class="col-md-4">
                        <div class="graph-container" id="monthly-sales-container">
                                <h4>Monthly Sales (<?php echo date('Y'); ?>)</h4>
                                <canvas id="monthly-sales-chart" class="canvas"></canvas>
                        </div>
                </div>
        </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
        let dailyChart, paymentChart, monthlyChart;

        // Set default month to current month
        const currentMonth = new Date().getMonth() + 1;
        $('#month-select').val(currentMonth);

        // Function to refresh top sellers data
        function refreshTopSellers() {
                location.reload();
        }

        // Function to fetch and update charts
        function fetchSalesData() {
                const selectedMonth = $('#month-select').val();
                $.ajax({
                        url: 'fetch_sales_data.php',
                        method: 'GET',
                        data: { month: selectedMonth },
                        success: function(response) {
                                if (response.status !== 'success') {
                                        alert('Error: ' + response.message);
                                        return;
                                }

                                // Prepare daily sales data
                                const dailyLabels = response.daily_sales.map(item => item.date);
                                const dailyData = response.daily_sales.map(item => item.total);

                                // Update daily sales chart
                                if (dailyChart) {
                                        dailyChart.data.labels = dailyLabels;
                                        dailyChart.data.datasets[0].data = dailyData;
                                        dailyChart.update();
                                } else {
                                        dailyChart = new Chart(document.getElementById('daily-sales-chart'), {
                                                type: 'line',
                                                data: {
                                                        labels: dailyLabels,
                                                        datasets: [{
                                                                label: 'Daily Sales (KES)',
                                                                data: dailyData,
                                                                borderColor: 'blue',
                                                                fill: false
                                                        }]
                                                },
                                                options: {
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        scales: {
                                                                x: { title: { display: true, text: 'Date' } },
                                                                y: { title: { display: true, text: 'Sales (KES)' } }
                                                        }
                                                }
                                        });
                                }

                                // Prepare monthly sales data
                                const monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                const monthlyData = response.monthly_sales;

                                // Update monthly sales chart
                                if (monthlyChart) {
                                        monthlyChart.data.datasets[0].data = monthlyData;
                                        monthlyChart.update();
                                } else {
                                        monthlyChart = new Chart(document.getElementById('monthly-sales-chart'), {
                                                type: 'bar',
                                                data: {
                                                        labels: monthlyLabels,
                                                        datasets: [{
                                                                label: 'Monthly Sales (KES)',
                                                                data: monthlyData,
                                                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                                                borderColor: 'rgba(75, 192, 192, 1)',
                                                                borderWidth: 1
                                                        }]
                                                },
                                                options: {
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        scales: {
                                                                x: { title: { display: true, text: 'Month' } },
                                                                y: { title: { display: true, text: 'Sales (KES)' } }
                                                        }
                                                }
                                        });
                                }

                                // Prepare payment method sales data
                                const paymentMethods = ['Cash', 'Mpesa'];
                                const paymentDates = [...new Set(response.payment_sales.map(item => item.date))];
                                const paymentDatasets = paymentMethods.map(method => {
                                        const data = paymentDates.map(date => {
                                                const sale = response.payment_sales.find(s => s.date === date && s.payment_method === method);
                                                return sale ? sale.total : 0;
                                        });
                                        return {
                                                label: method,
                                                data: data,
                                                borderColor: method === 'Cash' ? 'green' : 'purple',
                                                fill: false
                                        };
                                });

                                // Update payment method sales chart
                                if (paymentChart) {
                                        paymentChart.data.labels = paymentDates;
                                        paymentChart.data.datasets = paymentDatasets;
                                        paymentChart.update();
                                } else {
                                        paymentChart = new Chart(document.getElementById('payment-sales-chart'), {
                                                type: 'line',
                                                data: {
                                                        labels: paymentDates,
                                                        datasets: paymentDatasets
                                                },
                                                options: {
                                                        responsive: true,
                                                        maintainAspectRatio: false,
                                                        scales: {
                                                                x: { title: { display: true, text: 'Date' } },
                                                                y: { title: { display: true, text: 'Sales (KES)' } }
                                                        }
                                                }
                                        });
                                }
                        },
                        error: function(xhr) {
                                console.error('AJAX Error:', xhr.responseText);
                                alert('Failed to fetch sales data.');
                        }
                });
        }

        // Initial fetch
        fetchSalesData();

        // Refresh every 3 minutes (180,000 ms) - includes top sellers refresh
        setInterval(function() {
                fetchSalesData();
                refreshTopSellers();
        }, 180000);

        // Update charts on month change
        $('#month-select').on('change', function() {
                fetchSalesData();
        });
});
</script>
</body>
</html>