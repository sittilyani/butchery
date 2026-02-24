<?php
include '../includes/config.php';

// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

// Build the base query
$whereClause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $whereClause = "WHERE productname LIKE ? OR brandname LIKE ? OR id LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $search];
    $types = 'sss';
}

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM products " . $whereClause;
$countStmt = $conn->prepare($countSql);
if (!empty($search)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
$countStmt->close();

// Fetch products with pagination
$sql = "SELECT * FROM products " . $whereClause . " ORDER BY date_created DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products List</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #dee2e6;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            padding: 30px;
            max-width: 100%;
            margin: 0 auto;
        }

        .page-header {
            background: #cc0000;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }

        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-container {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 25px;
            font-size: 1rem;

            background-color: white;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #657786;
        }

        .loading-spinner {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }

        .add-product-btn {
            background: #000099;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;

            text-decoration: none;
            font-size: 1rem;
        }

        .add-category-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;

            text-decoration: none;
            font-size: 1rem;
        }

        .add-product-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .add-product-btn i {
            margin-right: 8px;
        }

        .products-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.9rem;
        }

        thead {
            background: #cc0000;
            color: white;
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
            transform: translateX(5px);
        }

        tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-right: 5px;
            margin-bottom: 5px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }

        .btn i {
            margin-right: 5px;
            font-size: 0.8rem;
        }

        .btn-update {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-update:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-view {
            background-color: var(--success-color);
            color: white;
        }

        .btn-view:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-active {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .status-inactive {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background-color: #f8f9fa;
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            color: #657786;
            font-size: 0.9rem;
        }

        .pagination-controls {
            display: flex;
            gap: 10px;
        }

        .pagination-btn {
            padding: 10px 15px;
            border: 1px solid #e1e8ed;
            background-color: white;
            color: #657786;
            border-radius: 8px;
            cursor: pointer;

            text-decoration: none;
            font-size: 0.9rem;
        }

        .pagination-btn:hover:not(.disabled) {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #657786;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #e1e8ed;
        }

        .no-results h3 {
            margin-bottom: 10px;
            color: #14171a;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }

            .page-header {
                padding: 20px;
                text-align: center;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                max-width: none;
            }

            .table-container {
                font-size: 0.8rem;
            }

            th, td {
                padding: 10px 8px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .pagination-container {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        /* Loading and animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-row {
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner {
            animation: spin 1s linear infinite;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Total Products</h1>
        </div>

        <div class="controls-section">
            <div class="search-container">
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Search products by name, description, or ID..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    autocomplete="off"
                >
                <i class="far fa-search search-icon"></i>
                <i class="fas fa-spinner loading-spinner spinner"></i>
            </div>

            <a href="../stocks/products.php" class="add-product-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <div class="products-container">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Product Name</th>
                            <th>Brand Name</th>
                            <th>Pack Size</th>
                            <th>Pack Price</th>
                            <th>Unit Price</th>
                            <th>Selling Price</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="10">
                                    <div class="no-results">
                                        <i class="fas fa-search"></i>
                                        <h3>No Products Found</h3>
                                        <p>
                                            <?php echo !empty($search) ? "No products match your search criteria." : "No products available. Add your first product to get started."; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $index => $product): ?>
                                <tr class="table-row" style="animation-delay: <?php echo $index * 0.05; ?>s">
                                    <td><strong>#<?php echo htmlspecialchars($product['id']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($product['category']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($product['productname']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($product['brandname']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($product['packsize']); ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($product['pack_price']); ?></strong></td>
                                    <td>KES <?php echo number_format($product['unit_price'], 2); ?></td>
                                    <td><strong>KES <?php echo number_format($product['price'], 2); ?></strong></td>
                                    <td><?php echo htmlspecialchars($product['reorder_level']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $product['currentstatus'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo htmlspecialchars($product['currentstatus']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($product['date_created'])); ?></td>
                                    <td>
                                        <div class="action-buttons">

                                            <button class="btn btn-update" onclick="location.href='../views/update_product.php?id=<?php echo $product['id']; ?>'"> Edit
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-delete" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo addslashes($product['productname']); ?>')"> Delete
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($products)): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $limit, $totalRows); ?> of <?php echo $totalRows; ?> products
                        <?php if (!empty($search)): ?>
                            for "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                    </div>

                    <div class="pagination-controls">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php else: ?>
                            <span class="pagination-btn disabled">
                                <i class="fas fa-chevron-left"></i> Previous
                            </span>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                               class="pagination-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="pagination-btn disabled">
                                Next <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            let searchTimeout;

            // Real-time search functionality
            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val().trim();

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Show loading spinner for searches with 3+ characters
                if (searchTerm.length >= 3) {
                    $('.search-icon').hide();
                    $('.loading-spinner').show();
                }

                // Set new timeout
                searchTimeout = setTimeout(function() {
                    if (searchTerm.length >= 3) {
                        // Perform search
                        window.location.href = `?search=${encodeURIComponent(searchTerm)}&page=1`;
                    } else if (searchTerm.length === 0) {
                        // Clear search
                        window.location.href = window.location.pathname;
                    }
                }, 500); // 500ms delay
            });

            // Hide loading spinner when search completes
            $(window).on('load', function() {
                $('.loading-spinner').hide();
                $('.search-icon').show();
            });
        });

        // Confirm delete function
        function confirmDelete(productId, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"?\n\nThis action cannot be undone.`)) {
                // You can add loading state here
                window.location.href = `../views/delete_product.php?id=${productId}`;
            }
        }

        // Add smooth scrolling for pagination
        $('.pagination-btn').on('click', function(e) {
            if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                // Add loading state or smooth transition here if needed
                $('body').addClass('loading');
            }
        });
    </script>
</body>
</html>