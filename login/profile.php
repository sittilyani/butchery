<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$user = [];
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Check if user_id is provided
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];

    // Non-admin users can only view their own profile
    if ($_SESSION['userrole'] != 'Admin' && $_SESSION['user_id'] != $user_id) {
        header("Location: ../index.php?error=access_denied");
        exit();
    }

    // Corrected query to get data from both users and staff tables
    $sql = "SELECT u.user_id, u.username, u.userrole, u.date_created,
                   s.staff_id, s.staff_number, s.job_title, s.first_name, s.last_name,
                   s.nick_name, s.sex, s.email, s.dob, s.marital_status, s.religion,
                   s.id_number, s.phone, s.address, s.photo
            FROM users u
            INNER JOIN staff s ON u.staff_number = s.staff_number
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            header("Location: userslist.php?error=user_not_found");
            exit();
        }
        $stmt->close();
    } else {
        // Handle SQL error
        die("SQL error: " . $conn->error);
    }
} else {
    // Default to current user's profile
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT u.user_id, u.username, u.userrole, u.date_created,
                   s.staff_id, s.staff_number, s.job_title, s.first_name, s.last_name,
                   s.nick_name, s.sex, s.email, s.dob, s.marital_status, s.religion,
                   s.id_number, s.phone, s.address, s.photo
            FROM users u
            INNER JOIN staff s ON u.staff_number = s.staff_number
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    } else {
        // Handle SQL error
        die("SQL error: " . $conn->error);
    }
}

