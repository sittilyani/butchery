<?php
ob_start();
session_start();
include '../includes/config.php';

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_SESSION['user_id'];

// Restrict non-admins to their own profile
if ($_SESSION['userrole'] !== 'Admin' && $_SESSION['user_id'] != $user_id) {
    header("Location: ../login/login.php?error=access_denied");
    exit();
}

// Fetch user
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->num_rows > 0 ? $result->fetch_assoc() : null;

if (!$user) {
    header("Location: userslist.php?error=user_not_found");
    exit();
}

// Default photo path
$default_photo = '../assets/images/Logo2-rb2.png';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        header("Location: profile.php?user_id=$user_id");
        exit();
    }

    // Sanitize inputs
    $username       = trim($_POST['username'] ?? '');
    $first_name     = trim($_POST['first_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $sex            = $_POST['sex'] ?? '';
    $mobile         = trim($_POST['mobile'] ?? '');
    $status         = trim($_POST['status'] ?? 'active');
    $userrole       = ($_SESSION['userrole'] === 'Admin') ? ($_POST['userrole'] ?? $user['userrole']) : $user['userrole'];

    // Validation
    $errors = [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($sex)) {
        $errors[] = "Sex is required.";
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode(" ", $errors);
        header("Location: profile.php?user_id=$user_id");
        exit();
    }

    // Check if username already exists (excluding current user)
    $check_sql = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $username, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Username already exists. Please choose another.";
        header("Location: profile.php?user_id=$user_id");
        exit();
    }

    // Check if email already exists (excluding current user)
    $check_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Email already exists. Please use another.";
        header("Location: profile.php?user_id=$user_id");
        exit();
    }

    $photo = $user['photo']; // Keep existing photo by default

    // Handle photo upload (file upload)
    $upload_dir = '../uploads/users/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // File upload handling
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $file = $_FILES['photo'];

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed)) {
            $_SESSION['error_message'] = "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
            header("Location: profile.php?user_id=$user_id");
            exit();
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error_message'] = "File too large. Maximum size is 5MB.";
            header("Location: profile.php?user_id=$user_id");
            exit();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $first_name . '_' . $last_name) . "_{$user_id}_" . date('Ymd_His') . ".{$ext}";
        $filepath = $upload_dir . $filename;

        // Delete old photo if it exists and is a file
        if (!empty($photo) && file_exists($upload_dir . $photo) && !is_dir($upload_dir . $photo)) {
            unlink($upload_dir . $photo);
        }

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $photo = $filename; // Store filename in database
        } else {
            $_SESSION['error_message'] = "Failed to upload photo. Please check directory permissions.";
            header("Location: profile.php?user_id=$user_id");
            exit();
        }
    }
    // Handle webcam capture (base64)
    elseif (!empty($_POST['webcam_photo'])) {
        $data = $_POST['webcam_photo'];
        if (preg_match('/^data:image\/(jpeg|png);base64,/', $data)) {
            $data = preg_replace('/^data:image\/(jpeg|png);base64,/', '', $data);
            $data = str_replace(' ', '+', $data);
            $image = base64_decode($data);

            if ($image !== false) {
                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $first_name . '_' . $last_name) . "_{$user_id}_" . date('Ymd_His') . ".jpg";
                $filepath = $upload_dir . $filename;

                // Delete old photo if it exists
                if (!empty($photo) && file_exists($upload_dir . $photo) && !is_dir($upload_dir . $photo)) {
                    unlink($upload_dir . $photo);
                }

                if (file_put_contents($filepath, $image)) {
                    $photo = $filename;
                } else {
                    $_SESSION['error_message'] = "Failed to save webcam photo.";
                    header("Location: profile.php?user_id=$user_id");
                    exit();
                }
            }
        }
    }

    // Update database
    $sql = "UPDATE users SET
            username = ?,
            first_name = ?,
            last_name = ?,
            email = ?,
            sex = ?,
            mobile = ?,
            status = ?,
            userrole = ?,
            photo = ?
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error_message'] = "Database error: " . $conn->error;
        header("Location: profile.php?user_id=$user_id");
        exit();
    }

    $stmt->bind_param(
        "sssssssssi",
        $username,
        $first_name,
        $last_name,
        $email,
        $sex,
        $mobile,
        $status,
        $userrole,
        $photo,
        $user_id
    );

    if ($stmt->execute()) {
        // Update session if it's the logged-in user
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['full_name'] = $first_name . ' ' . $last_name;
            $_SESSION['username'] = $username;
            $_SESSION['userrole'] = $userrole;
        }

        $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
        $_SESSION['error_message'] = "Update failed: " . $stmt->error;
    }

    $stmt->close();
    header("Location: profile.php?user_id=$user_id");
    exit();
}

