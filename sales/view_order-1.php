<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

$result = $conn->query("SELECT draft_id, receipt_id, payment_method, payment_status, brandname,
quantity, price, total_amount, discount, tax_amount, grand_total, tendered_amount, transBy, transDate FROM sales_drafts GROUP BY receipt_id ORDER BY transDate DESC");
$has_pending_orders = $result && $result->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Orders</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../assets/fontawesome-7.1.1/css/all.min.css" type="text/css">

    <style>
        .no-orders-message {
            margin-top: 20px;
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }


    </style>
</head>
<body>
    <div class="main-content">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars(urldecode($_GET['message'])); ?></div>
        <?php endif; ?>
        <div class='next-order;'>
            <h2>Checkout Basket</h2>
        </div>
        <table class="table table-bordered" style="width: 95%;">
            <thead>
                <tr>
                    <th>Draft ID</th>
                    <th>Receipt ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>% Discount</th>
                    <th>Grand Total</th>
                    <th>Tendered</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Served By</th>
                    <th>Transaction Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($has_pending_orders): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['draft_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['receipt_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['brandname']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['discount']); ?></td>
                            <td><?php echo htmlspecialchars($row['grand_total']); ?></td>
                            <td><?php echo htmlspecialchars($row['tendered_amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['transBy']); ?></td>
                            <td><?php echo htmlspecialchars($row['transDate']); ?></td>
                            <td>
                                <!--<button class="btn btn-sm btn-primary edit-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Edit</button>
                                <button class="btn btn-sm btn-success mark-paid-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Mark as Paid</button>-->
                                <button class="btn btn-sm btn-warning update-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Check Out</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (!$has_pending_orders): ?>
            <div class="no-orders-message">No pending orders found.</div>
        <?php endif; ?>
    </div>

    <script>
    $(document).ready(function() {
        $('.edit-btn').click(function() {
            const receiptId = $(this).data('receipt-id');
            if (!receiptId) return alert("Receipt ID missing.");
            window.location.href = 'edit_order.php?receipt_id=' + encodeURIComponent(receiptId);
        });

        $('.mark-paid-btn').click(function() {
            const receiptId = $(this).data('receipt-id');
            if (!receiptId) return alert("Receipt ID missing.");
            if (!confirm("Mark this order as paid?")) return;
            window.location.href = 'mark_paid.php?receipt_id=' + encodeURIComponent(receiptId);
        });

        $('.update-btn').click(function() {
            const receiptId = $(this).data('receipt-id');
            if (!receiptId) return alert("Receipt ID missing.");
            window.location.href = 'edit_order.php?receipt_id=' + encodeURIComponent(receiptId);
        });

        $('.delete-btn').click(function() {
            const receiptId = $(this).data('receipt-id');
            if (!receiptId) return alert("Receipt ID missing.");
            if (!confirm("Are you sure you want to delete this draft?")) return;
            window.location.href = 'delete_order.php?receipt_id=' + encodeURIComponent(receiptId);
        });
    });
    </script>
</body>
</html>
<?php
if ($result) {
    $result->free();
}
?>