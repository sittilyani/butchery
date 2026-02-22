<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['full_name'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get user role and info
$userName = $_SESSION['full_name'];
$userRole = $_SESSION['role'] ?? 'User';
$userId = $_SESSION['user_id'] ?? 0;

// Define which roles can access dashboard
$canAccessDashboard = in_array($userRole, ['Admin', 'Supervisor', 'Manager']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Butchery Management System</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/ico" sizes="32x32" href="../assets/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="../assets/favicon/site.webmanifest">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f4f7fc;overflow:hidden}
        .app-wrapper{display:flex;height:100vh;width:100%;overflow:hidden}
        .sidebar{width:280px;background:#CC0000;color:#fff;height:100vh;position:fixed;left:0;top:0;overflow-y:auto;transition:.3s;z-index:1000;box-shadow:4px 0 10px rgba(0,0,0,.1)}
        .sidebar::-webkit-scrollbar{width:6px}
        .sidebar::-webkit-scrollbar-track{background:#334155}
        .sidebar::-webkit-scrollbar-thumb{background:#475569;border-radius:3px}
        .sidebar-header{padding:25px 20px;border-bottom:1px solid #334155;margin-bottom:20px}
        .logo-area{display:flex;align-items:center;gap:12px}
        .logo-icon{width:45px;height:45px;background:#4361ee;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff}
        .logo-text h3{font-size:18px;font-weight:600;margin:0;color:#fff}
        .logo-text p{font-size:12px;color:#94a3b8;margin:3px 0 0}
        .user-info{padding:15px 20px;background:#334155;margin:0 15px 20px;border-radius:12px;display:flex;align-items:center;gap:12px}
        .user-avatar{width:45px;height:45px;background:#4361ee;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff}
        .user-details h4{font-size:14px;font-weight:600;margin:0;color:#fff}
        .user-details span{font-size:11px;color:#94a3b8}
        .nav-menu{padding:0 15px}
        .nav-section{margin-bottom:25px}
        .nav-section-title{font-size:18px; font-weight: 600;text-transform:uppercase;letter-spacing:.5px;color: #80FF80;padding:0 10px;margin-bottom:10px}
        .nav-item{list-style:none;margin-bottom:5px}
        .nav-link{display:flex;align-items:center;gap:12px;padding:12px 15px;color:#cbd5e1;text-decoration:none;border-radius:10px;transition:.3s;font-size:14px}
        .nav-link:hover{background:#334155;color:#fff}
        .nav-link.active{background:#4361ee;color:#fff}
        .main-content{flex:1;margin-left:280px;height:100vh;display:flex;flex-direction:column;background:#f4f7fc}
        .top-navbar{height:70px;background:#fff;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;padding:0 25px;position:sticky;top:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,.03)}
        .menu-toggle{display:none;background:none;border:none;font-size:24px;color:#475569;cursor:pointer}
        .page-title{font-size:20px;font-weight:700;color:#CC0000;margin:0}
        .top-nav-actions{display:flex;align-items:center;gap:20px}
        .notification-btn{background:#f1f5f9;border:none;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#475569;position:relative;cursor:pointer}
        .notification-badge{position:absolute;top:-5px;right:-5px;background:#f72585;color:#fff;font-size:10px;padding:3px 6px;border-radius:30px;min-width:18px;text-align:center}
        .user-dropdown{display:flex;align-items:center;gap:10px;background:#f1f5f9;padding:8px 15px;border-radius:10px;cursor:pointer}
        .user-dropdown .avatar{width:35px;height:35px;background:#4361ee;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600}
        .iframe-container{flex:1;overflow-y:auto;padding:20px 25px;background:#f4f7fc}
        .iframe-container iframe{width:100%;height:100%;border:none;background:#fff;border-radius:15px;box-shadow:0 4px 12px rgba(0,0,0,.03)}
        .dropdown-menu-custom{position:absolute;right:0;top:100%;margin-top:10px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.1);min-width:200px;display:none;z-index:1000}
        .dropdown-menu-custom.show{display:block}
        .dropdown-item{padding:12px 20px;display:flex;align-items:center;gap:10px;color:#1e293b;text-decoration:none}
        .dropdown-item:hover{background:#f1f5f9}
        .dropdown-divider{height:1px;background:#e2e8f0;margin:5px 0}
        .iframe-loading{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;display:none}
        .spinner{width:40px;height:40px;border:3px solid #e2e8f0;border-top-color:#4361ee;border-radius:50%;animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        @media(max-width:992px){
            .sidebar{transform:translateX(-100%)}
            .sidebar.show{transform:translateX(0)}
            .main-content{margin-left:0}
            .menu-toggle{display:block}
        }
        @media(max-width:768px){
            .top-navbar{padding:0 15px}
            .page-title{font-size:18px}
            .user-dropdown .info{display:none}
            .iframe-container{padding:15px}
        }
        @media(max-width:576px){
            .top-nav-actions{gap:10px}
            .notification-btn{width:35px;height:35px}
            .user-dropdown{padding:5px 10px}
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-area">
                    <div class="logo-icon"><i class="bi bi-shop"></i></div>
                    <div class="logo-text">
                        <h3>Butchery Pro</h3>
                        <p>Butchery Management</p>
                    </div>
                </div>
            </div>

            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($userName); ?></h4>
                    <span><?php echo htmlspecialchars($userRole); ?></span>
                </div>
            </div>

            <div class="nav-menu">
                <!-- Main Navigation -->
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <?php if ($canAccessDashboard): ?>
                    <div class="nav-item">
                        <a href="../records/dashboard.php" target="contentFrame" class="nav-link" style="background: #FFFF00; color: #000000;">
                            <i class="fa fa-chart-pie"></i> Dashboard
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="nav-item">
                        <a href="../sales/direct_orders.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-shopping-cart"></i> Quick Sales
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../backup/view_backups.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-database"></i> Backup
                        </a>
                    </div>
                </div>

                <!-- Inventory Section -->
                <div class="nav-section">
                    <div class="nav-section-title">Inventory</div>
                    <div class="nav-item">
                        <a href="../stocks/viewstocks_sum.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-boxes"></i> Stock Management
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../stocks/view_categories.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-pills"></i> Categories
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../views/view_product.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-pills"></i> Products
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../views/view_lowstocks.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-exclamation-triangle"></i> Low Stock
                        </a>
                    </div>
                </div>

                <!-- Sales Section -->
                <div class="nav-section">
                    <div class="nav-section-title">Sales</div>
                    <div class="nav-item">
                        <a href="../sales/view_order.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-receipt"></i> Orders
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../sales/financial_report.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-chart-line"></i> Reports
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../views/view_credit_sales.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-credit-card"></i> Credit Sales
                        </a>
                    </div>
                </div>

                <!-- System Section -->
                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <div class="nav-item">
                        <a href="../users/settings.php" target="contentFrame" class="nav-link">
                            <i class="fa fa-cog"></i> User Settings
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../login/logout.php" class="nav-link">
                            <i class="fa fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="page-title" id="pageTitle">Quick Sales</h1>
                </div>

                <div class="top-nav-actions">
                    <button class="notification-btn">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>

                    <div class="user-dropdown" onclick="toggleUserMenu()">
                        <div class="avatar"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
                        <div class="info">
                            <div class="name"><?php echo htmlspecialchars($userName); ?></div>
                            <div class="role"><?php echo htmlspecialchars($userRole); ?></div>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <!-- User Dropdown Menu -->
                    <div class="dropdown-menu-custom" id="userMenu">
                        <a href="../users/profile.php" target="contentFrame" class="dropdown-item">
                            <i class="bi bi-person"></i> Profile
                        </a>
                        <a href="../users/settings.php" target="contentFrame" class="dropdown-item">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../login/logout.php" class="dropdown-item">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Iframe Content Area -->
            <div class="iframe-container" id="iframeContainer">
                <div class="iframe-loading" id="iframeLoading">
                    <div class="spinner"></div>
                    <p style="margin-top: 10px; color: #64748b;">Loading...</p>
                </div>
                <iframe name="contentFrame" src="../sales/orders.php" id="contentFrame" frameborder="0" onload="hideLoading()"></iframe>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function toggleUserMenu() {
            document.getElementById('userMenu').classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const userDropdown = document.querySelector('.user-dropdown');
            const userMenu = document.getElementById('userMenu');
            if (!userDropdown.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.remove('show');
            }
        });

        function hideLoading() {
            document.getElementById('iframeLoading').style.display = 'none';
        }

        // Show loading when iframe starts loading
        document.getElementById('contentFrame').addEventListener('load', function() {
            hideLoading();
        });

        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                document.getElementById('sidebar').classList.remove('show');
            }
        });

        // Update page title based on iframe content (optional)
        document.getElementById('contentFrame').addEventListener('load', function() {
            try {
                const iframeTitle = this.contentDocument.title;
                if (iframeTitle) {
                    document.getElementById('pageTitle').textContent = iframeTitle;
                }
            } catch(e) {
                // Cross-origin restrictions may prevent accessing title
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>