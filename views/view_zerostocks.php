<?php
include '../includes/config.php';
include '../includes/header.php';

// Corrected SQL query
$sql = "
    SELECT
        p.productname,
        p.brandname,
        p.packsize,
        p.reorder_level,
        s.stockBalance,
        MAX(s.transDate) AS last_transDate
    FROM products p
    LEFT JOIN stocks s ON p.brandname = s.brandname
    GROUP BY p.productname, p.brandname, p.packsize, p.reorder_level, s.stockBalance
    HAVING s.stockBalance = 0 OR s.stockBalance IS NULL
";

$result = $conn->query($sql);

$users = [];
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle the case where the query fails, though it should be fixed now
    echo "Error: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zero Stocks</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <style>


        .main-content {
            width: 95%;
            margin: 40px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            color: #000099;
            text-align: center;
            margin-bottom: 25px;
            font-size: 2.5em;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden; /* Ensures rounded corners are applied to the table */
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #000099;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.3s ease;
        }

        .no-stocks {
            text-align: center;
            font-size: 1.2em;
            color: #6c757d;
            margin-top: 20px;
            padding: 20px;
            background-color: #e2e3e5;
            border-radius: 8px;
            border: 1px solid #d6d8db;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-order {
            background-color: #17a2b8;
        }

        .btn-order:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2 class="page-title">Products Out of Stock</h2>
    <?php if (empty($users)): ?>
        <div class="no-stocks">
            <p>No products are out of stock. All is well! ??</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Brand Name</th>
                    <th>Pack Size</th>
                    <th>Reorder Level</th>
                    <th>Current Stock</th>
                    <th>Last Transaction Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= htmlspecialchars($user['productname'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['brandname'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['packsize'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['reorder_level'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['stockBalance'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($user['last_transDate'] ?? 'N/A') ?></td>
                        <td>
                            <a href="#" class="btn btn-order">Place Order</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


</body>
</html>