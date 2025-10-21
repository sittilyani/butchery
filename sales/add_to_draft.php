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

// Get the full name from session
$transBy = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Unknown User';

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
$tendered_amount = floatval($data['tendered_amount'] ?? 0.00);
$items = $data['items'];

// Validate items
if (empty($items)) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'No items provided']);
    exit;
}

// Validate stock for all items
foreach ($items as $item) {
    if (!isset($item['id']) || !isset($item['brandname']) || !isset($item['quantity']) || !isset($item['price']) || !isset($item['discount'])) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Missing required item fields']);
        exit;
    }

    $id = (int)$item['id'];
    $brandname = mysqli_real_escape_string($conn, $item['brandname']);
    $quantity = (int)$item['quantity'];

    // Fetch latest stock balance
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

// DEBUG: Check if transBy has a value
error_log("transBy value: " . $transBy);

// Insert new items - Let's debug the parameter binding
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
    $discount = floatval($item['discount']);

    // Calculate amounts
    $total_amount = $quantity * $price;
    $discount_amount = $total_amount * ($discount / 100);
    $taxable_amount = $total_amount - $discount_amount;
    $tax_amount = $taxable_amount * 0.015;
    $grand_total = $taxable_amount;

    // DEBUG: Log the values being inserted
    error_log("Inserting: receipt_id=$receipt_id, brandname=$brandname, quantity=$quantity, price=$price, discount=$discount, total_amount=$total_amount, tax_amount=$tax_amount, grand_total=$grand_total, payment_method=$payment_method, payment_status=$payment_status, tendered_amount=$tendered_amount, transBy=$transBy");

    // FIX: Let's try a different approach - bind each parameter individually
    // to ensure the correct data types
    $stmt->bind_param(
        "ssiddddsssds", // 12 parameters: s,s,i,d,d,d,d,s,s,s,d,s
        $receipt_id,                    // s - string
        $brandname,                     // s - string
        $quantity,                      // i - integer
        $price,                         // d - double
        $discount,                      // d - double
        $total_amount,                  // d - double
        $tax_amount,                    // d - double
        $grand_total,                   // d - double
        $payment_method,                // s - string
        $payment_status,                // s - string
        $tendered_amount,               // d - double
        $transBy                        // s - string
    );

    if (!$stmt->execute()) {
        // DEBUG: Log the error
        error_log("MySQL Error: " . $stmt->error);
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert item: ' . $stmt->error]);
        $stmt->close();
        exit;
    } else {
        // DEBUG: Log success
        error_log("Item inserted successfully");
    }
}
$stmt->close();

ob_end_clean();
echo json_encode(['status' => 'success', 'message' => 'Send to cashier successfully for payment']);
$conn->close();
?>