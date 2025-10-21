<?php
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $supplierId = $_POST['supplier_id'];
    $productIds = $_POST['product_id'];
    $quantities = $_POST['quantity'];

    // Insert into purchase_orders table
    $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, order_date) VALUES (?, NOW())");
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    $purchaseOrderId = $stmt->insert_id;

    // Insert into purchase_order_items
    foreach ($productIds as $key => $productId) {
        $quantity = $quantities[$key];

        // Get unit price from products table
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

    $_SESSION['success_message'] = "Purchase Order saved successfully.";
    header("Location: purchaseorder.php");
    exit;
}
?>
