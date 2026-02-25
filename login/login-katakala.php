<?php
// login.php
session_start();

// Database connection
include '../includes/config.php';
include '../includes/session_check.php';
$error_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../sales/orders.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Get user data with staff information using user_locations
    $query = "SELECT u.*, s.first_name, s.last_name, s.photo, s.staff_number,
                     l.location_name, l.location_code, d.department_name, d.department_code,
                     p.position_name
              FROM users u
              JOIN staff s ON u.staff_number = s.staff_number
              LEFT JOIN user_locations ul ON u.user_id = ul.user_id AND ul.status = 'active'
              LEFT JOIN locations l ON ul.location_id = l.location_id
              LEFT JOIN departments d ON ul.department_id = d.department_id
              LEFT JOIN positions p ON u.role = p.position_name
              WHERE u.username = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // Record successful login
        $track_query = "INSERT INTO login_tracking (user_id, username, ip_address, user_agent, session_id, status)
                        VALUES (?, ?, ?, ?, ?, 'success')";
        $track_stmt = mysqli_prepare($conn, $track_query);
        mysqli_stmt_bind_param($track_stmt, 'issss', $user['user_id'], $username, $ip_address, $user_agent, session_id());
        mysqli_stmt_execute($track_stmt);
        $track_id = mysqli_insert_id($conn);

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['position'] = $user['position_name'];
        $_SESSION['photo'] = $user['photo'];
        $_SESSION['staff_number'] = $user['staff_number'];
        $_SESSION['login_track_id'] = $track_id;

        // Set location and department from user_locations (or defaults if not assigned)
        $_SESSION['location'] = $user['location_name'] ?? 'Not Assigned';
        $_SESSION['department'] = $user['department_name'] ?? 'Not Assigned';
        $_SESSION['location_code'] = $user['location_code'] ?? 'N/A';
        $_SESSION['department_code'] = $user['department_code'] ?? 'N/A';

        // Check if password needs to be changed (first login)
        if ($user['first_login'] == 1) {
            $_SESSION['change_password'] = true;
            header("Location: reset_password.php");
        } else {
            header("Location: ../dashboard/dashboard.php");
        }
        exit();
    } else {
        // Record failed login attempt
        $failure_reason = $user ? 'Invalid password' : 'User not found';
        $track_query = "INSERT INTO login_tracking (user_id, username, ip_address, user_agent, status, failure_reason)
                        VALUES (?, ?, ?, ?, 'failed', ?)";
        $track_stmt = mysqli_prepare($conn, $track_query);
        $user_id = $user ? $user['user_id'] : NULL;
        mysqli_stmt_bind_param($track_stmt, 'issss', $user_id, $username, $ip_address, $user_agent, $failure_reason);
        mysqli_stmt_execute($track_stmt);

        $error_message = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Katakala System</title>
    <style>
        :root {
            --primary-color: #cc0000;
            --light-color: #ffcccc;
            --dark-color: #990000;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: var(--primary-color);
            font-size: 28px;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px var(--light-color);
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            width: 100%;
        }

        .btn:hover {
            background-color: var(--dark-color);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .first-login-notice {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Katakala System</h1>
            <p>Enter your credentials to continue</p>
        </div>
        <?php if (isset($_GET['timeout'])): ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i> Your session has expired due to inactivity. Please login again.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['password_changed'])): ?>
            <div class="alert alert-success">
                Password changed successfully! Please log in with your new password.
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="first-login-notice">
            <strong>First time logging in?</strong> You'll be prompted to change your default password.
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="forgot-password">
            <a href="forgot_password.php">Forgot your password?</a>
        </div>
    </div>
</body>
</html>