<?php
// update_order.php
session_start();
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_POST['draft_id'], $_POST['waiter_name'], $_POST['payment_method'], $_POST['tendered_amount'], $_POST['items'])) {
    die("Missing required fields.");
}

$draft_id = intval($_POST['draft_id']);
$waiter_name = $_POST['waiter_name'];
$payment_method = $_POST['payment_method'];
$tendered_amount = floatval($_POST['tendered_amount']);
$items = $_POST['items'];

// Recalculate totals
$total_amount = 0;
foreach ($items as &$item) {
    $item['total'] = $item['quantity'] * $item['price'];
    $total_amount += $item['total'];
}
$tax_amount = round($total_amount * 0.015, 2);
$grand_total = $total_amount;
$items_json = json_encode($items);

try {
    $stmt = $conn->prepare("UPDATE sales_drafts SET waiter_name = ?, payment_method = ?, tendered_amount = ?, items = ?, total_amount = ?, tax_amount = ?, grand_total = ? WHERE draft_id = ?");
    $stmt->bind_param("ssdsssdi", $waiter_name, $payment_method, $tendered_amount, $items_json, $total_amount, $tax_amount, $grand_total, $draft_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<span style='background-color: #DDFCAF; color: green; font-size: 18px; height: 60px; line-height: 40px; padding: 5px 10px; margin-bottom: 10px;'>Order updated successfully.</span>";
        // Redirect if no error
        header("Refresh: 2; URL=../views/view_order.php");
        exit();
    } else {
        echo "No changes made or update failed.";
    }

    $stmt->close();
} catch (Exception $e) {
    echo "Error updating order: " . $e->getMessage();
}

$conn->close();
?>
