<?php
ob_start();
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: ../public/login.php");
    exit;
}

include('../includes/config.php');
include('../includes/header.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = "User Registration";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid CSRF token";
        header("Location: user_registration.php");
        exit;
    }

    // Sanitize and validate input
    $staff_number = mysqli_real_escape_string($conn, $_POST['staff_number'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name'] ?? '');
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name'] ?? '');
    $userrole = mysqli_real_escape_string($conn, $_POST['userrole'] ?? '');

    // Validate required fields
    if (empty($staff_number) || empty($username) || empty($first_name) || empty($last_name)) {
        $_SESSION['error_message'] = "All fields are required";
        header("Location: user_registration.php");
        exit;
    }

    // Set default password
    $default_password = '123456';
    $hashed_password = password_hash($default_password, PASSWORD_BCRYPT);

    // Prepare and execute SQL statement
    $sql = "INSERT INTO users (staff_number, username, first_name, last_name, userrole)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $staff_number, $username, $first_name, $last_name, $userrole);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User added successfully. Default password is 123456 - please change it after login.";
        header("Location: ../views/userslist.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Registration failed: " . $stmt->error;
        header("Location: user_registration.php");
        exit();
    }

    $stmt->close();
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../iorpms/assets/css/forms.css" type="text/css">
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
            max-width: 700px;
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
            grid-template-columns: repeat(2, 1fr); /* Three equal columns */
            gap: 25px; /* Spacing between columns and rows */
            padding: 20px;
            background-color: #66ff00; /* Original light yellow background */
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
                 <div><?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                </div>
                    <h2><?php echo htmlspecialchars($page_title); ?></h2>
                <form method="post" action="user_registration.php">
                    <div class="form-group">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="password" name="password" value="123456" hidden>

                        <label for="staff_number">Staff Number</label>
                        <input type="text" class="form-control" id="staff_number" name="staff_number" required>
                    </div>
                    <div class="form-group">
                        <label for="username">User Name</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="userrole">User Role</label>
                            <select class="form-control" id="userrole" name="userrole" required>
                                    <?php
                                    $result = $conn->query("SELECT id, role FROM userroles");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='".htmlspecialchars($row['role'])."'>".htmlspecialchars($row['role'])."</option>";
                                    }
                                    ?>
                            </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="custom-submit-btn">Register New User</button>
                    </div>
            </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>