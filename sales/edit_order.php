<?php
// Start output buffering
ob_start();

// Ensure the session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../includes/config.php";
include "../includes/header.php";

$page_title = "Edit Draft Order";
$error_message = null;

// Check for receipt_id
if (!isset($_GET['receipt_id'])) {
    $error_message = "Receipt ID missing.";
}

$receipt_id = $_GET['receipt_id'] ?? null;

if (!$error_message) {
    // Check if order is already paid
    $is_paid = false;
    $stmt = $conn->prepare("SELECT payment_status FROM sales WHERE receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $is_paid = $row['payment_status'] === 'Paid';
    }
    $stmt->close();

    // Fetch draft items
    $items = [];
    $stmt = $conn->prepare("
        SELECT draft_id, brandname, quantity, price, discount, total_amount, tax_amount, grand_total,
               payment_method, payment_status, tendered_amount
        FROM sales_drafts WHERE receipt_id = ?
    ");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();

    $draft = $items[0] ?? [
        'receipt_id' => $receipt_id,
        'payment_method' => 'Cash',
        'payment_status' => 'Pending',
        'tendered_amount' => '0.00',
        'discount' => 0.00
    ];

    // Fetch stock items for display
    $sql = "
        SELECT s1.id, s1.brandname, COALESCE(p.price, 0.00) AS price, s1.stockBalance as newBalance, s1.expiryDate, s1.status
        FROM stocks s1
        INNER JOIN (
            SELECT brandname, MAX(transDate) AS maxTransDate, MAX(id) AS maxId
            FROM stocks
            GROUP BY brandname
        ) s2 ON s1.brandname = s2.brandname AND s1.transDate = s2.maxTransDate AND s1.id = s2.maxId
        LEFT JOIN products p ON s1.brandname = p.brandname
        WHERE p.currentstatus = 'Active'
    ";

    $result = $conn->query($sql);
    if (!$result) {
        $error_message = "Error fetching stocks: " . $conn->error;
        $stocks = [];
    } else {
        $stocks = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        .container-fluid {
            width: 100%;
            margin-top: 70px;
        }
        .product-row {
            cursor: pointer;
        }
        .product-row:hover {
            background-color: #f8f9fa;
        }
        .product-row.invalid {
            background-color: #ffe6e6;
            cursor: not-allowed;
        }
        #order-items tr td {
            vertical-align: middle;
        }
        .quantity-input, .discount-input, .price-input {
            width: 70px;
            text-align: center;
        }
        #credit-form {
            display: none;
            margin-top: 20px;
            background-color: #f8f9fa;
            border: 2px solid #ffc107;
        }
        #search-input {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 6px 10px;
            font-size: 14px;
            text-align: left;
        }
        .hidden {
            display: none;
        }
        .success-message {
            background-color: #DDFCAF;
            color: green;
            font-size: 18px;
            padding: 5px 10px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            font-size: 16px;
            padding: 10px;
            margin-bottom: 10px;
            display: none;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        .price-input {
            background-color: #fffde7;
            font-weight: bold;
        }
        .credit-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="loading-overlay" id="loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Processing...</span>
    </div>
</div>

<div class="main-content">
    <div>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
    </div>
    <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?> - Receipt ID: <?php echo htmlspecialchars($receipt_id); ?></h2>
    <?php
    if ($error_message) {
        echo '<div class="alert alert-danger" id="php-error-alert">' . htmlspecialchars($error_message) . '</div>';
    } else if ($is_paid) {
        echo '<div class="alert alert-danger">This order is already paid and cannot be edited.</div>';
    }
    ?>

    <?php if (!$error_message && !$is_paid): ?>
    <div class="error-message" id="error-message"></div>
    <div class="row">
        <div class="col-md-8">
            <h4>Products</h4>
            <input type="text" id="search-input" class="form-control" placeholder="Type the name of the product to retrieve...">
            <p>Products in stocks</p>
            <table id="stocks-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>SOH</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $index => $stock): ?>
                        <?php
                        $isValidExpiry = empty($stock['expiryDate']) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $stock['expiryDate']);
                        ?>
                        <tr class="product-row <?php echo $index >= 10 ? 'hidden' : ''; ?> <?php echo !$isValidExpiry ? 'invalid' : ''; ?>"
                            data-product-id="<?php echo htmlspecialchars($stock['id']); ?>"
                            data-brand-name="<?php echo htmlspecialchars($stock['brandname']); ?>"
                            data-product-price="<?php echo htmlspecialchars((float)$stock['price']); ?>"
                            data-stock-balance="<?php echo htmlspecialchars($stock['newBalance']); ?>"
                            data-expiry-date="<?php echo htmlspecialchars($stock['expiryDate'] ?: 'NA'); ?>">
                            <td data-label="Product ID"><?php echo htmlspecialchars($stock['id']); ?></td>
                            <td data-label="Product Name"><?php echo htmlspecialchars($stock['brandname']); ?> (KES <?php echo htmlspecialchars(number_format((float)$stock['price'], 2)); ?>)</td>
                            <td data-label="Stock Balance"><?php echo htmlspecialchars($stock['newBalance']); ?></td>
                            <td data-label="Expiry Date"><?php echo htmlspecialchars($stock['expiryDate'] ?: 'N/A'); ?></td>
                            <td data-label="Status"><?php echo htmlspecialchars($stock['status'] ?: 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-4">
            <h4>Order Summary</h4>
            <form id="order-form" method="post">
                <input type="hidden" name="receipt_id" id="receipt_id" value="<?php echo htmlspecialchars($draft['receipt_id'] ?? $receipt_id); ?>">
                <input type="hidden" id="user-role" value="<?php echo htmlspecialchars($user_role ?? ''); ?>">

                <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-control" id="payment_method" name="payment_method">
                        <option value="Cash" <?php echo $draft['payment_method'] === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="Mpesa" <?php echo $draft['payment_method'] === 'Mpesa' ? 'selected' : ''; ?>>Mpesa</option>
                        <option value="Credit" <?php echo $draft['payment_method'] === 'Credit' ? 'selected' : ''; ?>>Credit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="payment_status" class="form-label">Payment Status</label>
                    <select class="form-control" id="payment_status" name="payment_status" disabled>
                        <option value="Pending" <?php echo $draft['payment_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo $draft['payment_status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Discount %</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="order-items"></tbody>
                </table>

                <div class="mb-3">
                    <p><strong>Total Amount:</strong> KES <span id="total-amount">0.00</span></p>
                    <p><strong>Tax (1.5%):</strong> KES <span id="tax-amount">0.00</span></p>
                    <p><strong>Total Discount:</strong> KES <span id="total-discount">0.00</span></p>
                    <p><strong>Grand Total:</strong> KES <span id="grand-total">0.00</span></p>
                    <div class="form-group">
                        <label for="tendered-amount" class="form-label">Tendered Amount</label>
                        <input type="number" class="form-control" id="tendered-amount" name="tendered_amount" value="<?php echo htmlspecialchars($draft['tendered_amount']); ?>" step="0.01" min="0">
                    </div>
                    <p><strong>Change:</strong> KES <span id="change-amount">0.00</span></p>
                </div>

                <div id="credit-form" class="card p-3">
                    <h5 class="text-warning">?? Credit Sale</h5>
                    <div class="credit-warning">
                        <strong>Balance Due:</strong> KES <span id="balance-display">0.00</span>
                    </div>
                    <div class="mb-3">
                        <label for="customer-name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer-name" name="customer_name">
                    </div>
                    <div class="mb-3">
                        <label for="customer-phone" class="form-label">Customer Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer-phone" name="customer_phone" placeholder="e.g., 0712345678">
                    </div>
                    <div class="mb-3">
                        <label for="balance-amount" class="form-label">Balance Amount (Unpaid)</label>
                        <input type="number" class="form-control" id="balance-amount" name="balance_amount" readonly>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" id="save-credit">Complete Credit Sale</button>
                        <button type="button" class="btn btn-secondary" id="cancel-credit">Cancel</button>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="mark-paid">Check Out</button>
                    <button type="button" class="btn btn-info" id="print-receipt">Print Receipt</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
try {
    $(document).ready(function() {
        let orderItems = <?php echo json_encode($items); ?>.map(item => ({
            draft_id: item.draft_id || 0,
            brandname: item.brandname || 'Unknown Product',
            quantity: parseInt(item.quantity) || 1,
            price: parseFloat(item.price) || 0,
            discount: parseFloat(item.discount) || 0,
            total_amount: parseFloat(item.total_amount) || 0,
            tax_amount: parseFloat(item.tax_amount) || 0,
            grand_total: parseFloat(item.grand_total) || 0
        }));

        const userRole = $('#user-role').val();
        const maxDiscountPercent = (userRole === 'Admin' || userRole === 'Supervisor') ? 0.15 : 0.10;

        // Function to validate date format
        function isValidDate(date) {
            if (!date || date === 'N/A' || date === '') return true;
            return /^\d{4}-\d{2}-\d{2}$/.test(date) && !isNaN(new Date(date).getTime());
        }

        // Show error message
        function showError(message) {
            $('#error-message').text(message).show();
            setTimeout(() => $('#error-message').hide(), 5000);
        }

        // Show/hide loading overlay
        function setLoading(show) {
            $('#loading-overlay').toggle(show);
        }

        // Search products
        $('#search-input').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const rows = $('#stocks-table tbody tr');
            let visibleCount = 0;

            rows.each(function() {
                const brandname = $(this).find('td[data-label="Product Name"]').text().toLowerCase();
                if (brandname.includes(searchTerm) && visibleCount < 10) {
                    $(this).removeClass('hidden');
                    visibleCount++;
                } else {
                    $(this).addClass('hidden');
                }
            });
        });

        // Add product to basket
        $('#stocks-table').on('click', '.product-row', function() {
            if (<?php echo json_encode($is_paid); ?>) {
                showError('This order is already paid and cannot be edited.');
                return;
            }

            if ($(this).hasClass('invalid')) {
                showError('This product has an invalid expiry date (' + $(this).data('expiry-date') + '). Please update the stock record.');
                return;
            }

            const productId = $(this).data('product-id');
            const brandname = $(this).data('brand-name') || 'Unknown Product';
            const rawPrice = $(this).data('product-price');
            const productPrice = parseFloat(rawPrice) || 0;
            const stockBalance = parseInt($(this).data('stock-balance')) || 0;
            const expiryDate = $(this).data('expiry-date');

            if (productPrice <= 0) {
                showError('Product price is invalid or zero (' + rawPrice + '). Please check the product data.');
                return;
            }

            if (stockBalance <= 0) {
                showError('This product is out of stock and cannot be added.');
                return;
            }

            if (!isValidDate(expiryDate)) {
                showError('Invalid expiry date (' + (expiryDate || 'N/A') + ') for ' + brandname + '. Please update the stock record.');
                return;
            }

            const existingItem = orderItems.find(item => item.brandname === brandname);
            if (existingItem) {
                if (existingItem.quantity + 1 > stockBalance) {
                    showError('Cannot add more of ' + brandname + '. Only ' + stockBalance + ' in stock.');
                    return;
                }
                existingItem.quantity++;
            } else {
                if (1 > stockBalance) {
                    showError('Cannot add ' + brandname + '. Only ' + stockBalance + ' in stock.');
                    return;
                }
                orderItems.push({
                    draft_id: 0,
                    brandname: brandname,
                    quantity: 1,
                    price: productPrice,
                    discount: 0,
                    total_amount: 0,
                    tax_amount: 0,
                    grand_total: 0
                });
            }
            updateOrderTable();
        });

        // Update quantity, discount, or price
        $('#order-items').on('change', '.quantity-input, .discount-input, .price-input', function() {
            if (<?php echo json_encode($is_paid); ?>) {
                showError('This order is already paid and cannot be edited.');
                return;
            }

            const draftId = $(this).data('draft-id');
            const item = orderItems.find(item => item.draft_id == draftId);
            if (!item) return;

            const quantity = parseInt($('.quantity-input[data-draft-id="' + draftId + '"]').val()) || 1;
            const discount = parseFloat($('.discount-input[data-draft-id="' + draftId + '"]').val()) || 0;
            const price = parseFloat($('.price-input[data-draft-id="' + draftId + '"]').val()) || 0;
            const maxDiscount = price * quantity * maxDiscountPercent;

            if (discount > maxDiscount) {
                showError(`Discount cannot exceed ${maxDiscountPercent * 100}% of total amount (KES ${maxDiscount.toFixed(2)}).`);
                $('.discount-input[data-draft-id="' + draftId + '"]').val(item.discount.toFixed(2));
                return;
            }

            // Validate stock balance
            const stockRow = $(`#stocks-table tr[data-brand-name="${item.brandname}"]`);
            const stockBalance = parseInt(stockRow.data('stock-balance')) || 0;

            if (quantity > stockBalance) {
                showError('Cannot set quantity to ' + quantity + '. Only ' + stockBalance + ' in stock for ' + item.brandname + '.');
                $('.quantity-input[data-draft-id="' + draftId + '"]').val(item.quantity);
                return;
            }

            item.quantity = quantity;
            item.discount = discount;
            item.price = price;
            item.total_amount = item.price * item.quantity;
            item.tax_amount = item.total_amount * 0.015;
            item.grand_total = item.total_amount - (item.total_amount * (item.discount / 100));

            updateOrderTable();
        });

        // Remove item
        $('#order-items').on('click', '.remove-item', function() {
            if (<?php echo json_encode($is_paid); ?>) {
                showError('This order is already paid and cannot be edited.');
                return;
            }

            const draftId = $(this).data('draft-id');
            orderItems = orderItems.filter(item => item.draft_id != draftId);
            if (draftId && parseInt(draftId) > 0) {
                $.ajax({
                    url: 'remove_draft_item.php',
                    method: 'POST',
                    data: JSON.stringify({ draft_id: draftId }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status !== 'success') {
                            showError('Error removing item: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        showError('Error removing item. Please try again.');
                    }
                });
            }
            updateOrderTable();
        });

        // Update order table
        function updateOrderTable() {
            let html = '';
            orderItems.forEach((item, index) => {
                item.total_amount = item.price * item.quantity;
                item.tax_amount = item.total_amount * 0.015;
                item.grand_total = item.total_amount - (item.total_amount * (item.discount / 100));

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.brandname}</td>
                        <td><input type="number" class="form-control quantity-input" data-draft-id="${item.draft_id}" value="${item.quantity}" min="1"></td>
                        <td><input type="number" class="form-control price-input" data-draft-id="${item.draft_id}" value="${parseFloat(item.price).toFixed(2)}" step="0.01" min="0"></td>
                        <td><input type="number" class="form-control discount-input" data-draft-id="${item.draft_id}" value="${parseFloat(item.discount).toFixed(2)}" step="0.01" min="0"></td>
                        <td>${parseFloat(item.grand_total).toFixed(2)}</td>
                        <td><button class="btn btn-danger btn-sm remove-item" data-draft-id="${item.draft_id}">Remove</button></td>
                    </tr>
                `;
            });
            $('#order-items').html(html);
            updateTotals();
        }

        // Update totals
        function updateTotals() {
            const total_amount = orderItems.reduce((sum, item) => sum + parseFloat(item.total_amount), 0);
            const tax_amount = orderItems.reduce((sum, item) => sum + parseFloat(item.tax_amount), 0);
            const total_discount = orderItems.reduce((sum, item) => sum + (item.total_amount * (item.discount / 100)), 0);
            const grand_total = total_amount - total_discount;

            $('#total-amount').text(total_amount.toFixed(2));
            $('#tax-amount').text(tax_amount.toFixed(2));
            $('#total-discount').text(total_discount.toFixed(2));
            $('#grand-total').text(grand_total.toFixed(2));

            const tendered = parseFloat($('#tendered-amount').val()) || 0;
            const change = tendered - grand_total;

            $('#change-amount').text(change >= 0 ? change.toFixed(2) : '0.00');

            // Update payment status based on tendered amount
            if (tendered >= grand_total && tendered > 0) {
                $('#payment_status').val('Paid');
                $('#credit-form').hide();
            } else if (tendered > 0 && tendered < grand_total) {
                $('#payment_status').val('Credit');
            } else {
                $('#payment_status').val('Pending');
            }

            // Show/hide credit form and update balance
            if (tendered > 0 && tendered < grand_total) {
                const balance = grand_total - tendered;
                $('#balance-amount').val(balance.toFixed(2));
                $('#balance-display').text(balance.toFixed(2));
                $('#credit-form').show();
            } else if (tendered === 0 && grand_total > 0) {
                $('#balance-amount').val(grand_total.toFixed(2));
                $('#balance-display').text(grand_total.toFixed(2));
            } else {
                $('#credit-form').hide();
            }
        }

        // Handle tendered amount input
        $('#tendered-amount').on('input', updateTotals);

        // Handle payment method change
        $('#payment_method').on('change', function() {
            const method = $(this).val();
            if (method === 'Credit') {
                const grand_total = parseFloat($('#grand-total').text()) || 0;
                $('#tendered-amount').val('0.00');
                $('#balance-amount').val(grand_total.toFixed(2));
                $('#balance-display').text(grand_total.toFixed(2));
                $('#credit-form').show();
                updateTotals();
            }
        });

        // Cancel credit
        $('#cancel-credit').click(function() {
            $('#credit-form').hide();
            $('#customer-name').val('');
            $('#customer-phone').val('');
            $('#balance-amount').val('0.00');
            $('#payment_method').val('Cash');
            updateTotals();
        });

        // Mark as Paid
        $('#mark-paid').click(function() {
            if (<?php echo json_encode($is_paid); ?>) {
                showError('This order is already paid.');
                return;
            }
            if (orderItems.length === 0) {
                showError('Please add items to the order.');
                return;
            }

            const tendered = parseFloat($('#tendered-amount').val()) || 0;
            const grand_total = parseFloat($('#grand-total').text()) || 0;
            const paymentMethod = $('#payment_method').val();

            // If payment method is Credit or tendered is 0
            if (paymentMethod === 'Credit' || tendered === 0) {
                if (!confirm('No payment received. Do you want to create a credit sale?')) {
                    return;
                }
                $('#balance-amount').val(grand_total.toFixed(2));
                $('#balance-display').text(grand_total.toFixed(2));
                $('#credit-form').show();
                return;
            }

            // If partial payment
            if (tendered < grand_total) {
                if (!confirm('Tendered amount (KES ' + tendered.toFixed(2) + ') is less than grand total (KES ' + grand_total.toFixed(2) + '). Continue with credit sale for the balance?')) {
                    return;
                }
                const balance = grand_total - tendered;
                $('#balance-amount').val(balance.toFixed(2));
                $('#balance-display').text(balance.toFixed(2));
                $('#credit-form').show();
                return;
            }

            // Full payment
            processPayment('full');
        });

        // Save credit balance
        $('#save-credit').click(function() {
            if (<?php echo json_encode($is_paid); ?>) {
                showError('This order is already paid and cannot be edited.');
                return;
            }

            if (orderItems.length === 0) {
                showError('Please add items to the order.');
                return;
            }

            const customerName = $('#customer-name').val().trim();
            const customerPhone = $('#customer-phone').val().trim();
            const balanceAmount = parseFloat($('#balance-amount').val()) || 0;

            if (!customerName) {
                showError('Please enter customer name.');
                $('#customer-name').focus();
                return;
            }

            if (!customerPhone) {
                showError('Please enter customer phone number.');
                $('#customer-phone').focus();
                return;
            }

            // Validate phone number format (basic validation)
            const phoneRegex = /^[0-9]{10,15}$/;
            if (!phoneRegex.test(customerPhone.replace(/[\s\-\+]/g, ''))) {
                showError('Please enter a valid phone number (10-15 digits).');
                $('#customer-phone').focus();
                return;
            }

            if (balanceAmount <= 0) {
                showError('Balance amount must be greater than 0.');
                return;
            }

            processPayment('credit', customerName, customerPhone);
        });

        // Process payment
        function processPayment(paymentType, customerName = '', customerPhone = '') {
            const grand_total = parseFloat($('#grand-total').text()) || 0;
            const tendered = parseFloat($('#tendered-amount').val()) || 0;

            // For credit sales, always set payment_status to 'Credit' and payment_method to 'Credit'
            const data = {
                receipt_id: $('#receipt_id').val(),
                payment_method: paymentType === 'credit' ? 'Credit' : $('#payment_method').val(),
                payment_status: paymentType === 'credit' ? 'Credit' : 'Paid',
                tendered_amount: tendered,
                total_amount: parseFloat($('#total-amount').text()),
                tax_amount: parseFloat($('#tax-amount').text()),
                total_discount: parseFloat($('#total-discount').text()),
                grand_total: grand_total,
                customer_name: customerName,
                customer_phone: customerPhone,
                balance_amount: paymentType === 'credit' ? parseFloat($('#balance-amount').val()) : 0,
                is_credit_sale: paymentType === 'credit',
                items: orderItems.map(item => ({
                    brandname: item.brandname,
                    quantity: item.quantity,
                    price: item.price,
                    discount: item.discount,
                    total_amount: item.total_amount,
                    tax_amount: item.tax_amount,
                    grand_total: item.grand_total
                }))
            };

            setLoading(true);

            $.ajax({
                url: 'process_payment.php',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    setLoading(false);
                    if (response.status === 'success') {
                        const successMessage = $('<div>')
                            .text(paymentType === 'credit' ? 'Credit sale processed successfully!' : 'Order processed successfully.')
                            .addClass('success-message');
                        $('#order-form').prepend(successMessage);

                        // Clear credit form
                        $('#customer-name').val('');
                        $('#customer-phone').val('');
                        $('#credit-form').hide();

                        setTimeout(function() {
                            window.location.href = response.redirect || '../sales/view_order.php';
                        }, 2000);
                    } else {
                        showError('Error processing payment: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    setLoading(false);
                    let errorMessage = 'An unexpected error occurred. Please try again.';
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        if (xhr.status === 404) {
                            errorMessage = 'The requested resource was not found.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error. Please check the server logs.';
                        } else {
                            errorMessage = xhr.responseText || errorMessage;
                        }
                    }
                    showError(errorMessage);
                }
            });
        }

        // Print receipt
        $('#print-receipt').click(function() {
            if (orderItems.length === 0) {
                showError('No items to print.');
                return;
            }

            const printWindow = window.open('', '_blank');

            let itemsHtml = `
                <table style="width:100%;border-collapse:collapse;font-size:7px;">
                <tr><th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Disc%</th><th>Total</th></tr>
            `;

            orderItems.forEach((item, i) => {
                itemsHtml += `
                    <tr>
                        <td style="padding:1px;border:1px solid #000;">${i + 1}</td>
                        <td style="padding:1px;border:1px solid #000;">${item.brandname}</td>
                        <td style="padding:1px;border:1px solid #000;">${item.quantity}</td>
                        <td style="padding:1px;border:1px solid #000;">${parseFloat(item.price).toFixed(2)}</td>
                        <td style="padding:1px;border:1px solid #000;">${parseFloat(item.discount).toFixed(2)}%</td>
                        <td style="padding:1px;border:1px solid #000;">${parseFloat(item.grand_total).toFixed(2)}</td>
                    </tr>`;
            });

            itemsHtml += '</table>';

            const paymentStatus = $('#payment_status').val();
            const customerName = $('#customer-name').val();
            const customerPhone = $('#customer-phone').val();
            const balanceAmount = $('#balance-amount').val();

            let creditInfo = '';
            if (paymentStatus === 'Credit' && customerName && customerPhone) {
                creditInfo = `
                    <div style="border-top: 1px dashed #000; padding-top: 3px; margin-top: 5px;">
                        <p><strong>CREDIT SALE</strong></p>
                        <p>Customer: ${customerName}</p>
                        <p>Phone: ${customerPhone}</p>
                        <p>Balance Due: KES ${balanceAmount}</p>
                    </div>
                `;
            }

            printWindow.document.write(`
                <html><head><title>Receipt</title>
                <style>
                    @media print {
                        @page {
                            size: 148mm auto;
                            margin: 2mm;
                        }
                    }
                    body {
                        width: auto;
                        font-family: "Courier New", monospace;
                        font-size: 8px;
                        padding: 2mm;
                        margin: 0;
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
                    }
                    th {
                        font-size: 6px;
                        padding: 1px;
                    }
                    .totals {
                        border-top: 1px dashed #000;
                        padding-top: 3px;
                    }
                </style>
                </head><body>
                <h2>Order Receipt</h2>
                <div class="logo">
                    <div class="logo">
                        <img src="../assets/images/Logo-round-nobg-2.png" width="100" height="98" alt="Logo">
                    </div>
                    <p><span class="company-name">Retail Pharma POS</span></p>
                    <p><span class="slogan">Human medicines & supplies</span></p>
                </div>
                <div class="receipt-info">
                    <p><strong>Receipt ID:</strong> ${$('#receipt_id').val()}</p>
                    <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                    <p><strong>Payment Method:</strong> ${$('#payment_method').val()}</p>
                </div>
                ${itemsHtml}
                <div class="totals">
                    <p>Total Amount: KES ${$('#total-amount').text()}</p>
                    <p>Tax (1.5%): KES ${$('#tax-amount').text()}</p>
                    <p>Total Discount: KES ${$('#total-discount').text()}</p>
                    <p>Grand Total: KES ${$('#grand-total').text()}</p>
                    <p>Tendered: KES ${$('#tendered-amount').val() || '0.00'}</p>
                    <p>Change: KES ${$('#change-amount').text()}</p>
                    <p>Payment Status: ${paymentStatus}</p>
                </div>
                ${creditInfo}
                </body></html>
            `);
            printWindow.document.close();
            printWindow.print();
        });

        // Initialize
        updateOrderTable();
        updateTotals();
        console.log('Navigation script initialized successfully');
    });
    } catch (e) {
        console.error('Error in navigation script:', e);
    }
</script>
</body>
</html>
<?php ob_end_flush(); ?>