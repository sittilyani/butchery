<?php
// edit_inventory_item.php
include '../includes/config.php';
include '../includes/header.php';
include '../includes/footer.php';

// Get item ID from URL
$item_id = isset($_GET['inventory_id']) ? $_GET['inventory_id'] : null;

// Fetch item details
$item = null;
if ($item_id) {
    $sql = "SELECT * FROM inventory_items WHERE inventory_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $item_id) {
    $inventory_name = trim($_POST["inventory_name"]);
    $unit_price = trim($_POST["unit_price"]);
    $selling_price = trim($_POST["selling_price"]);
    $reorder_level = trim($_POST["reorder_level"]);

    // File upload handling (optional, if you want to allow photo/file updates)
    $file_name = $_FILES['inventory_photo']['name'];
    if($file_name){
        $file_size = $_FILES['inventory_photo']['size'];
        $file_tmp = $_FILES['inventory_photo']['tmp_name'];
        $file_type = $_FILES['inventory_photo']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $extensions = array("jpeg", "jpg", "png", "pdf");
        if(in_array($file_ext, $extensions) === false){
            $errors[]="extension not allowed, please choose a JPEG, PNG, or PDF file.";
        }

        if($file_size > 480000){
            $errors[]='File size must be less than 480 KB';
        }

        if(empty($errors)){
            $file_destination = "../uploads/" . $file_name; // Create uploads folder in your root directory
            move_uploaded_file($file_tmp, $file_destination);
        } else {
            foreach($errors as $error){
                echo "<p style='color:red;'>".$error."</p>";
            }
        }
        $sql = "UPDATE inventory_items SET inventory_name = ?, unit_price = ?, selling_price = ?, reorder_level = ?, inventory_photo = ? WHERE inventory_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sddisi", $inventory_name, $unit_price, $selling_price, $reorder_level, $file_name, $item_id);

        }
    } else {
        $sql = "UPDATE inventory_items SET inventory_name = ?, unit_price = ?, selling_price = ?, reorder_level = ? WHERE inventory_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sddii", $inventory_name, $unit_price, $selling_price, $reorder_level, $item_id);
        }
    }

    if ($stmt->execute()) {
        echo "<script>alert('Item updated successfully.'); window.location.href='view_inventory_items.php';</script>";
    } else {
        echo "Something went wrong. Please try again later.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Inventory Item</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ... your existing styles ... */
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Inventory Item</h2>
        <?php if ($item): ?>
            <form method="post" action="edit_inventory_items.php?inventory_id=<?php echo $item_id; ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Inventory Name</label>
                    <input type="text" name="inventory_name" class="form-control" value="<?php echo htmlspecialchars($item['inventory_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Unit Price</label>
                    <input type="number" name="unit_price" class="form-control" value="<?php echo htmlspecialchars($item['unit_price']); ?>">
                </div>
                <div class="form-group">
                    <label>Selling Price</label>
                    <input type="number" name="selling_price" class="form-control" value="<?php echo htmlspecialchars($item['selling_price']); ?>">
                </div>
                <div class="form-group">
                    <label>Reorder Level</label>
                    <input type="number" name="reorder_level" class="form-control" value="<?php echo htmlspecialchars($item['reorder_level']); ?>">
                </div>
                <div class="form-group">
                    <label>Inventory Photo/File (Max 480KB, JPEG, PNG, PDF)</label>
                    <input type="file" name="inventory_photo" class="form-control" accept="image/jpeg, image/png, application/pdf">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Update Item">
                </div>
            </form>
        <?php else: ?>
            <p>Item not found.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>