// fetch_products.php (example implementation below)
<?php
include "../includes/config.php";

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id > 0) {
    $query = "SELECT id, name, price, photo FROM products WHERE category_id = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    while ($row = $result->fetch_assoc()) {
        $output .= '
            <div class="col-2 mb-3">
                <div class="product-item"
                     data-product-id="' . $row['id'] . '"
                     data-product-name="' . htmlspecialchars($row['name']) . '"
                     data-product-price="' . $row['price'] . '">
                    <img src="../' . htmlspecialchars($row['photo'] ?: 'assets/images/default.jpg') . '"
                         alt="' . htmlspecialchars($row['name']) . '"
                         style="width: 100%; height: 80px; object-fit: cover;">
                    <h6>' . htmlspecialchars($row['name']) . '</h6>
                    <p>KES ' . number_format($row['price'], 2) . '</p>
                </div>
            </div>';
    }
    echo $output;
    $stmt->close();
} else {
    echo '<p>No products found.</p>';
}
?>