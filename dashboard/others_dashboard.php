<?php

include('../includes/master.php'); // Assuming these functions are NOT here

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Assets/css/admindashboard.css" type="text/css">
    <title>Others Dashboard</title>
    <style>

    </style>
</head>
<body>
    <div class="main-content">
        <!-- Stock Levels Card -->
        <div><h2>Stocks Monitoring</h2></div>
        <div class="summary-section">
            <div class="stat-card">
                <h3>Total Category</h3>
                <div class="stat-value"><?php include '../counts/category_count.php'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Brand</h3>
                <div class="stat-value"><?php include '../counts/brand_count.php'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Item in Stock</h3>
                <div class="stat-value"><?php include '../counts/inventory_count.php'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Stocked Items</h3>
                <div class="stat-number"><?php include '../stocks/itemsStockedCount.php'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Low Stocks</h3>
                <div class="stat-number warning"><?php include '../stocks/lowStocksCount.php'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Zero Stocks</h3>
                <div class="stat-number danger"><?php include '../stocks/zeroStocksCount.php'; ?></div>
            </div>
            <div class="stat-card">
                <h3>Near Expiry</h3>
                <div class="stat-number danger"><?php include '../stocks/nearExpiryCount.php'; ?></div>
            </div>

        </div>


        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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