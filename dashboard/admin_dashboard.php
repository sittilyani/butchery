<?php
include('../includes/header.php'); // Assuming these functions are NOT here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <title>Admin Dashboard</title>
    <style>
         :root {
            --primary-color: #000099;
            --secondary-color: #FBD875;
            --tertiary-color: #75C0FB;
            --light-gray: #E5E8E8;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            margin-left: 40px;
        }

        .stat-card {
            background: #99ccff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        #stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            width: 200px;
        }

        .stat-card h3 {
            color: var(--primary-color);
            margin-top: 0;
            font-size: clamp(16px, 2vw, 20px);
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .stat-card .stat-value {
            font-size: clamp(16px, 2vw, 18px);
            margin: 15px 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .stat-card a {
            color: inherit;
            text-decoration: none;
            font-size: clamp(24px, 3vw, 30px);
            font-weight: bold;
            display: block;
        }

        .stat-card.users { background-color: var(--light-gray); }
        .stat-card.sales { background-color: var(--secondary-color); }
        .stat-card.stock { background-color: var(--tertiary-color); }
        .stat-card.staff { background-color: var(--light-gray); }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            font-weight: bold;
            color: #555;
        }

        .success { color: var(--success-color); }
        .danger { color: var(--danger-color); }
        .warning { color: var(--warning-color); }

        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin: 0 20px 30px 20px;
            padding: 0 10px;
        }

        .user-wise-orders {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px;
            overflow-x: auto;
        }

        .user-wise-orders table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .user-wise-orders th,
        .user-wise-orders td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .user-wise-orders th {
            background-color: var(--primary-color);
            color: white;
            white-space: nowrap;
        }

        .user-wise-orders tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .total-row {
            background-color: var(--light-gray) !important;
        }

        a.staff-link {
            text-decoration: none;
            font-weight: bold;
        }

        a.staff-link:hover {
            text-decoration: underline;
        }

        h2 {
            margin-left: 25px;
            color: #330099;
            font-size: clamp(20px, 3vw, 24px);
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        /* Tablet devices */
        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: repeat(3, 1fr);
                margin-left: 20px;
                margin-right: 20px;
                gap: 15px;
            }

            .summary-section {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
                margin: 0 15px 25px 15px;
            }

            .stat-card {
                padding: 15px;
            }

            h2 {
                margin-left: 20px;
            }
        }

        /* Small tablets and large phones */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: repeat(2, 1fr);
                margin-left: 15px;
                margin-right: 15px;
                gap: 12px;
            }

            .summary-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                margin: 0 10px 20px 10px;
            }

            .stat-card {
                padding: 12px;
            }

            .stat-card h3 {
                font-size: 14px;
                padding-bottom: 8px;
                margin-bottom: 10px;
            }

            .stat-card a {
                font-size: 22px;
            }

            .stat-card .stat-value {
                font-size: 14px;
            }

            h2 {
                margin-left: 15px;
                font-size: 20px;
                margin-top: 15px;
            }

            .user-wise-orders {
                padding: 15px;
                margin: 15px 10px;
            }

            .user-wise-orders th,
            .user-wise-orders td {
                padding: 8px;
                font-size: 14px;
            }
        }

        /* Mobile phones */
        @media (max-width: 480px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                margin-left: 10px;
                margin-right: 10px;
                gap: 10px;
            }

            .summary-section {
                grid-template-columns: 1fr;
                gap: 10px;
                margin: 0 10px 15px 10px;
                padding: 0;
            }

            .stat-card {
                padding: 15px 10px;
            }

            .stat-card h3 {
                font-size: 14px;
                padding-bottom: 6px;
                margin-bottom: 8px;
            }

            .stat-card a {
                font-size: 24px;
            }

            .stat-card .stat-value {
                font-size: 14px;
                margin: 10px 0;
            }

            h2 {
                margin-left: 10px;
                font-size: 18px;
                margin-top: 12px;
                margin-bottom: 10px;
            }

            .user-wise-orders {
                padding: 12px;
                margin: 12px 5px;
            }

            .user-wise-orders th,
            .user-wise-orders td {
                padding: 6px;
                font-size: 12px;
            }
        }

        /* Very small phones */
        @media (max-width: 360px) {
            .stat-card h3 {
                font-size: 13px;
            }

            .stat-card a {
                font-size: 20px;
            }

            .stat-card .stat-value {
                font-size: 13px;
            }

            h2 {
                font-size: 16px;
                margin-left: 8px;
            }

            .summary-section {
                margin: 0 8px 12px 8px;
            }
        }

        /* Landscape orientation on mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            .stat-card {
                padding: 10px;
            }

            .stat-card h3 {
                font-size: 13px;
                padding-bottom: 5px;
                margin-bottom: 5px;
            }

            .stat-card a {
                font-size: 20px;
            }

            h2 {
                font-size: 16px;
                margin-top: 10px;
                margin-bottom: 8px;
            }

            .summary-section {
                gap: 8px;
                margin-bottom: 15px;
            }
        }

        /* Extra large screens */
        @media (min-width: 1400px) {
            .summary-section {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                max-width: 1600px;
                margin-left: auto;
                margin-right: auto;
            }

            .dashboard-container {
                max-width: 1600px;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body>
<!-- Stock Levels Card -->
<div><h2>Stocks Monitoring</h2></div>
<div class="summary-section">
    <div class="stat-card">
        <h3>Total Category</h3>
        <a href="../stocks/view_categories.php">
        <?php include '../counts/category_count.php'; ?></a>
    </div>
    <div class="stat-card">
        <h3>Total Brands</h3>
        <a href="../views/view_product.php">
        <?php include '../counts/brand_count.php'; ?></a>
    </div>
    <div class="stat-card">
        <h3>Total Items in Stock</h3>
        <a href="../stocks/viewstocks_sum.php">
            <?php include '../counts/inventory_count.php'; ?></a>
    </div>

    <div class="stat-card">
        <h3>Low Stocks</h3>
        <a href="../views/view_lowstocks.php">
        <?php include '../stocks/lowStocksCount.php'; ?></a>
    </div>
    <div class="stat-card">
        <h3>Zero Stocks</h3>
        <a href="../views/view_zerostocks.php">
        <?php include '../stocks/zeroStocksCount.php'; ?></a>
    </div>
    <!-- Near Expiry Section -->


    <div class="stat-card">
        <h3>Expiry Tracking</h3>
        <a href="../stocks/view_sixmonths_expiry.php">
        <?php include '../stocks/nearExpiryCount.php'; ?></a>
    </div>
</div>

<div><h2>Sales Monitoring</h2></div>
<div class="summary-section">
<!-- Summary Cards -->
    <div class="stat-card">
        <h3>Total stock Value</h3>
        <div class="stat-value">KES <?php include '../counts/total_stock_value_count.php'; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Sales</h3>
        <a href="../sales/view_paid_orders.php">
        <?php include '../counts/completed.php'; ?></a>
    </div>
    <div class="stat-card">
        <h3>Cash Sales</h3>
        <div class="stat-value">KES <?php include '../counts/daily_cash_sales.php'; ?></div>
    </div>
    <div class="stat-card">
        <h3>Credits Value</h3>
        <div class="stat-value">KES <?php include '../counts/daily_credit_sales.php'; ?></div>
    </div>
    <div class="stat-card">
        <h3>Mpesa Sales</h3>
        <div class="stat-value">KES <?php include '../counts/daily_mpesa_sales.php'; ?></div>
    </div>
    <div class="stat-card">
        <h3>Creditors</h3>
        <a href="../views/view_credit_sales.php">
            <?php include '../counts/creditors_sales.php'; ?>
        </a>
    </div>

    <div class="stat-card">
        <h3>Pending Orders</h3>
        <a href="../sales/view_order.php">
            <?php include '../counts/pending.php'; ?>
        </a>
    </div>
</div>

<script src="../assets/fontawesome-7.1.1/js/all.min.js"></script>
<script src="../assets/js/bootstrap.bundle.js"></script>
<script>
    $(document).ready(function() {
        // Handle click event to toggle active class
        $(".dropdown").click(function() {
            $(this).toggleClass("active");
        });
    });
</script>

</body>
</html>