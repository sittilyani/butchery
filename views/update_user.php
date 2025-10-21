<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php'; // Ensure this doesn't output conflicting HTML
// Remove footer.php include if it outputs HTML prematurely

// Initialize $user to an empty array to avoid warnings if no user is found
$user = [];

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT user_id, first_name, last_name, email, sex, mobile, userrole, staff_number, date_created, username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
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
    header("Location: userslist.php?=?");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $sex = trim($_POST['sex']);
    $mobile = trim($_POST['mobile']);
    $userrole = trim($_POST['userrole']);

    // Basic validation
    if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || empty($sex) || empty($mobile) || empty($userrole)) {
        $error = "All fields are required.";
    } else {
        $sql = "UPDATE users SET username =?, first_name = ?, last_name = ?, email = ?, sex = ?, mobile = ?, userrole = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssi', $username, $first_name, $last_name, $email, $sex, $mobile, $userrole, $user_id);

        if ($stmt->execute()) {
            header("Location: userslist.php?success=user_updated");
            exit();
        } else {
            $error = "Failed to update user. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
   <style>
        /* CSS styles are unchanged and remain the same */
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

        body {
            background-color: var(--background-light);
            font-family: var(--font-family);
            color: var(--text-color);
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
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 20px;
            background-color: #66ccff;
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
            box-sizing: border-box;
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

        /* Responsive adjustments */
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
        <div class="form-container">
            <h2>Edit User</h2>

            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <?php if (!empty($user)): ?>
                <form method="POST" action="">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">

                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="sex" class="form-label">Sex</label>
                        <select class="form-control" id="sex" name="sex" required>
                            <option value="Male" <?php echo $user['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $user['sex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="userrole" class="form-label">User Role</label>
                        <select class="form-control" id="userrole" name="userrole" required>
                            <option value="Admin" <?php echo $user['userrole'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="Cashier" <?php echo $user['userrole'] == 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                            <option value="Security" <?php echo $user['userrole'] == 'Security' ? 'selected' : ''; ?>>Security</option>
                            <option value="Supervisor" <?php echo $user['userrole'] == 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                            <option value="Manager" <?php echo $user['userrole'] == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="staff_number" class="form-label">Staff Number</label>
                        <input type="text" class="form-control" id="staff_number" name="staff_number" value="<?php echo htmlspecialchars($user['staff_number']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_created" class="form-label">Date Created</label>
                        <input type="text" class="form-control" id="date_created" value="<?php echo htmlspecialchars($user['date_created']); ?>" readonly>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">Update User</button>
                    <a href="userslist.php" class="btn btn-secondary">Back to User List</a>
                </form>
            <?php else: ?>
                <p>User not found.</p>
                <a href="userslist.php" class="btn btn-secondary">Back to User List</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; // Include footer at the end ?>
</body>
</html>