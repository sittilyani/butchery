<?php
include '../includes/config.php';

$receipt_id = $_GET['receipt_id'] ?? '';

if (!$receipt_id) {
    echo "Invalid receipt ID.";
    exit;
}

$sql = "SELECT * FROM sales_items WHERE receipt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $receipt_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt Details - <?= htmlspecialchars($receipt_id) ?></title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Receipt #<?= htmlspecialchars($receipt_id) ?></h2>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price (KES)</th>
                <th>Total (KES)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $grand_total = 0;
        while ($row = $result->fetch_assoc()):
            $grand_total += $row['total_amount'];
        ?>
            <tr>
                <td><?= htmlspecialchars($row['items']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td><?= $row['total']?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right;">Grand Total:</th>
                <th colspan="3">KES <?= number_format($grand_total, 2) ?></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
