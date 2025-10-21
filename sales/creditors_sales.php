<?php
// Establish connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "pharmacy");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate total creditors amount (sum of all outstanding balances)
$sql = "SELECT SUM(balance_amount) AS total_creditors FROM credit_balances WHERE balance_amount > 0";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_creditors = $row['total_creditors'] ? number_format($row['total_creditors'], 2) : '0.00';
} else {
    $total_creditors = '0.00';
}

$conn->close();

// Output the total creditors amount
echo $total_creditors;
?>