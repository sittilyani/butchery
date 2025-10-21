<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

// Function to fetch data for a given period (default: all time)
// You can pass start_date and end_date as GET parameters, e.g., ?start_date=2025-01-01&end_date=2025-08-30
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '1900-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Note: Assuming 'stocks' table is the inventory/products table, with 'unit_price' as buying cost.
// 'price' in stocks might be selling price, but we'll use sales data for actual sales prices.
// COGS calculation assumes we can approximate based on sales items (parsing 'items' column in sales, assuming it's JSON like [{"productname":"item","quantity":x,"price":y}]).
// For simplicity, we'll sum total_amount from sales as revenue, and estimate COGS if possible.
// Credit balances for accounts receivable.

// Suggested Additional Tables:
// Since no tables exist for expenses, donations out, expiries, etc., here are creation queries you can run in your DB:

// CREATE TABLE expenses (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     expense_type VARCHAR(255),  // e.g., 'bills', 'salaries', 'other'
//     amount DECIMAL(10,2),
//     description TEXT,
//     transDate DATE,
//     created_by VARCHAR(255)
// );

// CREATE TABLE donations_out (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     productname VARCHAR(255),
//     quantity INT,
//     value DECIMAL(10,2),  // estimated value
//     transDate DATE,
//     created_by VARCHAR(255)
// );

// CREATE TABLE expiries (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     productname VARCHAR(255),
//     quantity INT,
//     value DECIMAL(10,2),  // cost value
//     expiry_date DATE,
//     transDate DATE,
//     created_by VARCHAR(255)
// );

// If you create these, add queries below to include them in the report.

// Query for Sales Revenue
$sales_sql = "SELECT SUM(grand_total) AS total_revenue FROM sales WHERE transDate BETWEEN '$start_date' AND '$end_date' AND payment_status = 'paid'";
$sales_result = $conn->query($sales_sql);
$total_revenue = $sales_result->fetch_assoc()['total_revenue'] ?? 0;

// Query for Accounts Receivable (from credit_balances, assuming balance_amount is outstanding)
$credits_sql = "SELECT SUM(balance_amount) AS total_receivables FROM credit_balances WHERE transDate BETWEEN '$start_date' AND '$end_date' AND status = 'pending'";
$credits_result = $conn->query($credits_sql);
$total_receivables = $credits_result->fetch_assoc()['total_receivables'] ?? 0;

// Approximate COGS: This requires parsing 'items' from sales. Assuming 'items' is JSON array of objects with 'productname', 'quantity', 'price'.
// We'll fetch all sales, parse items, lookup unit_price from stocks, sum (unit_price * quantity) for COGS.
$cogs = 0;
$sales_items_sql = "SELECT items FROM sales WHERE transDate BETWEEN '$start_date' AND '$end_date'";
$sales_items_result = $conn->query($sales_items_sql);
if ($sales_items_result->num_rows > 0) {
    while ($row = $sales_items_result->fetch_assoc()) {
        $items = json_decode($row['items'], true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $productname = mysqli_real_escape_string($conn, $item['productname']);
                $quantity = $item['quantity'] ?? 0;
                // Lookup unit_price (buying cost) from stocks
                $stock_sql = "SELECT unit_price FROM stocks WHERE productname = '$productname' LIMIT 1";
                $stock_result = $conn->query($stock_sql);
                $unit_price = $stock_result->fetch_assoc()['unit_price'] ?? 0;
                $cogs += $unit_price * $quantity;
            }
        }
    }
}

// Gross Profit
$gross_profit = $total_revenue - $cogs;

// For Expenses, Donations, Expiries: Placeholder 0 (add queries if tables created)
$total_expenses = 0;  // Query: SELECT SUM(amount) FROM expenses WHERE transDate BETWEEN '$start_date' AND '$end_date';
$total_donations_out = 0;  // Query: SELECT SUM(value) FROM donations_out WHERE transDate BETWEEN '$start_date' AND '$end_date';
$total_expiries = 0;  // Query: SELECT SUM(value) FROM expiries WHERE transDate BETWEEN '$start_date' AND '$end_date';

// Net Profit
$net_profit = $gross_profit - $total_expenses - $total_donations_out - $total_expiries;

// Inventory Value (current stock value): Sum (unit_price * assumed quantity, but no quantity in stocks!)
// Note: Stocks table lacks quantity column. Assuming you add one, e.g., ALTER TABLE stocks ADD quantity INT DEFAULT 0;
// For now, placeholder.
$inventory_value = 0;  // Query: SELECT SUM(unit_price * quantity) FROM stocks;

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
            <tr><td>Expiries/Losses</td><td><?php echo number_format($total_expiries, 2); ?></td></tr>
            <tr><td><strong>Net Profit</strong></td><td><strong><?php echo number_format($net_profit, 2); ?></strong></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Balance Sheet Excerpt</h2>
        <table>
            <tr><th>Assets</th><th>Amount</th></tr>
            <tr><td>Accounts Receivable (Credit Balances)</td><td><?php echo number_format($total_receivables, 2); ?></td></tr>
            <tr><td>Inventory Value</td><td><?php echo number_format($inventory_value, 2); ?> (Note: Add quantity to stocks table for accurate calc)</td></tr>
        </table>
        <!-- Add more sections like liabilities if data available -->
    </div>

    <div class="section">
        <h2>Notes</h2>
        <ul>
            <li>COGS is approximated by parsing 'items' in sales (assumed JSON) and matching to unit_price in stocks.</li>
            <li>No cash flow statement included; add if needed.</li>
            <li>For expenses, donations, expiries: Create suggested tables and update PHP queries.</li>
            <li>Stocks table needs a 'quantity' column for inventory value; currently placeholder.</li>
            <li>Run this PHP file on a server with DB access. Filter by dates via URL params.</li>
        </ul>
    </div>
</body>
</html>