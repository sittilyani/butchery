<?php
// fetch_credit_details.php
include '../includes/config.php';

header('Content-Type: application/json');

if (isset($_POST['receipt_id'])) {
    $receipt_id = $_POST['receipt_id'];

    // First, get the sales_id from the sales table using receipt_id
    $sql_sales = "SELECT sales_id FROM sales WHERE receipt_id = ? AND payment_status = 'credit'";
    $stmt_sales = $conn->prepare($sql_sales);
    $stmt_sales->bind_param("s", $receipt_id);
    $stmt_sales->execute();
    $result_sales = $stmt_sales->get_result();

    if ($result_sales->num_rows > 0) {
        $sales_row = $result_sales->fetch_assoc();
        $sales_id = $sales_row['sales_id'];

        // Now get the items from sale_items table using sales_id
        $sql_items = "SELECT * FROM sale_items WHERE sales_id = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $sales_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        $items = [];
        while ($row = $result_items->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No credit sale found with this receipt ID.'
        ]);
    }

    $stmt_sales->close();
    if (isset($stmt_items)) {
        $stmt_items->close();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No receipt ID provided.'
    ]);
}

$conn->close();
?>