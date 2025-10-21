<?php
header('Content-Type: text/html; charset=UTF-8');
include "../includes/config.php";

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

if (empty($search) && $id === 0) {
    echo '<div class="col-12"><p class="text-muted">Start typing to search for products...</p></div>';
    exit;
}

try {
    $sql = "
        SELECT p.id, p.productname, p.brandname, p.price,
        COALESCE(
            (SELECT s.stockBalance
             FROM stocks s
             WHERE s.id = p.id
               AND s.brandname = p.brandname
               AND s.productname = p.productname
             ORDER BY s.stockID DESC
             LIMIT 1),
            0
        ) AS stockBalance
        FROM products p
        WHERE 1=1
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

    $sql .= " ORDER BY p.brandname ASC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $html = '';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stockStatus = $row['stockBalance'] > 0 ? '' : 'disabled';
            $stockMessage = $row['stockBalance'] > 0 ? "In Stock: {$row['stockBalance']}" : 'Out of Stock';
            $stockClass = $row['stockBalance'] > 0 ? 'text-success' : 'text-danger';

            $html .= '
                <div class="col-12 mb-2">
                    <div class="product-item ' . $stockStatus . ' d-flex justify-content-between align-items-center flex-wrap"
                        data-product-id="' . $row['id'] . '"
                        data-product-name="' . htmlspecialchars($row['productname']) . '"
                        data-brand-name="' . htmlspecialchars($row['brandname']) . '"
                        data-product-price="' . $row['price'] . '"
                        style="cursor: pointer; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <div class="d-flex flex-column text-left flex-grow-1">
                            <h6 class="mb-0"><strong>' . htmlspecialchars($row['brandname']) . '</strong></h6>
                            <p class="mb-0 ' . $stockClass . ' ">' . $stockMessage . '</p>
                            <p class="mb-0">' . htmlspecialchars($row['productname']) . ' - <strong>KES ' . number_format($row['price'], 2) . '</strong></p>
                        </div>
                    </div>
                </div>
            ';
        }
    } else {
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