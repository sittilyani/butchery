<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

// Function to fetch data for a given period (default: all time)
// You can pass start_date and end_date as GET parameters, e.g., ?start_date=2025-01-01&end_date=2025-08-30
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1900-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Note: Assuming 'stocks' table has additional columns: transactionType, quantityIn, quantityOut, and date_created is used for transaction date.
// unit_price is purchase cost, price is selling price.
// Revenue from SUM(grand_total) in sales.
// COGS: Calculate average unit_price per brandname from purchase transactions, then sum(quantityOut * avg_unit_price) for transactionType = 'sale'.
// Similarly for donations, expiries, negative adjustments as losses.
// Inventory value: sum( (sum(quantityIn) - sum(quantityOut)) * avg_unit_price ) per brandname.

// Suggested Additional Tables (as before):
// CREATE TABLE expenses (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     expense_type VARCHAR(255),  // e.g., 'bills', 'salaries', 'other'
//     amount DECIMAL(10,2),
//     description TEXT,
//     transDate DATE,
//     created_by VARCHAR(255)
// );

// If stocks doesn't have transactionType, quantityIn, quantityOut, add them:
// ALTER TABLE stocks ADD transactionType VARCHAR(255);
// ALTER TABLE stocks ADD quantityIn INT DEFAULT 0;
// ALTER TABLE stocks ADD quantityOut INT DEFAULT 0;

// Query for Sales Revenue
$sales_sql = "SELECT SUM(grand_total) AS total_revenue FROM sales WHERE transDate BETWEEN '$start_date' AND '$end_date' AND payment_status = 'paid'";
$sales_result = $conn->query($sales_sql);
$total_revenue = $sales_result->fetch_assoc()['total_revenue'] ?? 0;

// Query for Accounts Receivable (from credit_balances, assuming balance_amount is outstanding)
$credits_sql = "SELECT SUM(balance_amount) AS total_receivables FROM credit_balances WHERE transDate BETWEEN '$start_date' AND '$end_date' AND status = 'pending'";
$credits_result = $conn->query($credits_sql);
$total_receivables = $credits_result->fetch_assoc()['total_receivables'] ?? 0;

// Get average unit_price per brandname from purchase transactions
$avg_prices = [];
$avg_sql = "SELECT brandname, AVG(unit_price) AS avg_unit_price FROM stocks WHERE transactionType = 'purchase' AND date_created BETWEEN '$start_date' AND '$end_date' GROUP BY brandname";
$avg_result = $conn->query($avg_sql);
while ($row = $avg_result->fetch_assoc()) {
    $avg_prices[$row['brandname']] = $row['avg_unit_price'] ?? 0;
}

// Calculate COGS: sum(quantityOut * avg_unit_price) for sales
$cogs = 0;
$cogs_sql = "SELECT brandname, SUM(quantityOut) AS sold_qty FROM stocks WHERE transactionType = 'sale' AND date_created BETWEEN '$start_date' AND '$end_date' GROUP BY brandname";
$cogs_result = $conn->query($cogs_sql);
while ($row = $cogs_result->fetch_assoc()) {
    $brandname = $row['brandname'];
    $sold_qty = $row['sold_qty'] ?? 0;
    $avg_price = $avg_prices[$brandname] ?? 0;
    $cogs += $sold_qty * $avg_price;
}

// Calculate Donations Out loss
$total_donations_out = 0;
$donations_sql = "SELECT brandname, SUM(quantityOut) AS qty_out FROM stocks WHERE transactionType = 'donation' AND date_created BETWEEN '$start_date' AND '$end_date' GROUP BY brandname";
$donations_result = $conn->query($donations_sql);
while ($row = $donations_result->fetch_assoc()) {
    $brandname = $row['brandname'];
    $qty_out = $row['qty_out'] ?? 0;
    $avg_price = $avg_prices[$brandname] ?? 0;
    $total_donations_out += $qty_out * $avg_price;
}

// Calculate Expiries loss
$total_expiries = 0;
$expiries_sql = "SELECT brandname, SUM(quantityOut) AS qty_out FROM stocks WHERE transactionType = 'expiry' AND date_created BETWEEN '$start_date' AND '$end_date' GROUP BY brandname";
$expiries_result = $conn->query($expiries_sql);
while ($row = $expiries_result->fetch_assoc()) {
    $brandname = $row['brandname'];
    $qty_out = $row['qty_out'] ?? 0;
    $avg_price = $avg_prices[$brandname] ?? 0;
    $total_expiries += $qty_out * $avg_price;
}

