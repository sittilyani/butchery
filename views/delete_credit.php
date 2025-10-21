<?php
// Include necessary files and start a session
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Check for user login and GET request
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['receipt_id'])) {
    header("Location: view_credit_sales.php");
    exit();
}

$receipt_id = filter_input(INPUT_GET, 'receipt_id', FILTER_SANITIZE_STRING);
$message = '';

// Start a transaction
$conn->begin_transaction();

try {
    // 1. Fetch the record from credit_balances to be moved
    $stmt = $conn->prepare("SELECT * FROM credit_balances WHERE receipt_id = ? FOR UPDATE");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        throw new Exception("Credit record not found for deletion.");
    }

    // 2. Insert the record into the defaulters table
    $insert_stmt = $conn->prepare("
        INSERT INTO defaulters (
            receipt_id, customer_name, customer_phone, balance_amount, total_amount,
            tendered_amount, transDate, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insert_stmt->bind_param(
        "sssdddsss",
        $row['receipt_id'],
        $row['customer_name'],
        $row['customer_phone'],
        $row['balance_amount'],
        $row['total_amount'],
        $row['tendered_amount'],
        $row['transDate'],
        $row['status'],
        $row['created_by']
    );

    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to save record to defaulters table: " . $insert_stmt->error);
    }
    $insert_stmt->close();

    // 3. Delete the record from the credit_balances table
    $delete_stmt = $conn->prepare("DELETE FROM credit_balances WHERE receipt_id = ?");
    $delete_stmt->bind_param("s", $receipt_id);

    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete record from credit_balances: " . $delete_stmt->error);
    }
    $delete_stmt->close();

    // 4. Commit the transaction
    $conn->commit();
    $message = "Credit record for receipt ID {$receipt_id} has been moved to defaulters successfully.";

} catch (Exception $e) {
    $conn->rollback();
    $message = "Error: " . $e->getMessage();
}

// Close connection and redirect with message
$conn->close();
header("Location: view_credit_sales.php?message=" . urlencode($message));
exit();
?>