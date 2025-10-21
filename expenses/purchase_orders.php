<?php
include '../includes/config.php';
include '../includes/header.php';
$orders = $conn->query("SELECT po.*, s.name as supplier_name FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.supplier_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <title>Purchase Orders</title>
    <style>
        th{
            background-color: #66CCFF;
            border: solid 1px;
            padding: 5px 10px;
        }
        td{
            border: solid 1px;
            padding: 5px 20px;
        }
         a{
            text-decoration: none;
            color: white;
         }
         button{
             background-color: #003DB8;
             color: white;
             border: none;
             border-radius: 5px;
             margin-bottom: 10px;
             padding: 5px 10px;

         }

    </style>
</head>
<body>
    <div class="main-content">
    <h2>Purchase Orders List</h2>
    <button><a href="../expenses/purchaseorder.php">Add New Purchase Order</a> </button>
    <table border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th style="min-width: 200px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; while($order = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $counter++; ?></td>
                <td><?= htmlspecialchars($order['supplier_name']); ?></td>
                <td><?= htmlspecialchars($order['order_date']); ?></td>
                <td><?= htmlspecialchars($order['total_amount']); ?></td>
                <td><?= htmlspecialchars($order['status']); ?></td>
                <td>
                    <a href="edit_purchaseorder.php?id=<?= $order['id']; ?>"><span style="color: blue;">Edit</span></a> |
                    <a href="generate_invoice.php?id=<?= $order['id']; ?>" target="_blank"><span style="color: #009966;">View PDF</span></a> |
                    <a href="delete_invoice.php?id=<?= $order['id']; ?>" target="_blank"><span style="color: red;">Delete Invoice</span></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
