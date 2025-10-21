<?php
include '../includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sales</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/bootstrap.min.js"></script>
    <style>
        .container { width: 80%; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .paid { color: green; font-weight: bold; }
        .pending { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sales Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Receipt ID</th>
                    <th>Customer Name</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM sales ORDER BY time_of_transaction DESC";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $receipt_id = $row['receipt_id'];
                    $customer_name = $row['customer_name'];
                    $total_amount = $row['total_amount'];
                    $status = $row['status'];

                    echo "<tr>
                        <td>{$receipt_id}</td>
                        <td>{$customer_name}</td>
                        <td>{$total_amount}</td>
                        <td class='".($status === 'Paid' ? "paid" : "pending")."'>{$status}</td>
                        <td>
                            <a href='view_receipt.php?receipt_id={$receipt_id}' class='btn btn-info btn-sm'>View</a>
                            ";
                            if ($status === 'Pending') {
                                echo "<a href='edit_sale.php?receipt_id={$receipt_id}' class='btn btn-warning btn-sm'>Edit</a>";
                            } else {
                                echo "<button class='btn btn-secondary btn-sm' disabled>Edit</button>";
                            }
                            echo "
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
