<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Initialize $user to an empty array to avoid warnings if no user is found
$user = [];

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $sql = "SELECT * FROM users WHERE user_id = ?";
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
    header("Location: userslist.php?error=user_id_missing");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $userrole = $_POST['userrole'];

    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, sex = ?, mobile = ?, userrole = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssi', $first_name, $last_name, $email, $gender, $mobile, $userrole, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: userslist.php?success=user_updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - <?php echo htmlspecialchars($user['first_name'] ?? 'Unknown'); ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        
        .main-content { margin: 20px auto; max-width: 800px; padding: 0 15px; }
        .views { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0, 0, 153, 0.3); padding: 40px; margin: 20px auto; }
        .views h2 { color: #000099; font-weight: bold; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #000099; text-align: center; }
        .user-info-card {  background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%); border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0, 0, 153, 0.1); }
        .info-row { display: flex;   padding: 15px 0;  border-bottom: 1px solid #e8e8ff; transition: background 0.3s ease; }
        .info-row:last-child {  border-bottom: none; }
        .info-row:hover {  background: rgba(0, 0, 153, 0.05); border-radius: 5px;  padding-left: 10px; }
        .info-label { font-weight: bold; color: #000099; width: 150px; flex-shrink: 0; }
        .info-value {  color: #333; flex: 1; }
        .btn-back {  background: #000099; color: white; padding: 12px 30px;  border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 30px; font-weight: bold;transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0, 0, 153, 0.3);}
        .btn-back:hover {background: #0000cc;color: white; text-decoration: none;transform: translateY(-2px);  box-shadow: 0 6px 20px rgba(0, 0, 153, 0.4);}
        .no-user {  text-align: center; padding: 40px;  color: #666;font-size: 18px; }

        @media (max-width: 768px) {
            .views {padding: 25px 20px;}
            .info-row {flex-direction: column;}
            .info-label { width: 100%; margin-bottom: 5px;}
            .user-info-card { padding: 20px; }
        }

        @media (max-width: 480px)
            { .views {   padding: 20px 15px;   border-radius: 10px;  }
            .views h2 { font-size: 24px; }
            .btn-back { width: 100%;text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="views">
            <h2>User Details</h2>

            <?php if (!empty($user)): ?>
   <div class="user-info-card">
       <div class="info-row">
           <span class="info-label">User ID:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['user_id']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">First Name:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['first_name']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">Last Name:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['last_name']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">Email:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">Gender:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['sex']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">Mobile:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['mobile']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">User Role:</span>
           <span class="info-value"><?php echo htmlspecialchars($user['userrole']); ?></span>
       </div>

       <div class="info-row">
           <span class="info-label">Date Created:</span>
           <span class="info-value"><?php echo htmlspecialchars(date('F j, Y - g:i A', strtotime($user['date_created']))); ?></span>
       </div>
   </div>

   <div style="text-align: center;">
       <a href="userslist.php" class="btn-back">? Back to User List</a>
   </div>
            <?php else: ?>
   <div class="no-user">
       <p>User not found.</p>
       <a href="userslist.php" class="btn-back">? Back to User List</a>
   </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>