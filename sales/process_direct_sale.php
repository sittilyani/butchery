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
// NOTE: $conn variable is expected to be available after this include.
require_once __DIR__ . '/../includes/config.php';

// Include Dompdf autoloader if enabled
if (SAVE_PDF_IF_POSSIBLE) {
    // Ensure this path is correct for your environment
    require_once __DIR__ . '/../dompdf/vendor/autoload.php';
}

if (!isset($_SESSION['full_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['items']) || empty($input['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'No items in order.']);
    exit;
}

try {
    // Type casting and variable assignment
    $receipt_id = $input['receipt_id'];
    $grand_total = floatval($input['grand_total']);
    $tendered_amount = floatval($input['tendered_amount']);
    $total_amount = floatval($input['total_amount']);
    $tax_amount = floatval($input['tax_amount']);
    $discount = floatval($input['discount']);
    $payment_method = $input['payment_method'];
    $payment_status = $input['payment_status'];
    $transBy = $_SESSION['full_name'];
    $items_list = $input['items'];

    // Validate tendered amount
    if ($tendered_amount < $grand_total && strtolower($payment_status) !== 'credit') {
        throw new Exception('Tendered amount cannot be less than grand total for non-credit sales.');
    }

    $conn->begin_transaction();

    // Prepare items string for sales table
    $items = '';
    foreach ($items_list as $item) {
        $items .= $item['brandname'] . ' (' . $item['quantity'] . '), ';
    }
    $items = rtrim($items, ', ');

    // --- CORRECTION 1 & 2: Fixed SQL syntax and hardcoding ---
    // Total 10 placeholders: ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
    $sales_sql = "INSERT INTO sales (receipt_id, items, total_amount, tax_amount, discount, grand_total, tendered_amount, payment_method, payment_status, transBy, transDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $sales_stmt = $conn->prepare($sales_sql);

    if ($sales_stmt === false) {
        throw new Exception('Sales SQL preparation failed: ' . $conn->error);
    }

    // --- CORRECTION 3: Corrected parameter type string ---
    // s:receipt_id, s:items, d:total_amount, d:tax_amount, d:discount, d:grand_total, d:tendered_amount, s:payment_method, s:payment_status, s:transBy
    // Type string: "ssdddddsss" (5 strings, 5 doubles)
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

    // Insert into sale_items table
    $sale_items_sql = "INSERT INTO sale_items (sales_id, brandname, quantity, unit_price, discount, total_amount, tax_amount, grand_total, sales_date, transBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
    $sale_items_stmt = $conn->prepare($sale_items_sql);
    if ($sale_items_stmt === false) {
        throw new Exception('Sale Items SQL preparation failed: ' . $conn->error);
    }

    // Prepare SQL for stock insertion (transactionType is hardcoded to 'sales')
    // Placeholders: id, brandname, productname, reorderLevel, openingBalance, quantityOut, stockBalance, transBy (8 placeholders)
    $stock_sql = "INSERT INTO stocks (id, transactionType, brandname, productname, reorderLevel, openingBalance, quantityIn, batch, expiryDate, receivedFrom, quantityOut, stockBalance, transBy, transDate) VALUES (?, 'sales', ?, ?, ?, ?, 0, '', NULL, '', ?, ?, ?, NOW())";
    $stock_stmt = $conn->prepare($stock_sql);
    if ($stock_stmt === false) {
        throw new Exception('Stocks SQL preparation failed: ' . $conn->error);
    }

    foreach ($items_list as $item) {
        // Insert sale item
        // isiddddds: sale_id(i), brandname(s), quantity(i), unit_price(d), discount(d), total_amount(d), tax_amount(d), grand_total(d), transBy(s)
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

        // --- Fetch product information and latest stock balance ---
        $product_id = null;
        $productname = '';
        $reorder_level = 10;
        $latest_stockBalance = 0;
        $product_stmt = null;

        // Fetch product details and latest stock balance
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
            // Handle case where product might exist but has no stock records yet
            // Fetch product basics
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
                // latest_stockBalance remains 0
            } else {
                throw new Exception("Product not found for brandname: " . $item['brandname']);
            }
            $product_basic_stmt->close();
        }
        // Ensure $product_stmt is closed if it was opened
        if ($product_stmt !== null) {
            $product_stmt->close();
        }

        // Check if sufficient stock exists
        if ($latest_stockBalance < $item['quantity']) {
            throw new Exception("Insufficient stock for " . $item['brandname'] . ". Available: " . $latest_stockBalance . ", Required: " . $item['quantity']);
        }

        // Calculate new stock balance
        $new_stockBalance = $latest_stockBalance - $item['quantity'];

        // --- CORRECTION 4: Corrected parameter type string for stocks ---
        // i:product_id, s:brandname, s:productname, i:reorderLevel, i:openingBalance, i:quantityOut, i:stockBalance, s:transBy
        // Type string: "issiiiss" (5 integers, 3 strings)
        $stock_stmt->bind_param(
            "issiiiss",
            $product_id,
            $item['brandname'],
            $productname,
            $reorder_level,
            $latest_stockBalance, // openingBalance for this transaction
            $item['quantity'],    // quantityOut
            $new_stockBalance,    // stockBalance
            $transBy
        );
        if (!$stock_stmt->execute()) {
             throw new Exception('Stocks insertion failed for ' . $item['brandname'] . ': ' . $stock_stmt->error);
        }

        // Also update stock_movements table
        $stock_movements_sql = "INSERT INTO stock_movements (id, transactionType, brandname, productname, openingBalance, quantityIn, quantityOut, receivedFrom, expiryDate, stockBalance, transBy, transDate) VALUES (?, 'sales', ?, ?, ?, 0, ?, 'None', '2030-01-01', ?, ?, NOW())";
        $stock_movements_stmt = $conn->prepare($stock_movements_sql);

        if ($stock_movements_stmt === false) {
             throw new Exception('Stock Movements SQL preparation failed: ' . $conn->error);
        }

        // issiiis: id(i), brandname(s), productname(s), openingBalance(i), quantityOut(i), stockBalance(i), transBy(s)
        $stock_movements_stmt->bind_param("issiiis",
            $product_id,
            $item['brandname'],
            $productname,
            $latest_stockBalance, // openingBalance
            $item['quantity'],    // quantityOut
            $new_stockBalance,    // stockBalance
            $transBy
        );

        if (!$stock_movements_stmt->execute()) {
             throw new Exception('Stock Movements insertion failed for ' . $item['brandname'] . ': ' . $stock_movements_stmt->error);
        }
        $stock_movements_stmt->close();
    }

    $sale_items_stmt->close();
    $stock_stmt->close();

    $conn->commit();

    // Generate PDF receipt after successful transaction
    if (SAVE_PDF_IF_POSSIBLE && (strtolower($payment_status) === 'paid' || strtolower($payment_status) === 'credit')) {

        error_log("PDF Generation: Starting for receipt $receipt_id");

        // Use Dompdf\Options and Dompdf\Dompdf
        $options = new Options();
        // Necessary settings for basic image and HTML parsing in Dompdf
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true); // Note: Enabling PHP can be a security risk in other contexts.
        $dompdf = new Dompdf($options);

        // Format items table for thermal printer (narrow width)
        $itemsHtml = '';
        $counter = 1;

        foreach ($items_list as $item) {
            // Trim and pad strings for consistent column-like output in monospace
            $brandname = substr($item['brandname'], 0, 20);
            $quantity = str_pad($item['quantity'], 3, ' ', STR_PAD_LEFT);
            $price = str_pad(number_format($item['price'], 2), 8, ' ', STR_PAD_LEFT);
            // $discountAmt is calculated but not displayed in the row. Let's remove the variable to avoid confusion.
            $total = str_pad(number_format($item['grand_total'], 2), 10, ' ', STR_PAD_LEFT);

            $itemsHtml .= "<div style='font-family: monospace; font-size: 9px; line-height: 1.1; white-space: pre;'>";
            $itemsHtml .= "<span style='width: 3%; display: inline-block;'>$counter</span>";
            $itemsHtml .= "<span style='width: 35%; display: inline-block;'>$brandname</span>";
            $itemsHtml .= "<span style='width: 10%; display: inline-block; text-align: right;'>$quantity</span>";
            $itemsHtml .= "<span style='width: 15%; display: inline-block; text-align: right;'>$price</span>";
            $itemsHtml .= "<span style='width: 20%; display: inline-block; text-align: right;'>$total</span>";
            $itemsHtml .= "</div>";
            $counter++;
        }

        $currentDate = date('Y-m-d H:i:s');
        $change_amount = max(0, $tendered_amount - $grand_total);

        // Convert logo to base64 for Dompdf reliability
        $logo_path = __DIR__ . '/../assets/images/Logo2-rb2.png';
        $logo_base64 = '';
        if (file_exists($logo_path)) {
            $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $logo_data = file_get_contents($logo_path);
            $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);
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
                    font-family: monospace; /* Ensure header aligns with items */
                }
                .items-header span {
                    display: inline-block;
                }
                .items-header .num { width: 3%; }
                .items-header .product { width: 35%; }
                .items-header .qty { width: 10%; text-align: right; }
                .items-header .price { width: 15%; text-align: right; }
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
                <div class='company-name'>Katakala Butchery & Restaurant</div>
                <div class='slogan'>Great Cuts and Bites</div>
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

            <div class='footer'>
                <div>Thank you for your business!</div>
                <div>Katakalabutchery@gmail.com</div>
                <div>0110 990598</div>
            </div>
        </body>
        </html>";

        try {
            $dompdf->loadHtml($html);
            // Paper size width for 80mm thermal printer (234 points is roughly 80mm)
            $dompdf->setPaper([0, 0, 234, 1000], 'portrait');
            $dompdf->render();

            // Ensure receipts directory exists and is writable
            if (!is_dir(RECEIPTS_DIR) && !mkdir(RECEIPTS_DIR, 0755, true)) {
                 throw new Exception("Failed to create receipts directory: " . RECEIPTS_DIR);
            }
            if (!is_writable(RECEIPTS_DIR)) {
                 throw new Exception("Receipts directory is not writable: " . RECEIPTS_DIR);
            }

            $filename = RECEIPTS_DIR . "/{$receipt_id}.pdf";

            // Save the PDF
            $pdf_output = $dompdf->output();
            if (file_put_contents($filename, $pdf_output)) {
                error_log("PDF Generation: Successfully saved receipt to $filename");
            } else {
                throw new Exception("Failed to write PDF file: $filename");
            }

        } catch (Exception $pdf_error) {
            error_log("PDF Generation Error (Non-Critical): " . $pdf_error->getMessage());
            // PDF error is logged but execution continues to return success for the sale itself
        }
    }

    // Send success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Sale processed successfully!',
        'sale_id' => $sale_id,
        'receipt_id' => $receipt_id,
        'clear_cart' => true // Add this flag to indicate cart should be cleared
    ]);

} catch (Exception $e) {
    // Rollback transaction on any error
    $conn->rollback();
    error_log("Sale processing error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error processing sale: ' . $e->getMessage()
    ]);
}

$conn->close();
?>