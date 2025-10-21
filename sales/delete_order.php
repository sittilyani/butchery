<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Check permissions
if ($_SESSION['userrole'] !== 'Admin' && $_SESSION['userrole'] !== 'Manager' && $_SESSION['userrole'] !== 'Cashier') {
    header('Location: access_denied.php');
    exit;
}

$receipt_id = $_GET['receipt_id'] ?? '';

if (empty($receipt_id)) {
    header('Location: view_order.php?message=' . urlencode('Draft ID missing'));
    exit;
}

// Delete the draft
$stmt = $conn->prepare("DELETE FROM sales_drafts WHERE receipt_id = ?");
$stmt->bind_param("s", $receipt_id);

if ($stmt->execute()) {
    header('Location: view_order.php?message=' . urlencode('Draft deleted successfully'));
} else {
    header('Location: view_order.php?message=' . urlencode('Error deleting draft: ' . $conn->error));
}

$stmt->close();
$conn->close();
?>