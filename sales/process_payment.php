<?php
session_start();

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

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Include Dompdf autoloader if enabled
if (SAVE_PDF_IF_POSSIBLE) {
    require_once '../dompdf/vendor/autoload.php';
}

// Check if user is logged in
if (!isset($_SESSION['full_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['items']) || empty($input['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'No items in order.']);
    exit;
}

try {
    $conn->begin_transaction();

    // Store variables for receipt generation
    $receipt_id = $input['receipt_id'];
    $grand_total = floatval($input['grand_total']);
    $tendered_amount = floatval($input['tendered_amount']);
    $total_amount = floatval($input['total_amount']);
    $tax_amount = floatval($input['tax_amount']);
    $discount = floatval(isset($input['discount']) ? $input['discount'] : 0.0);
    $payment_method = $input['payment_method']; // e.g., 'Cash', 'Mpesa', 'Credit'
    $payment_status = $input['payment_status']; // e.g., 'Paid', 'Credit', 'Pending'
    $transBy = $_SESSION['full_name'];
    $items_list = $input['items'];

    // Check if this is a credit sale
    $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] === true;
    $customer_name = isset($input['customer_name']) ? trim($input['customer_name']) : '';
    $customer_phone = isset($input['customer_phone']) ? trim($input['customer_phone']) : '';
    $balance_amount = isset($input['balance_amount']) ? floatval($input['balance_amount']) : 0;

    // Validate tendered amount - ONLY for non-credit sales
    if (!$is_credit_sale && strtolower($payment_status) !== 'credit') {
        if ($tendered_amount < $grand_total) {
            throw new Exception('Tendered amount cannot be less than grand total for non-credit sales.');
        }
    }

    // For credit sales, validate customer information
    if ($is_credit_sale || strtolower($payment_status) === 'credit') {
        if (empty($customer_name)) {
            throw new Exception('Customer name is required for credit sales.');
        }
        if (empty($customer_phone)) {
            throw new Exception('Customer phone is required for credit sales.');
        }
        if ($balance_amount <= 0) {
            throw new Exception('Balance amount must be greater than 0 for credit sales.');
        }
    }

    // Prepare items string for sales table
    $items = '';
    foreach ($items_list as $item) {
        $items .= $item['brandname'] . ' (' . $item['quantity'] . '), ';
    }
    $items = rtrim($items, ', ');

    // Sales table insertion
    $sales_sql = "INSERT INTO sales (receipt_id, items, total_amount, tax_amount, discount, grand_total, tendered_amount, payment_method, payment_status, transBy, transDate)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $sales_stmt = $conn->prepare($sales_sql);
    if ($sales_stmt === false) {
        throw new Exception('Sales insert preparation failed: ' . $conn->error);
    }
    $sales_stmt->bind_param(
        "ssdddddsss",
        $receipt_id,
        $items,
        $total_amount,
        $tax_amount,
        $discount,
        $grand_total,
        $tendered_amount,
        $payment_method,
        $payment_status,
        $transBy
    );
    if (!$sales_stmt->execute()) {
        throw new Exception('Sales insertion failed: ' . $sales_stmt->error);
    }
    $sale_id = $conn->insert_id;
    $sales_stmt->close();

    // If credit sale, insert into credit_balances table
    if ($is_credit_sale || strtolower($payment_status) === 'credit') {
        $credit_sql = "INSERT INTO credit_balances (receipt_id, customer_name, customer_phone, total_amount, tendered_amount, balance_amount, status, created_by, transDate)
                       VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())";
        $credit_stmt = $conn->prepare($credit_sql);
        if ($credit_stmt === false) {
            throw new Exception('Credit balance insert preparation failed: ' . $conn->error);
        }
        $credit_stmt->bind_param(
            "sssddds",
            $receipt_id,
            $customer_name,
            $customer_phone,
            $grand_total,
            $tendered_amount,
            $balance_amount,
            $transBy
        );
        if (!$credit_stmt->execute()) {
            throw new Exception('Credit balance insertion failed: ' . $credit_stmt->error);
        }
        $credit_stmt->close();
    }

    // Delete drafts
    $delete_stmt = $conn->prepare("DELETE FROM sales_drafts WHERE receipt_id = ?");
    $delete_stmt->bind_param("s", $receipt_id);
    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete drafts: " . $delete_stmt->error);
    }
    $delete_stmt->close();

    // Insert into sale_items table
    $sale_items_sql = "INSERT INTO sale_items (sales_id, brandname, quantity, unit_price, discount, total_amount, tax_amount, grand_total, sales_date, transBy)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $sale_items_stmt = $conn->prepare($sale_items_sql);
    if ($sale_items_stmt === false) {
        throw new Exception('Sale items insert preparation failed: ' . $conn->error);
    }

    // Prepare SQL for stock insertion
    $stock_sql = "INSERT INTO stocks (id, transactionType, brandname, productname, reorderLevel, openingBalance, quantityIn, batch, expiryDate, receivedFrom, quantityOut, stockBalance, transBy, transDate)
                  VALUES (?, 'sales', ?, ?, ?, ?, 0, '', NULL, '', ?, ?, ?, NOW())";
    $stock_stmt = $conn->prepare($stock_sql);
    if ($stock_stmt === false) {
        throw new Exception('Stock insert preparation failed: ' . $conn->error);
    }

    // Prepare SQL for stock movements insertion
    $stock_movements_sql = "INSERT INTO stock_movements (id, transactionType, brandname, productname, openingBalance, quantityIn, quantityOut, receivedFrom, expiryDate, stockBalance, transBy, transDate)
                            VALUES (?, 'sales', ?, ?, ?, 0, ?, 'None', '2030-01-01', ?, ?, NOW())";
    $stock_movements_stmt = $conn->prepare($stock_movements_sql);
    if ($stock_movements_stmt === false) {
        throw new Exception('Stock movements insert preparation failed: ' . $conn->error);
    }

    foreach ($items_list as $item) {
        // Validate quantity
        if (!is_numeric($item['quantity'])) {
            throw new Exception("Invalid quantity for " . $item['brandname']);
        }

        // Insert sale item
        $sale_items_stmt->bind_param(
            "isiddddds",
            $sale_id,
            $item['brandname'],
            $item['quantity'],
            $item['price'],
            $item['discount'],
            $item['total_amount'],
            $item['tax_amount'],
            $item['grand_total'],
            $transBy
        );
        if (!$sale_items_stmt->execute()) {
            throw new Exception('Sale item insertion failed for ' . $item['brandname'] . ': ' . $sale_items_stmt->error);
        }

        // Get product information and latest stock balance
        $product_id = null;
        $productname = '';
        $reorder_level = 10;
        $latest_stockBalance = 0;

        $product_sql = "SELECT p.id, p.productname, p.reorder_level, s.stockBalance
                        FROM products p
                        LEFT JOIN stocks s ON p.id = s.id
                        WHERE p.brandname = ?
                        ORDER BY s.transDate DESC
                        LIMIT 1";
        $product_stmt = $conn->prepare($product_sql);
        if ($product_stmt === false) {
            throw new Exception('Product details fetch preparation failed: ' . $conn->error);
        }
        $product_stmt->bind_param("s", $item['brandname']);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();

        if ($product_result->num_rows > 0) {
            $product_data = $product_result->fetch_assoc();
            $product_id = $product_data['id'];
            $productname = $product_data['productname'];
            $reorder_level = $product_data['reorder_level'] ?? 10;
            $latest_stockBalance = $product_data['stockBalance'] ?? 0;
        } else {
            $product_basic_sql = "SELECT id, productname, reorder_level FROM products WHERE brandname = ? LIMIT 1";
            $product_basic_stmt = $conn->prepare($product_basic_sql);
            if ($product_basic_stmt === false) {
                throw new Exception('Product basic fetch preparation failed: ' . $conn->error);
            }
            $product_basic_stmt->bind_param("s", $item['brandname']);
            $product_basic_stmt->execute();
            $product_basic_result = $product_basic_stmt->get_result();
            if ($product_basic_result->num_rows > 0) {
                $product_basic_data = $product_basic_result->fetch_assoc();
                $product_id = $product_basic_data['id'];
                $productname = $product_basic_data['productname'];
                $reorder_level = $product_basic_data['reorder_level'] ?? 10;
            } else {
                throw new Exception("Product not found for brandname: " . $item['brandname']);
            }
            $product_basic_stmt->close();
        }
        $product_stmt->close();

        // Check if sufficient stock exists
        if ($latest_stockBalance < $item['quantity']) {
            throw new Exception("Insufficient stock for " . $item['brandname'] . ". Available: " . $latest_stockBalance . ", Required: " . $item['quantity']);
        }

        // Calculate new stock balance
        $new_stockBalance = $latest_stockBalance - $item['quantity'];

        // Validate data types for stock insertion
        if (!is_numeric($product_id) || !is_string($item['brandname']) || !is_string($productname) ||
            !is_numeric($reorder_level) || !is_numeric($latest_stockBalance) || !is_numeric($item['quantity']) ||
            !is_numeric($new_stockBalance) || !is_string($transBy)) {
            throw new Exception("Invalid data types for stock insertion: " . json_encode([
                'product_id' => $product_id,
                'brandname' => $item['brandname'],
                'productname' => $productname,
                'reorder_level' => $reorder_level,
                'latest_stockBalance' => $latest_stockBalance,
                'quantity' => $item['quantity'],
                'new_stockBalance' => $new_stockBalance,
                'transBy' => $transBy
            ]));
        }

        // Insert into stocks table
        $stock_stmt->bind_param(
            "issiisis",
            $product_id,
            $item['brandname'],
            $productname,
            $reorder_level,
            $latest_stockBalance,
            $item['quantity'],
            $new_stockBalance,
            $transBy
        );
        if (!$stock_stmt->execute()) {
            throw new Exception('Stocks insertion failed for ' . $item['brandname'] . ': ' . $stock_stmt->error);
        }

        // Insert into stock_movements table
        $stock_movements_stmt->bind_param(
            "issiiis",
            $product_id,
            $item['brandname'],
            $productname,
            $latest_stockBalance,
            $item['quantity'],
            $new_stockBalance,
            $transBy
        );
        if (!$stock_movements_stmt->execute()) {
            throw new Exception('Stock Movements insertion failed for ' . $item['brandname'] . ': ' . $stock_movements_stmt->error);
        }
    }

    $sale_items_stmt->close();
    $stock_stmt->close();
    $stock_movements_stmt->close();
    $conn->commit();

    // Generate PDF receipt after successful transaction
    if (SAVE_PDF_IF_POSSIBLE && (strtolower($payment_status) === 'paid' || strtolower($payment_status) === 'credit')) {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($options);

        // Convert logo to base64
        $logo_path = __DIR__ . '/../assets/images/Logo-round-nobg-2.png';
        $logo_base64 = '';
        if (file_exists($logo_path)) {
            $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $logo_data = file_get_contents($logo_path);
            $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);
        }

        // Format items table for thermal printer
        $itemsHtml = '';
        $counter = 1;
        foreach ($items_list as $item) {
            $brandname = substr($item['brandname'], 0, 20);
            $quantity = str_pad($item['quantity'], 3, ' ', STR_PAD_LEFT);
            $price = str_pad(number_format($item['price'], 2), 8, ' ', STR_PAD_LEFT);
            $discount_amt = str_pad(number_format($item['discount'], 2), 6, ' ', STR_PAD_LEFT);
            $total = str_pad(number_format($item['grand_total'], 2), 10, ' ', STR_PAD_LEFT);
            $itemsHtml .= "<div style='font-family: monospace; font-size: 9px; line-height: 1.1; white-space: pre; border-bottom: 1px dashed #E0E0E0;'>";
            $itemsHtml .= "<span style='width: 3%; display: inline-block;'>$counter</span>";
            $itemsHtml .= "<span style='width: 30%; display: inline-block;'>$brandname</span>";
            $itemsHtml .= "<span style='width: 8%; display: inline-block; text-align: right;'>$quantity</span>";
            $itemsHtml .= "<span style='width: 15%; display: inline-block; text-align: right;'>$price</span>";
            $itemsHtml .= "<span style='width: 12%; display: inline-block; text-align: right;'>$discount_amt</span>";
            $itemsHtml .= "<span style='width: 20%; display: inline-block; text-align: right;'>$total</span>";
            $itemsHtml .= "</div>";
            $counter++;
        }

        $currentDate = date('Y-m-d H:i:s');
        $change_amount = max(0, $tendered_amount - $grand_total);

        // Add credit info section if applicable
        $creditInfoHtml = '';
        if ($is_credit_sale || strtolower($payment_status) === 'credit') {
            $creditInfoHtml = "
            <div style='border-top: 2px dashed #000; margin-top: 5px; padding-top: 5px;'>
                <div style='text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 3px;'>CREDIT SALE</div>
                <div class='total-row'>
                    <span>Customer:</span>
                    <span>" . htmlspecialchars($customer_name) . "</span>
                </div>
                <div class='total-row'>
                    <span>Phone:</span>
                    <span>" . htmlspecialchars($customer_phone) . "</span>
                </div>
                <div class='total-row' style='font-weight: bold; font-size: 11px;'>
                    <span>Balance Due:</span>
                    <span>KES " . number_format($balance_amount, 2) . "</span>
                </div>
            </div>";
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
            <style>
                @page {
                    margin: 1mm 2mm;
                    padding: 0;
                }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 10px;
                    font-weight: bold;
                    width: 80mm;
                    margin: 0;
                    padding: 1mm 2mm;
                    line-height: 1.1;
                    color: #000;
                    background: white;
                }
                .header {
                    text-align: center;
                    margin-bottom: 3px;
                    border-bottom: 1px dashed #000;
                    padding-bottom: 3px;
                }
                .logo {
                    text-align: center;
                    margin: 2px 0;
                }
                .logo img {
                    max-width: 40px;
                    height: auto;
                }
                .company-name {
                    font-weight: bold;
                    font-size: 11px;
                    margin: 1px 0;
                }
                .slogan {
                    font-style: italic;
                    font-size: 8px;
                    margin: 1px 0;
                }
                .receipt-title {
                    text-align: center;
                    font-weight: bold;
                    font-size: 11px;
                    margin: 3px 0;
                    text-transform: uppercase;
                }
                .receipt-info {
                    margin: 2px 0;
                    font-size: 9px;
                }
                .receipt-info p {
                    margin: 1px 0;
                    display: flex;
                    justify-content: space-between;
                }
                .receipt-info .label {
                    font-weight: bold;
                }
                .items-header {
                    display: flex;
                    font-weight: bold;
                    border-bottom: 1px solid #000;
                    margin: 3px 0 2px 0;
                    padding-bottom: 1px;
                    font-size: 9px;
                    font-family: monospace;
                }
                .items-header span {
                    display: inline-block;
                }
                .items-header .num { width: 3%; }
                .items-header .product { width: 30%; }
                .items-header .qty { width: 8%; text-align: right; }
                .items-header .price { width: 15%; text-align: right; }
                .items-header .discount { width: 12%; text-align: right; }
                .items-header .total { width: 20%; text-align: right; }
                .totals {
                    border-top: 1px dashed #000;
                    margin-top: 3px;
                    padding-top: 3px;
                    font-size: 10px;
                }
                .total-row {
                    display: flex;
                    justify-content: space-between;
                    margin: 1px 0;
                }
                .total-row.total-final {
                    font-weight: bold;
                    border-top: 1px solid #000;
                    padding-top: 2px;
                    margin-top: 2px;
                }
                .footer {
                    text-align: center;
                    margin-top: 5px;
                    font-size: 8px;
                    border-top: 1px dashed #000;
                    padding-top: 3px;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                " . ($logo_base64 ? "<div class='logo'><img src='$logo_base64' alt='Logo'></div>" : "") . "
                <div class='company-name'>Retail Pharma POS</div>
                <div class='slogan'>Human medicines & supplies</div>
            </div>
            <div class='receipt-title'>SALES RECEIPT</div>
            <div class='receipt-info'>
                <p><span class='label'>Receipt ID:</span> <span>" . htmlspecialchars($receipt_id) . "</span></p>
                <p><span class='label'>Date:</span> <span>" . date('Y-m-d H:i:s') . "</span></p>
                <p><span class='label'>Cashier:</span> <span>" . htmlspecialchars($transBy) . "</span></p>
                <p><span class='label'>Payment Method:</span> <span>" . htmlspecialchars($payment_method) . "</span></p>
            </div>
            <div class='items-header'>
                <span class='num'>#</span>
                <span class='product'>PRODUCT</span>
                <span class='qty'>QTY</span>
                <span class='price'>PRICE</span>
                <span class='discount'>DISC</span>
                <span class='total'>TOTAL</span>
            </div>
            <div class='items-list'>
                $itemsHtml
            </div>
            <div class='totals'>
                <div class='total-row'>
                    <span>Subtotal:</span>
                    <span>KES " . number_format($total_amount, 2) . "</span>
                </div>
                <div class='total-row'>
                    <span>Tax:</span>
                    <span>KES " . number_format($tax_amount, 2) . "</span>
                </div>
                <div class='total-row'>
                    <span>Discount:</span>
                    <span>KES " . number_format($discount, 2) . "</span>
                </div>
                <div class='total-row total-final'>
                    <span>GRAND TOTAL:</span>
                    <span>KES " . number_format($grand_total, 2) . "</span>
                </div>
                <div class='total-row'>
                    <span>Tendered:</span>
                    <span>KES " . number_format($tendered_amount, 2) . "</span>
                </div>
                <div class='total-row total-final'>
                    <span>CHANGE:</span>
                    <span>KES " . number_format($change_amount, 2) . "</span>
                </div>
                <div class='total-row'>
                    <span>Status:</span>
                    <span>" . htmlspecialchars(strtoupper($payment_status)) . "</span>
                </div>
            </div>
            $creditInfoHtml
            <div class='footer'>
                <div>Thank you for your business!</div>
                <div>www.sittilyani@gmail.com</div>
                <div>+ 254-722-42-77-21</div>
            </div>
        </body>
        </html>";

        try {
            $dompdf->loadHtml($html);
            $dompdf->setPaper([0, 0, 234, 1000], 'portrait');
            $dompdf->render();
            if (!is_dir(RECEIPTS_DIR) && !mkdir(RECEIPTS_DIR, 0755, true)) {
                throw new Exception("Failed to create receipts directory: " . RECEIPTS_DIR);
            }
            if (!is_writable(RECEIPTS_DIR)) {
                throw new Exception("Receipts directory is not writable: " . RECEIPTS_DIR);
            }
            $filename = RECEIPTS_DIR . "/{$receipt_id}.pdf";
            $pdf_output = $dompdf->output();
            if (file_put_contents($filename, $pdf_output)) {
                error_log("PDF Generation: Successfully saved receipt to $filename");
            } else {
                throw new Exception("Failed to write PDF file: $filename");
            }
        } catch (Exception $pdf_error) {
            error_log("PDF Generation Error (Non-Critical): " . $pdf_error->getMessage());
        }
    }

    // Send success response
    echo json_encode([
        'status' => 'success',
        'message' => $is_credit_sale || strtolower($payment_status) === 'credit'
            ? 'Credit sale processed successfully!'
            : 'Sale processed successfully!',
        'sale_id' => $sale_id
    ]);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Sale processing error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error processing sale: ' . $e->getMessage()
    ]);
}

$conn->close();
?>