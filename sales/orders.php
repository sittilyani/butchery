<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

$page_title = "Process Sell";

// Check for logged-in user
if (!isset($_SESSION['full_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

$receipt_id = isset($_GET['receipt_id']) ? $_GET['receipt_id'] : 'ORD' . date('Ymd') . sprintf("%04d", rand(1, 9999));

// Load draft order if receipt_id is provided
$draft = null;
$items = [];
if (isset($_GET['receipt_id'])) {
    $stmt = $conn->prepare("SELECT * FROM sales_drafts WHERE receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $draft = $items[0];
    }
    $stmt->close();
}

// Fetch all products with current stock
$products_query = "SELECT
    p.id,
    p.brandname,
    p.productname,
    p.unit_price as price,
    COALESCE((
        SELECT stockBalance
        FROM stocks
        WHERE brandname = p.brandname
        ORDER BY stockID DESC
        LIMIT 1
    ), 0) as current_stock
FROM products p
WHERE p.currentstatus = 'active'
ORDER BY p.brandname ASC";
$products_result = $conn->query($products_query);
$all_products = $products_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="../assets/fontawesome-7.1.1/css/all.min.css" type="text/css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f4f7fc}
        .main-content{padding:20px}
        .product-item{cursor:pointer;padding:10px;border:1px solid #ddd;background-color:#99ccff;transition:all .3s ease;height:120px;color:#000;font-size:18px;display:flex;flex-direction:column;justify-content:center;border-radius:8px}
        .product-item:hover{background-color:#CCFFCC;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,.1)}
        .product-item.disabled{background-color:#f8d7da;cursor:not-allowed;opacity:0.7}
        .product-item h6{font-size:.9rem;margin:5px 0;font-weight:700;color:#000}
        .product-item p{font-size:18px;margin:2px 0;color:#000}
        .product-item.disabled h6,.product-item.disabled p{color:#f00}
        .product-item small{font-size:12px;color:#666}
        #products-container{max-height:70vh;padding-right:10px;overflow-y:auto}
        #products-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px;padding:5px 0}
        .order-summary{background-color:#f8f9fa;padding:20px;border-radius:8px;border:1px solid #dee2e6;min-height:40vh;overflow-y:auto}
        #order-items tr td{vertical-align:middle;padding:8px}
        .quantity-input,.discount-input,.amount-input{width:80px;text-align:center;border:1px solid #ced4da;border-radius:4px;padding:5px}
        .amount-input{width:100px}
        .discount-input{-moz-appearance:textfield;width:60px}
        .discount-input::-webkit-outer-spin-button,.discount-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
        .search-container{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
        .btn-remove{padding:2px 8px;font-size:.8rem}
        .total-section{background-color:#fff;padding:15px;border-radius:5px;margin:15px 0;border:1px solid #dee2e6}
        .success-message{background-color:#DDFCAF;color:green;font-size:18px;padding:5px 10px;margin-bottom:10px;display:inline-block;border-radius:4px}
        .discount-error{border-color:#dc3545!important;background-color:#f8d7da!important}
        .stock-badge{font-size:11px;padding:2px 6px;border-radius:10px;margin-left:5px}
        .stock-badge.low{background:#fee2e2;color:#dc2626}
        .stock-badge.normal{background:#dcfce7;color:#16a34a}
        .buttons{display:flex;gap:10px;flex-wrap:wrap}
        .buttons button{flex:1;min-width:120px}

        @media(max-width:1200px){#products-list{grid-template-columns:repeat(auto-fill,minmax(180px,1fr))}}
        @media(max-width:992px){#products-list{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}.main-content{padding:15px}}
        @media(max-width:768px){
            #products-list{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
            .product-item{height:100px;padding:8px}
            .order-summary{padding:15px}
            .buttons{flex-direction:column}
            .buttons button{width:100%}
        }
        @media(max-width:576px){
            #products-list{grid-template-columns:1fr}
            .product-item{height:auto;min-height:90px}
            .quantity-input,.amount-input,.discount-input{width:100%!important}
            .table-responsive{overflow-x:auto}
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?> - Receipt ID: <span id="receipt_display"><?php echo htmlspecialchars($receipt_id); ?></span></h2>
    <div class="row">
        <div class="col-md-6">
            <h4 class="mb-3"><i class="fas fa-boxes"></i> Products</h4>
            <div class="search-container">
                <input type="text" id="product-search" class="form-control" placeholder="Search by product name...">
                <button type="button" id="clear-search" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</button>
            </div>
            <div id="products-container">
                <div id="products-list">
                    <?php foreach ($all_products as $product):
                        $stock_class = $product['current_stock'] <= 0 ? 'disabled' : '';
                        $stock_status = $product['current_stock'] <= 5 ? 'low' : 'normal';
                    ?>
                    <div class="product-item <?php echo $stock_class; ?>"
                         data-product-id="<?php echo $product['id']; ?>"
                         data-brand-name="<?php echo htmlspecialchars($product['brandname']); ?>"
                         data-product-price="<?php echo $product['price']; ?>"
                         data-product-stock="<?php echo $product['current_stock']; ?>">
                        <h6><?php echo htmlspecialchars($product['brandname']); ?></h6>
                        <?php if (!empty($product['productname'])): ?>
                            <small><?php echo htmlspecialchars($product['productname']); ?></small>
                        <?php endif; ?>
                        <p>KES <?php echo number_format($product['price'], 2); ?></p>
                        <small>Stock: <?php echo $product['current_stock']; ?> units
                            <span class="stock-badge <?php echo $stock_status; ?>"><?php echo $stock_status == 'low' ? 'Low Stock' : 'In Stock'; ?></span>
                        </small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="order-summary">
                <h4 class="mb-3"><i class="fas fa-shopping-cart"></i> Order Summary</h4>
                <form id="order-form" method="post">
                    <input type="hidden" name="receipt_id" id="receipt_id" value="<?php echo htmlspecialchars($draft['receipt_id'] ?? $receipt_id); ?>">
                    <input type="hidden" name="draft_id" id="draft_id" value="<?php echo htmlspecialchars($draft['draft_id'] ?? ''); ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <option value="Cash" <?php echo ($draft['payment_method'] ?? '') === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                <option value="Mpesa" <?php echo ($draft['payment_method'] ?? '') === 'Mpesa' ? 'selected' : ''; ?>>Mpesa</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-control" id="payment_status" name="payment_status">
                                <option value="Pending" <?php echo ($draft['payment_status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Paid" <?php echo ($draft['payment_status'] ?? '') === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Disc %</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="order-items"></tbody>
                        </table>
                    </div>

                    <div class="total-section">
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Total Amount:</strong></span>
                            <span>KES <span id="total-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Tax (1.5%):</strong></span>
                            <span>KES <span id="tax-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Discount Amount:</strong></span>
                            <span>KES <span id="discount-amount-display">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Grand Total:</strong></span>
                            <span class="text-primary fw-bold">KES <span id="grand-total">0.00</span></span>
                        </div>
                        <div class="form-group mb-3">
                            <label for="tendered-amount" class="form-label">Tendered Amount</label>
                            <input type="number" class="form-control" id="tendered-amount" name="tendered_amount" value="<?php echo htmlspecialchars($draft['tendered_amount'] ?? '0.00'); ?>" step="1" min="0">
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Change:</strong></span>
                            <span class="text-success fw-bold">KES <span id="change-amount">0.00</span></span>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn btn-outline-primary" id="save-draft">
                            <i class="fas fa-save"></i> Send to Cashier
                        </button>
                        <button type="button" class="btn btn-primary" id="submit-order">
                            <i class="fas fa-check-circle"></i> Check Out
                        </button>
                        <button type="button" class="btn btn-info" id="print-receipt">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    // Expose PHP session data to JavaScript
    const userRole = "<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>";
    const receiptId = "<?php echo htmlspecialchars($receipt_id); ?>";
    const draftItems = <?php echo json_encode($items); ?>;

    $(document).ready(function() {
        let orderItems = draftItems.length > 0 ? draftItems.map(item => ({
            id: item.id,
            brandname: item.brandname,
            quantity: parseFloat(item.quantity),
            price: parseFloat(item.price),
            discount: parseFloat(item.discount || 0),
            total_amount: parseFloat(item.total_amount),
            tax_amount: parseFloat(item.tax_amount),
            grand_total: parseFloat(item.grand_total)
        })) : [];

        // Search functionality
        $('#product-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            if (searchTerm === '') {
                $('.product-item').show();
            } else {
                $('.product-item').each(function() {
                    const productName = $(this).find('h6').text().toLowerCase();
                    const description = $(this).find('small').first().text().toLowerCase();
                    if (productName.includes(searchTerm) || description.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        $('#clear-search').click(function() {
            $('#product-search').val('');
            $('.product-item').show();
        });

        // Product click handler
        $('.product-item:not(.disabled)').click(function() {
            const productId = $(this).data('product-id');
            const brandName = $(this).data('brand-name');
            const productPrice = parseFloat($(this).data('product-price'));
            const stockAvailable = parseFloat($(this).data('product-stock'));

            const existingItem = orderItems.find(item => item.id === productId);

            if (existingItem) {
                const newQuantity = existingItem.quantity + 1;
                if (newQuantity <= stockAvailable) {
                    existingItem.quantity = newQuantity;
                } else {
                    alert('Insufficient stock! Available: ' + stockAvailable);
                    return;
                }
            } else {
                if (1 <= stockAvailable) {
                    orderItems.push({
                        id: productId,
                        brandname: brandName,
                        quantity: 1,
                        price: productPrice,
                        discount: 0
                    });
                } else {
                    alert('Out of stock!');
                    return;
                }
            }
            updateOrderTable();
        });

        // Quantity input handling
        $('#order-items').on('input', '.quantity-input', function() {
            const row = $(this).closest('tr');
            const productId = row.data('id');
            const item = orderItems.find(item => item.id === productId);
            const stockAvailable = $('.product-item[data-product-id="' + productId + '"]').data('product-stock');

            if (item) {
                let newQuantity = parseFloat($(this).val()) || 0;
                if (newQuantity < 0) newQuantity = 0;

                if (newQuantity > stockAvailable) {
                    alert('Insufficient stock! Available: ' + stockAvailable);
                    $(this).val(item.quantity);
                    return;
                }

                item.quantity = newQuantity;
                calculateItemTotals(item, row);
            }
        });

        // Amount input handling (Enter total amount to calculate quantity)
        $('#order-items').on('input', '.amount-input', function() {
            const row = $(this).closest('tr');
            const productId = row.data('id');
            const item = orderItems.find(item => item.id === productId);
            const stockAvailable = $('.product-item[data-product-id="' + productId + '"]').data('product-stock');

            if (item && item.price > 0) {
                let totalAmount = parseFloat($(this).val()) || 0;
                if (totalAmount < 0) totalAmount = 0;

                // Calculate quantity based on total amount and price
                let calculatedQuantity = totalAmount / item.price;
                // Round to 2 decimal places for quantity
                calculatedQuantity = Math.round(calculatedQuantity * 100) / 100;

                if (calculatedQuantity > stockAvailable) {
                    alert('Insufficient stock! Available: ' + stockAvailable);
                    $(this).val((item.quantity * item.price).toFixed(2));
                    return;
                }

                if (calculatedQuantity > 0) {
                    item.quantity = calculatedQuantity;
                    row.find('.quantity-input').val(calculatedQuantity.toFixed(2));
                    calculateItemTotals(item, row);
                }
            }
        });

        // Discount handling
        $('#order-items').on('input', '.discount-input', function() {
            const row = $(this).closest('tr');
            const productId = row.data('id');
            const item = orderItems.find(item => item.id === productId);

            if (item) {
                const discountPercent = parseFloat($(this).val()) || 0;

                if (userRole !== 'Admin' && userRole !== 'Manager' && discountPercent > 10) {
                    alert('You are not allowed to give more than 10% discount.');
                    $(this).val(item.discount);
                    $(this).addClass('discount-error');
                    return;
                }

                $(this).removeClass('discount-error');
                item.discount = discountPercent;
                calculateItemTotals(item, row);
            }
        });

        function calculateItemTotals(item, row) {
            const totalForItem = item.quantity * item.price;
            const discountAmount = totalForItem * (item.discount / 100);
            const grandTotalForItem = totalForItem - discountAmount;
            const taxForItem = grandTotalForItem * 0.015;

            item.total_amount = totalForItem;
            item.tax_amount = taxForItem;
            item.grand_total = grandTotalForItem;

            row.find('td').eq(4).text(grandTotalForItem.toFixed(2)); // Update total column
            row.find('.amount-input').val(totalForItem.toFixed(2)); // Update amount input
            updateTotals();
        }

        $('#order-items').on('click', '.remove-item', function() {
            const productId = $(this).data('id');
            orderItems = orderItems.filter(item => item.id !== productId);
            updateOrderTable();
        });

        function updateOrderTable() {
            let html = '';
            orderItems.forEach((item, index) => {
                const totalForItem = item.quantity * item.price;
                const discountAmount = totalForItem * (item.discount / 100);
                const grandTotalForItem = totalForItem - discountAmount;
                const taxForItem = grandTotalForItem * 0.015;

                item.total_amount = totalForItem;
                item.tax_amount = taxForItem;
                item.grand_total = grandTotalForItem;

                html += `
                    <tr data-id="${item.id}">
                        <td>${index + 1}</td>
                        <td>${item.brandname}</td>
                        <td>
                            <input type="number" class="form-control quantity-input" value="${item.quantity.toFixed(2)}" step="0.01" min="0" style="width:70px">
                        </td>
                        <td>${parseFloat(item.price).toFixed(2)}</td>
                        <td>
                            <input type="number" class="form-control amount-input" value="${totalForItem.toFixed(2)}" step="0.01" min="0" style="width:90px">
                        </td>
                        <td>
                            <input type="number" class="form-control discount-input" value="${item.discount.toFixed(1)}" step="0.1" min="0" max="100" style="width:60px">
                        </td>
                        <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button></td>
                    </tr>
                `;
            });

            $('#order-items').html(html);
            updateTotals();
        }

        function updateTotals() {
            let totalAmount = 0;
            let totalTax = 0;
            let totalDiscountAmount = 0;

            orderItems.forEach(item => {
                totalAmount += item.total_amount;
                totalTax += item.tax_amount;
                totalDiscountAmount += item.total_amount * (item.discount / 100);
            });

            $('#total-amount').text(totalAmount.toFixed(2));
            $('#tax-amount').text(totalTax.toFixed(2));
            $('#discount-amount-display').text(totalDiscountAmount.toFixed(2));
            $('#grand-total').text((totalAmount - totalDiscountAmount).toFixed(2));
            updateChange();
        }

        function updateChange() {
            const grandTotal = parseFloat($('#grand-total').text()) || 0;
            const tendered = parseFloat($('#tendered-amount').val()) || 0;
            const change = tendered - grandTotal;

            $('#change-amount').text(change.toFixed(2));
            if (tendered >= grandTotal) {
                $('#payment_status').val('Paid');
            }
        }

        $('#tendered-amount').on('input', updateChange);

        $('#save-draft').click(function() {
            if (orderItems.length === 0) {
                alert('Please add items to the order.');
                return;
            }

            const data = {
                receipt_id: receiptId,
                payment_method: $('#payment_method').val(),
                payment_status: $('#payment_status').val(),
                tendered_amount: $('#tendered-amount').val() || '0',
                username: "<?php echo htmlspecialchars($_SESSION['full_name']); ?>",
                items: orderItems
            };

            $.ajax({
                url: 'add_to_draft.php',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const successMessage = $('<span>').text('Draft saved successfully.').addClass('success-message');
                        $('#order-form').prepend(successMessage);
                        setTimeout(() => successMessage.fadeOut(), 2000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Error saving draft.');
                }
            });
        });

        $('#submit-order').click(function() {
            if (orderItems.length === 0) {
                alert('Please add items to the order.');
                return;
            }

            // Validate tendered amount
            const grandTotal = parseFloat($('#grand-total').text());
            const tendered = parseFloat($('#tendered-amount').val()) || 0;

            if ($('#payment_status').val() !== 'Credit' && tendered < grandTotal) {
                if (!confirm('Tendered amount is less than grand total. Continue anyway?')) {
                    return;
                }
            }

            const data = {
                receipt_id: receiptId,
                payment_method: $('#payment_method').val(),
                payment_status: $('#payment_status').val(),
                tendered_amount: $('#tendered-amount').val() || '0.00',
                items: orderItems,
                total_amount: $('#total-amount').text(),
                tax_amount: $('#tax-amount').text(),
                discount: $('#discount-amount-display').text(),
                grand_total: $('#grand-total').text(),
                username: "<?php echo htmlspecialchars($_SESSION['full_name']); ?>"
            };

            $.ajax({
                url: 'submit_order.php',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const successMessage = $('<span>').text('Sale completed successfully!').addClass('success-message');
                        $('#order-form').prepend(successMessage);

                        // Clear the cart
                        orderItems = [];
                        updateOrderTable();

                        // Generate new receipt ID
                        const newReceiptId = 'ORD' + new Date().toISOString().slice(0,10).replace(/-/g,"") + Math.floor(1000 + Math.random() * 9000);
                        $('#receipt_id').val(newReceiptId);
                        $('#receipt_display').text(newReceiptId);

                        // Reset form fields
                        $('#tendered-amount').val('');
                        $('#payment_status').val('Pending');

                        setTimeout(function() {
                            successMessage.fadeOut();
                        }, 3000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Error submitting order.');
                }
            });
        });

        // Print receipt function
        $('#print-receipt').click(function() {
            if (orderItems.length === 0) {
                alert('No items to print.');
                return;
            }

            const printWindow = window.open('', '_blank');

            // Format items table for thermal printer
            let itemsHtml = '';
            let counter = 1;
            orderItems.forEach(item => {
                const totalForItem = item.quantity * item.price;
                const discountAmount = totalForItem * (item.discount / 100);
                const itemTotal = totalForItem - discountAmount;

                const brandname = item.brandname.substring(0, 20);
                const quantity = item.quantity.toFixed(2).toString().padStart(6, ' ');
                const price = parseFloat(item.price).toFixed(2).padStart(8, ' ');
                const discount = parseFloat(item.discount).toFixed(1).padStart(5, ' ') + '%';
                const total = itemTotal.toFixed(2).padStart(10, ' ');

                itemsHtml += `<div style="font-family: monospace; font-size: 9px; line-height: 1.1; white-space: pre; border-bottom: 1px dashed #E0E0E0;">`;
                itemsHtml += `<span style="width: 3%; display: inline-block;">${counter}</span>`;
                itemsHtml += `<span style="width: 30%; display: inline-block;">${brandname}</span>`;
                itemsHtml += `<span style="width: 8%; display: inline-block; text-align: right;">${quantity}</span>`;
                itemsHtml += `<span style="width: 15%; display: inline-block; text-align: right;">${price}</span>`;
                itemsHtml += `<span style="width: 12%; display: inline-block; text-align: right;">${discount}</span>`;
                itemsHtml += `<span style="width: 20%; display: inline-block; text-align: right;">${total}</span>`;
                itemsHtml += `</div>`;
                counter++;
            });

            const totalAmount = parseFloat($('#total-amount').text());
            const taxAmount = parseFloat($('#tax-amount').text());
            const discountAmount = parseFloat($('#discount-amount-display').text());
            const grandTotal = parseFloat($('#grand-total').text());
            const tenderedAmount = parseFloat($('#tendered-amount').val()) || 0;
            const changeAmount = tenderedAmount - grandTotal;
            const paymentMethod = $('#payment_method').val();
            const paymentStatus = $('#payment_status').val();
            const cashier = "<?php echo htmlspecialchars($_SESSION['full_name']); ?>";
            const currentDate = new Date().toLocaleString();

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                    <style>
                        @page { size: 80mm auto; margin: 1mm 2mm; }
                        @media print { body { width: 80mm; font-family: 'Courier New', Courier, monospace; font-size: 10px; font-weight: bold; margin: 0; padding: 1mm 2mm; line-height: 1.1; color: #000; background: white; } }
                        body { width: 80mm; font-family: 'Courier New', Courier, monospace; font-size: 10px; font-weight: bold; margin: 0; padding: 1mm 2mm; line-height: 1.1; color: #000; background: white; }
                        .header { text-align: center; margin-bottom: 3px; border-bottom: 1px dashed #000; padding-bottom: 3px; }
                        .logo { text-align: center; margin: 2px 0; }
                        .logo img { max-width: 40px; height: auto; }
                        .company-name { font-weight: bold; font-size: 11px; margin: 1px 0; }
                        .slogan { font-style: italic; font-size: 8px; margin: 1px 0; }
                        .receipt-title { text-align: center; font-weight: bold; font-size: 11px; margin: 3px 0; text-transform: uppercase; }
                        .receipt-info { margin: 2px 0; font-size: 9px; }
                        .receipt-info p { margin: 1px 0; display: flex; justify-content: space-between; }
                        .receipt-info .label { font-weight: bold; }
                        .items-header { display: flex; font-weight: bold; border-bottom: 1px solid #000; margin: 3px 0 2px 0; padding-bottom: 1px; font-size: 9px; font-family: monospace; }
                        .items-header span { display: inline-block; }
                        .items-header .num { width: 3%; }
                        .items-header .product { width: 30%; }
                        .items-header .qty { width: 8%; text-align: right; }
                        .items-header .price { width: 15%; text-align: right; }
                        .items-header .discount { width: 12%; text-align: right; }
                        .items-header .total { width: 20%; text-align: right; }
                        .totals { border-top: 1px dashed #000; margin-top: 3px; padding-top: 3px; font-size: 10px; }
                        .total-row { display: flex; justify-content: space-between; margin: 1px 0; }
                        .total-row.total-final { font-weight: bold; border-top: 1px solid #000; padding-top: 2px; margin-top: 2px; }
                        .footer { text-align: center; margin-top: 5px; font-size: 8px; border-top: 1px dashed #000; padding-top: 3px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <div class='logo'><img src='../assets/images/Logo2-rb2.png' alt='Logo' style='max-width: 40px; height: auto;'></div>
                        <div class='company-name'>Katakala Butchery & Restaurant</div>
                        <div class='slogan'>Great Cuts and Bites</div>
                    </div>
                    <div class='receipt-title'>SALES RECEIPT</div>
                    <div class='receipt-info'>
                        <p><span class='label'>Receipt ID:</span> <span>${receiptId}</span></p>
                        <p><span class='label'>Date:</span> <span>${currentDate}</span></p>
                        <p><span class='label'>Cashier:</span> <span>${cashier}</span></p>
                        <p><span class='label'>Payment:</span> <span>${paymentMethod}</span></p>
                    </div>
                    <div class='items-header'>
                        <span class='num'>#</span>
                        <span class='product'>PRODUCT</span>
                        <span class='qty'>QTY</span>
                        <span class='price'>PRICE</span>
                        <span class='discount'>DISC</span>
                        <span class='total'>TOTAL</span>
                    </div>
                    <div class='items-list'>${itemsHtml}</div>
                    <div class='totals'>
                        <div class='total-row'><span>Subtotal:</span><span>KES ${totalAmount.toFixed(2)}</span></div>
                        <div class='total-row'><span>Tax:</span><span>KES ${taxAmount.toFixed(2)}</span></div>
                        <div class='total-row'><span>Discount:</span><span>KES ${discountAmount.toFixed(2)}</span></div>
                        <div class='total-row total-final'><span>GRAND TOTAL:</span><span>KES ${grandTotal.toFixed(2)}</span></div>
                        <div class='total-row'><span>Tendered:</span><span>KES ${tenderedAmount.toFixed(2)}</span></div>
                        <div class='total-row total-final'><span>CHANGE:</span><span>KES ${changeAmount.toFixed(2)}</span></div>
                        <div class='total-row'><span>Status:</span><span>${paymentStatus.toUpperCase()}</span></div>
                    </div>
                    <div class='footer'>
                        <div>Thank you for your business!</div>
                        <div>Katakalabutchery@gmail.com</div>
                        <div>0110 990598</div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        });

        // Initial setup
        updateOrderTable();
    });
</script>
</body>
</html>