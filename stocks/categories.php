<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include your database configuration
include "../includes/config.php";

// Set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "Add New Category";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid security token. Please try again.";
    } else {
        // Validate and sanitize input
        $name = trim(mysqli_real_escape_string($conn, $_POST['name'] ?? ''));
        $description = trim(mysqli_real_escape_string($conn, $_POST['description'] ?? ''));
        $status = trim(mysqli_real_escape_string($conn, $_POST['status'] ?? 'active'));

        // Handle file upload
        $photo_name = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../uploads/categories/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($file_ext, $allowed_extensions)) {
                $check = getimagesize($_FILES['photo']['tmp_name']);
                if ($check !== false) {
                    $photo_name = uniqid() . '.' . $file_ext;
                    $target_file = $target_dir . $photo_name;

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                        // File uploaded successfully
                    } else {
                        $photo_name = '';
                        $_SESSION['error_message'] = "Failed to upload file.";
                    }
                } else {
                    $_SESSION['error_message'] = "File is not a valid image.";
                }
            } else {
                $_SESSION['error_message'] = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
            }
        }

        // Validate required fields
        if (empty($name)) {
            $_SESSION['error_message'] = "Category name is required.";
        } else {
            // Insert into database
            $query = "INSERT INTO categories (name, description, photo, status) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssss", $name, $description, $photo_name, $status);

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Category added successfully!";
                    // Clear POST data on success
                    $_POST = array();
                } else {
                    $_SESSION['error_message'] = "Database error: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
            }
        }
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Variables for easy theme changes */
        :root {
            --primary-color: #000099;
            --primary-hover: #0000cc;
            --secondary-color: #6c757d;
            --background-light: #f0f8ff;
            --card-background: #ffffff;
            --border-color: #dee2e6;
            --success-color: #155724;
            --success-bg-color: #d4edda;
            --success-border: #c3e6cb;
            --error-color: #721c24;
            --error-bg-color: #f8d7da;
            --error-border: #f5c6cb;
            --text-color: #343a40;
            --input-border: #ced4da;
            --input-focus-border: #000099;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --active-color: #28a745;
            --inactive-color: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .main-content {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
        }

        .form-container {
            background: var(--card-background);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            background: var(--primary-color);
            padding: 25px 30px;
            text-align: center;
        }

        .form-header h2 {
            color: white;
            font-size: 2em;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-header i {
            margin-right: 10px;
        }

        .alert {
            padding: 15px 20px;
            margin: 20px 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease-out;
        }

        .alert i {
            font-size: 1.2em;
        }

        .alert-success {
            background-color: var(--success-bg-color);
            color: var(--success-color);
            border: 1px solid var(--success-border);
        }

        .alert-danger {
            background-color: var(--error-bg-color);
            color: var(--error-color);
            border: 1px solid var(--error-border);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        form {
            padding: 30px;
            background: #99ff99;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95em;
        }

        label i {
            margin-right: 8px;
            color: var(--primary-color);
            width: 20px;
        }

        .required::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }

        input[type="text"],
        textarea,
        input[type="file"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 1em;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
        }

        input[type="text"]:hover,
        textarea:hover,
        select:hover {
            border-color: #999;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(0, 0, 153, 0.1);
            outline: none;
        }

        .file-input-wrapper {
            position: relative;
        }

        input[type="file"] {
            padding: 10px;
            background: #f8f9fa;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 15px;
            transition: background 0.3s ease;
        }

        input[type="file"]::file-selector-button:hover {
            background: var(--primary-hover);
        }

        .file-info {
            font-size: 0.85em;
            color: var(--secondary-color);
            margin-top: 5px;
        }

        .custom-submit-btn {
            width: 100%;
            padding: 15px 25px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .custom-submit-btn i {
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }

        .custom-submit-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 153, 0.3);
        }

        .custom-submit-btn:hover i {
            transform: translateX(5px);
        }

        .custom-submit-btn:active {
            transform: translateY(0);
        }

        .custom-submit-btn:disabled {
            background: var(--secondary-color);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Status colors */
        .status-active {
            color: var(--active-color);
            font-weight: 600;
        }

        .status-inactive {
            color: var(--inactive-color);
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-header h2 {
                font-size: 1.5em;
            }

            form {
                padding: 20px;
            }

            .alert {
                margin: 15px 20px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-folder-plus"></i> <?php echo htmlspecialchars($page_title); ?></h2>
            </div>

            <!-- Error and success messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                        echo htmlspecialchars($_SESSION['error_message']);
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                        echo htmlspecialchars($_SESSION['success_message']);
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <form id="category-form" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="name" class="required">
                        <i class="fas fa-tag"></i> Category Name
                    </label>
                    <input type="text" id="name" name="name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           placeholder="Enter category name"
                           required
                           maxlength="100">
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" name="description"
                              placeholder="Enter category description (optional)"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select id="status" name="status">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="photo">
                        <i class="fas fa-camera"></i> Category Photo
                    </label>
                    <div class="file-input-wrapper">
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i> Allowed: JPG, JPEG, PNG, GIF, WEBP (Max size: 2MB)
                    </div>
                </div>

                <button type="submit" class="custom-submit-btn" id="submit-btn">
                    <i class="fas fa-plus-circle"></i> Add Category
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const photo = document.getElementById('photo').files[0];
            const submitBtn = document.getElementById('submit-btn');

            if (!name) {
                alert('Please enter a category name.');
                return false;
            }

            // Optional: File size validation (2MB limit)
            if (photo && photo.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB.');
                return false;
            }

            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Adding...';

            return true;
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>