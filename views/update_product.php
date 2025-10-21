<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/config.php';
include '../includes/header.php';

// Set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Retrieve product details
$product = [];
if ($productId > 0) {
    $sql = "SELECT id, category, productname, brandname, packsize, pack_price, unit_price, price, reorder_level, currentstatus FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $_SESSION['error_message'] = "Product not found.";
        header("Location: ../views/view_products.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "Invalid product ID.";
    header("Location: ../views/view_product.php");
    exit;
}

// Fetch categories for dropdown
$categories = [];
$catSql = "SELECT id, name FROM categories ORDER BY name";
$catResult = $conn->query($catSql);
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}
$catResult->free();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    // Validate and sanitize input
    $category_id = intval($_POST['category_id'] ?? 0);
    $productname = mysqli_real_escape_string($conn, trim($_POST['productname'] ?? ''));
    $brandname = mysqli_real_escape_string($conn, trim($_POST['brandname'] ?? ''));
    $packsize = floatval($_POST['packsize'] ?? 0);
    $pack_price = floatval($_POST['pack_price'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $reorder_level = intval($_POST['reorder_level'] ?? 0);
    $currentstatus = mysqli_real_escape_string($conn, $_POST['currentstatus'] ?? '');

    // Calculate unit_price
    $unit_price = ($packsize > 0) ? $pack_price / $packsize : 0;

    // Validate required fields
    $errors = [];
    if ($category_id <= 0) {
        $errors[] = "Please select a valid category.";
    } else {
        $catStmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $catStmt->bind_param("i", $category_id);
        $catStmt->execute();
        if (!$catStmt->get_result()->fetch_assoc()) {
            $errors[] = "Selected category does not exist.";
        }
        $catStmt->close();
    }
    if (empty($productname)) {
        $errors[] = "Product name is required.";
    }
    if ($packsize <= 0) {
        $errors[] = "Pack size must be greater than zero.";
    }
    if ($pack_price <= 0) {
        $errors[] = "Pack price must be greater than zero.";
    }
    if ($unit_price <= 0) {
        $errors[] = "Unit price must be greater than zero.";
    }
    if ($price <= 0) {
        $errors[] = "Selling price must be greater than zero.";
    }
    if ($reorder_level < 0) {
        $errors[] = "Reorder level cannot be negative.";
    }
    if (!in_array($currentstatus, ['Active', 'Inactive'])) {
        $errors[] = "Invalid currentstatus selected.";
    }

    if (empty($errors)) {
        // Update product details
        $sqlUpdate = "UPDATE products SET category = ?, productname = ?, brandname = ?, packsize = ?, pack_price = ?, unit_price = ?, price = ?, reorder_level = ?, currentstatus = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("issdddsisi", $category_id, $productname, $brandname, $packsize, $pack_price, $unit_price, $price, $reorder_level, $currentstatus, $productId);

        if ($stmtUpdate->execute()) {
            $_SESSION['success_message'] = "Product updated successfully!";
            header("Location: ../views/view_product.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Error updating product: " . $stmtUpdate->error;
        }
        $stmtUpdate->close();
    } else {
        $_SESSION['error_message'] = implode(" ", $errors);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['error_message'] = "Invalid CSRF token.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #6c757d;
            --background-light: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --success-bg-color: #d4edda;
            --text-color: #343a40;
            --input-border: #ced4da;
            --input-focus-border: #80bdff;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --font-family: 'Arial', sans-serif;
        }

        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
            background-color: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 4px 10px var(--shadow-light);
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8em;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .alert-success {
            background-color: var(--success-bg-color);
            color: var(--success-color);
            border-color: var(--success-color);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #dc3545;
            border-color: #f5c6cb;
        }

        form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 20px;
            background-color: #66ff00;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 5px var(--shadow-light);
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .readonly-input {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .custom-submit-btn {
            grid-column: 1 / -1;
            padding: 15px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-submit-btn:hover {
            background-color: #004085;
            transform: translateY(-2px);
        }

        .custom-submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 992px) {
            form {
                grid-template-columns: repeat(2, 1fr);
            }
            .custom-submit-btn {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Update Product Details</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="update_product.php?id=<?php echo $productId; ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="id" value="<?php echo $productId; ?>">
        <div class="form-group">
            <label for="category_id" class="form-label">Category</label>
            <select id="category_id" name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $product['category'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="productname" class="form-label">Product Name</label>
            <input type="text" id="productname" name="productname" class="form-control" value="<?php echo htmlspecialchars($product['productname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="brandname" class="form-label">Brand Name</label>
            <input type="text" id="brandname" name="brandname" class="form-control" value="<?php echo htmlspecialchars($product['brandname'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="packsize" class="form-label">Pack Size</label>
            <input type="number" id="packsize" name="packsize" class="form-control" step="1" min="1" value="<?php echo htmlspecialchars($product['packsize'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="pack_price" class="form-label">Pack Price (KES)</label>
            <input type="number" id="pack_price" name="pack_price" class="form-control" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product['pack_price'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="unit_price" class="form-label">Unit Price (KES)</label>
            <input type="number" id="unit_price" name="unit_price" class="form-control readonly-input" step="0.01" min="0" value="<?php echo htmlspecialchars($product['unit_price'] ?? ''); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="price" class="form-label">Selling Price (KES)</label>
            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="reorder_level" class="form-label">Reorder Level</label>
            <input type="number" id="reorder_level" name="reorder_level" class="form-control" min="0" value="<?php echo htmlspecialchars($product['reorder_level'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="currentstatus" class="form-label">currentstatus</label>
            <select id="currentstatus" name="currentstatus" class="form-control" required>
                <option value="Active" <?php echo ($product['currentstatus'] ?? '') == 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo ($product['currentstatus'] ?? '') == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" name="submit" class="custom-submit-btn">Update Product</button>
        </div>
    </form>
</div>

<script>
    // Calculate unit_price dynamically
    function calculateUnitPrice() {
        const packPrice = parseFloat(document.getElementById('pack_price').value) || 0;
        const packSize = parseInt(document.getElementById('packsize').value) || 0;
        const unitPriceInput = document.getElementById('unit_price');
        const unitPrice = packSize > 0 ? (packPrice / packSize).toFixed(2) : 0;
        unitPriceInput.value = unitPrice;
    }

    // Attach event listeners to pack_price and packsize
    document.getElementById('pack_price').addEventListener('input', calculateUnitPrice);
    document.getElementById('packsize').addEventListener('input', calculateUnitPrice);

    // Initial calculation
    calculateUnitPrice();
</script>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>