<?php
include '../includes/config.php';
include '../includes/header.php';

// Fetch all suppliers
$suppliers = $conn->query("SELECT supplier_id, name FROM suppliers");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Place Purchase Order</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- for adding/removing rows -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        table {
            width: 70%;
            border-collapse: collapse;
        }
        table, th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background: #f0f0f0;
        }
        @media(max-width: 600px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            tr {
                margin-bottom: 10px;
            }
            th {
                background: #f4f4f4;
                font-weight: bold;
            }
        }    #productSearch{
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
    <div class="main-content">
    <h2>Place Purchase Order</h2>

    <!-- Select Supplier -->
    <form action="save_purchase_order.php" method="POST" id="purchaseForm">
        <div>
            <label for="supplier">Select Supplier:</label>
            <select name="supplier_id" id="supplier" required>
                <option value="">-- Select Supplier --</option>
                <?php while($row = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo $row['supplier_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <br>

        <!-- Purchase Items Table -->
        <input type="text" id="productSearch" placeholder="Search product..." autocomplete="off">
        <div id="suggestions" style="border:1px solid #ccc; display:none; max-height:150px; overflow-y:auto; position:absolute; background:#fff;"></div>

        <table id="orderTable" border="1">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <!-- Items will be added dynamically -->
            </tbody>
        </table>
        <br>

        <button type="button" id="addItem">Add Product</button>
        <br><br>

        <button type="submit" name="save">Save Order</button>
        <button type="button" id="printPDF">Print as PDF</button>
        <button type="button" id="generateInvoice"><a href="generate_invoice.php">Generate Invoice</a></button>

    </form>

<script>
let itemNumber = 1;

// Add a new item row
$('#addItem').click(function() {
    let newRow = `
    <tr>
        <td>${itemNumber}</td>
        <td>
            <input type="text" name="product_name[]" class="product-search" placeholder="Search product..." required>
            <input type="hidden" name="product_id[]">
        </td>
        <td><input type="number" name="quantity[]" min="1" required></td>
        <td><button type="button" class="removeItem">Remove</button></td>
    </tr>
    `;
    $('#itemsBody').append(newRow);
    itemNumber++;
});

// Remove item row
$(document).on('click', '.removeItem', function() {
    $(this).closest('tr').remove();
    itemNumber--;
});

// TODO: Add AJAX search for products
// TODO: Implement PDF generation
$(document).on('input', '.product-search', function() {
    let $input = $(this);
    let search = $input.val();

    $.get('search_products.php', { q: search }, function(data) {
        let products = JSON.parse(data);

        if (products.length > 0) {
            // You can build a dropdown or just auto-select first match for simplicity
            $input.val(products[0].name);
            $input.next('input[type="hidden"]').val(products[0].id);
        }
    });
});

$('#printPDF').click(function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.text("Your Company Name", 10, 10);
    doc.setFontSize(12);
    doc.text("Your Company Address", 10, 20);

    let y = 40;
    $('#itemsBody tr').each(function(index, tr) {
        let product = $(tr).find('input[name="product_name[]"]').val();
        let qty = $(tr).find('input[name="quantity[]"]').val();
        doc.text(`${index+1}. ${product} - Quantity: ${qty}`, 10, y);
        y += 10;
    });

    doc.save('purchase_order.pdf');
});

</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
        let counter = 1; // for numbering items

        $('#productSearch').keyup(function() {
                var query = $(this).val();
                if (query.length >= 2) {
                        $.ajax({
                                url: 'search_product.php',
                                method: 'POST',
                                data: {query:query},
                                success:function(data){
                                        $('#suggestions').fadeIn();
                                        $('#suggestions').html(data);
                                }
                        });
                } else {
                        $('#suggestions').fadeOut();
                }
        });

        // Click on suggestion
        $(document).on('click', '.suggestion-item', function(){
                var productId = $(this).data('id');
                var productName = $(this).text();
                var unitPrice = $(this).data('price');

                // Add to order table
                var row = '<tr>'+
                                        '<td>' + (counter++) + '</td>'+
                                        '<td>' + productName + '<input type="hidden" name="product_ids[]" value="'+productId+'"></td>'+
                                        '<td><input type="number" name="quantities[]" value="1" min="1" required></td>'+
                                        '<td><input type="text" name="unit_prices[]" value="'+unitPrice+'" required></td>'+
                                        '<td><button type="button" class="remove">Remove</button></td>'+
                                    '</tr>';
                $('#orderTable tbody').append(row);

                // Clear search
                $('#productSearch').val('');
                $('#suggestions').fadeOut();
        });

        // Remove item
        $(document).on('click', '.remove', function(){
                $(this).closest('tr').remove();
        });

});
</script>
</body>
</html>
