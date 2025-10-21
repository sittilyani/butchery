<?php
session_start();
if (!isset($_SESSION['login_id'])) {
    header('location:login.php');
    exit();
}


include('../includes/config.php');
include('../includes/footer.php');
include('../includes/header.php');

// Read sales data
$sql = "SELECT receipt_id, waiter_name, customer_name, total_amount,
               discount, tax_amount, grand_total, payment_method,
               customer_id, time_of_transaction, payment_status
        FROM sales
        WHERE payment_status in ('paid', 'unpaid', 'pending')
        GROUP BY receipt_id, waiter_name, customer_name, total_amount,
                 discount, tax_amount, grand_total, payment_method,
                 customer_id, time_of_transaction, payment_status";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_SESSION['system']['name']) ? htmlspecialchars($_SESSION['system']['name']) : 'Pharmacy Sales' ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <style>

        .main-content { /* Changed to container-fluid for full width */
            padding: 5px;
            margin-top: 40px;
            margin-left: 5px;
            width: 100%;
            font-size: 14px;
        }

        h2 {
            color: #000099;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .table-responsive {
            overflow-x: 100%;
            -webkit-overflow-scrolling: touch;
        }

        /* Optional: make table cells wrap text if needed */
        .table td, .table th {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<!-- Main Content -->
<div class="main-content">
    <h2>Sales Summary</h2>
    <!-- Sales Table - Wrapped in responsive div -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">

            <thead class="thead-dark">
                <tr>
                    <th>Receipt ID</th>
                    <th>Waiter Name</th>
                    <th>Customer Name</th>
                    <th>Total Amount</th>
                    <th>Tax Payable</th>
                    <th>Grand Total</th>
                    <th>Payment Mode</th>
                    <th>Customer ID</th>
                    <th>Transaction Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= htmlspecialchars($sale['receipt_id']) ?></td>
                        <td><?= htmlspecialchars($sale['waiter_name']) ?></td>
                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                        <td><?= htmlspecialchars($sale['total_amount']) ?></td>
                        <td><?= htmlspecialchars($sale['tax_amount']) ?></td>
                        <td><?= htmlspecialchars($sale['grand_total']) ?></td>
                        <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                        <td><?= htmlspecialchars($sale['customer_id']) ?></td>
                        <td><?= htmlspecialchars($sale['time_of_transaction']) ?></td>
                        <td><?= htmlspecialchars($sale['payment_status']) ?></td>
                        <td>
                            <a href="edit_receipt.php?receipt_id=<?= urlencode($sale['receipt_id']) ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="view_receipt.php?receipt_id=<?= urlencode($sale['receipt_id']) ?>" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>