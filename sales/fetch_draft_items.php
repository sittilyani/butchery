<?php
include '../includes/config.php';
header('Content-Type: application/json');

$receipt_id = isset($_GET['receipt_id']) ? mysqli_real_escape_string($conn, $_GET['receipt_id']) : '';
$items = [];
if ($receipt_id) {
    $stmt = $conn->prepare("SELECT product_id, productname, quantity, price, discount, total_amount, tax_amount, grand_total FROM sales_drafts WHERE receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
echo json_encode($items);
?>