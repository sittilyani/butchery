<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$receipt_id = $_GET['receipt_id'] ?? '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_amount'])) {
    $payment_amount = filter_input(INPUT_POST, 'pay_amount', FILTER_VALIDATE_FLOAT);
    $receipt_id_post = filter_input(INPUT_POST, 'receipt_id', FILTER_SANITIZE_STRING);

    if ($payment_amount === false || $payment_amount <= 0) {
        $error = "Invalid payment amount. Please enter a positive number.";
    } elseif (!$receipt_id_post) {
        $error = "Receipt ID is missing.";
    } else {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("SELECT * FROM credit_balances WHERE receipt_id = ? FOR UPDATE");
            $stmt->bind_param("s", $receipt_id_post);
            $stmt->execute();
            $result = $stmt->get_result();
            $credit_row = $result->fetch_assoc();
            $stmt->close();

            if (!$credit_row) {
                throw new Exception("Credit record not found.");
            }

            $current_balance = $credit_row['balance_amount'];

            // Check if the current balance is already 0
            if ($current_balance <= 0) {
                 throw new Exception("This credit balance has already been paid in full.");
            }

            $new_balance = $current_balance - $payment_amount;

            // Determine the new status
            $new_status = 'Pending'; // Default status
            if ($new_balance <= 0) {
                $new_balance = 0; // Ensure balance does not go negative
                $new_status = 'Paid';
            } elseif ($payment_amount > 0 && $payment_amount < $current_balance) {
                $new_status = 'Partially Paid';
            }

            // Update the existing credit record with the new balance and status
            $update_stmt = $conn->prepare("UPDATE credit_balances SET balance_amount = ?, tendered_amount = tendered_amount + ?, status = ? WHERE receipt_id = ?");
            $update_stmt->bind_param("ddss", $new_balance, $payment_amount, $new_status, $receipt_id_post);

            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update credit balance: " . $update_stmt->error);
            }
            $update_stmt->close();

            $conn->commit();
            $message = "Payment of KES " . number_format($payment_amount, 2) . " successfully processed. New balance is KES " . number_format($new_balance, 2) . ".";
            header("Location: view_credit_sales.php?message=" . urlencode($message));
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

// Fetch the credit details for displaying the form
$credit_data = null;
if ($receipt_id) {
    $stmt = $conn->prepare("SELECT * FROM credit_balances WHERE receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $credit_data = $result->fetch_assoc();
    $stmt->close();
    if (!$credit_data) {
        $error = "Credit record not found.";
    }
} else {
    $error = "Receipt ID is missing from the request.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Credit Balance</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
          .main-content{
              width: 20%;
              margin-left: auto;
              margin-right: auto;
          }
           .card-header{
               background-color: #ccccff;
           }

    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>Update Credit Balance</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($credit_data): ?>
                    <div class="mb-3">
                        <p><strong>Receipt ID:</strong> <?php echo htmlspecialchars($credit_data['receipt_id']); ?></p>
                        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($credit_data['customer_name']); ?></p>
                        <p><strong>Current Balance:</strong> KES <?php echo number_format($credit_data['balance_amount'], 2); ?></p>
                        <p><strong>Credit Date:</strong> <?php echo htmlspecialchars($credit_data['transDate']); ?></p>
                    </div>

                    <form method="post">
                        <div class="form-group mb-3">
                            <label for="pay_amount">Enter Payment Amount:</label>
                            <input type="number" class="form-control" id="pay_amount" name="pay_amount" step="0.01" min="0.01" max="<?php echo htmlspecialchars($credit_data['balance_amount']); ?>" required>
                        </div>
                        <input type="hidden" name="receipt_id" value="<?php echo htmlspecialchars($credit_data['receipt_id']); ?>">
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                        <a href="view_credit_sales.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php else: ?>
                    <p class="text-danger">Credit record not found or an error occurred.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>