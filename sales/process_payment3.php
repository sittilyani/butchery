<?php
// Use statements at the top
use Dompdf\Dompdf;
use Dompdf\Options;

// Prevent direct execution from command line
if (PHP_SAPI === 'cli') {
    exit('This script is not intended to be run from the command line.');
}

// Constants
const PRINTER_ENABLED = true;
const PRINTER_NAME = '2023XP1028S-002C';
const RECEIPTS_DIR = __DIR__ . '/../receipts';
const SAVE_PDF_IF_POSSIBLE = true;

// Clean output buffers to prevent JSON/HTML mixing
while (ob_get_level()) {
    ob_end_clean();
}

// Set response header to JSON
header('Content-Type: application/json; charset=UTF-8');

// Start session
session_start();

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Include Dompdf autoloader if enabled
if (SAVE_PDF_IF_POSSIBLE) {
    require_once '../dompdf/vendor/autoload.php';
}

// Initialize response and transaction flag
$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];
$rollbackNeeded = false;

try {
    // Basic Guards
    if (!isset($_SESSION['username'])) {
        throw new Exception('User not logged in. Please log in to proceed.');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST requests are allowed.');
    }

    // Parse JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    // Validate required fields
    $required_fields = ['receipt_id', 'payment_method', 'payment_status', 'total_amount', 'tax_amount', 'grand_total', 'tendered_amount', 'items'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: `{$field}`.");
        }
    }
    if (!is_array($data['items']) || empty($data['items'])) {
        throw new Exception('Invalid or empty items data.');
    }

    // Sanitize and cast inputs
    $receipt_id = $data['receipt_id'];
    $payment_method = $data['payment_method'];
    $payment_status = $data['payment_status'];
    $total_amount = (float)$data['total_amount'];
    $tax_amount = (float)$data['tax_amount'];
    $discount = isset($data['total_discount']) ? (float)$data['total_discount'] : 0.0;
    $grand_total = (float)$data['grand_total'];
    $tendered_amount = (float)$data['tendered_amount'];
    $items = json_encode($data['items']); // Encode items as JSON string for sales table
    $transBy = $_SESSION['username'] ?? 'System';
    $transDate = date('Y-m-d H:i:s');
    $customer_name = isset($data['customer_name']) ? $data['customer_name'] : '';
    $customer_phone = isset($data['customer_phone']) ? $data['customer_phone'] : '';

    // Validate credit sale details
    if (strcasecmp($payment_status, 'Credit') === 0 && (empty($customer_name) || empty($customer_phone))) {
        throw new Exception('Customer name and phone are required for credit sales.');
    }

    // Begin transaction
    $conn->begin_transaction();
    $rollbackNeeded = true;

    // Check if sale exists
    $stmt = $conn->prepare("SELECT sales_id FROM sales WHERE receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_sale = $result->fetch_assoc();
    $stmt->close();

    // Insert or update sales table
    if ($existing_sale) {
        $stmt = $conn->prepare("
            UPDATE sales
            SET items = ?, total_amount = ?, tax_amount = ?, discount = ?, grand_total = ?,
                payment_method = ?, payment_status = ?, tendered_amount = ?, transBy = ?, transDate = ?
            WHERE receipt_id = ?
        ");
        $stmt->bind_param(
            "sddddssssss",
            $items,
            $total_amount,
            $tax_amount,
            $discount,
            $grand_total,
            $payment_method,
            $payment_status,
            $tendered_amount,
            $transBy,
            $transDate,
            $receipt_id
        );
    } else {
        $stmt = $conn->prepare("
            INSERT INTO sales (
                receipt_id, items, total_amount, tax_amount, discount, grand_total,
                payment_method, payment_status, tendered_amount, transBy, transDate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssddddsssss",
            $receipt_id,
            $items,
            $total_amount,
            $tax_amount,
            $discount,
            $grand_total,
            $payment_method,
            $payment_status,
            $tendered_amount,
            $transBy,
            $transDate
        );
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to save sale: " . $stmt->error);
    }
    $sales_id = $existing_sale ? $existing_sale['sales_id'] : $conn->insert_id;
    $stmt->close();

    // Prepare statements for stock and sale items
    $getLatestStock = $conn->prepare("
        SELECT id, brandname, stockBalance, expiryDate, reorderLevel, status, batch, stockID, productname
        FROM stocks
        WHERE brandname = ?
        ORDER BY transDate DESC, stockID DESC
        LIMIT 1
    ");

    $insertSaleItem = $conn->prepare("
        INSERT INTO sale_items (
            sales_id, brandname, quantity, price, discount, total_amount, tax_amount, grand_total, transBy
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $insertStockSale = $conn->prepare("
        INSERT INTO stocks (
            id, transactionType, productname, brandname, openingBalance, quantityIn,
            quantityOut, receivedFrom, batch, expiryDate, transBy, stockBalance,
            status, transDate
        ) VALUES (?, 'Sales', ?, ?, ?, 0, ?, 'None', ?, ?, ?, ?, 'Active', ?)
    ");

    $printed_items = [];

    // Process each item
    foreach ($data['items'] as $item) {
        $brandname = $item['brandname'];
        $quantity = (int)$item['quantity'];
        $price = (float)$item['price'];
        $discount_pct = isset($item['discount']) ? (float)$item['discount'] : 0.0;
        $total_amount_item = (float)$item['total_amount'];
        $tax_amount_item = (float)$item['tax_amount'];
        $grand_total_item = (float)$item['grand_total'];

        // Fetch latest stock
        $getLatestStock->bind_param("s", $brandname);
        $getLatestStock->execute();
        $stockRow = $getLatestStock->get_result()->fetch_assoc();
        if (!$stockRow) {
            throw new Exception("Stock record not found for `{$brandname}`.");
        }

        $stock_id = (int)$stockRow['id'];
        $openingBalance = (int)$stockRow['stockBalance'];
        $productname = $stockRow['productname'] ?? $brandname; // Fallback to brandname
        $batch = $stockRow['batch'] ?? null;
        $expiryDate = $stockRow['expiryDate'] ?: null;
        $status = $stockRow['status'] ?: 'Active';

        if ($openingBalance < $quantity) {
            throw new Exception("Insufficient stock for `{$brandname}`. Have `{$openingBalance}`, need `{$quantity}`.");
        }

        // Insert sale item
        $insertSaleItem->bind_param(
            "isiddddds",
            $sales_id,
            $brandname,
            $quantity,
            $price,
            $discount_pct,
            $total_amount_item,
            $tax_amount_item,
            $grand_total_item,
            $transBy
        );
        if (!$insertSaleItem->execute()) {
            throw new Exception("Failed to insert sale item (`{$brandname}`): " . $insertSaleItem->error);
        }

        // Insert stock sale transaction
        $newBalance = $openingBalance - $quantity;
        $insertStockSale->bind_param(
            "issiiissss",
            $stock_id,           // id
            $productname,       // productname
            $brandname,         // brandname
            $openingBalance,    // openingBalance
            $quantity,          // quantityOut
            $batch,             // batch
            $expiryDate,        // expiryDate
            $transBy,           // transBy
            $newBalance,        // stockBalance
            $transDate          // transDate
        );
        if (!$insertStockSale->execute()) {
            throw new Exception("Failed to insert stock sale record (`{$brandname}`): " . $insertStockSale->error);
        }

        $printed_items[] = [
            'brandname' => $brandname,
            'quantity' => $quantity,
            'price' => $price,
            'discount' => $discount_pct,
            'grand_total' => $grand_total_item
        ];
    }

    $getLatestStock->close();
    $insertSaleItem->close();
    $insertStockSale->close();

    // Delete drafts
    $stmt = $conn->prepare("DELETE FROM sales_drafts WHERE receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete drafts: " . $stmt->error);
    }
    $stmt->close();

    // Record credit balance
    if (strcasecmp($payment_status, 'Credit') === 0) {
        $balance_amount = max(0, $grand_total - $tendered_amount);
        $stmt = $conn->prepare("
            INSERT INTO credit_balances (
                receipt_id, customer_name, customer_phone, total_amount, tendered_amount, balance_amount, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)
            ON DUPLICATE KEY UPDATE
                customer_name = ?, customer_phone = ?, total_amount = ?, tendered_amount = ?, balance_amount = ?, status = 'Pending', created_by = ?
        ");
        $stmt->bind_param(
            "sssdddsdsddds",
            $receipt_id,
            $customer_name,
            $customer_phone,
            $grand_total,
            $tendered_amount,
            $balance_amount,
            $transBy,
            $customer_name,
            $customer_phone,
            $grand_total,
            $tendered_amount,
            $balance_amount,
            $transBy
        );
        if (!$stmt->execute()) {
            throw new Exception("Failed to save credit balance: " . $stmt->error);
        }
        $stmt->close();
    }

    // Generate PDF receipt
    if (SAVE_PDF_IF_POSSIBLE && ($payment_status === 'Paid' || $payment_status === 'Credit')) {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'times');
        $dompdf = new Dompdf($options);

        $itemsHtml = '<table style="width:100%; border-collapse:collapse; font-size:10px; border:1px solid #000;">
            <tr>
                <th style="border:1px solid #000; padding:3px;">#</th>
                <th style="border:1px solid #000; padding:3px;">Product</th>
                <th style="border:1px solid #000; padding:3px;">Qty</th>
                <th style="border:1px solid #000; padding:3px;">Price</th>
                <th style="border:1px solid #000; padding:3px;">% Disc</th>
                <th style="border:1px solid #000; padding:3px;">Total</th>
            </tr>';
        foreach ($printed_items as $i => $item) {
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
        $change_amount = max(0, $tendered_amount - $grand_total);

        $html = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            width: auto;
            margin: 2mm;
            line-height: 1.2;
        }
        .logo {
            text-align: center;
            margin-bottom: 8px;
        }
        .logo p {
            margin: 1px 0;
        }
        .company-name {
            font-weight: bold;
            font-size: 9px;
            color: red;
        }
        .slogan {
            font-style: italic;
            font-size: 7px;
        }
        h2 {
            text-align: center;
            margin: 3px 0;
            font-size: 9px;
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
    </style>
</head>
<body>
    <h2>Order Receipt</h2>
    <div class='logo'>
        <div class='logo'><img src='../assets/images/Logo-original.JPEG' width='50' height='50' alt='Joima Pharma Logo'></div>
        <p><span class='company-name'>Joima Pharmaceuticals</span></p>
        <p><span class='slogan'>Caring Beyond Prescriptions</span></p>
    </div>
    <div class='receipt-info'>
        <p><strong>Receipt ID:</strong> " . htmlspecialchars($receipt_id) . "</p>
        <p><strong>Date:</strong> {$currentDate}</p>
        <p><strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>
    </div>
    {$itemsHtml}
    <div class='totals'>
        <p>Total Amount: KES " . number_format($total_amount, 2) . "</p>
        <p>Tax (1.5%): KES " . number_format($tax_amount, 2) . "</p>
        <p>Total Discount: KES " . number_format($discount, 2) . "</p>
        <p>Grand Total: KES " . number_format($grand_total, 2) . "</p>
        <p>Tendered: KES " . number_format($tendered_amount, 2) . "</p>
        <p>Change: KES " . number_format($change_amount, 2) . "</p>
        <p>Payment Status: " . htmlspecialchars($payment_status) . "</p>
    </div>
</body>
</html>
";

        $dompdf->loadHtml($html);
        $dompdf->setPaper(array(0, 0, 165, 600), 'portrait'); // 58mm x 210mm thermal paper
        $dompdf->render();

        if (!is_dir(RECEIPTS_DIR)) {
            mkdir(RECEIPTS_DIR, 0755, true);
        }
        $filename = RECEIPTS_DIR . "/{$receipt_id}-" . date('YmdHis') . ".pdf";
        file_put_contents($filename, $dompdf->output());
    }

    // Commit transaction
    $conn->commit();
    $rollbackNeeded = false;

    // Success response
    $response = [
        'status' => 'success',
        'message' => 'Sales submitted successfully.',
        'redirect' => '../sales/view_order.php?message=' . urlencode('Order submitted successfully')
    ];

} catch (Exception $e) {
    if ($rollbackNeeded) {
        $conn->rollback();
    }
    error_log('Sales transaction failed: ' . $e->getMessage());
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
} finally {
    $conn->close();
    echo json_encode($response);
}
?>