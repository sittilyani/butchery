<?php
ob_start();
include '../includes/header.php'; // Includes config.php and session_start()
header('Content-Type: application/json');

// Validate session - check for both user_id and username
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// If you need the full name for display purposes, you can still access it if it exists
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';

// Parse JSON input
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['receipt_id']) || !isset($data['items']) || !is_array($data['items'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing input data']);
    exit;
}

$receipt_id = mysqli_real_escape_string($conn, $data['receipt_id']);
$payment_method = mysqli_real_escape_string($conn, $data['payment_method'] ?? 'Cash');
$payment_status = mysqli_real_escape_string($conn, $data['payment_status'] ?? 'Pending');
$total_discount = floatval($data['total_discount'] ?? 0.00);
$tendered_amount = floatval($data['tendered_amount'] ?? 0.00);
$transBy = $_SESSION['full_name'];
$items = $data['items'];

// Validate items
if (empty($items)) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'No items provided']);
    exit;
}

// Validate stock for all items
foreach ($items as $item) {
    if (!isset($item['id']) || !isset($item['brandname']) || !isset($item['quantity']) || !isset($item['price']) || !isset($item['discount']) || !isset($item['total_amount']) || !isset($item['tax_amount']) || !isset($item['grand_total'])) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Missing required item fields']);
        exit;
    }

    $id = (int)$item['id'];
    $brandname = mysqli_real_escape_string($conn, $item['brandname']);
    $quantity = (int)$item['quantity'];

    // Fetch latest stock balance - FIXED QUERY
    $stock_query = $conn->prepare("
        SELECT s1.stockBalance
        FROM stocks s1
        WHERE s1.brandname = ?
        ORDER BY s1.transDate DESC, s1.id DESC
        LIMIT 1
    ");

    if (!$stock_query) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare stock query: ' . $conn->error]);
        exit;
    }

    $stock_query->bind_param("s", $brandname);

    if (!$stock_query->execute()) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute stock query: ' . $stock_query->error]);
        $stock_query->close();
        exit;
    }

    $stock_result = $stock_query->get_result();

    if ($stock_result && $stock_row = $stock_result->fetch_assoc()) {
        $stockBalance = (int)$stock_row['stockBalance'];
        if ($stockBalance <= 0 || $stockBalance < $quantity) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => "Not enough stock for $brandname (Available: $stockBalance, Requested: $quantity)"]);
            $stock_query->close();
            exit;
        }
    } else {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => "Stock record not found for $brandname"]);
        $stock_query->close();
        exit;
    }
    $stock_query->close();
}

// Clear existing draft items
$stmt = $conn->prepare("DELETE FROM sales_drafts WHERE receipt_id = ?");
if (!$stmt) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare DELETE statement: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $receipt_id);
if (!$stmt->execute()) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete existing drafts: ' . $stmt->error]);
    $stmt->close();
    exit;
}
$stmt->close();

// Insert new items - FIXED: Use discount instead of total_discount
$stmt = $conn->prepare("
    INSERT INTO sales_drafts (
        receipt_id, brandname, quantity, price, discount,
        total_amount, tax_amount, grand_total, payment_method, payment_status, tendered_amount, transBy
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare INSERT statement: ' . $conn->error]);
    exit;
}

foreach ($items as $item) {
    $id = (int)$item['id'];
    $brandname = mysqli_real_escape_string($conn, $item['brandname']);
    $quantity = (int)$item['quantity'];
    $price = floatval($item['price']);
    $discount = floatval($item['discount']); // Use discount percentage, not total_discount
    $total_amount = floatval($item['total_amount']);
    $tax_amount = floatval($item['tax_amount']);
    $grand_total = floatval($item['grand_total']);

    // Bind parameters - FIXED: Use discount instead of total_discount
    $stmt->bind_param(
        "ssiddddsssds",
        $receipt_id, $brandname, $quantity, $price, $discount,
        $total_amount, $tax_amount, $grand_total, $payment_method, $payment_status, $tendered_amount, $transBy
    );

    if (!$stmt->execute()) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert item: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
}
$stmt->close();

ob_end_clean();
echo json_encode(['status' => 'success', 'message' => 'Draft saved successfully']);
$conn->close();
?>