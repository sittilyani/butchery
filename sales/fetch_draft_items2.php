<?php
header('Content-Type: text/html; charset=UTF-8');
include "../includes/config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $sql = "
        SELECT p.id, p.productname, brandname, p.price,
               COALESCE((SELECT stockBalance
                         FROM stocks s
                         WHERE s.productname = p.productname
                         ORDER BY s.transDate DESC
                         LIMIT 1), 0) AS stockBalance
        FROM products p
        WHERE p.currentstatus = 'active'
    ";
    $params = [];
    $types = '';

    if ($id > 0) {
        $sql .= " AND p.id = ?";
        $params[] = $id;
        $types .= 'i';
    }

    if ($search !== '') {
        $sql .= " AND (p.productname OR brandname LIKE ?)";
        $params[] = "%$search%";
        $types .= 's';
    }

    $sql .= " ORDER BY p.brandname";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '';
    while ($row = $result->fetch_assoc()) {
        $stockStatus = $row['stockBalance'] > 0 ? '' : 'disabled';
        $stockMessage = $row['stockBalance'] > 0 ? "In Stock: {$row['stockBalance']}" : 'Out of Stock';
        $html .= '
            <div class="col-12">
                <div class="product-item ' . $stockStatus . '"
                     data-product-id="' . $row['id'] . '"
                     data-product-name="' . htmlspecialchars($row['productname']) . '" &nbsp;&nbsp;
                     data-brand-name="' . htmlspecialchars($row['brandname']) . '" &nbsp;&nbsp;
                     data-product-price="' . $row['price'] . '">  &nbsp;&nbsp;
                  <p>  <h6>' . htmlspecialchars($row['productname']) . '</h6> &nbsp;&nbsp;
                    Price: KES ' . $row['price'] . '
                    ' . $stockMessage . '</p>  &nbsp;&nbsp;
                </div>
            </div>
        ';
    }

    if ($result->num_rows === 0) {
        $html = '<div class="col-12"><p>No products found.</p></div>';
    }

    echo $html;
    $stmt->close();
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    echo '<div class="col-12"><p>Error loading products.</p></div>';
}
$conn->close();
?>