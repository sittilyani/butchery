<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['full_name'];
$page_title = 'Change Password';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the new password and confirm password match
    if ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match. Please try again.";
    } else {
        // Fetch the user's current password hash from the database
        $query = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_password_hash);
        $stmt->fetch();
        $stmt->close();

        // Verify the old password
        if (password_verify($old_password, $current_password_hash)) {
            // Hash the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("si", $hashed_new_password, $user_id);

            if ($stmt_update->execute()) {
                $success_message = "Password updated successfully. Redirecting to the dashboard...";
                // Close the connection
                $stmt_update->close();
                $conn->close();

                // Redirect to the dashboard after 3 seconds
                echo "<script>
                    alert('$success_message');
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 1000);
                </script>";
                exit;
            } else {
                $error_message = "Failed to update password. Please try again.";
            }
        } else {
            $error_message = "Old password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
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
            max-width: 800px;
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
            background-color: #66ccff; /* Original light yellow background */
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
        input[type="password"],
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
            background-color: #000099;
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
        <div> <center>
            <?php
            // Display success or error message
            if (isset($success_message)) {
                echo "<p style='color: green;'>$success_message</p>";
            }
            if (isset($error_message)) {
                echo "<p style='color: red;'>$error_message</p>";
            }
            ?> </center>
        </div>
        <h2><?php echo htmlspecialchars($page_title); ?> for <?php echo htmlspecialchars($full_name); ?> </h2>
        <form method="post" action="reset_password.php">
            <div class="form-group">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="custom-submit-btn">Update Password</button>
            </div>
        </form>
    </div>

</body>
</html>
