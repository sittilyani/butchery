<?php
session_start();
include '../includes/config.php';

$error_message = '';

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['username'])) {
        $error_message = "Username is required";
    } elseif (empty($_POST['password'])) {
        $error_message = "Password is required";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $sql = "SELECT user_id, password, userrole, first_name, last_name FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $hashed_password, $userrole, $first_name, $last_name);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['userrole'] = $userrole;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $first_name . ' ' . $last_name;
                $_SESSION['role'] = $userrole;
                session_regenerate_id(true);
                header("Location: ../includes/layout.php?page=sales/orders.php");
                exit();
            } else {
                $error_message = "Invalid credentials. Please try again.";
            }
        } else {
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
    <title>Login - Butchery System</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #E3E3E3;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 20px;
            position: relative;
        }

        /* Login Container */
        .login-container {
            width: 100%;
            max-width: 450px;
            margin: auto;
        }

        .login-card {
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            animation: slideUp .5s ease;
            margin-bottom: 20px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section img {
            max-width: 120px;
            margin-bottom: 20px;
        }

        .logo-section h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo-section p {
            color: #666;
            font-size: 14px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: .3s;
            background: #fff;
        }

        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102,126,234,.1);
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: .3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,.4);
        }

        .btn-login.loading {
            position: relative;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake .5s;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            10%,30%,50%,70%,90% { transform: translateX(-5px); }
            20%,40%,60%,80% { transform: translateX(5px); }
        }

        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        /* Footer Styles */
        .footer {
            background: #CC0000;
            color: #fff;
            padding: 30px 20px 20px;
            width: 100%;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .footer-title {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 10px;
            padding-bottom: 10px;
            position: relative;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #4361ee;
            border-radius: 2px;
        }

        /* Contact Links */
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .contact-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(255,255,255,.05);
            transition: .3s;
            word-break: break-word;
        }

        .contact-link:hover {
            background: #4361ee;
            color: #fff;
            transform: translateX(5px);
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            font-size: 18px;
            position: relative;
            overflow: hidden;
            transition: .3s;
        }

        .social-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,.2);
            transition: .3s;
        }

        .social-link:hover::before {
            left: 0;
        }

        .social-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,.3);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%,100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .social-link.facebook { background: #1877f2; }
        .social-link.twitter { background: #000; }
        .social-link.instagram { background: radial-gradient(circle at 30% 30%, #fdf497, #fd5949, #d6249f, #285AEB); }
        .social-link.linkedin { background: #0077b5; }
        .social-link.tiktok {
            background: #000;
            position: relative;
        }
        .social-link.tiktok::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, #25f4ee, #fe2c55);
            opacity: .7;
            z-index: 0;
        }
        .social-link.tiktok i { position: relative; z-index: 1; }

        /* Website Link */
        .visit-us {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .website-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 10px 15px;
            background: rgba(255,255,255,.05);
            border-radius: 8px;
            transition: .3s;
            font-size: 14px;
            color: #cbd5e1;
            word-break: break-word;
        }

        .website-link:hover {
            background: #4361ee;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Copyright */
        .copyright {
            color: #94a3b8;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 10px 0;
            line-height: 1.5;
        }

        .copyright i {
            margin-top: 3px;
        }

        /* Tooltip */
        .social-link[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
                justify-content: flex-start;
            }

            .login-container {
                margin: 20px auto;
            }

            .login-card {
                padding: 30px 20px;
            }

            .footer {
                padding: 25px 15px 15px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .footer-section {
                text-align: center;
                align-items: center;
            }

            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .contact-info {
                align-items: center;
                width: 100%;
                max-width: 350px;
            }

            .contact-link {
                width: 100%;
                justify-content: center;
            }

            .social-links {
                justify-content: center;
            }

            .visit-us {
                align-items: center;
                width: 100%;
                max-width: 350px;
            }

            .website-link {
                width: 100%;
                justify-content: center;
            }

            .copyright {
                justify-content: center;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 25px 15px;
            }

            .form-control {
                padding: 12px 12px 12px 40px;
            }

            .btn-login {
                padding: 12px;
            }

            .footer {
                padding: 20px 10px 10px;
            }

            .social-link {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .contact-link,
            .website-link {
                padding: 8px 12px;
                font-size: 13px;
            }

            .copyright {
                font-size: 12px;
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="../assets/images/Logo2-rb2.png" alt="Butchery Logo">
                <h1>ButcherSys Pro</h1>
                <p>Katakala Butchery & Restaurant</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Username</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" id="username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               placeholder="Enter username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter password" required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer">
        <div class="footer-content">
            <!-- Contact Info Section -->
            <div class="footer-section">
                <h4 class="footer-title">Contact Us</h4>
                <div class="contact-info">
                    <a href="mailto:sittilyani@gmail.com" class="contact-link" target="_blank">
                        <i class="fas fa-envelope"></i>
                        <span>sittilyani@gmail.com</span>
                    </a>
                    <a href="https://wa.me/254722427721" class="contact-link" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                        <span>+254 722 42 77 21</span>
                    </a>
                    <a href="tel:+254722427721" class="contact-link">
                        <i class="fas fa-phone-alt"></i>
                        <span>+254 722 42 77 21</span>
                    </a>
                </div>
            </div>

            <!-- Social Media Section -->
            <div class="footer-section">
                <h4 class="footer-title">Follow Us</h4>
                <div class="social-links">
                    <a href="https://facebook.com/thetouchhaven" class="social-link facebook" target="_blank" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/thetouchhaven" class="social-link twitter" target="_blank" title="Twitter/X">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                    <a href="https://instagram.com/thetouchhaven" class="social-link instagram" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://linkedin.com/company/the-touch-haven" class="social-link linkedin" target="_blank" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://tiktok.com/@thetouchhaven" class="social-link tiktok" target="_blank" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>

            <!-- Website & Copyright Section -->
            <div class="footer-section">
                <h4 class="footer-title">Visit Us</h4>
                <div class="visit-us">
                    <a href="https://the-touch-haven-investments.store" class="website-link" target="_blank">
                        <i class="fas fa-globe"></i>
                        <span>the-touch-haven-investments.store</span>
                    </a>
                    <div class="copyright">
                        <i class="far fa-copyright"></i>
                        <span><?php echo date('Y'); ?> The Touch-Haven Investments.<br>All rights reserved.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Add loading state to login button
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').classList.add('loading');
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>