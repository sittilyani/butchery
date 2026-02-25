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
        *{margin:0;padding:0;box-sizing:border-box}
        body{background: #E3E3E3;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Inter',sans-serif;padding:20px}
        .login-container{width:100%;max-width:450px}
        .login-card{background:rgba(255,255,255,.95);backdrop-filter:blur(10px);border-radius:20px;padding:40px 30px;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:slideUp .5s ease}
        @keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .logo-section{text-align:center;margin-bottom:30px}
        .logo-section img{max-width:120px;margin-bottom:20px}
        .logo-section h1{color:#333;font-size:28px;font-weight:700;margin-bottom:5px}
        .logo-section p{color:#666;font-size:14px}
        .form-group{margin-bottom:20px}
        .input-group{position:relative}
        .input-icon{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#999;z-index:10}
        .form-control{width:100%;padding:15px 15px 15px 45px;border:2px solid #e0e0e0;border-radius:12px;font-size:15px;transition:.3s;background:#fff}
        .form-control:focus{border-color:#667eea;outline:none;box-shadow:0 0 0 3px rgba(102,126,234,.1)}
        .form-label{display:block;margin-bottom:8px;color:#555;font-weight:500;font-size:14px}
        .btn-login{width:100%;padding:15px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;border-radius:12px;color:#fff;font-size:16px;font-weight:600;cursor:pointer;transition:.3s;margin-top:10px}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(102,126,234,.4)}
        .alert{padding:15px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px;animation:shake .5s}
        @keyframes shake{0%,100%{transform:translateX(0)}10%,30%,50%,70%,90%{transform:translateX(-5px)}20%,40%,60%,80%{transform:translateX(5px)}}
        .alert-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
        .password-toggle{position:absolute;right:15px;top:50%;transform:translateY(-50%);color:#999;cursor:pointer;z-index:10}
        .password-toggle:hover{color:#667eea}
        .demo-credentials{margin-top:25px;padding:15px;background:#f8f9fa;border-radius:12px;font-size:13px;color:#666;border:1px dashed #667eea}
        .demo-credentials p{margin:5px 0;display:flex;align-items:center;gap:10px}
        .btn-login.loading{position:relative;color:transparent}
        .btn-login.loading::after{content:'';position:absolute;width:20px;height:20px;top:50%;left:50%;transform:translate(-50%,-50%);border:2px solid #fff;border-radius:50%;border-top-color:transparent;animation:spin .8s linear infinite}
        @keyframes spin{to{transform:translate(-50%,-50%) rotate(360deg)}}
        @media(max-width:480px){.login-card{padding:25px 15px}.form-control{padding:12px 12px 12px 40px}.btn-login{padding:12px}}
        .footer{background:#CC0000;color:#fff;padding:30px 40px 20px;position:fixed;bottom:0;left:0;width:100%;z-index:1000}
        .footer-content{display:grid;grid-template-columns:repeat(3,1fr);gap:40px;width:100%;margin:0 auto}
        .footer-section{display:flex;flex-direction:column;gap:15px}
        .footer-title{color:#fff;font-size:18px;font-weight:600;margin:0 0 10px;padding-bottom:10px;position:relative}
        .footer-title::after{content:'';position:absolute;bottom:0;left:0;width:50px;height:3px;background:#4361ee;border-radius:2px}
        .contact-info{display:flex;flex-direction:row;gap:12px}
        .contact-link{display:flex;align-items:center;gap:12px;color:#cbd5e1;text-decoration:none;padding:8px 12px;border-radius:8px;background:rgba(255,255,255,.05);transition:.3s}
        .contact-link:hover{background:#4361ee;color:#fff;transform:translateX(5px)}
        .social-links{display:flex;gap:12px;flex-wrap:wrap}
        .social-link{width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:18px;position:relative;overflow:hidden;transition:.3s}
        .social-link::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:rgba(255,255,255,.2);transition:.3s}
        .social-link:hover::before{left:0}
        .social-link:hover{transform:translateY(-3px);box-shadow:0 5px 15px rgba(0,0,0,.3)}
        .social-link.facebook{background:#1877f2}
        .social-link.twitter{background:#000}
        .social-link.instagram{background:radial-gradient(circle at 30% 30%,#fdf497,#fd5949,#d6249f,#285AEB)}
        .social-link.linkedin{background:#0077b5}
        .social-link.tiktok{background:#000;position:relative}
        .social-link.tiktok::after{content:'';position:absolute;inset:0;background:linear-gradient(45deg,#25f4ee,#fe2c55);opacity:.7;z-index:0}
        .social-link.tiktok i{position:relative;z-index:1}
        .website-link{display:flex;flex-direction: row; align-items:center;gap:10px;text-decoration:none;padding:10px 15px;background:rgba(255,255,255,.05);border-radius:8px;transition:.3s;font-size:14px}
        .website-link:hover{background:#4361ee;color:#fff;transform:translateY(-2px)}
        .copyright{margin-top:1px;color:#94a3b8;font-size:13px;display:flex;align-items:center;gap:8px;padding-top:15px;}
        .social-link[title]:hover::after{content:attr(title);position:absolute;bottom:-30px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;padding:5px 10px;border-radius:5px;font-size:12px;white-space:nowrap;z-index:10}
        @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}
        .social-link:hover{animation:pulse 1s infinite}
        @media(max-width:992px){.footer-content{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.footer{padding:25px 20px 15px}.footer-content{grid-template-columns:1fr;gap:25px}.footer-section{text-align:center}.footer-title::after{left:50%;transform:translateX(-50%)}.contact-link,.social-links,.website-link,.copyright{justify-content:center}}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <img src="../assets/images/Logo2-rb2.png" alt="Pharmacy Logo">
                <h1>ButcherSys Pro</h1>
                <p>Butchery Management System</p>
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
                        <input type="text" class="form-control" name="username"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               placeholder="Enter username" required>
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

    <div class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="contact-info">
                    <a href="mailto:sittilyani@gmail.com" class="contact-link"><i class="fas fa-envelope"></i> sittilyani@gmail.com</a>
                    <a href="https://wa.me/254722427721" class="contact-link"><i class="fab fa-whatsapp"></i> +254 722 42 77 21</a>
                    <a href="tel:+254722427721" class="contact-link"><i class="fas fa-phone-alt"></i> +254 722 42 77 21</a>
                </div>
            </div>
            <div class="footer-section">

                <div class="social-links">
                    <a href="#" class="social-link facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="social-link instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link linkedin"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link tiktok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">

                <a href="https://the-touch-haven-investments.store" class="website-link" target="_blank">
                    <i class="fas fa-globe"></i> the-touch-haven-investments.store  <i class="far fa-copyright"></i> <?php echo date('Y'); ?> All rights reserved.
                </a>
            </div>
        </div>
    </div>

    <script>
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
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').classList.add('loading');
        });
        document.getElementById('username').focus();
        if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);
    </script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>