<?php
session_start();
include('../includes/config.php');

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header('Location: ../public/login.php');
    exit();
}

// Check if user has permission (Admin only)
if($_SESSION['userrole'] !== 'Admin'){
    $_SESSION['error_msg'] = "You don't have permission to disable users.";
    header('Location: userslist.php');
    exit();
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if($user_id > 0){
    // Admin CAN disable their own account - removed the blocking condition

    // Get current status before updating
    $check_query = "SELECT status FROM users WHERE user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    $current_status = mysqli_fetch_assoc($check_result)['status'];

    // Update status to Inactive
    $query = "UPDATE users SET status = 'Inactive' WHERE user_id = $user_id";

    if(mysqli_query($conn, $query)){
        // Check if this is the admin's own account
        if($user_id == $_SESSION['user_id']){
            $_SESSION['success_msg'] = "Your account has been disabled successfully!";

            // Optional: If admin disables their own account, you might want to log them out
            // session_destroy();
            // header('Location: ../public/login.php');
            // exit();
        } else {
            $_SESSION['success_msg'] = "User disabled successfully!";
        }

        // Log the action for audit trail
        $action = "User ID: $user_id disabled by Admin: " . $_SESSION['user_id'];
        error_log($action);

    } else {
        $_SESSION['error_msg'] = "Error disabling user: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error_msg'] = "Invalid user ID!";
}

header('Location: userslist.php');
exit();
?>