// Debug: Check what data we have
error_log("User data: " . print_r($user, true));

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $user_id = (int)$_POST['user_id'];
    $staff_number = $_POST['staff_number'];
    $job_title = $_POST['job_title'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $nick_name = $_POST['nick_name'];
    $sex = $_POST['sex'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $marital_status = $_POST['marital_status'];
    $religion = $_POST['religion'];
    $id_number = $_POST['id_number'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $userrole = $_POST['userrole'];

    // Non-admin users can't change their role
    if ($_SESSION['userrole'] != 'Admin') {
        $userrole = $user['userrole'];
    }

    // Update staff table
    $sql_staff = "UPDATE staff SET
                job_title = ?, first_name = ?, last_name = ?, nick_name = ?,
                sex = ?, email = ?, dob = ?, marital_status = ?, religion = ?,
                id_number = ?, phone = ?, address = ?
                WHERE staff_number = ?";
    $stmt_staff = $conn->prepare($sql_staff);
    if ($stmt_staff) {
        $stmt_staff->bind_param('sssssssssssss',
            $job_title, $first_name, $last_name, $nick_name,
            $sex, $email, $dob, $marital_status, $religion,
            $id_number, $phone, $address, $staff_number
        );

        // Update users table
        $sql_users = "UPDATE users SET username = ?, userrole = ? WHERE user_id = ?";
        $stmt_users = $conn->prepare($sql_users);
        if ($stmt_users) {
            $stmt_users->bind_param('ssi', $username, $userrole, $user_id);

            // Execute both updates
            $staff_success = $stmt_staff->execute();
            $users_success = $stmt_users->execute();

            if ($staff_success && $users_success) {
                header("Location: profile.php?user_id=$user_id&success=Profile updated successfully");
                exit();
            } else {
                $error_msg = "Update failed: " . $conn->error;
                header("Location: user_profile.php?user_id=$user_id&error=" . urlencode($error_msg));
                exit();
            }

            $stmt_users->close();
        } else {
            $error_msg = "Users update preparation failed: " . $conn->error;
            header("Location: user_profile.php?user_id=$user_id&error=" . urlencode($error_msg));
            exit();
        }
        $stmt_staff->close();
    } else {
        $error_msg = "Staff update preparation failed: " . $conn->error;
        header("Location: profile.php?user_id=$user_id&error=" . urlencode($error_msg));
        exit();
    }
}

// Helper function to safely output values
function safeOutput($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?= safeOutput($user['first_name']) . ' ' . safeOutput($user['last_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../assets/favicons/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/favicons/favicon.ico" type="image/x-icon">
    <style>
        .profile-card {
            max-width: 1200px;
            margin: 30px auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .profile-header {
            background: #000099;
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .profile-body {
            padding: 30px;
            background-color: #f8f9fa;
        }
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .form-grid {
             display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            padding: 20px;
            background-color: #66ccff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 5px var(--shadow-light);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #495057;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .form-group input[readonly] {
            background-color: #e9ecef;
            opacity: 0.8;
        }
        .btn-custom {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #218838, #1aa179);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            margin: 20px auto;
            max-width: 800px;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .profile-card {
                margin: 15px;
            }
        }
        .profile-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #000099;
        }
        .debug-info {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= safeOutput($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= safeOutput($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($user)): ?>
            <div class="profile-card">
                <div class="profile-header">
                    <img src="<?= safeOutput($user['photo'] ?? '../photos/BSP00001.PNG'); ?>" alt="Profile Photo" class="profile-photo">
                    <h2><?= safeOutput($user['first_name']) . ' ' . safeOutput($user['last_name']); ?></h2>
                    <p><?= safeOutput($user['userrole']); ?></p>
                </div>
                <div class="profile-body">
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?= safeOutput($user['user_id']); ?>">
                        <input type="hidden" name="staff_number" value="<?= safeOutput($user['staff_number']); ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="staff_number">Staff Number</label>
                                <input type="text" id="staff_number_display" value="<?= safeOutput($user['staff_number']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="job_title">Job Title</label>
                                <input type="text" id="job_title" name="job_title" value="<?= safeOutput($user['job_title']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?= safeOutput($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?= safeOutput($user['last_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="nick_name">Nick Name</label>
                                <input type="text" id="nick_name" name="nick_name" value="<?= safeOutput($user['nick_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?= safeOutput($user['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="sex">Gender</label>
                                <select id="sex" name="sex" required>
                                    <option value="Male" <?= ($user['sex'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?= ($user['sex'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= safeOutput($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth</label>
                                <input type="date" id="dob" name="dob" value="<?= safeOutput($user['dob']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="marital_status">Marital Status</label>
                                <input type="text" id="marital_status" name="marital_status" value="<?= safeOutput($user['marital_status']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="religion">Religion</label>
                                <input type="text" id="religion" name="religion" value="<?= safeOutput($user['religion']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="id_number">ID Number</label>
                                <input type="text" id="id_number" name="id_number" value="<?= safeOutput($user['id_number']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" id="phone" name="phone" value="<?= safeOutput($user['phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="<?= safeOutput($user['address']); ?>">
                            </div>

                            <?php if ($_SESSION['userrole'] == 'Admin'): ?>
                                <div class="form-group">
                                    <label for="userrole">User Role</label>
                                    <select id="userrole" name="userrole" required>
                                        <option value="User" <?= ($user['userrole'] ?? '') == 'User' ? 'selected' : ''; ?>>User</option>
                                        <option value="Admin" <?= ($user['userrole'] ?? '') == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="userrole" value="<?= safeOutput($user['userrole']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Date Created</label>
                            <input type="text" value="<?= safeOutput($user['date_created']); ?>" readonly>
                        </div>
                        <button type="submit" name="submit" class="btn-custom"><i class="fas fa-save"></i> Update Profile</button>
                    </form>
                    <a href="../sales/orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

                    <!-- Debug section (remove in production) -->
                    <?php if (isset($_GET['debug'])): ?>
                        <div class="debug-info mt-4">
                            <h4>Debug Information:</h4>
                            <pre><?php print_r($user); ?></pre>
                            <p>SQL: SELECT u.user_id, u.username, u.userrole, u.date_created,
                            s.staff_id, s.staff_number, s.job_title, s.first_name, s.last_name,
                            s.nick_name, s.sex, s.email, s.dob, s.marital_status, s.religion,
                            s.id_number, s.phone, s.address, s.photo
                            FROM users u
                            INNER JOIN staff s ON u.staff_number = s.staff_number
                            WHERE u.user_id = <?= $user_id ?? 'NULL' ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                User not found. <a href="?debug=1">Enable debug</a>
            </div>
        <?php endif; ?>
    </div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>


</body>
</html>