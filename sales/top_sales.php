<?php
// Establish connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "pharmacy");
// Check connection
if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
}

// Get current date and month
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m');

// Get top sellers for current date
$sql_today = "SELECT transBy, SUM(grand_total) as daily_total, COUNT(*) as transactions_count
                            FROM sales
                            WHERE DATE(created_at) = '$currentDate'
                            AND payment_status = 'paid'
                            GROUP BY transBy
                            ORDER BY daily_total DESC
                            LIMIT 10";

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
                            WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'
                            AND payment_status = 'paid'
                            GROUP BY transBy
                            ORDER BY monthly_total DESC
                            LIMIT 10";

$result_month = $conn->query($sql_month);
$top_sellers_month = [];
if ($result_month) {
        while ($row = $result_month->fetch_assoc()) {
                $top_sellers_month[] = $row;
        }
}

// Close the database connection
$conn->close();

// Include footer if needed
include '../includes/footer.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Top Sellers Dashboard</title>
        <style>
                body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        background-color: #f8f9fa;
                }
                .container {
                        max-width: 1200px;
                        margin: 0 auto;
                }
                .section {
                        background: white;
                        margin: 20px 0;
                        padding: 25px;
                        border-radius: 10px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .section h2 {
                        color: #2c3e50;
                        border-bottom: 3px solid #3498db;
                        padding-bottom: 10px;
                        margin-bottom: 20px;
                }
                table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 15px;
                }
                th {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 12px;
                        text-align: left;
                        font-weight: 600;
                }
                td {
                        padding: 12px;
                        border-bottom: 1px solid #eee;
                }
                tr:nth-child(even) {
                        background-color: #f8f9fa;
                }
                tr:hover {
                        background-color: #e3f2fd;
                }
                .rank {
                        font-weight: bold;
                        color: #3498db;
                        text-align: center;
                }
                .gold { color: #f1c40f; }
                .silver { color: #95a5a6; }
                .bronze { color: #e67e22; }
                .no-data {
                        text-align: center;
                        color: #7f8c8d;
                        font-style: italic;
                        padding: 30px;
                }
                .total-amount {
                        font-weight: bold;
                        color: #27ae60;
                }
                .header-icon {
                        display: inline-block;
                        margin-right: 10px;
                        font-size: 24px;
                }
        </style>
</head>
<body>
        <div class="container">
                <h1>?? Top Sellers Dashboard</h1>

                <!-- Top Sellers Today Section -->
                <div class="section">
                        <h2><span class="header-icon">??</span>Top Sellers - Today (<?php echo date('M d, Y'); ?>)</h2>
                        <?php if (count($top_sellers_today) > 0): ?>
                                <table>
                                        <thead>
                                                <tr>
                                                        <th style="width: 10%;">Rank</th>
                                                        <th style="width: 40%;">Seller (transBy)</th>
                                                        <th style="width: 25%;">Total Sales</th>
                                                        <th style="width: 25%;">Transactions</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ($top_sellers_today as $index => $seller): ?>
                                                <tr>
                                                        <td class="rank <?php
                                                                if ($index == 0) echo 'gold';
                                                                elseif ($index == 1) echo 'silver';
                                                                elseif ($index == 2) echo 'bronze';
                                                        ?>">
                                                                <?php echo ($index + 1); ?>
                                                                <?php if ($index < 3): ?>
                                                                        <?php echo ['??', '??', '??'][$index]; ?>
                                                                <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($seller['transBy']); ?></td>
                                                        <td class="total-amount">$<?php echo number_format($seller['daily_total'], 2); ?></td>
                                                        <td><?php echo $seller['transactions_count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>
                        <?php else: ?>
                                <div class="no-data">?? No sales recorded for today.</div>
                        <?php endif; ?>
                </div>

                <!-- Top Sellers This Month Section -->
                <div class="section">
                        <h2><span class="header-icon">??</span>Top Sellers - This Month (<?php echo date('F Y'); ?>)</h2>
                        <?php if (count($top_sellers_month) > 0): ?>
                                <table>
                                        <thead>
                                                <tr>
                                                        <th style="width: 10%;">Rank</th>
                                                        <th style="width: 40%;">Seller (transBy)</th>
                                                        <th style="width: 25%;">Total Sales</th>
                                                        <th style="width: 25%;">Transactions</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ($top_sellers_month as $index => $seller): ?>
                                                <tr>
                                                        <td class="rank <?php
                                                                if ($index == 0) echo 'gold';
                                                                elseif ($index == 1) echo 'silver';
                                                                elseif ($index == 2) echo 'bronze';
                                                        ?>">
                                                                <?php echo ($index + 1); ?>
                                                                <?php if ($index < 3): ?>
                                                                        <?php echo ['??', '??', '??'][$index]; ?>
                                                                <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($seller['transBy']); ?></td>
                                                        <td class="total-amount">$<?php echo number_format($seller['monthly_total'], 2); ?></td>
                                                        <td><?php echo $seller['transactions_count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>
                        <?php else: ?>
                                <div class="no-data">?? No sales recorded for this month.</div>
                        <?php endif; ?>
                </div>

                <div style="text-align: center; color: #888; font-size: 14px; margin-top: 30px;">
                        Last updated: <?php echo date('M d, Y g:i A'); ?>
                </div>
        </div>
</body>
</html>