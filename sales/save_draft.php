<?php
header('Content-Type: application/json');
include "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
error_log('Save Draft Input: ' . $input);

if (!$data || !isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing items data']);
    exit;
}

$required_fields = ['receipt_id', 'payment_method', 'payment_status', 'tendered_amount'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing field: $field"]);
        exit;
    }
}

$receipt_id = $data['receipt_id'];
$payment_method = $data['payment_method'];
$payment_status = $data['payment_status'];
$tendered_amount = (float)$data['tendered_amount'];
$transBy = $_SESSION['full_name'] ?? 'System';

try {
    $conn->begin_transaction();

    // Validate stock for all items
    foreach ($data['items'] as $item) {
        $productname = $item['productname'];
        $quantity = (int)$item['quantity'];

        // Fetch latest stock balance
        $stock_query = $conn->prepare("
            SELECT stockBalance
            FROM stocks s1
            INNER JOIN (
                SELECT productname, MAX(transDate) AS maxTransDate, MAX(id) AS maxId
                FROM stocks
                GROUP BY productname
            ) s2 ON s1.productname = s2.productname AND s1.transDate = s2.maxTransDate AND s1.id = s2.maxId
            WHERE s1.productname = ?
        ");
        $stock_query->bind_param("s", $productname);
        $stock_query->execute();
        $stock_result = $stock_query->get_result();
        $stockBalance = 0;
        if ($stock_row = $stock_result->fetch_assoc()) {
            $stockBalance = $stock_row['stockBalance'];
        }
        $stock_query->close();

        // Check if stock is sufficient
        if ($stockBalance <= 0 || $stockBalance < $quantity) {
            throw new Exception("Not enough stock for $productname");
        }
    }

    // Delete existing drafts for receipt_id
    $delete_stmt = $conn->prepare("DELETE FROM sales_drafts WHERE receipt_id = ?");
    $delete_stmt->bind_param("s", $receipt_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Insert new drafts
    $draft_stmt = $conn->prepare("
        INSERT INTO sales_drafts (
            receipt_id, payment_method, payment_status, productname, quantity, price, total_amount,
            tax_amount, grand_total, tendered_amount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Prepare stock update statement
    $stock_stmt = $conn->prepare("
        INSERT INTO stocks (
            id, productname, quantityOut, stockBalance, transBy, reorderLevel
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['items'] as $item) {
        $required_item_fields = ['productname', 'quantity', 'price', 'total_amount', 'tax_amount', 'grand_total'];
        foreach ($required_item_fields as $field) {
            if (!isset($item[$field])) {
                throw new Exception("Missing item field: $field");
            }
        }

        $productname = $item['productname'];
        $quantity = (int)$item['quantity'];
        $price = (float)$item['price'];
        $total_amount = (float)$item['total_amount'];
        $tax_amount = (float)$item['tax_amount'];
        $grand_total = (float)$item['grand_total'];

        // Save draft
        $draft_stmt->bind_param(
            "ssiddddssd",
            $receipt_id, $productname, $quantity, $price, $total_amount,
            $tax_amount, $grand_total, $payment_method, $payment_status, $tendered_amount
        );
        $draft_stmt->execute();

        // Fetch latest stock balance and product details
        $stock_query = $conn->prepare("
            SELECT stockBalance, id
            FROM stocks s1
            INNER JOIN (
                SELECT productname, MAX(transDate) AS maxTransDate, MAX(id) AS maxId
                FROM stocks
                GROUP BY productname
            ) s2 ON s1.productname = s2.productname AND s1.transDate = s2.maxTransDate AND s1.id = s2.maxId
            WHERE s1.productname = ?
        ");
        $stock_query->bind_param("s", $productname);
        $stock_query->execute();
        $stock_result = $stock_query->get_result();
        $stockBalance = 0;
        $id = 0;
        if ($stock_row = $stock_result->fetch_assoc()) {
            $stockBalance = $stock_row['stockBalance'];
            $id = $stock_row['id'];
        }

        // Fetch reorder_level from products
        $product_query = $conn->prepare("SELECT reorder_level FROM products WHERE productname = ?");
        $product_query->bind_param("s", $productname);
        $product_query->execute();
        $product_result = $product_query->get_result();
        $reorder_level = null;
        if ($product_row = $product_result->fetch_assoc()) {
            $reorder_level = $product_row['reorder_level'];
        }
        $product_query->close();
        $stock_query->close();

        // Update stock
        $newStockBalance = $stockBalance - $quantity;
        $stock_stmt->bind_param(
            "isdisi",
            $id, $productname, $quantity, $newStockBalance, $transBy, $reorder_level
        );
        $stock_stmt->execute();
    }

    $draft_stmt->close();
    $stock_stmt->close();
    $conn->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Draft saved successfully',
        'redirect' => '../sales/view_order.php?message=' . urlencode('Draft saved successfully')
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log('Exception: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
?>