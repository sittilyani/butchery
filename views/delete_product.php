<?php
include '../includes/config.php';
include '../includes/header.php';

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']); // Correct variable and force it to an integer for safety

    // Delete product from the database
    $sqlDeleteproduct = "DELETE FROM products WHERE id = ?";
    $stmtDeleteproduct = $conn->prepare($sqlDeleteproduct);

    if ($stmtDeleteproduct) {
        $stmtDeleteproduct->bind_param('i', $productId);

        if ($stmtDeleteproduct->execute()) {
            $_SESSION['success_message'] = "product deleted successfully!";
            echo '<script>
                setTimeout(function() {
                    window.location.href = "../views/view_product.php";
                }, 1000);
            </script>';
            exit;
        } else {
            $_SESSION['error_message'] = "Error deleting product: " . $stmtDeleteproduct->error;
        }
    } else {
        $_SESSION['error_message'] = "Error preparing delete statement: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "No product ID specified.";
    header("Location: ../views/view_products.php");
    exit;
}
?>
