<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../includes/config.php";

$page_title = "Direct Orders";

// Check for logged-in user and get full name
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../login/login.php?error=' . urlencode('Please login to access this page'));
    exit;
}

// Get full_name from session or database if not set
if (!isset($_SESSION['full_name']) || empty($_SESSION['full_name'])) {
    // Fetch from database
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    } else {
        $_SESSION['full_name'] = $_SESSION['username']; // Fallback to username
    }
    $stmt->close();
}

// Store full_name in a variable for easy access
$user_full_name = $_SESSION['full_name'];
$user_role = $_SESSION['userrole'] ?? 'User';


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
        .product-item{cursor:pointer;padding:10px;border:1px solid #ddd;background-color:#99ccff;transition:all .3s ease;height:120px;color:#000;font-size:18px;display:flex;flex-direction:column;justify-content:center}
        .product-item:hover{background-color:#CCFFCC;transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,.1)}
        .product-item.disabled{background-color:#f8d7da;cursor:not-allowed}
        .product-item h6{font-size:.9rem;margin:5px 0;font-weight:700;color:#000}
        .product-item p{font-size:18px;margin:2px 0;color:#000}
        .product-item.disabled h6,.product-item.disabled p{color:#f00}
        #products-container{max-height:70vh;padding-right:10px}
        #products-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px;padding:5px 0}
        .order-summary{background-color:#f8f9fa;padding:20px;border-radius:8px;border:1px solid #dee2e6;min-height:40vh;overflow-y:auto}
        #order-items tr td{vertical-align:middle;padding:8px}
        .quantity-input,.discount-input{width:70px;text-align:center}
        .discount-input{-moz-appearance:textfield}
        .discount-input::-webkit-outer-spin-button,.discount-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
        .search-container{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
        .main-content{padding:20px}
        .btn-remove{padding:2px 8px;font-size:.8rem}
        .total-section{background-color:#fff;padding:15px;border-radius:5px;margin:15px 0}
        .sales-mode-selector{margin-bottom:20px;text-align:center}
        .sales-mode-btn{margin:0 10px;padding:10px 20px;font-weight:700}
        .mode-active{background-color:#007bff;color:#fff}
        .success-message{background-color:#DDFCAF;color:green;font-size:18px;padding:5px 10px;margin-bottom:10px;display:inline-block;border-radius:4px}
        .out-of-stock{color:red}
        .discount-error{border-color:#dc3545!important;background-color:#f8d7da!important}
        .user-info{background-color:#f0f0f0;padding:10px 15px;border-radius:5px;margin-bottom:15px;text-align:right}
        .user-info strong{color:#000099}
        @media(max-width:1200px){#products-list{grid-template-columns:repeat(auto-fill,minmax(180px,1fr))}.main-content{padding:18px}}
        @media(max-width:992px){#products-list{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}.product-item{height:110px;padding:8px}.main-content{padding:15px}}
        @media(max-width:768px){#products-list{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}.product-item{height:100px;font-size:16px}.product-item h6{font-size:.85rem}.product-item p{font-size:16px}.order-summary{padding:15px;min-height:35vh}.search-container{gap:8px}.sales-mode-btn{margin:0 5px;padding:8px 15px;font-size:.9rem}.main-content{padding:12px}.user-info{text-align:center;font-size:14px}}
        @media(max-width:576px){#products-list{grid-template-columns:1fr;gap:8px}.product-item{height:auto;min-height:90px;padding:10px}.product-item h6{font-size:.8rem}.product-item p{font-size:14px}.order-summary{padding:12px;min-height:30vh}.quantity-input,.discount-input{width:60px;font-size:.85rem}.search-container{flex-direction:column;gap:8px}.sales-mode-btn{display:block;width:100%;margin:5px 0;padding:10px}.total-section{padding:12px}.success-message{font-size:16px}.main-content{padding:10px}}
        @media(max-width:400px){.product-item{font-size:14px;min-height:80px}.product-item h6{font-size:.75rem}.product-item p{font-size:13px}.quantity-input,.discount-input{width:50px;font-size:.8rem}.btn-remove{padding:2px 6px;font-size:.7rem}.sales-mode-btn{padding:8px;font-size:.85rem}}
        @media(max-height:600px)and (orientation:landscape){#products-container{max-height:60vh}.order-summary{min-height:50vh}.product-item{height:90px;padding:8px}.main-content{padding:10px}}
    </style>
</head>
<body>
<div class="main-content">
    <!-- User Information Display -->
    <div class="user-info">
        <i class="fas fa-user-circle"></i>
        <strong>Logged in as:</strong> <?php echo htmlspecialchars($user_full_name); ?>
        <span class="text-muted">(<?php echo htmlspecialchars($user_role); ?>)</span>
    </div>

    <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?> - Receipt ID: <?php echo htmlspecialchars($receipt_id); ?></h2>

    <!-- Sales Mode Selector -->
    <div class="sales-mode-selector">
        <button type="button" class="btn btn-outline-primary sales-mode-btn mode-active" data-mode="direct">
            <i class="fas fa-bolt"></i> Quick Sale
        </button>
        <button type="button" class="btn btn-outline-secondary sales-mode-btn" data-mode="draft">
            <i class="fas fa-save"></i> Save as Draft
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h4 class="mb-3">Products</h4>
            <div class="search-container">
                <input type="text" id="product-search" class="form-control" placeholder="Search by brand name or generic name">
                <button type="button" id="clear-search" class="btn btn-secondary">Clear</button>
            </div>
            <div id="products-container">
                <div id="products-list"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="order-summary">
                <h4 class="mb-3">Order Summary</h4>
                <form id="order-form" method="post">
                    <input type="hidden" name="receipt_id" id="receipt_id" value="<?php echo htmlspecialchars($draft['receipt_id'] ?? $receipt_id); ?>">
                    <input type="hidden" name="draft_id" id="draft_id" value="<?php echo htmlspecialchars($draft['draft_id'] ?? ''); ?>">
                    <input type="hidden" name="sales_mode" id="sales_mode" value="direct">

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="Cash">Cash</option>
                            <option value="Mpesa">Mpesa</option>
                            <!--<option value="Credit">Credit</option>-->
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
                                <th>Disc %</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="order-items"></tbody>
                    </table>
                    <div class="total-section">
                        <div class="d-flex justify-content-between mb-3">
                            <span><strong>Total Amount:</strong></span>
                            <span>KES <span id="total-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><strong>Tax(ToT) (1.5%):</strong></span>
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
                        <button type="button" class="btn btn-success" id="process-direct-sale">
                            <i class="fas fa-bolt"></i> Process Direct Sale
                        </button>
                        <button type="button" class="btn btn-primary" id="save-draft" style="display: none;">
                            <i class="fas fa-save"></i> Save Draft
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

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Expose PHP session data to JavaScript
    const userRole = "<?php echo htmlspecialchars($user_role); ?>";
    const userFullName = "<?php echo htmlspecialchars($user_full_name); ?>";
    const receiptId = "<?php echo htmlspecialchars($receipt_id); ?>";
    const draftItems = <?php echo json_encode($items); ?>;

    $(document).ready(function() {
        let orderItems = draftItems.length > 0 ? draftItems.map(item => ({
            id: item.id,
            brandname: item.brandname,
            quantity: parseInt(item.quantity),
            price: parseFloat(item.price),
            discount: parseFloat(item.discount || 0),
            total_amount: parseFloat(item.total_amount),
            tax_amount: parseFloat(item.tax_amount),
            grand_total: parseFloat(item.grand_total)
        })) : [];

        // Sales mode handling
        $('.sales-mode-btn').click(function() {
            const mode = $(this).data('mode');
            $('.sales-mode-btn').removeClass('mode-active');
            $(this).addClass('mode-active');
            $('#sales_mode').val(mode);

            if (mode === 'direct') {
                $('#process-direct-sale').show();
                $('#save-draft').hide();
            } else {
                $('#process-direct-sale').hide();
                $('#save-draft').show();
            }
        });

        function loadProducts(search = '') {
            $.ajax({
                url: 'fetch_products.php',
                method: 'POST',
                data: { search: search },
                success: function(response) {
                    $('#products-list').html(response);
                    attachProductClickHandlers();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { status, error, responseText: xhr.responseText });
                    alert('Failed to load products.');
                }
            });
        }

        function attachProductClickHandlers() {
            $('#products-list .product-item:not(.disabled)').off('click').on('click', function() {
                const productId = $(this).data('product-id');
                const brandName = $(this).data('brand-name') || 'Unknown Product';
                const productPrice = parseFloat($(this).data('product-price')) || 0;

                const existingItem = orderItems.find(item => item.id === productId);
                if (existingItem) {
                    existingItem.quantity += 1;
                } else {
                    orderItems.push({
                        id: productId,
                        brandname: brandName,
                        quantity: 1,
                        price: productPrice,
                        discount: 0
                    });
                }
                updateOrderTable();
            });
        }

        $('#product-search').on('input', function() {
            const search = $(this).val().trim();
            if (search.length >= 1) {
                loadProducts(search);
            } else {
                $('#products-list').empty();
            }
        });

        $('#clear-search').click(function() {
            $('#product-search').val('');
            $('#products-list').empty();
        });

        let quantityTimeout;
        $('#order-items').on('input', '.quantity-input', function() {
            clearTimeout(quantityTimeout);
            const $input = $(this);
            const row = $input.closest('tr');
            const productId = row.data('id');
            const item = orderItems.find(item => item.id === productId);

            if (item) {
                const newQuantity = parseInt($input.val()) || 1;
                if (newQuantity < 1) {
                    item.quantity = 1;
                    $input.val(1);
                } else {
                    item.quantity = newQuantity;
                }
                const totalForItem = item.quantity * item.price;
                const discountAmount = totalForItem * (item.discount / 100);
                const grandTotalForItem = totalForItem - discountAmount;
                const taxForItem = grandTotalForItem * 0.015;

                item.total_amount = totalForItem;
                item.tax_amount = taxForItem;
                item.grand_total = grandTotalForItem;

                row.find('td').eq(5).text(grandTotalForItem.toFixed(2));
                updateTotals();
            }

            quantityTimeout = setTimeout(() => {
                updateOrderTable();
            }, 500);
        });

        $('#order-items').on('input', '.discount-input', function() {
            const row = $(this).closest('tr');
            const productId = row.data('id');
            const item = orderItems.find(item => item.id === productId);

            if (item) {
                const discountPercent = parseFloat($(this).val()) || 0;

                if (userRole !== 'Admin' && userRole !== 'Manager' && discountPercent > 10) {
                    alert('Error: You are not allowed to give more than 10% discount on this item.');
                    $(this).val(item.discount);
                    $(this).addClass('discount-error');
                    return;
                }

                $(this).removeClass('discount-error');
                item.discount = discountPercent;

                const totalForItem = item.quantity * item.price;
                const discountAmount = totalForItem * (item.discount / 100);
                const grandTotalForItem = totalForItem - discountAmount;
                const taxForItem = grandTotalForItem * 0.015;

                item.total_amount = totalForItem;
                item.tax_amount = taxForItem;
                item.grand_total = grandTotalForItem;

                row.find('td').eq(5).text(grandTotalForItem.toFixed(2));
                updateTotals();
            }
        });

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
                        <td><input type="text" class="form-control quantity-input no-arrows" value="${item.quantity}" pattern="[0-9]*" inputmode="numeric"></td>
                        <td>${parseFloat(item.price).toFixed(2)}</td>
                        <td>
                            <input type="number" class="form-control discount-input" value="${item.discount.toFixed(2)}" step="0.1" min="0" max="100">
                        </td>
                        <td>${grandTotalForItem.toFixed(2)}</td>
                        <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
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
            $('#payment_status').val(tendered >= grandTotal ? 'Paid' : 'Pending');
        }

        $('#tendered-amount').on('input', function() {
            updateChange();
        });

        $('#process-direct-sale').click(function() {
            if (orderItems.length === 0) {
                alert('Please add items to the order.');
                return;
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
                transBy: userFullName,
                sales_mode: 'direct'
            };

            $.ajax({
                url: 'process_direct_sale.php',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const successMessage = $('<span>').text('Direct sale processed successfully!').addClass('success-message');
                        $('#order-form').prepend(successMessage);

                        orderItems = [];
                        updateOrderTable();

                        const newReceiptId = 'ORD' + new Date().toISOString().slice(0,10).replace(/-/g,"") + Math.floor(1000 + Math.random() * 9000);
                        $('#receipt_id').val(newReceiptId);
                        $('h2.text-center').text('<?php echo htmlspecialchars($page_title); ?> - Receipt ID: ' + newReceiptId);

                        setTimeout(function() {
                            successMessage.fadeOut();
                        }, 3000);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Error processing direct sale.');
                }
            });
        });

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
                transBy: userFullName,
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

        $('#print-receipt').click(function() {
            if (orderItems.length === 0) {
                alert('No items to print.');
                return;
            }

            const printWindow = window.open('', '_blank');
            let itemsHtml = `<table style="width:100%;border-collapse:collapse;font-size:12px;">
            <tr><th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Discount %</th><th>Total</th></tr>`;

            orderItems.forEach((item, i) => {
                const discountAmount = (item.quantity * item.price) * (item.discount / 100);
                const itemTotal = (item.quantity * item.price) - discountAmount;

                itemsHtml += `
                    <tr>
                        <td style="padding:2px;border:1px solid #000;">${i + 1}</td>
                        <td style="padding:2px;border:1px solid #000;">${item.brandname}</td>
                        <td style="padding:2px;border:1px solid #000;">${item.quantity}</td>
                        <td style="padding:2px;border:1px solid #000;">${parseFloat(item.price).toFixed(2)}</td>
                        <td style="padding:2px;border:1px solid #000;">${parseFloat(item.discount).toFixed(2)}%</td>
                        <td style="padding:2px;border:1px solid #000;">${itemTotal.toFixed(2)}</td>
                    </tr>`;
            });
            itemsHtml += '</table>';

            printWindow.document.write(`
                <html><head><title>Receipt</title><style>
                    @media print {
                        @page { size: 80mm auto; margin: 2mm; padding: 2px; }
                    }
                    body { width: 84mm; font-family: "Times New Roman", Times, serif; font-size: 10px; padding: 5px; margin: 5px; }
                    .logo { text-align: center; margin-bottom: 10px; }
                    h2 { text-align: center; margin: 5px 0; font-size: 12px; }
                    .receipt-info, .totals { margin: 5px 0; }
                    .receipt-info p, .totals p { margin: 2px 0; }
                    table, th, td { border: 1px solid #000; border-collapse: collapse; text-align: left; }
                </style></head><body>
                <div class="logo">
                     <img src="../assets/images/Logo-round-nobg-2.png" width="100" height="98" alt="Logo">
                </div>
                <h2>Order Receipt</h2>
                <div class="receipt-info">
                    <p><strong>Receipt ID:</strong> ${receiptId}</p>
                    <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                    <p><strong>You were served by:</strong> ${userFullName}</p>
                    <p><strong>Payment Method:</strong> ${$('#payment_method').val()}</p>
                </div>
                ${itemsHtml}
                <div class="totals">
                    <p>Total Amount: KES ${$('#total-amount').text()}</p>
                    <p>Tax (1.5%): KES ${$('#tax-amount').text()}</p>
                    <p>Discount: KES ${$('#discount-amount-display').text()}</p>
                    <p>Grand Total: KES ${$('#grand-total').text()}</p>
                    <p>Tendered: KES ${$('#tendered-amount').val() || '0.00'}</p>
                    <p>Change: KES ${$('#change-amount').text()}</p>
                    <p>Payment Status: ${$('#payment_status').val()}</p>
                    <p><span style="font-style: italic;">Human medicines & supplies</span></p>
                    <p><div style="width: auto; background-color: #CC66FF; height: 30px;"></div></p>
                </div>
                </body></html>
            `);
            printWindow.document.close();
            printWindow.print();
        });

        // Initial setup
        updateOrderTable();
        loadProducts();
    });
</script>
</body>
</html>
