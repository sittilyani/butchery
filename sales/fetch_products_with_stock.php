<?php
include "../includes/config.php";

if (isset($_GET['all_products'])) {
    // Set JSON content type header
    header('Content-Type: application/json; charset=UTF-8');

    // Query to get the latest record for each productname from stocks with price from products
    $query = "
        SELECT s1.id, s1.productname, p.id, p.price, s1.stockBalance, s1.expiryDate, s1.status
        FROM stocks s1
        INNER JOIN (
            SELECT productname, MAX(transDate) AS maxTransDate, MAX(id) AS maxId
            FROM stocks
            GROUP BY productname
        ) s2 ON s1.productname = s2.productname AND s1.transDate = s2.maxTransDate AND s1.id = s2.maxId
        INNER JOIN products p ON s1.productname = p.productname
    ";

    $result = $conn->query($query);

    // Check for query failure
    if (!$result) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database query failed: ' . $conn->error]);
        exit();
    }

    // Fetch all products
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Free result set
    $result->free();

    // Output JSON
    echo json_encode($products);
    exit();
}

// Return error if 'all_products' parameter is not set
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>