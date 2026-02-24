<?php
ob_start();
session_start();
include "../includes/config.php";

// Set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "Add New Product";

// Fetch categories for the dropdown
$categories = [];
$query = "SELECT id, name FROM categories ORDER BY name";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    mysqli_free_result($result);
} else {
    $_SESSION['error_message'] = "Error fetching categories: " . mysqli_error($conn);
}

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
        // Insert into database using prepared statement
        $query = "INSERT INTO products (category, productname, brandname, packsize, pack_price, unit_price, price, reorder_level, currentstatus, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("issdddsis", $category_id, $productname, $brandname, $packsize, $pack_price, $unit_price, $price, $reorder_level, $currentstatus);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Product added successfully!";
                header("Location: viewstocks_sum.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Failed to prepare statement: " . $conn->error;
        }
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
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        /* CSS Variables for easy theme changes */
        :root {
            --primary-color: #0056b3; /* Darker blue for primary actions */
            --secondary-color: #6c757d; /* Grey for secondary elements */
            --background-light: #f8f9fa; /* Light background for overall page */
            --card-background: #ffffff; /* White for form background */
            --border-color: #dee2e6;
            --success-color: #28a745;
            --success-bg-color: #d4edda;
            --text-color: #343a40;
            --input-border: #ced4da;
            --input-focus-border: #80bdff;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --font-family: 'Arial', sans-serif; /* Changed from Times New Roman for a modern look */
        }

        .main-content {
            padding: 20px;
            max-width: 60%;
            margin: 20px auto; /* Center the main content */
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

        #success-message {
            background-color: var(--success-bg-color);
            color: var(--success-color);
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid var(--success-color);
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        #success-message .fas {
            font-size: 1.2em;
        }


        form {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Three equal columns */
            gap: 25px; /* Spacing between columns and rows */
            padding: 20px;
            background-color: #99ff99; /* Original light yellow background */
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
        input[type="email"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .readonly-input {
            background-color: #e9ecef; /* Light gray for readonly fields */
            cursor: not-allowed;
        }

        .custom-submit-btn {
            grid-column: 1 / -1; /* Make the button span all three columns */
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
            background-color: #004085; /* Darker shade on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        .custom-submit-btn:active {
            transform: translateY(0);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            form {
                grid-template-columns: repeat(2, 1fr); /* Two columns on medium screens */
            }
            .custom-submit-btn {
                grid-column: 1 / -1; /* Still span full width */
            }
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mt-3"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?></h2>

    <form id="product-form" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-group">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="productname" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="productname" name="productname" value="<?php echo isset($_POST['productname']) ? htmlspecialchars($_POST['productname']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="brandname" class="form-label">Brand Name</label>
            <input type="text" class="form-control" id="brandname" name="brandname" value="<?php echo isset($_POST['brandname']) ? htmlspecialchars($_POST['brandname']) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="packsize" class="form-label">Pack Size</label>
            <input type="number" class="form-control" id="packsize" name="packsize" step="1" min="1" value="<?php echo isset($_POST['packsize']) ? htmlspecialchars($_POST['packsize']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="pack_price" class="form-label">Pack Price (KES)</label>
            <input type="number" class="form-control" id="pack_price" name="pack_price" step="0.01" min="0.01" value="<?php echo isset($_POST['pack_price']) ? htmlspecialchars($_POST['pack_price']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="unit_price" class="form-label">Unit Price (KES)</label>
            <input type="number" class="form-control" id="unit_price" name="unit_price" step="0.01" min="0" value="<?php echo isset($_POST['unit_price']) ? htmlspecialchars($_POST['unit_price']) : ''; ?>" readonly>
        </div>
        <div class="form-group">
            <label for="price" class="form-label">Selling Price (KES)</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="reorder_level" class="form-label">Reorder Level</label>
            <input type="number" class="form-control" id="reorder_level" name="reorder_level" min="0" value="<?php echo isset($_POST['reorder_level']) ? htmlspecialchars($_POST['reorder_level']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="currentstatus" class="form-label">currentstatus</label>
            <select class="form-control" id="currentstatus" name="currentstatus" required>
                <option value="Active" <?php echo (isset($_POST['currentstatus']) && $_POST['currentstatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo (isset($_POST['currentstatus']) && $_POST['currentstatus'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="custom-submit-btn">Add Product</button>
        </div>
    </form>
</div>
<script src="../assets/js/bootstrap.bundle.js"></script>
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

    // Initial calculation if values exist
    calculateUnitPrice();
</script>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
