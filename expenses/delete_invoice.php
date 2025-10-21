<?php
include '../includes/config.php';
include '../includes/header.php';

if (isset($_GET['id'])) {
    $invoiceId = intval($_GET['id']); // Correct variable and force it to an integer for safety

    // Delete invoice from the database
    $sqlDeleteinvoice = "DELETE FROM purchase_orders WHERE id = ?";
    $stmtDeleteinvoice = $conn->prepare($sqlDeleteinvoice);

    if ($stmtDeleteinvoice) {
        $stmtDeleteinvoice->bind_param('i', $invoiceId);

        if ($stmtDeleteinvoice->execute()) {
            $_SESSION['success_message'] = "invoice deleted successfully!";
            echo '<script>
                setTimeout(function() {
                    window.location.href = "../expenses/purchase_orders.php";
                }, 3000);
            </script>';
            exit;
        } else {
            $_SESSION['error_message'] = "Error deleting invoice: " . $stmtDeleteinvoice->error;
        }
    } else {
        $_SESSION['error_message'] = "Error preparing delete statement: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "No invoice ID specified.";
    header("Location: ../views/view_invoices.php");
    exit;
}
?>
