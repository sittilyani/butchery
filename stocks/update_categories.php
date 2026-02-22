<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/config.php';

// Set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "Update Category";

// Initialize variables
$category = [];
$error = '';

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch category data
    $sql = "SELECT id, name, description, photo, status FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Category not found.";
        header("Location: view_categories.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: view_categories.php");
    exit();
}

// Process form submission for UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid security token. Please try again.";
        header("Location: update_categories.php?id=" . $id);
        exit();
    }

    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if ($action === 'update') {
        // Update category details
        $name = trim(mysqli_real_escape_string($conn, $_POST['name'] ?? ''));
        $description = trim(mysqli_real_escape_string($conn, $_POST['description'] ?? ''));
        $status = trim(mysqli_real_escape_string($conn, $_POST['status'] ?? 'active'));

        // Handle file upload
        $photo_name = $category['photo']; // Keep existing photo by default

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
                    // Delete old photo if exists
                    if (!empty($category['photo']) && file_exists($target_dir . $category['photo'])) {
                        unlink($target_dir . $category['photo']);
                    }

                    $photo_name = uniqid() . '.' . $file_ext;
                    $target_file = $target_dir . $photo_name;

                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                        $photo_name = $category['photo']; // Revert to old photo on failure
                        $_SESSION['error_message'] = "Failed to upload new photo.";
                        header("Location: update_categories.php?id=" . $id);
                        exit();
                    }
                } else {
                    $_SESSION['error_message'] = "File is not a valid image.";
                    header("Location: update_categories.php?id=" . $id);
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
                header("Location: update_categories.php?id=" . $id);
                exit();
            }
        }

        // Validate required fields
        if (empty($name)) {
            $_SESSION['error_message'] = "Category name is required.";
            header("Location: update_categories.php?id=" . $id);
            exit();
        } else {
            // Update database
            $sql = "UPDATE categories SET name = ?, description = ?, photo = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssi', $name, $description, $photo_name, $status, $id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Category updated successfully!";
                header("Location: view_categories.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Failed to update category: " . $stmt->error;
                header("Location: update_categories.php?id=" . $id);
                exit();
            }
            $stmt->close();
        }
    } elseif ($action === 'disable') {
        // Just update status to inactive
        $sql = "UPDATE categories SET status = 'inactive' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category disabled successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to disable category: " . $stmt->error;
        }
        $stmt->close();
        header("Location: view_categories.php");
        exit();
    } elseif ($action === 'enable') {
        // Just update status to active
        $sql = "UPDATE categories SET status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Category enabled successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to enable category: " . $stmt->error;
        }
        $stmt->close();
        header("Location: view_categories.php");
        exit();
    }
}

// Get updated category data after any changes
$sql = "SELECT id, name, description, photo, status FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Variables for consistent theming */
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .status-banner {
            padding: 15px 30px;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .status-banner.active {
            background-color: var(--success-bg-color);
            color: var(--success-color);
            border-bottom: 2px solid var(--success-border);
        }

        .status-banner.inactive {
            background-color: var(--error-bg-color);
            color: var(--error-color);
            border-bottom: 2px solid var(--error-border);
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

        .current-photo {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed var(--border-color);
        }

        .current-photo p {
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-color);
        }

        .photo-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid var(--border-color);
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

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-update {
            flex: 2;
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
        }

        .btn-update i {
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }

        .btn-update:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 153, 0.3);
        }

        .btn-update:hover i {
            transform: translateX(5px);
        }

        .btn-disable {
            flex: 1;
            padding: 15px 25px;
            background: var(--inactive-color);
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
        }

        .btn-disable:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-enable {
            flex: 1;
            padding: 15px 25px;
            background: var(--active-color);
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
        }

        .btn-enable:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-cancel {
            flex: 1;
            padding: 15px 25px;
            background: var(--secondary-color);
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
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
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

            .button-group {
                flex-direction: column;
            }

            .btn-update, .btn-disable, .btn-enable, .btn-cancel {
                width: 100%;
            }

            .photo-preview {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-edit"></i> <?php echo htmlspecialchars($page_title); ?></h2>
            </div>

            <!-- Status Banner -->
            <div class="status-banner <?php echo $category['status']; ?>">
                <i class="fas <?php echo $category['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                This category is currently <strong><?php echo strtoupper($category['status']); ?></strong>
            </div>

            <!-- Display session messages -->
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

            <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
                <input type="hidden" name="action" id="form-action" value="update">

                <div class="form-group">
                    <label for="name" class="required">
                        <i class="fas fa-tag"></i> Category Name
                    </label>
                    <input type="text" id="name" name="name"
                           value="<?php echo htmlspecialchars($category['name']); ?>"
                           placeholder="Enter category name"
                           required
                           maxlength="100">
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" name="description"
                              placeholder="Enter category description"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select id="status" name="status">
                        <option value="active" <?php echo $category['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $category['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="photo">
                        <i class="fas fa-camera"></i> Category Photo
                    </label>

                    <?php if (!empty($category['photo'])): ?>
                        <div class="current-photo">
                            <p><i class="fas fa-image"></i> Current Photo:</p>
                            <img src="../uploads/categories/<?php echo htmlspecialchars($category['photo']); ?>"
                                 alt="Category Photo"
                                 class="photo-preview"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>

                    <div class="file-input-wrapper">
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i> Leave empty to keep current photo. Allowed: JPG, JPEG, PNG, GIF, WEBP (Max size: 2MB)
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-update" id="update-btn">
                        <i class="fas fa-save"></i> Update Category
                    </button>

                    <?php if ($category['status'] === 'active'): ?>
                        <button type="button" class="btn-disable" onclick="confirmDisable(<?php echo $category['id']; ?>)">
                            <i class="fas fa-ban"></i> Disable Category
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn-enable" onclick="confirmEnable(<?php echo $category['id']; ?>)">
                            <i class="fas fa-check-circle"></i> Enable Category
                        </button>
                    <?php endif; ?>

                    <a href="view_categories.php" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Separate forms for disable/enable actions -->
    <form id="disable-form" method="POST" action="" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
        <input type="hidden" name="action" value="disable">
    </form>

    <form id="enable-form" method="POST" action="" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
        <input type="hidden" name="action" value="enable">
    </form>

    <script>
        // Form validation for update
        function validateForm() {
            const name = document.getElementById('name').value.trim();
            const photo = document.getElementById('photo').files[0];
            const updateBtn = document.getElementById('update-btn');

            if (!name) {
                alert('Please enter a category name.');
                return false;
            }

            // File size validation (2MB limit)
            if (photo && photo.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB.');
                return false;
            }

            // Disable submit button to prevent double submission
            updateBtn.disabled = true;
            updateBtn.innerHTML = '<span class="loading"></span> Updating...';

            return true;
        }

        // Confirm disable action
        function confirmDisable(categoryId) {
            if (confirm('Are you sure you want to disable this category? Disabled categories cannot be deleted and will be hidden from active lists.')) {
                document.getElementById('disable-form').submit();
            }
        }

        // Confirm enable action
        function confirmEnable(categoryId) {
            if (confirm('Are you sure you want to enable this category?')) {
                document.getElementById('enable-form').submit();
            }
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

        // Preview image before upload
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.querySelector('.photo-preview');
                        if (preview) {
                            preview.src = e.target.result;
                        } else {
                            // Create preview if it doesn't exist
                            const currentPhoto = document.querySelector('.current-photo');
                            if (currentPhoto) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'photo-preview';
                                img.alt = 'New Photo Preview';
                                currentPhoto.appendChild(img);
                            }
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    </script>
</body>
</html>