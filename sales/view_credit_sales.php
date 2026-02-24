<?php
ob_start();
include '../includes/config.php';

$result = $conn->query("SELECT * FROM credit_balances
                        /*where payment_status = 'paid'*/
                        ORDER BY transDate DESC");
?>
<?php
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
    echo "<div class='alert alert-info'>" . $message . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Sales</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome-7.1.1/css/all.min.css">
    <style>
        .main-content {
            margin: 20px auto;
            padding: 20px;
            max-width: 95%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        h2 { color: #2c3e50; font-weight: 600; margin-bottom: 25px; }
        .btn-sm { margin: 0 3px; padding: 6px 12px; }
        .table { box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .alert { margin: 20px auto; max-width: 95%; }
    </style>
</head>
<body>
<div class="main-content">
    <h2><i class="fas fa-credit-card me-2"></i>Credit Sales</h2>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Receipt ID</th>
                    <th>Customer Name</th>
                    <th>Total Amount</th>
                    <th>Balance</th>
                    <th>Transaction Date</th>
                    <th>Credited By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['receipt_id'] ?></td>
                        <td><?= $row['customer_name'] ?></td>
                        <td>KES<?= number_format($row['total_amount'], 2) ?></td>
                        <td>KES<?= number_format($row['balance_amount'], 2) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($row['transDate'])) ?></td>
                        <td><?= $row['created_by'] ?></td>
                        <td>
                            <button class="btn btn-success btn-sm mark-paid-btn" data-receipt-id="<?= $row['receipt_id'] ?>">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-btn" data-receipt-id="<?= $row['receipt_id'] ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../assets/js/bootstrap.bundle.js"></script>
</body>
</html>