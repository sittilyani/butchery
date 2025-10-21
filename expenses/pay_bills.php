<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/config.php';
include '../includes/header.php';

$page_title = "Pay Pending Bills";

// Check for logged-in user
if (!isset($_SESSION['username'])) {
    $_SESSION['error_message'] = "Please log in to access this page.";
    header("Location: ../login.php");
    exit;
}

// Process actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];

    if ($action == 'pay') {
        $payment_method = $_POST['payment_method'];
        $stmt = $conn->prepare("UPDATE purchase_orders SET status = 'Paid', payment_method = ? WHERE order_id = ?");
        $stmt->bind_param("si", $payment_method, $order_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Bill paid successfully.";
        } else {
            $_SESSION['error_message'] = "Error paying bill: " . $conn->error;
        }
        $stmt->close();
    } elseif ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE purchase_orders SET status = 'Rejected' WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Bill rejected successfully.";
        } else {
            $_SESSION['error_message'] = "Error rejecting bill: " . $conn->error;
        }
        $stmt->close();
    } elseif ($action == 'delete') {
        $stmt = $conn->prepare("DELETE FROM purchase_orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Bill deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting bill: " . $conn->error;
        }
        $stmt->close();
    }
    header("Location: pay_bills.php");
    exit;
}

// Fetch purchase orders
$orders = [];
$stmt = $conn->prepare("
    SELECT po.id, po.supplier_id, po.total_amount, s.name AS supplier_name, po.order_date, po.status
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.supplier_id
    ORDER BY po.order_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        

    </style>
</head>
<body>
<div class="main-content">

        <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?></h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Total Amount (KES)</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $index => $order): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($order['supplier_name']); ?></td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td><?php echo $order['total_amount']; ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td>
                                <?php if ($order['status'] == 'Pending'): ?>
                                    <form action="pay_bills.php" method="post" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="action" value="pay">
                                        <select name="payment_method" class="form-select d-inline-block w-auto" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Mpesa">Mpesa</option>
                                            <option value="Bank">Bank</option>
                                        </select>
                                        <button type="submit" class="btn btn-success btn-sm">Pay</button>
                                    </form>
                                    <form action="pay_bills.php" method="post" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                <?php endif; ?>
                                <form action="pay_bills.php" method="post" style="display:inline;">
                                    <input type="text" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure you want to update this order?');">Update</button>
                                    <button></button>
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>