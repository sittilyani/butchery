<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/config.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role_filter']) ? trim($_GET['role_filter']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// Build query with search
$sql = "SELECT * FROM users WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (username LIKE '%$search%'
                   OR first_name LIKE '%$search%'
                   OR last_name LIKE '%$search%'
                   OR email LIKE '%$search%'
                   OR mobile LIKE '%$search%'
                   OR userrole LIKE '%$search%')";
}

if (!empty($role_filter)) {
    $role_filter = mysqli_real_escape_string($conn, $role_filter);
    $sql .= " AND userrole = '$role_filter'";
}

if (!empty($status_filter)) {
    $status_filter = mysqli_real_escape_string($conn, $status_filter);
    $sql .= " AND status = '$status_filter'";
}

$sql .= " ORDER BY user_id DESC";

$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Get unique roles for filter dropdown
$roles_sql = "SELECT DISTINCT userrole FROM users WHERE userrole IS NOT NULL AND userrole != '' ORDER BY userrole";
$roles_result = $conn->query($roles_sql);
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #cc0000;
            --primary-hover: #ff0000;
            --secondary-color: #6c757d;
            --background-light: #f8f9fa;
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
            --input-focus-border: #cc0000;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --update-color: #17a2b8;
            --delete-color: #dc3545;
            --view-color: #28a745;
            --enable-color: #28a745;
            --disable-color: #dc3545;
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
            padding: 20px;
        }

        .main-content {
            width: 95%;
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
            box-shadow: 0 5px 15px rgba(204, 0, 0, 0.3);
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
            flex: 2;
            min-width: 250px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .search-group label,
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.95em;
        }

        .search-group label i,
        .filter-group label i {
            margin-right: 5px;
            color: var(--primary-color);
        }

        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(204, 0, 0, 0.1);
            outline: none;
        }

        .search-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-size: 0.95em;
            cursor: pointer;
            background-color: white;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
        }

        .btn-search {
            background-color: var(--update-color);
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

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: white;
        }

        .btn-action i {
            font-size: 0.9em;
        }

        .btn-update {
            background-color: var(--update-color);
        }

        .btn-update:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }

        .btn-enable {
            background-color: var(--enable-color);
        }

        .btn-enable:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .btn-disable {
            background-color: var(--disable-color);
        }

        .btn-disable:hover {
            background-color: #bd2130;
            transform: translateY(-2px);
        }

        .btn-view {
            background-color: var(--view-color);
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

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-role {
            background-color: #e9ecef;
            color: #495057;
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
            background-color: var(--success-bg-color);
            color: var(--success-color);
            border: 1px solid var(--success-border);
        }

        .status-inactive {
            background-color: var(--error-bg-color);
            color: var(--error-color);
            border: 1px solid var(--error-border);
        }

        /* Stats cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-card i {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 2em;
            font-weight: bold;
        }

        .stat-card .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .main-content {
                width: 98%;
                padding: 20px;
            }
        }

        @media screen and (max-width: 768px) {
            .main-content {
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

            .search-group,
            .filter-group {
                width: 100%;
            }

            .btn-search,
            .btn-reset {
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
                flex-direction: row;
                gap: 5px;
                width: 100%;
                justify-content: flex-end;
            }

            .btn-action {
                padding: 6px 10px;
                font-size: 0.85em;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h1><i class="fas fa-users"></i> User Management</h1>

    <!-- Display session messages -->
    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php
                echo htmlspecialchars($_SESSION['error_msg']);
                unset($_SESSION['error_msg']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php
                echo htmlspecialchars($_SESSION['success_msg']);
                unset($_SESSION['success_msg']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Display GET messages -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 'user_id_missing'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> Error: User ID is missing. Please select a user to edit.
        </div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'user_updated'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> User updated successfully.
        </div>
    <?php elseif (isset($_GET['success']) && $_GET['success'] == 'user_deleted'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> User deleted successfully.
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-users"></i>
            <div class="stat-value"><?php echo count($users); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-user-tie"></i>
            <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['userrole'] === 'Admin'; })); ?></div>
            <div class="stat-label">Admins</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-user-clock"></i>
            <div class="stat-value"><?php echo count(array_filter($users, function($u) { return $u['userrole'] === 'Staff'; })); ?></div>
            <div class="stat-label">Staff</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-calendar-alt"></i>
            <div class="stat-value"><?php echo count(array_filter($users, function($u) {
                return date('Y-m-d', strtotime($u['date_created'])) === date('Y-m-d');
            })); ?></div>
            <div class="stat-label">New Today</div>
        </div>
    </div>

    <div class="header-actions">
        <a href="../login/user_registration.php" class="btn-primary">
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <div class="search-group">
                <label><i class="fas fa-search"></i> Search Users</label>
                <input type="text" name="search" class="search-input"
                       placeholder="Search by username, name, email, mobile, or role..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Filter by Role</label>
                <select name="role_filter" class="search-select">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <?php if (!empty($role['userrole'])): ?>
                        <option value="<?php echo htmlspecialchars($role['userrole']); ?>"
                                <?php echo $role_filter === $role['userrole'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role['userrole']); ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="fas fa-toggle-on"></i> Filter by Status</label>
                <select name="status_filter" class="search-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-search">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
            <a href="userslist.php" class="btn-reset">
                <i class="fas fa-undo"></i> Reset
            </a>
        </form>
    </div>

    <!-- Results count -->
    <div style="margin-bottom: 15px; color: #666;">
        <i class="fas fa-database"></i> Showing <?php echo count($users); ?> user(s)
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> User ID</th>
                    <th><i class="fas fa-user"></i> Username</th>
                    <th><i class="fas fa-id-card"></i> First Name</th>
                    <th><i class="fas fa-id-card"></i> Last Name</th>
                    <th><i class="fas fa-envelope"></i> Email</th>
                    <th><i class="fas fa-venus-mars"></i> Sex</th>
                    <th><i class="fas fa-phone"></i> Mobile</th>
                    <th><i class="fas fa-user-tag"></i> User Role</th>
                    <th><i class="fas fa-toggle-on"></i> Status</th>
                    <th><i class="fas fa-calendar"></i> Date Created</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="11" class="no-records">
                            <i class="fas fa-user-slash"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td data-label="User ID"><?= htmlspecialchars($user['user_id']) ?></td>
                            <td data-label="Username">
                                <i class="fas fa-user-circle" style="color: #cc0000; margin-right: 5px;"></i>
                                <?= htmlspecialchars($user['username']) ?>
                            </td>
                            <td data-label="First Name"><?= htmlspecialchars($user['first_name']) ?></td>
                            <td data-label="Last Name"><?= htmlspecialchars($user['last_name']) ?></td>
                            <td data-label="Email">
                                <i class="fas fa-envelope" style="color: #666; margin-right: 5px;"></i>
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td data-label="Sex">
                                <span class="badge badge-role">
                                    <i class="fas <?= $user['sex'] === 'Male' ? 'fa-mars' : 'fa-venus' ?>"
                                       style="color: <?= $user['sex'] === 'Male' ? '#17a2b8' : '#e83e8c' ?>"></i>
                                    <?= htmlspecialchars($user['sex']) ?>
                                </span>
                            </td>
                            <td data-label="Mobile">
                                <i class="fas fa-phone-alt" style="color: #666; margin-right: 5px;"></i>
                                <?= htmlspecialchars($user['mobile']) ?>
                            </td>
                            <td data-label="User Role">
                                <span class="badge badge-role">
                                    <i class="fas fa-user-tag"></i>
                                    <?= htmlspecialchars($user['userrole']) ?>
                                </span>
                            </td>
                            <td data-label="Status">
                                <span class="status-badge <?= $user['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <i class="fas <?= $user['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                    <?= ucfirst(htmlspecialchars($user['status'] ?? 'active')) ?>
                                </span>
                            </td>
                            <td data-label="Date Created">
                                <i class="fas fa-calendar-alt" style="color: #666; margin-right: 5px;"></i>
                                <?= date('M d, Y', strtotime($user['date_created'])) ?>
                            </td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <button class="btn-action btn-update" onclick="location.href='update_user.php?user_id=<?= $user['user_id'] ?>'">
                                        <i class="fas fa-edit"></i> Update
                                    </button>

                                    <?php if ($user['status'] === 'active'): ?>
                                        <button class="btn-action btn-disable" onclick="confirmStatusChange(<?= $user['user_id'] ?>, 'disable')">
                                            <i class="fas fa-ban"></i> Disable
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-action btn-enable" onclick="confirmStatusChange(<?= $user['user_id'] ?>, 'enable')">
                                            <i class="fas fa-check-circle"></i> Enable
                                        </button>
                                    <?php endif; ?>

                                    <button class="btn-action btn-view" onclick="location.href='view_user.php?user_id=<?= $user['user_id'] ?>'">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-reset" onclick="location.href='../login/reset_user_password.php?user_id=<?= $user['user_id'] ?>'">
                                        <i class="fas fa-refresh"></i> Reset
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
    function confirmStatusChange(userId, action) {
        let message = action === 'disable'
            ? 'Are you sure you want to disable this user? They will not be able to log in.'
            : 'Are you sure you want to enable this user? They will be able to log in again.';

        if (confirm(message)) {
            window.location.href = `../login/disable_user.php?user_id=${userId}&action=${action}`;
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