// Get user data again after potential update
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get photo for display (handle BLOB or file path)
$photo_display = '../assets/images/Logo2-rb2.png'; // Default

if (!empty($user['photo'])) {
    if (is_string($user['photo']) && file_exists('../uploads/users/' . $user['photo'])) {
        // It's a file path
        $photo_display = '../uploads/users/' . $user['photo'];
    } elseif (strlen($user['photo']) > 100) {
        // It might be BLOB data - use display_photo.php
        $photo_display = 'display_photo.php?user_id=' . $user_id;
    }
}

$conn->close();
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - <?= htmlspecialchars($user['username'] ?? '') ?></title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #000099;
            --primary-hover: #0000cc;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --border: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
          
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .profile-card {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-header {
            background: var(--primary);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(to bottom right, transparent 50%, white 50%);
        }

        .photo-container {
            width: 150px;
            height: 150px;
            margin: 0 auto 15px;
            position: relative;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            background: white;
            transition: transform 0.3s ease;
        }

        .profile-photo:hover {
            transform: scale(1.05);
        }

        .photo-edit-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-edit-badge:hover {
            background: var(--primary-hover);
            transform: scale(1.1);
        }

        .form-container {
            padding: 40px;
            background: #99ff99;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
            font-size: 0.95em;
        }

        label i {
            margin-right: 8px;
            color: var(--primary);
            width: 20px;
        }

        .required::after {
            content: "*";
            color: var(--danger);
            margin-left: 4px;
        }

        .form-control, select {
            padding: 12px 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 0, 153, 0.1);
            outline: none;
        }

        .readonly {
            background: #e9ecef;
            cursor: not-allowed;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 15px 25px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 153, 0.3);
        }

        .btn-submit i {
            transition: transform 0.3s ease;
        }

        .btn-submit:hover i {
            transform: translateX(5px);
        }

        .btn-submit:disabled {
            background: var(--secondary);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .webcam-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .webcam-preview {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            border: 2px dashed var(--border);
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #video, #canvas, #preview {
            width: 100%;
            max-height: 200px;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .btn-webcam {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-webcam:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease-out;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                margin: 10px;
            }

            .form-container {
                padding: 20px;
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .text-danger {
            color: var(--danger);
        }

        .d-flex {
            display: flex;
        }

        .gap-2 {
            gap: 10px;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .mt-1 {
            margin-top: 5px;
        }

        .mt-4 {
            margin-top: 20px;
        }

        .text-center {
            text-align: center;
        }

        .text-decoration-none {
            text-decoration: none;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

<div class="profile-card">
    <div class="profile-header">
        <div class="photo-container">
            <img src="<?= $photo_display ?>" alt="Profile Photo" class="profile-photo" id="profile-photo" onerror="this.src='<?= $default_photo ?>'">
            <div class="photo-edit-badge" onclick="document.getElementById('photo').click()">
                <i class="fas fa-camera"></i>
            </div>
        </div>
        <h3><i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['username']) ?></h3>
        <p><span class="badge"><?= htmlspecialchars($user['userrole']) ?></span></p>
    </div>

    <div class="form-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="profileForm" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="webcam_photo" id="webcam_photo">

            <div class="form-grid">
                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="required">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username"
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>" required maxlength="50">
                </div>

                <!-- First Name -->
                <div class="form-group">
                    <label for="first_name" class="required">
                        <i class="fas fa-id-card"></i> First Name
                    </label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                           value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required maxlength="50">
                </div>

                <!-- Last Name -->
                <div class="form-group">
                    <label for="last_name" class="required">
                        <i class="fas fa-id-card"></i> Last Name
                    </label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                           value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required maxlength="50">
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="required">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required maxlength="100">
                </div>

                <!-- Sex -->
                <div class="form-group">
                    <label for="sex" class="required">
                        <i class="fas fa-venus-mars"></i> Sex
                    </label>
                    <select class="form-control" id="sex" name="sex" required>
                        <option value="">-- Select --</option>
                        <option value="Male" <?= ($user['sex'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($user['sex'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <!-- Mobile -->
                <div class="form-group">
                    <label for="mobile">
                        <i class="fas fa-phone"></i> Mobile
                    </label>
                    <input type="text" class="form-control" id="mobile" name="mobile"
                           value="<?= htmlspecialchars($user['mobile'] ?? '') ?>" maxlength="15">
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select class="form-control" id="status" name="status">
                        <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <!-- User Role (Admin Only) -->
                <?php if ($_SESSION['userrole'] === 'Admin'): ?>
                <div class="form-group">
                    <label for="userrole">
                        <i class="fas fa-user-tag"></i> User Role
                    </label>
                    <select class="form-control" id="userrole" name="userrole">
                        <?php
                        $roles = ['User', 'Admin', 'Manager', 'Staff'];
                        foreach ($roles as $role) {
                            $selected = ($user['userrole'] ?? '') === $role ? 'selected' : '';
                            echo "<option value='$role' $selected>$role</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php else: ?>
                    <input type="hidden" name="userrole" value="<?= htmlspecialchars($user['userrole']) ?>">
                <?php endif; ?>

                <!-- Photo Upload -->
                <div class="form-group">
                    <label for="photo">
                        <i class="fas fa-camera"></i> Upload Photo
                    </label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    <small class="text-muted mt-1"><i class="fas fa-info-circle"></i> Max 5MB. JPEG/PNG/GIF</small>
                </div>

                <!-- Webcam -->
                <div class="form-group webcam-container full-width">
                    <label>
                        <i class="fas fa-camera-retro"></i> Or Take Photo
                    </label>
                    <div class="webcam-preview">
                        <video id="video" autoplay style="display:none;"></video>
                        <canvas id="canvas" style="display:none;"></canvas>
                        <img id="preview" style="display:none;">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" id="start-webcam" class="btn-webcam flex-grow-1">
                            <i class="fas fa-video"></i> Start Webcam
                        </button>
                        <button type="button" id="capture-btn" class="btn-webcam flex-grow-1" style="display:none;">
                            <i class="fas fa-camera"></i> Capture
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group full-width">
                    <button type="submit" name="submit" class="btn-submit" id="submit-btn">
                        <i class="fas fa-save"></i> Update Profile
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="../views/userslist.php" class="text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back to Users List
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script>
function validateForm() {
    const username = document.getElementById('username').value.trim();
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const sex = document.getElementById('sex').value;
    const submitBtn = document.getElementById('submit-btn');

    if (!username || !firstName || !lastName || !email || !sex) {
        alert('Please fill in all required fields.');
        return false;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return false;
    }

    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading"></span> Updating...';

    return true;
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Webcam functionality
$(document).ready(function() {
    const video = $('#video')[0];
    const canvas = $('#canvas')[0];
    const preview = $('#preview')[0];
    const startBtn = $('#start-webcam');
    const captureBtn = $('#capture-btn');
    const webcamInput = $('#webcam_photo');

    startBtn.click(async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            video.style.display = 'block';
            preview.style.display = 'none';
            captureBtn.show();
            startBtn.hide();
        } catch (e) {
            alert('Webcam access denied. Please allow camera access or use file upload.');
        }
    });

    captureBtn.click(() => {
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        preview.src = dataUrl;
        preview.style.display = 'block';
        webcamInput.val(dataUrl);
        video.style.display = 'none';
        captureBtn.hide();
        startBtn.show();

        // Stop video stream
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
    });

    // Preview uploaded image
    $('#photo').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profile-photo').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>

</body>
</html>