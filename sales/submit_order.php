<?php
ob_start();
include "../includes/header.php"; // Includes config.php and session_start()
require_once '../dompdf/vendor/autoload.php'; // Composer autoload for Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

// Validate session
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
error_log('Submit Order Input: ' . $input);

if (!$data || !isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing items data']);
    exit;
}

$required_fields = ['receipt_id', 'payment_method', 'payment_status', 'total_amount', 'tax_amount', 'discount', 'grand_total', 'tendered_amount'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing field: $field"]);
        exit;
    }
}

$receipt_id = mysqli_real_escape_string($conn, $data['receipt_id']);
$payment_method = mysqli_real_escape_string($conn, $data['payment_method']);
$payment_status = mysqli_real_escape_string($conn, $data['payment_status']);
$total_amount = (float)$data['total_amount'];
$tax_amount = (float)$data['tax_amount'];
$discount = (float)$data['discount'];
$grand_total = (float)$data['grand_total'];
$tendered_amount = (float)$data['tendered_amount'];
$transBy = $_SESSION['username'];

// Validate items
foreach ($data['items'] as $item) {
    if (!isset($item['id']) || !isset($item['brandname']) || !isset($item['quantity']) || !isset($item['price']) || !isset($item['discount']) || !isset($item['total']) || !isset($item['tax_amount']) || !isset($item['grand_total'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required item fields']);
        exit;
    }
}

// Check if already paid
$stmt = $conn->prepare("SELECT payment_status FROM sales WHERE receipt_id = ?");
$stmt->bind_param("s", $receipt_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if ($row['payment_status'] === 'Paid') {
        echo json_encode(['status' => 'error', 'message' => 'Order is already paid']);
        exit;
    }
}
$stmt->close();

try {
    $conn->begin_transaction();

    // Validate stock for all items
    foreach ($data['items'] as $item) {
        $id = (int)$item['id'];
        $brandname = mysqli_real_escape_string($conn, $item['brandname']);
        $quantity = (int)$item['quantity'];

        // Fetch latest stock balance
        $stock_query = $conn->prepare("
            SELECT stockBalance, id
            FROM stocks s1
            INNER JOIN (
                SELECT brandname, MAX(transDate) AS maxTransDate, MAX(id) AS maxId
                FROM stocks
                GROUP BY brandname
            ) s2 ON s1.brandname = s2.brandname AND s1.transDate = s2.maxTransDate AND s1.id = s2.maxId
            WHERE s1.brandname = ? AND s1.id = ?
        ");
        $stock_query->bind_param("si", $productname, $id);
        $stock_query->execute();
        $stock_result = $stock_query->get_result();
        if ($stock_row = $stock_result->fetch_assoc()) {
            $stockBalance = (int)$stock_row['stockBalance'];
            if ($stockBalance <= 0 || $stockBalance < $quantity) {
                throw new Exception("Not enough stock for $brandname (Available: $stockBalance, Requested: $quantity)");
            }
        } else {
            throw new Exception("Stock record not found for $brandname");
        }
        $stock_query->close();
    }

    // Insert into sales
    $stmt = $conn->prepare("
        INSERT INTO sales (
            receipt_id, total_amount, tax_amount, discount, grand_total,
            payment_method, payment_status, tendered_amount, transBy
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sddddssss",
        $receipt_id, $total_amount, $tax_amount, $discount, $grand_total,
        $payment_method, $payment_status, $tendered_amount, $transBy
    );
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert into sales: " . $stmt->error);
    }
    $sales_id = $conn->insert_id;
    $stmt->close();

    // Insert sale items and update stocks
    $item_stmt = $conn->prepare("
        INSERT INTO sale_items (
            sales_id, id, productname, brandname, quantity, price, discount,
            total_amount, tax_amount, grand_total, transBy
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stock_stmt = $conn->prepare("
        UPDATE stocks
        SET stockBalance = stockBalance - ?, transBy = ?, transDate = NOW()
        WHERE id = ? AND brandname = ?
    ");

    foreach ($data['items'] as $item) {
        $id = (int)$item['id'];
        $productname = (int)$item['productname'];
        $brandname = mysqli_real_escape_string($conn, $item['brandname']);
        $quantity = (int)$item['quantity'];
        $price = (float)$item['price'];
        $discount = (float)$item['discount'];
        $total_amount_item = (float)$item['total'];
        $tax_amount_item = (float)$item['tax_amount'];
        $grand_total_item = (float)$item['grand_total'];

        // Insert sale item
        $item_stmt->bind_param(
            "issisddddss",
            $sales_id, $id, $productname, $brandname, $quantity, $price, $discount,
            $total_amount_item, $tax_amount_item, $grand_total_item, $transBy
        );
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to insert sale item for $brandname: " . $item_stmt->error);
        }

        // Update stock
        $stock_stmt->bind_param("isiss", $quantity, $transBy, $id, $brandname, $productname);
        if (!$stock_stmt->execute()) {
            throw new Exception("Failed to update stock for $productname: " . $stock_stmt->error);
        }
    }
    $item_stmt->close();
    $stock_stmt->close();

    // Delete drafts
    $delete_stmt = $conn->prepare("DELETE FROM sales_drafts WHERE receipt_id = ?");
    $delete_stmt->bind_param("s", $receipt_id);
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete drafts: " . $delete_stmt->error);
    }
    $delete_stmt->close();

    // Generate PDF receipt
    if ($payment_status === 'Paid') {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'times');
        $dompdf = new Dompdf($options);

        // Build HTML for receipt
        $itemsHtml = '<table style="width:100%; border-collapse:collapse; font-size:12px; border:1px solid #000;">
            <tr>
                <th style="border:1px solid #000; padding:3px;">#</th>
                <th style="border:1px solid #000; padding:3px;">Product</th>
                <th style="border:1px solid #000; padding:3px;">Qty</th>
                <th style="border:1px solid #000; padding:3px;">Price</th>
                <th style="border:1px solid #000; padding:3px;">Discount</th>
                <th style="border:1px solid #000; padding:3px;">Total</th>
            </tr>';
        foreach ($data['items'] as $i => $item) {
            $itemsHtml .= "<tr>
                <td style=\"border:1px solid #000; padding:3px;\">" . ($i + 1) . "</td>
                <td style=\"border:1px solid #000; padding:3px;\">" . htmlspecialchars($item['brandname']) . "</td>
                <td style=\"border:1px solid #000; padding:3px;\">" . $item['quantity'] . "</td>
                <td style=\"border:1px solid #000; padding:3px;\">" . number_format($item['price'], 2) . "</td>
                <td style=\"border:1px solid #000; padding:3px;\">" . number_format($item['discount'], 2) . "</td>
                <td style=\"border:1px solid #000; padding:3px;\">" . number_format($item['grand_total'], 2) . "</td>
            </tr>";
        }
        $itemsHtml .= '</table>';

        $currentDate = date('Y-m-d H:i:s');
        $change_amount = $tendered_amount - $grand_total;
        $html = <<<EOD
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            width: 148mm;
            margin: 2mm;
            line-height: 1.2;
        }
        .logo {
            text-align: center;
            margin-bottom: 8px;
        }
        h2 {
            text-align: center;
            margin: 3px 0;
            font-size: 10px;
            font-weight: bold;
        }
        .receipt-info, .totals {
            margin: 3px 0;
        }
        .receipt-info p, .totals p {
            margin: 1px 0;
        }
        table, th, td {
            border: 1px solid #000;
            border-collapse: collapse;
            text-align: left;
            font-size: 7px;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 3px;
        }
        .slogan {
            text-align: center;
            font-style: italic;
            margin-top: 5px;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <h2>Order Receipt</h2>
    <div class="logo">
        <img src="../assets/images/JaimoLogo4.png" width="50" height="50" alt="">
    </div>
    <div class="receipt-info">
        <p><strong>Receipt ID:</strong> {$receipt_id}</p>
        <p><strong>Date:</strong> {$currentDate}</p>
        <p><strong>Served by:</strong> {$transBy}</p>
        <p><strong>Payment Method:</strong> {$payment_method}</p>
    </div>
    {$itemsHtml}
    <div class="totals">
        <p>Total Amount: KES {$total_amount}</p>
        <p>ToT (Tax) (1.5%): KES {$tax_amount}</p>
        <p>Total Discount: KES {$discount}</p>
        <p>Grand Total: KES {$grand_total}</p>
        <p>Tendered: KES {$tendered_amount}</p>
        <p>Change: KES {$change_amount}</p>
        <p>Payment Status: {$payment_status}</p>
        <div class="slogan">
            <p>Caring Beyond Prescriptions</p>
        </div>
    </div>
</body>
</html>
EOD;

        $dompdf->loadHtml($html);
        $dompdf->setPaper(array(0, 0, 165, 600), 'portrait'); // 58mm x 210mm thermal paper
        $dompdf->render();

        // Ensure receipts directory exists
        $receiptsDir = dirname(__DIR__) . '/receipts';
        if (!is_dir($receiptsDir)) {
            mkdir($receiptsDir, 0755, true);
        }

        $filename = "{$receiptsDir}/{$receipt_id}-" . date('YmdHis') . ".pdf";
        file_put_contents($filename, $dompdf->output());
    }

    $conn->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Order submitted successfully',
        'redirect' => '../sales/view_order.php?message=' . urlencode('Order submitted successfully')
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log('Exception: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();
?>