// Calculate Negative Adjustments loss
$total_negative_adjustments = 0;
$adjustments_sql = "SELECT brandname, SUM(quantityOut) AS qty_out FROM stocks WHERE transactionType = 'negative adjustment' AND date_created BETWEEN '$start_date' AND '$end_date' GROUP BY brandname";
$adjustments_result = $conn->query($adjustments_sql);
while ($row = $adjustments_result->fetch_assoc()) {
    $brandname = $row['brandname'];
    $qty_out = $row['qty_out'] ?? 0;
    $avg_price = $avg_prices[$brandname] ?? 0;
    $total_negative_adjustments += $qty_out * $avg_price;
}

// Total losses from donations, expiries, adjustments
$total_losses = $total_donations_out + $total_expiries + $total_negative_adjustments;

// Gross Profit
$gross_profit = $total_revenue - $cogs;

// Expenses (if table exists)
$total_expenses = 0;
$expenses_sql = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE transDate BETWEEN '$start_date' AND '$end_date'";
$expenses_result = $conn->query($expenses_sql);
if ($expenses_result) {
    $total_expenses = $expenses_result->fetch_assoc()['total_expenses'] ?? 0;
}

// Net Profit
$net_profit = $gross_profit - $total_expenses - $total_losses;

// Inventory Value
$inventory_value = 0;
$inventory_sql = "SELECT brandname, SUM(quantityIn) - SUM(quantityOut) AS current_qty FROM stocks GROUP BY brandname";
$inventory_result = $conn->query($inventory_sql);
while ($row = $inventory_result->fetch_assoc()) {
    $brandname = $row['brandname'];
    $current_qty = $row['current_qty'] ?? 0;
    if ($current_qty > 0) {  // Avoid negative inventory
        $avg_price = $avg_prices[$brandname] ?? 0;
        $inventory_value += $current_qty * $avg_price;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Financial Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-bottom: 40px; }
    </style>
</head>
<body>
    <h1>Full Financial Report</h1>
    <p>Period: <?php echo $start_date; ?> to <?php echo $end_date; ?></p>

    <div class="section">
        <h2>Profit and Loss Statement</h2>
        <table>
            <tr><th>Description</th><th>Amount</th></tr>
            <tr><td>Revenue from Sales</td><td><?php echo number_format($total_revenue, 2); ?></td></tr>
            <tr><td>Cost of Goods Sold (COGS)</td><td><?php echo number_format($cogs, 2); ?></td></tr>
            <tr><td>Gross Profit</td><td><?php echo number_format($gross_profit, 2); ?></td></tr>
            <tr><td>Expenses (Bills, Salaries, etc.)</td><td><?php echo number_format($total_expenses, 2); ?></td></tr>
            <tr><td>Donations Out</td><td><?php echo number_format($total_donations_out, 2); ?></td></tr>
            <tr><td>Expiries</td><td><?php echo number_format($total_expiries, 2); ?></td></tr>
            <tr><td>Negative Adjustments</td><td><?php echo number_format($total_negative_adjustments, 2); ?></td></tr>
            <tr><td><strong>Net Profit</strong></td><td><strong><?php echo number_format($net_profit, 2); ?></strong></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Balance Sheet Excerpt</h2>
        <table>
            <tr><th>Assets</th><th>Amount</th></tr>
            <tr><td>Accounts Receivable (Credit Balances)</td><td><?php echo number_format($total_receivables, 2); ?></td></tr>
            <tr><td>Inventory Value</td><td><?php echo number_format($inventory_value, 2); ?></td></tr>
        </table>
        <!-- Add more sections like liabilities if data available -->
    </div>

    <div class="section">
        <h2>Notes</h2>
        <ul>
            <li>Revenue uses SUM(grand_total) from sales.</li>
            <li>COGS uses average unit_price per brandname from purchases, multiplied by sum(quantityOut) for sales.</li>
            <li>Losses (donations, expiries, negative adjustments) calculated similarly using quantityOut and avg unit_price.</li>
            <li>Inventory value: (sum(quantityIn) - sum(quantityOut)) * avg_unit_price per brandname (positive only).</li>
            <li>Assumes stocks table has transactionType, quantityIn, quantityOut columns; add if missing.</li>
            <li>Expenses from separate 'expenses' table; create if needed.</li>
            <li>Run this PHP file on a server with DB access. Filter by dates via URL params.</li>
        </ul>
    </div>
</body>
</html>