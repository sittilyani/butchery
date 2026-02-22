<?php
include '../includes/config.php';

// Correctly count all rows in the 'credit_balances' table
$sql = "SELECT COUNT(*) AS total_creditors FROM credit_balances";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Get the count as a whole number.
    $total_creditors = $row['total_creditors'];
} else {
    // If no rows are found, the count is 0.
    $total_creditors = 0;
}

$conn->close();

// Output the total count
echo $total_creditors;
?>