<?php
session_start();

include '../includes/config.php';

$error_message = ''; // Initialize error message variable

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and collect form data
    if (empty($_POST['username'])) {
        $error_message = "Username is required";
    } elseif (empty($_POST['password'])) {
        $error_message = "Password is required";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Prepare and execute the SQL query to check user credentials
        $sql = "SELECT user_id, password, userrole FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        if ($stmt->errno) {
            die("Error executing query: " . $stmt->error);
        }

        $stmt->store_result();

        // Bind the results
        $stmt->bind_result($user_id, $hashed_password, $userrole);

        if ($stmt->num_rows > 0) {
            // User found, fetch the result
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, store user details in session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['userrole'] = $userrole;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Regenerate session ID for security


                // Redirect based on user role
                switch ($userrole) {
                    case 'Admin':
                    case 'Cashier':
                    case 'Security':
                    case 'Cleaner':
                    case 'Supervisor':
                    case 'Manager':
                    case 'Pharmtech':
                    case 'Pharmacist':
                        header("Location: ../sales/orders.php");
                        exit();
                    default:
                        // Unknown user role, handle accordingly
                        $error_message = "Invalid user role. Please contact support.";
                        break;
                }
            } else {
                // Invalid password
                $error_message = "Invalid credentials. Please try again.";
            }
        } else {
            // User not found
            $error_message = "Invalid credentials. Please try again.";
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
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Favicon link -->
    <link rel="icon" type="image/x-icon" href="../assets/favicon/favicon.ico">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Pharma POS'; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <style>
        *{box-sizing:border-box}
        body{background:linear-gradient(120deg,#330099 0%,#0000ff 100%);background-size:cover;background-position:center;min-height:100vh;margin:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;font-family:sans-serif}
        .main-content-wrapper{display:flex;flex-direction:column;align-items:center;width:100%;max-width:1200px}
        .container-main{width:100%;max-width:450px;padding:30px;text-align:center;background:rgba(255,255,255,0.1);border-radius:15px;backdrop-filter:blur(10px);box-shadow:0 8px 32px rgba(0,0,0,0.1)}
        .logo-container{text-align:center;margin-top:40px;padding:0 20px}
        .logo-container img{max-width:180px;height:auto;width:auto;margin-bottom:30px}
        h1,h2,label{font-weight:bold;color:#FFF;margin-top:5px}
        h1{font-size:clamp(24px,5vw,54px);margin-bottom:15px}
        h2{font-size:clamp(14px,3vw,20px);font-weight:normal}
        .form-control{background:rgba(255,255,255,0.9);border:none;border-radius:8px}
        .form-control:focus{background:rgba(255,255,255,1);box-shadow:0 0 0 0.2rem rgba(255,255,255,0.25)}
        .btn-primary{height:45px;width:100%;background-color:#FFF!important;color:#330099!important;border:none!important;border-radius:8px;font-weight:bold;font-size:16px;transition:all 0.3s ease}
        .btn-primary:hover{background-color:#f0f0f0!important;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.2)}
        .alert{border-radius:8px;font-size:14px}

        @media (max-width:768px){body{padding:15px}
        .container-main{padding:25px 20px;max-width:400px}
        .logo-container{margin-top:30px}
        .logo-container img{max-width:150px;margin-bottom:20px}}

        @media (max-width:480px){body{padding:10px;justify-content:flex-start;padding-top:20px}
        .container-main{padding:20px 15px;max-width:100%}.logo-container{margin-top:20px}
        .logo-container img{max-width:120px;margin-bottom:15px}.btn-primary{height:50px;font-size:18px}
        .form-control{font-size:16px;padding:12px}label{font-size:14px}}

        @media (max-width:320px){.container-main{padding:15px 10px}
        .logo-container img{max-width:100px}}
        @media (max-height:600px) and (orientation:landscape)
        {body{justify-content:flex-start;padding-top:10px}
        .logo-container{margin-top:15px}
        .logo-container img{max-width:100px;margin-bottom:10px}
        h1{font-size:clamp(18px,4vw,32px);margin-bottom:8px}
        h2{font-size:clamp(12px,2.5vw,16px)}}
    </style>
</head>
<body>
    <!-- Main content wrapper to control the vertical flow of form and logo -->
    <div class="main-content-wrapper">
        <div class="container-main">
            <!--Logo container -->
            <img src="../assets/images/Logo-round-nobg-2.png" width="180" height="176" alt="Pharmacy Logo" style="margin-bottom: 30px;">
            <div class="login-form">
                <form method="post" action="">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" name="username" class="form-control form-control-lg" placeholder="Enter username" required/>
                        <label class="form-label" for="form3Example3">User Name</label>
                    </div>

                    <div data-mdb-input-init class="form-outline mb-3">
                        <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter password" required/>
                        <label class="form-label" for="form3Example4">Password</label>
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Logo is now inside the main-content-wrapper, below the form -->
        <?php include '../includes/footer.php'; ?>
        <div class="logo-container">
            <!-- Added alt attribute for accessibility -->
            <!--<img src="../assets/images/bonsanteLogo.png" alt="Company Logo"> -->
            <h2 style="color: white;">PHARMACY POINT OF SALE SYSTEM</h2>
            <p style="color: white;"><i> Dealers in: human medicines, cosmetics, medical supplies and medical Devices </i></p>
        </div>
    </div>

    <!-- Script includes -->
    <script src="../assets/fontawesome-7.1.1/js/all.min.js"></script>
    <link rel="stylesheet" href="../assets/fontawesome-7.1.1/css/all.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>