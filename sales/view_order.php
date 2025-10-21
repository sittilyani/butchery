<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// The original query was failing due to MySQL's ONLY_FULL_GROUP_BY mode.
// We must use aggregation functions (SUM, MAX, GROUP_CONCAT) for columns
// that are not in the GROUP BY clause to ensure one row per receipt_id.
$sql = "
    SELECT
        -- Use MAX to get a single value for order-level details
        MAX(draft_id) AS draft_id,
        receipt_id,
        MAX(payment_method) AS payment_method,
        MAX(payment_status) AS payment_status,
        MAX(discount) AS discount,
        MAX(tax_amount) AS tax_amount,
        MAX(grand_total) AS grand_total,
        MAX(tendered_amount) AS tendered_amount,
        MAX(transBy) AS transBy,
        MAX(transDate) AS transDate,

        -- Use aggregation for item-level details:
        -- Concatenate all product names for the grouped receipt
        GROUP_CONCAT(brandname SEPARATOR ' | ') AS brandname,
        -- Sum up the quantities for all items in the receipt
        SUM(quantity) AS quantity,
        -- Sum up the line item totals to get the order sub-total
        SUM(total_amount) AS total_amount
    FROM sales_drafts
    GROUP BY receipt_id
    ORDER BY transDate DESC
";

$result = $conn->query($sql);

if (!$result) {
    // Handle SQL error for debugging
    error_log("SQL Error: " . $conn->error);
    $has_pending_orders = false;
    $error_message = "Database Error: " . $conn->error;
} else {
    $has_pending_orders = $result->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Orders</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <!-- Load jQuery (required for the event listeners) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .main-content {
            padding: 20px;
        }
        .alert-info {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars(urldecode($_GET['message'])); ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">Error fetching data: <?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class='next-order;'>
            <h2>Checkout Basket (Draft Orders)</h2>
        </div>

        <?php if ($has_pending_orders): ?>
            <table class="table table-bordered table-striped" style="width: 100%;">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Draft ID (Last Item)</th>
                        <th>Receipt ID</th>
                        <th>Product Name(s)</th>
                        <th>Total Quantity</th>
                        <th>Subtotal (Pre-Tax/Disc)</th>
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['draft_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['receipt_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['brandname']); ?></td> <!-- Now contains concatenated product names -->
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td> <!-- Now contains SUM of quantities -->
                            <td><?php echo htmlspecialchars(number_format($row['total_amount'], 2)); ?></td> <!-- Now contains SUM of total_amount -->
                            <td><?php echo htmlspecialchars($row['discount']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['grand_total'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['tendered_amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['transBy']); ?></td>
                            <td><?php echo htmlspecialchars($row['transDate']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning update-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Check Out</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders-message">No pending orders found.</div>
        <?php endif; ?>
    </div>

    <script>
    // Using jQuery, as was in the original script
    $(document).ready(function() {

        // Removed commented out buttons for cleanliness

        $('.update-btn').click(function() {
            const receiptId = $(this).data('receipt-id');
            // Replaced alert with console error/custom message logic for better practice, though keeping alert as it was in the original code for minimal change
            if (!receiptId) return console.error("Receipt ID missing for update.");
            window.location.href = 'edit_order.php?receipt_id=' + encodeURIComponent(receiptId);
        });

        $('.delete-btn').click(function() {
            const receiptId = $(this).data('receipt-id');
            if (!receiptId) return console.error("Receipt ID missing for delete.");

            // Replaced confirm() with a visual modal suggestion (still using a basic confirm() as a fallback to match your existing code structure,
            // but noting this should be replaced with a custom Bootstrap modal).
            if (window.confirm("Are you sure you want to delete this draft? This cannot be undone.")) {
                 window.location.href = 'delete_order.php?receipt_id=' + encodeURIComponent(receiptId);
            }
        });
    });
    </script>
</body>
</html>
<?php
// Ensure resources are freed
if (isset($result) && $result) {
    $result->free();
}
// Clean the output buffer
ob_end_flush();
?>