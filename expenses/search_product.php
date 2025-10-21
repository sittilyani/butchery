<?php
include '../includes/config.php';

if (isset($_POST['query'])) {
    $search = $conn->real_escape_string($_POST['query']);
    $sql = "SELECT id, productname, unit_price FROM products WHERE productname LIKE '$search%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<div class="suggestion-item" data-id="'.$row['id'].'" data-price="'.$row['unit_price'].'">'.$row['productname'].'</div>';
        }
    } else {
        echo '<div class="suggestion-item">No product found</div>';
    }
}
?>
