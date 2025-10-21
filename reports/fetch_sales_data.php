<?php
header('Content-Type: application/json');
include "../includes/config.php";

// Get selected month (1-12) or default to current month
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$current_year = (int)date('Y');

try {
    // Fetch daily sales for the selected month
    $daily_stmt = $conn->prepare("
        SELECT DATE(transDate) AS sale_date, SUM(grand_total) AS total_sales
        FROM sales
        WHERE MONTH(transDate) = ? AND YEAR(transDate) = ?
        GROUP BY DATE(transDate)
        ORDER BY sale_date
    ");
    $daily_stmt->bind_param("ii", $selected_month, $current_year);
    $daily_stmt->execute();
    $daily_result = $daily_stmt->get_result();
    $daily_sales = [];
    while ($row = $daily_result->fetch_assoc()) {
        $daily_sales[] = [
            'date' => $row['sale_date'],
            'total' => (float)$row['total_sales']
        ];
    }
    $daily_stmt->close();

    // Fetch monthly sales for the current year
    $monthly_stmt = $conn->prepare("
        SELECT MONTH(transDate) AS sale_month, SUM(grand_total) AS total_sales
        FROM sales
        WHERE YEAR(transDate) = ?
        GROUP BY MONTH(transDate)
        ORDER BY sale_month
    ");
    $monthly_stmt->bind_param("i", $current_year);
    $monthly_stmt->execute();
    $monthly_result = $monthly_stmt->get_result();
    $monthly_sales = array_fill(1, 12, 0); // Initialize array for all months
    while ($row = $monthly_result->fetch_assoc()) {
        $monthly_sales[$row['sale_month']] = (float)$row['total_sales'];
    }
    $monthly_stmt->close();

    // Fetch daily sales by payment method for the selected month
    $payment_stmt = $conn->prepare("
        SELECT DATE(transDate) AS sale_date, payment_method, SUM(grand_total) AS total_sales
        FROM sales
        WHERE MONTH(transDate) = ? AND YEAR(transDate) = ?
        GROUP BY DATE(transDate), payment_method
        ORDER BY sale_date, payment_method
    ");
    $payment_stmt->bind_param("ii", $selected_month, $current_year);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payment_sales = [];
    while ($row = $payment_result->fetch_assoc()) {
        $payment_sales[] = [
            'date' => $row['sale_date'],
            'payment_method' => $row['payment_method'],
            'total' => (float)$row['total_sales']
        ];
    }
    $payment_stmt->close();

    echo json_encode([
        'status' => 'success',
        'daily_sales' => $daily_sales,
        'monthly_sales' => array_values($monthly_sales), // Convert to 0-based array
        'payment_sales' => $payment_sales
    ]);
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
$conn->close();
?>