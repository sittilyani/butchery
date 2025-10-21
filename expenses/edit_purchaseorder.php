<?php
include '../includes/config.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    die("Order ID is required.");
}
$purchaseOrderId = $_GET['id'];

// Fetch supplier and status
$sqlOrder = "SELECT * FROM purchase_orders WHERE id = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param('i', $purchaseOrderId);
$stmtOrder->execute();
$order = $stmtOrder->get_result()->fetch_assoc();

if (!$order) {
    die("Purchase order not found.");
}

// Fetch order items
$sqlItems = "SELECT poi.*, p.productname
             FROM purchase_order_items poi
             JOIN products p ON poi.product_id = p.id
             WHERE poi.purchase_order_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param('i', $purchaseOrderId);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Purchase Order</title>
</head>
<body>
    <h2>Edit Purchase Order</h2>

    <form action="update_purchaseorder.php" method="POST" id="purchaseForm">
        <input type="hidden" name="purchase_order_id" value="<?php echo $purchaseOrderId; ?>">

        <div>
            <label for="supplier">Supplier:</label>
            <select name="supplier_id" id="supplier" required>
                <?php
                $suppliers = $conn->query("SELECT supplier_id, name FROM suppliers");
                while ($supplier = $suppliers->fetch_assoc()) {
                    $selected = ($supplier['supplier_id'] == $order['supplier_id']) ? "selected" : "";
                    echo "<option value='{$supplier['supplier_id']}' $selected>{$supplier['name']}</option>";
                }
                ?>
            </select>
        </div>
        <br>
        <label>Status:</label>
        <select name="status" required>
            <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Approved" <?= $order['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
            <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select> <br>

        <!-- Purchase Items Table -->
        <table border="1" id="itemsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                <?php
                $counter = 1;
                while($item = $resultItems->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td>
                        <input type="text" name="product_name[]" value="<?php echo htmlspecialchars($item['name']); ?>" readonly>
                        <input type="hidden" name="product_id[]" value="<?php echo $item['product_id']; ?>">
                    </td>
                    <td><input type="text" name="unit_price[]" value="<?php echo $item['unit_price']; ?>" readonly></td>
                    <td><input type="number" name="quantity[]" value="<?php echo $item['quantity']; ?>" min="1"></td>
                    <td class="total_price"><?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                    <td><button type="button" class="removeItem">Remove</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>Grand Total: <span id="grandTotal"></span></h3>

        <button type="submit" name="update">Update Order</button>
    </form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function calculateTotal() {
    let grandTotal = 0;
    $('#itemsBody tr').each(function() {
        let qty = $(this).find('input[name="quantity[]"]').val();
        let price = $(this).find('input[name="unit_price[]"]').val();
        let total = qty * price;
        $(this).find('.total_price').text(total.toFixed(2));
        grandTotal += total;
    });
    $('#grandTotal').text(grandTotal.toFixed(2));
}

// Recalculate when quantity changes
$(document).on('input', 'input[name="quantity[]"]', calculateTotal);

// Initial calculation
calculateTotal();

// Remove row
$(document).on('click', '.removeItem', function() {
    $(this).closest('tr').remove();
    calculateTotal();
});
</script>

</body>
</html>
