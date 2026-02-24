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
    $_SESSION['error_msg'] = "You don't have permission to enable users.";
    header('Location: userslist.php');
    exit();
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if($user_id > 0){
    // Admin CAN enable their own account - removed the blocking condition

    // Get current status before updating
    $check_query = "SELECT status FROM users WHERE user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    $current_status = mysqli_fetch_assoc($check_result)['status'];

    // Update status to Active
    $query = "UPDATE users SET status = 'Active' WHERE user_id = $user_id";

    if(mysqli_query($conn, $query)){
        // Check if this is the admin's own account
        if($user_id == $_SESSION['user_id']){
            $_SESSION['success_msg'] = "Your account has been enabled successfully!";

            // Optional: Update session status if you store it
            // $_SESSION['status'] = 'Active';
        } else {
            $_SESSION['success_msg'] = "User enabled successfully!";
        }

        // Log the action for audit trail
        $action = "User ID: $user_id enabled by Admin: " . $_SESSION['user_id'];
        error_log($action);

    } else {
        $_SESSION['error_msg'] = "Error enabling user: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error_msg'] = "Invalid user ID!";
}

header('Location: userslist.php');
exit();
?>