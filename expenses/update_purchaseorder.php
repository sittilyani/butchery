<?php
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $purchaseOrderId = $_POST['purchase_order_id'];
    $supplierId = $_POST['supplier_id'];
    $productIds = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $status = $_POST['status'];

    // Update supplier if changed
    $stmt = $conn->prepare("UPDATE purchase_orders SET supplier_id = ?, status = ? WHERE id = ?");
    $stmt->bind_param('isi', $supplierId, $status, $purchaseOrderId);
    $stmt->execute();

    // Delete old items
    $conn->query("DELETE FROM purchase_order_items WHERE purchase_order_id = $purchaseOrderId");

    // Insert new items
    foreach ($productIds as $key => $productId) {
        $quantity = $quantities[$key];

        // Get unit price
        $stmtProduct = $conn->prepare("SELECT unit_price FROM products WHERE id = ?");
        $stmtProduct->bind_param('i', $productId);
        $stmtProduct->execute();
        $stmtProduct->bind_result($unit_price);
        $stmtProduct->fetch();
        $stmtProduct->close();

        $stmtItem = $conn->prepare("INSERT INTO purchase_order_items (purchase_order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmtItem->bind_param('iiid', $purchaseOrderId, $productId, $quantity, $unit_price);
        $stmtItem->execute();
    }

    $_SESSION['success_message'] = "Purchase Order updated successfully.";
    header("Location: purchase_orders.php");
    exit;
}
?>
