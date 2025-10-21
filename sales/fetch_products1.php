<?php
header('Content-Type: text/html; charset=UTF-8');
include "../includes/config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $sql = "
        SELECT p.id, p.productname, p.brandname, p.price,
               COALESCE((SELECT stockBalance
                         FROM stocks s
                         WHERE s.brandname = p.brandname
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
        $sql .= " AND (p.productname LIKE ? OR p.brandname LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= 'ss';
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
        $stockClass = $row['stockBalance'] > 0 ? 'text-success' : 'text-danger';

        $html .= '
            <div class="col-12">
                <div class="product-item ' . $stockStatus . '"
                     data-product-id="' . $row['id'] . '"
                     data-product-name="' . htmlspecialchars($row['productname']) . '"
                     data-brand-name="' . htmlspecialchars($row['brandname']) . '"
                     data-product-price="' . $row['price'] . '"
                     style="cursor: pointer; padding: 10px; border: 1px solid #ddd; margin-bottom: 5px; border-radius: 5px;">
                    <h6 class="mb-2">' . htmlspecialchars($row['productname']) . '</h6>   &nbsp;&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;&nbsp;
                    <p class="mb-1"><strong>Brand:</strong> ' . htmlspecialchars($row['brandname']) . '</p> &nbsp;&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;&nbsp;
                    <p class="mb-1"><strong>Price:</strong> KES ' . number_format($row['price'], 2) . '</p> &nbsp;&nbsp;&nbsp;&nbsp;||&nbsp;&nbsp;&nbsp;&nbsp;
                    <p class="mb-0 ' . $stockClass . '"><strong>' . $stockMessage . '</strong></p>
                </div>
            </div>
        ';
    }

    if ($result->num_rows === 0) {
        $html = '<div class="col-12"><p class="text-muted">No products found matching your search.</p></div>';
    }

    echo $html;
    $stmt->close();

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    echo '<div class="col-12"><p class="text-danger">Error loading products. Please try again.</p></div>';
}

$conn->close();
?>