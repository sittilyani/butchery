<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../includes/config.php";
include "../includes/header.php";

$page_title = "Take Orders";

// Check for logged-in user
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit;
}

// Generate or use existing receipt ID
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
        $draft = $items[0]; // Use first row for defaults
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="../assets/css/bootstrap-grid.css" type="text/css">
    <style>
        .product-item {
            cursor: pointer;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #CC66FF;
            text-align: center;
            transition: all 0.3s ease;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .product-item:hover {
            background-color: #BB55EE;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .product-item.disabled {
            background-color: #f0f0f0;
            cursor: not-allowed;
            opacity: 0.6;
            color: #999;
        }
        .product-item h6 {
            font-size: 0.9rem;
            margin: 5px 0;
            font-weight: bold;
            color: white;
        }
        .product-item p {
            font-size: 0.8rem;
            margin: 2px 0;
            color: white;
        }
        .product-item.disabled h6,
        .product-item.disabled p {
            color: #999;
        }

        /* Product grid container */
        #products-container {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        #products-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            padding: 10px 0;
        }

        /* Order summary styling */
        .order-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            max-height: 80vh;
            overflow-y: auto;
        }

        #order-items tr td {
            vertical-align: middle;
            padding: 8px;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .main-content {
            padding: 20px;
        }

        .btn-remove {
            padding: 2px 8px;
            font-size: 0.8rem;
        }

        .total-section {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #products-list {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
            .product-item {
                height: 100px;
                padding: 10px;
            }
        }

        /* Success message styling */
        .success-message {
            background-color: #DDFCAF;
            color: green;
            font-size: 18px;
            padding: 5px 10px;
            margin-bottom: 10px;
            display: inline-block;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?> - Receipt ID: <?php echo htmlspecialchars($receipt_id); ?></h2>
    <div class="row">
        <!-- Products Grid Section -->
        <div class="col-md-7">
            <h4 class="mb-3">Products</h4>
            <div class="search-container">
                <input type="text" id="product-search" class="form-control" placeholder="Search by product name or barcode">
                <button type="button" id="clear-search" class="btn btn-secondary">Clear</button>
            </div>
            <div id="products-container">
                <div id="products-list"></div>
            </div>
        </div>

        <!-- Order Summary Section -->
        <div class="col-md-5">
            <div class="order-summary">
                <h4 class="mb-3">Order Summary</h4>
                <form id="order-form" method="post">
                    <input type="hidden" name="receipt_id" id="receipt_id" value="<?php echo htmlspecialchars($draft['receipt_id'] ?? $receipt_id); ?>">
                    <input type="hidden" name="draft_id" id="draft_id" value="<?php echo htmlspecialchars($draft['draft_id'] ?? ''); ?>">

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="Cash" <?php echo ($draft['payment_method'] ?? '') === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Mpesa" <?php echo ($draft['payment_method'] ?? '') === 'Mpesa' ? 'selected' : ''; ?>>Mpesa</option>
                            <option value="Credit" <?php echo ($draft['payment_method'] ?? '') === 'Credit' ? 'selected' : ''; ?>>Credit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select class="form-control" id="payment_status" name="payment_status">
                            <option value="Pending" <?php echo ($draft['payment_status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Paid" <?php echo ($draft['payment_status'] ?? '') === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    </div>

                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="order-items"></tbody>
                    </table>

                    <div class="total-section">
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Total Amount:</strong></span>
                            <span>KES <span id="total-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Tax (1.5%):</strong></span>
                            <span>KES <span id="tax-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Grand Total:</strong></span>
                            <span class="text-primary fw-bold">KES <span id="grand-total">0.00</span></span>
                        </div>

                        <div class="form-group mb-3">
                            <label for="tendered-amount" class="form-label">Tendered Amount</label>
                            <input type="number" class="form-control" id="tendered-amount" name="tendered_amount" value="<?php echo htmlspecialchars($draft['tendered_amount'] ?? '0.00'); ?>" step="0.01" min="0">
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Change:</strong></span>
                            <span class="text-success fw-bold">KES <span id="change-amount">0.00</span></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" id="save-draft">Save Draft</button>
                        <button type="button" class="btn btn-primary" id="submit-order">Submit Order</button>
                        <button type="button" class="btn btn-info" id="print-receipt">Print Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Client-side order items array
    let orderItems = [];

    // Load products
    function loadProducts(search = '') {
        $.ajax({
            url: 'fetch_products.php',
            method: 'GET',
            data: { search: search },
            success: function(response) {
                $('#products-list').html(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', { status, error, responseText: xhr.responseText });
                alert('Failed to load products.');
            }
        });
    }

    // Search input
    $('#product-search').on('input', function() {
        const search = $(this).val().trim();
        if (search.length >= 1) { // Start search after 1 character
            loadProducts(search);
        } else {
            $('#products-list').empty(); // Clear products when search is empty
        }
    });

    // Clear search
    $('#clear-search').click(function() {
        $('#product-search').val('');
        $('#products-list').empty();
    });

    // Add product to client-side order
    $('#products-list').on('click', '.product-item', function() {
        if ($(this).hasClass('disabled')) {
            alert('This product is out of stock and cannot be added.');
            return;
        }

        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name') || 'Unknown Product';
        const productPrice = parseFloat($(this).data('product-price')) || 0;

        // Check if product already exists
        const existingItem = orderItems.find(item => item.product_id === productId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            orderItems.push({
                product_id: productId,
                productname: productName,
                quantity: 1,
                price: productPrice,
                total_amount: productPrice,
                tax_amount: productPrice * 0.015,
                grand_total: productPrice
            });
        }

        updateOrderTable();
    });

    // Update quantity
    $('#order-items').on('change', '.quantity-input', function() {
        const productId = $(this).data('product-id');
        const quantity = parseInt($(this).val()) || 1;

        const item = orderItems.find(item => item.product_id === productId);
        if (item) {
            item.quantity = quantity;
            item.total_amount = item.price * quantity;
            item.tax_amount = item.total_amount * 0.015;
            item.grand_total = item.total_amount;
        }

        updateOrderTable();
    });

    // Remove item
    $('#order-items').on('click', '.remove-item', function() {
        const productId = $(this).data('product-id');
        orderItems = orderItems.filter(item => item.product_id !== productId);
        updateOrderTable();
    });

    // Update order table
    function updateOrderTable() {
        let html = '';
        orderItems.forEach((item, index) => {
            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.productname}</td>
                    <td><input type="number" class="form-control quantity-input" data-product-id="${item.product_id}" value="${item.quantity}" min="1"></td>
                    <td>${parseFloat(item.price).toFixed(2)}</td>
                    <td>${parseFloat(item.total_amount).toFixed(2)}</td>
                    <td><button class="btn btn-danger btn-sm remove-item" data-product-id="${item.product_id}">Remove</button></td>
                </tr>
            `;
        });
        $('#order-items').html(html);
        updateTotals();
    }

    // Update totals
    function updateTotals() {
        const total = orderItems.reduce((sum, item) => sum + parseFloat(item.total_amount), 0);
        const tax = orderItems.reduce((sum, item) => sum + parseFloat(item.tax_amount), 0);
        const grand = orderItems.reduce((sum, item) => sum + parseFloat(item.grand_total), 0);

        $('#total-amount').text(total.toFixed(2));
        $('#tax-amount').text(tax.toFixed(2));
        $('#grand-total').text(grand.toFixed(2));

        const tendered = parseFloat($('#tendered-amount').val()) || 0;
        const change = tendered - grand;

        $('#change-amount').text(change.toFixed(2));
        $('#payment_status').val(tendered >= grand ? 'Paid' : 'Pending');
    }

    // Save draft to database
    $('#save-draft').click(function() {
        if (orderItems.length === 0) {
            alert('Please add items to the order.');
            return;
        }

        const data = {
            receipt_id: $('#receipt_id').val(),
            payment_method: $('#payment_method').val(),
            payment_status: $('#payment_status').val(),
            tendered_amount: $('#tendered-amount').val() || '0.00',
            items: orderItems.map(item => ({
                productname: item.productname,
                quantity: item.quantity,
                price: item.price,
                total_amount: item.total_amount,
                tax_amount: item.tax_amount,
                grand_total: item.grand_total
            }))
        };
        console.log('Save Draft Data:', data);

        $.ajax({
            url: 'add_to_draft.php',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                console.log('Save Draft Response:', response);
                if (response.status === 'success') {
                    const successMessage = $('<span>')
                        .text('Draft saved successfully.')
                        .addClass('success-message');
                    $('#order-form').prepend(successMessage);
                    setTimeout(() => successMessage.fadeOut(), 2000);
                    // Clear client-side items after saving
                    orderItems = [];
                    updateOrderTable();
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

    // Update tendered amount
    $('#tendered-amount').on('input', function() {
        updateTotals();
    });

    // Submit order
    $('#submit-order').click(function() {
        if (orderItems.length > 0) {
            alert('Please save the draft before submitting the order.');
            return;
        }

        const receiptId = $('#receipt_id').val();
        $.ajax({
            url: 'fetch_draft_items.php',
            method: 'GET',
            data: { receipt_id: receiptId },
            dataType: 'json',
            success: function(items) {
                if (items.length === 0) return alert('Please add items to the order.');

                const data = {
                    receipt_id: $('#receipt_id').val(),
                    payment_method: $('#payment_method').val(),
                    payment_status: $('#payment_status').val(),
                    items: items.map(item => ({
                        productname: item.productname,
                        quantity: item.quantity,
                        price: item.price,
                        total: item.total_amount
                    })),
                    total_amount: $('#total-amount').text(),
                    tax_amount: $('#tax-amount').text(),
                    grand_total: $('#grand-total').text(),
                    tendered_amount: $('#tendered-amount').val() || '0.00'
                };
                console.log('Submit Order Data:', data);

                $.ajax({
                    url: 'submit_order.php',
                    method: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Submit Order Response:', response);
                        if (response.status === 'success') {
                            const successMessage = $('<span>')
                                .text(response.message)
                                .addClass('success-message');
                            $('#order-form').prepend(successMessage);
                            setTimeout(function() {
                                window.location.href = '../views/view_order.php';
                            }, 2000);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                        alert('Error submitting order.');
                    }
                });
            }
        });
    });

    // Print receipt
    $('#print-receipt').click(function() {
        const receiptId = $('#receipt_id').val();
        $.ajax({
            url: 'fetch_draft_items.php',
            method: 'GET',
            data: { receipt_id: receiptId },
            dataType: 'json',
            success: function(items) {
                if (items.length === 0 && orderItems.length === 0) return alert('No items to print.');

                const printItems = items.length > 0 ? items : orderItems;
                const printWindow = window.open('', '_blank');

                let itemsHtml = `
                    <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <tr><th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
                `;

                printItems.forEach((item, i) => {
                    itemsHtml += `
                        <tr>
                            <td style="padding:2px;border:1px solid #000;">${i + 1}</td>
                            <td style="padding:2px;border:1px solid #000;">${item.productname}</td>
                            <td style="padding:2px;border:1px solid #000;">${item.quantity}</td>
                            <td style="padding:2px;border:1px solid #000;">${parseFloat(item.price).toFixed(2)}</td>
                            <td style="padding:2px;border:1px solid #000;">${parseFloat(item.total_amount).toFixed(2)}</td>
                        </tr>`;
                });

                itemsHtml += '</table>';

                printWindow.document.write(`
                    <html><head><title>Receipt</title>
                    <style>
                        @media print {
                            @page {
                                size: 148mm auto;
                                margin: 5mm;
                                padding: 10px;
                            }
                        }
                        body {
                            width: 148mm;
                            font-family: Arial, sans-serif;
                            font-size: 12px;
                            padding: 10px;
                            margin: 10px;
                        }
                        .logo {
                            text-align: center;
                            margin-bottom: 10px;
                        }
                        h2 {
                            text-align: center;
                            margin: 5px 0;
                            font-size: 14px;
                        }
                        .receipt-info, .totals {
                            margin: 5px 0;
                        }
                        .receipt-info p, .totals p {
                            margin: 2px 0;
                        }
                        table, th, td {
                            border: 1px solid #000;
                            border-collapse: collapse;
                            text-align: left;
                        }
                    </style>
                    </head><body>
                    <h2>Order Receipt</h2>
                    <div><img src="../assets/images/DesCareLogo2.png" width="220" height="92.5" alt=""></div>
                    <div class="receipt-info">
                        <p><strong>Receipt ID:</strong> ${$('#receipt_id').val()}</p>
                        <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                        <p><strong>You were served by:</strong> Descare Cosmetics</p>
                        <p><strong>Payment Method:</strong> ${$('#payment_method').val()}</p>
                    </div>
                    ${itemsHtml}
                    <div class="totals">
                        <p>Total Amount: KES ${$('#total-amount').text()}</p>
                        <p>Tax (1.5%): KES ${$('#tax-amount').text()}</p>
                        <p>Grand Total: KES ${$('#grand-total').text()}</p>
                        <p>Tendered: KES ${$('#tendered-amount').val() || '0.00'}</p>
                        <p>Change: KES ${$('#change-amount').text()}</p>
                        <p>Payment Status: ${$('#payment_status').val()}</p>
                        <p><span style="font-weight: bold; font-size: 16px; color: blue;">Till Number: 0123456</span></p>
                        <p><span style="font-weight: bold; font-size: 16px; color: red;">Name: Till Number</span></p>
                        <p><span style="font-style: italic;">We Are You</span></p>
                        <p><div style="width: auto; background-color: #CC66FF; height: 30px;"></div></p>
                    </div>
                    </body></html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        });
    });

    // Initialize order table
    updateOrderTable();
});
</script>
</body>
</html>