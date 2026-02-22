<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../includes/config.php';

if (isset($_GET['id'])) {
    $categoryId = intval($_GET['id']);

    // First check if the category is active
    $checkSql = "SELECT status FROM categories WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);

    if ($checkStmt) {
        $checkStmt->bind_param('i', $categoryId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $category = $result->fetch_assoc();

        if ($category) {
            // Check if category is inactive
            if ($category['status'] === 'inactive') {
                $_SESSION['error_message'] = "Cannot delete inactive category. Please activate it first.";
                header("Location: ../views/view_categories.php");
                exit;
            }

            // Category is active, proceed with deletion
            $checkStmt->close();

            // Delete category from the database
            $sqlDeleteCategory = "DELETE FROM categories WHERE id = ?";
            $stmtDeleteCategory = $conn->prepare($sqlDeleteCategory);

            if ($stmtDeleteCategory) {
                $stmtDeleteCategory->bind_param('i', $categoryId);

                if ($stmtDeleteCategory->execute()) {
                    $_SESSION['success_message'] = "Category deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting Category: " . $stmtDeleteCategory->error;
                }
                $stmtDeleteCategory->close();
            } else {
                $_SESSION['error_message'] = "Error preparing delete statement: " . $conn->error;
            }
        } else {
            $_SESSION['error_message'] = "Category not found.";
        }
    } else {
        $_SESSION['error_message'] = "Error checking category status: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "No Category ID specified.";
}

header("Location: ../views/view_categories.php");
exit;
?>