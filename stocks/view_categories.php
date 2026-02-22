<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/config.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// Build query with search and status filter
$sql = "SELECT * FROM categories WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $sql .= " AND status = '$status_filter'";
}

$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Categories</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #000099;
            --primary-hover: #0000cc;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --active-color: #28a745;
            --inactive-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .main-content {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 30px;
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

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 15px;
        }

        h1 i {
            margin-right: 10px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 153, 0.3);
        }

        .btn-primary i {
            font-size: 1.1em;
        }

        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
        }

        .search-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-group {
            flex: 1;
            min-width: 200px;
        }

        .search-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95em;
        }

        .search-group label i {
            margin-right: 5px;
            color: var(--primary-color);
        }

        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 0, 153, 0.1);
            outline: none;
        }

        .search-select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-size: 0.95em;
            cursor: pointer;
            background-color: white;
        }

        .btn-search {
            background-color: var(--info-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-search:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .btn-reset {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-reset:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
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
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 10px;
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
        }

        th i {
            margin-right: 5px;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            min-width: 80px;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-action i {
            font-size: 0.9em;
        }

        .btn-update {
            background-color: var(--info-color);
            color: white;
        }

        .btn-update:hover:not(:disabled) {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover:not(:disabled) {
            background-color: #bd2130;
            transform: translateY(-2px);
        }

        .btn-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-view {
            background-color: var(--success-color);
            color: white;
        }

        .btn-view:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .no-records {
            text-align: center;
            padding: 40px;
            color: var(--secondary-color);
            font-size: 1.1em;
        }

        .no-records i {
            font-size: 3em;
            margin-bottom: 15px;
            color: #dee2e6;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .main-content {
                width: 95%;
                padding: 15px;
            }

            h1 {
                font-size: 2em;
            }

            .header-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-primary {
                width: 100%;
                justify-content: center;
            }

            .search-form {
                flex-direction: column;
            }

            .search-group {
                width: 100%;
            }

            .btn-search, .btn-reset {
                width: 100%;
                justify-content: center;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 10px;
            }

            td {
                border: none;
                padding: 8px 10px;
                position: relative;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                color: var(--primary-color);
                margin-right: 10px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h1><i class="fas fa-folder"></i> Categories Management</h1>

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

    <div class="header-actions">
        <a href="../stocks/categories.php" class="btn-primary">
            <i class="fas fa-plus-circle"></i> Add New Category
        </a>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <div class="search-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" name="search" class="search-input"
                       placeholder="Search by name or description..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="search-group">
                <label><i class="fas fa-filter"></i> Status Filter</label>
                <select name="status_filter" class="search-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-search">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
            <a href="view_categories.php" class="btn-reset">
                <i class="fas fa-undo"></i> Reset
            </a>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-tag"></i> Category Name</th>
                    <th><i class="fas fa-align-left"></i> Description</th>
                    <th><i class="fas fa-toggle-on"></i> Status</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="no-records">
                            <i class="fas fa-folder-open"></i>
                            <p>No categories found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td data-label="ID"><?= htmlspecialchars($category['id']) ?></td>
                            <td data-label="Category Name"><?= htmlspecialchars($category['name']) ?></td>
                            <td data-label="Description"><?= htmlspecialchars($category['description']) ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?= $category['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <i class="fas <?= $category['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                    <?= ucfirst(htmlspecialchars($category['status'])) ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <button class="btn-action btn-update" onclick="location.href='../stocks/update_categories.php?id=<?= $category['id'] ?>'">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                    <button class="btn-action btn-delete"
                                            onclick="confirmDelete(<?= $category['id'] ?>, '<?= $category['status'] ?>')"
                                            <?= $category['status'] === 'inactive' ? 'disabled' : '' ?>>
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(categoryId, status) {
        if (status === 'inactive') {
            alert('Cannot delete inactive category. Please activate it first.');
            return false;
        }

        if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
            window.location.href = `../stocks/delete_categories.php?id=${categoryId}`;
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
</script>
</body>
</html>