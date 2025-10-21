<?php
include '../includes/config.php';
include '../includes/header.php'; 

$sql = "SELECT * FROM stocks WHERE stockBalance < reorderLevel";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stocks</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <style>

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .users button {
            background-color: #6f42c1;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .users button a {
            text-decoration: none;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 6px 10px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #000099;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons button {
            padding: 4px 8px;
            font-size: 13px;
        }

        .btn-update {
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .btn-view {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
        }

        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                position: sticky;
                top: 0;
                background-color: #920000;
            }

            td {
                border: none;
                border-bottom: 1px solid #dee2e6;
                padding: 8px 5px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="main-content">

        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Product Name</th>
                    <th>Brand Name</th>
                    <th>Reorder Level</th>
                    <th>Stock Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['productname']) ?></td>
                        <td><?= htmlspecialchars($user['brandname']) ?></td>
                        <td><?= htmlspecialchars($user['reorderLevel']) ?></td>
                        <td><?= htmlspecialchars($user['stockBalance']) ?></td>
                        <td>Place Order</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
