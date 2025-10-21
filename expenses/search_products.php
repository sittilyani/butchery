<?php
include '../includes/config.php';

$search = $_GET['q'] ?? '';

$sql = "SELECT id, productname FROM products WHERE productname LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $search . "%";
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>
