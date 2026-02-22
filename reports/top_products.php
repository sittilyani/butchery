<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../includes/config.php";
include "../includes/header.php";

$page_title = "Top Products Report";

// Get years for filter
$years_query = "SELECT DISTINCT YEAR(transDate) as year FROM sales
                UNION
                SELECT DISTINCT YEAR(sales_date) as year FROM sale_items
                ORDER BY year DESC";
$years_result = $conn->query($years_query);
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['year'];
}
if (empty($years)) {
    $years[] = date('Y');
}

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : $years[0];
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'revenue';

// Get categories for filter
$categories_query = "SELECT id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// Build WHERE clause
$where = "WHERE YEAR(s.transDate) = $selected_year";
if ($selected_month > 0) {
    $where .= " AND MONTH(s.transDate) = $selected_month";
}
if ($selected_category > 0) {
    $where .= " AND p.category = $selected_category";
}

// Determine sort column
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'revenue':
        $order_by .= "total_revenue DESC";
        break;
    case 'profit':
        $order_by .= "total_profit DESC";
        break;
    case 'quantity':
        $order_by .= "total_quantity DESC";
        break;
    case 'margin':
        $order_by .= "profit_margin DESC";
        break;
    default:
        $order_by .= "total_revenue DESC";
}

// Get product statistics
$products_query = "SELECT
    p.id,
    p.brandname,
    p.productname,
    c.name as category_name,
    COALESCE(SUM(si.quantity), 0) as total_quantity,
    COALESCE(SUM(si.grand_total), 0) as total_revenue,
    COALESCE(SUM(si.profit), 0) as total_profit,
    COALESCE(AVG(si.price), 0) as avg_selling_price,
    COALESCE(AVG(si.unit_price), 0) as avg_cost_price,
    CASE
        WHEN COALESCE(SUM(si.grand_total), 0) > 0
        THEN (COALESCE(SUM(si.profit), 0) / COALESCE(SUM(si.grand_total), 0) * 100)
        ELSE 0
    END as profit_margin,
    COUNT(DISTINCT s.sales_id) as transaction_count
FROM products p
LEFT JOIN categories c ON p.category = c.id
LEFT JOIN sale_items si ON p.brandname = si.brandname
LEFT JOIN sales s ON si.sales_id = s.sales_id $where
GROUP BY p.id, p.brandname, p.productname, c.name
HAVING total_quantity > 0 OR total_revenue > 0
$order_by";

$products_result = $conn->query($products_query);
$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// Get summary statistics
$summary_query = "SELECT
    COALESCE(SUM(si.quantity), 0) as total_items_sold,
    COALESCE(SUM(si.grand_total), 0) as total_revenue,
    COALESCE(SUM(si.profit), 0) as total_profit,
    COUNT(DISTINCT si.brandname) as unique_products,
    COUNT(DISTINCT s.sales_id) as total_transactions
FROM sale_items si
JOIN sales s ON si.sales_id = s.sales_id
$where";
$summary = $conn->query($summary_query)->fetch_assoc();

// Get top categories by revenue
$category_query = "SELECT
    c.name,
    COALESCE(SUM(si.grand_total), 0) as revenue,
    COALESCE(SUM(si.profit), 0) as profit,
    COALESCE(SUM(si.quantity), 0) as quantity
FROM categories c
LEFT JOIN products p ON c.id = p.category
LEFT JOIN sale_items si ON p.brandname = si.brandname
LEFT JOIN sales s ON si.sales_id = s.sales_id $where
GROUP BY c.id, c.name
HAVING revenue > 0
ORDER BY revenue DESC
LIMIT 5";
$categories_perf = $conn->query($category_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo $selected_year; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
        }

        body {
            background: #f4f7fc;
            font-family: 'Inter', -apple-system, sans-serif;
        }

        .main-content {
            padding: 20px 25px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h2 {
            font-size: 28px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .filters {
            display: flex;
            gap: 10px;
            background: white;
            padding: 15px 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 500;
            color: #475569;
            font-size: 14px;
        }

        .filter-group select, .filter-group input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            outline: none;
            min-width: 120px;
        }

        .filter-group button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-group button:hover {
            background: var(--secondary);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card-icon.products { background: #e6f0ff; color: #4361ee; }
        .card-icon.revenue { background: #e6f7e6; color: #2ecc71; }
        .card-icon.profit { background: #fff4e6; color: #f39c12; }
        .card-icon.items { background: #f3e8ff; color: #7209b7; }

        .card-content h3 {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            margin: 0 0 5px;
        }

        .card-content .value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }

        .card-content .sub {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .table-responsive {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h4 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .sort-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .sort-btn {
            background: #f1f5f9;
            border: none;
            border-radius: 30px;
            padding: 6px 15px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .sort-btn.active {
            background: var(--primary);
            color: white;
        }

        .sort-btn:hover {
            background: #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px 10px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
        }

        th:hover {
            color: var(--primary);
        }

        td {
            padding: 12px 10px;
            font-size: 13px;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .product-info {
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-weight: 600;
            color: #1e293b;
        }

        .product-category {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-success {
            background: #d1fae5;
            color: #059669;
        }

        .badge-warning {
            background: #fed7aa;
            color: #b45309;
        }

        .progress {
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            margin: 5px 0;
        }

        .progress-bar {
            height: 100%;
            border-radius: 10px;
            background: var(--primary);
        }

        .profit-positive {
            color: #059669;
            font-weight: 600;
        }

        .profit-negative {
            color: #b91c1c;
            font-weight: 600;
        }

        .export-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .export-btn:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
                width: 100%;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select, .filter-group input {
                flex: 1;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="page-header">
        <h2><i class="bi bi-trophy" style="margin-right: 10px; color: var(--primary);"></i> Top Products Report</h2>
        <button class="export-btn" onclick="exportToCSV()">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export to CSV
        </button>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-group">
            <label><i class="bi bi-calendar"></i> Year:</label>
            <select id="year-select">
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $selected_year ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="bi bi-calendar-month"></i> Month:</label>
            <select id="month-select">
                <option value="0">All Months</option>
                <option value="1" <?php echo $selected_month == 1 ? 'selected' : ''; ?>>January</option>
                <option value="2" <?php echo $selected_month == 2 ? 'selected' : ''; ?>>February</option>
                <option value="3" <?php echo $selected_month == 3 ? 'selected' : ''; ?>>March</option>
                <option value="4" <?php echo $selected_month == 4 ? 'selected' : ''; ?>>April</option>
                <option value="5" <?php echo $selected_month == 5 ? 'selected' : ''; ?>>May</option>
                <option value="6" <?php echo $selected_month == 6 ? 'selected' : ''; ?>>June</option>
                <option value="7" <?php echo $selected_month == 7 ? 'selected' : ''; ?>>July</option>
                <option value="8" <?php echo $selected_month == 8 ? 'selected' : ''; ?>>August</option>
                <option value="9" <?php echo $selected_month == 9 ? 'selected' : ''; ?>>September</option>
                <option value="10" <?php echo $selected_month == 10 ? 'selected' : ''; ?>>October</option>
                <option value="11" <?php echo $selected_month == 11 ? 'selected' : ''; ?>>November</option>
                <option value="12" <?php echo $selected_month == 12 ? 'selected' : ''; ?>>December</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="bi bi-tag"></i> Category:</label>
            <select id="category-select">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $selected_category ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <button onclick="applyFilters()">Apply Filters</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="card-icon products">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="card-content">
                <h3>Unique Products</h3>
                <div class="value"><?php echo number_format($summary['unique_products']); ?></div>
                <div class="sub">with sales</div>
            </div>
        </div>
        <div class="card">
            <div class="card-icon items">
                <i class="bi bi-cart"></i>
            </div>
            <div class="card-content">
                <h3>Items Sold</h3>
                <div class="value"><?php echo number_format($summary['total_items_sold']); ?></div>
                <div class="sub">units</div>
            </div>
        </div>
        <div class="card">
            <div class="card-icon revenue">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="card-content">
                <h3>Total Revenue</h3>
                <div class="value">KES <?php echo number_format($summary['total_revenue'], 2); ?></div>
                <div class="sub">from products</div>
            </div>
        </div>
        <div class="card">
            <div class="card-icon profit">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="card-content">
                <h3>Total Profit</h3>
                <div class="value">KES <?php echo number_format($summary['total_profit'], 2); ?></div>
                <div class="sub">
                    Margin: <?php echo $summary['total_revenue'] > 0 ? number_format(($summary['total_profit'] / $summary['total_revenue'] * 100), 1) : 0; ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Top 10 Products by Revenue</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-header">
                <h3>Revenue by Category</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="table-responsive">
        <div class="table-header">
            <h4>Product Performance</h4>
            <div class="sort-buttons">
                <button class="sort-btn <?php echo $sort_by == 'revenue' ? 'active' : ''; ?>" onclick="sortBy('revenue')">By Revenue</button>
                <button class="sort-btn <?php echo $sort_by == 'profit' ? 'active' : ''; ?>" onclick="sortBy('profit')">By Profit</button>
                <button class="sort-btn <?php echo $sort_by == 'quantity' ? 'active' : ''; ?>" onclick="sortBy('quantity')">By Quantity</button>
                <button class="sort-btn <?php echo $sort_by == 'margin' ? 'active' : ''; ?>" onclick="sortBy('margin')">By Margin</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Qty Sold</th>
                    <th>Transactions</th>
                    <th>Avg Price</th>
                    <th>Revenue</th>
                    <th>Profit</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $index => $product): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td>
                        <div class="product-info">
                            <span class="product-name"><?php echo htmlspecialchars($product['brandname']); ?></span>
                            <span class="product-category"><?php echo htmlspecialchars($product['productname']); ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: #e6f0ff; color: #4361ee;">
                            <?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?>
                        </span>
                    </td>
                    <td><strong><?php echo number_format($product['total_quantity']); ?></strong></td>
                    <td><?php echo $product['transaction_count']; ?></td>
                    <td>KES <?php echo number_format($product['avg_selling_price'], 2); ?></td>
                    <td>KES <?php echo number_format($product['total_revenue'], 2); ?></td>
                    <td class="<?php echo $product['total_profit'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                        KES <?php echo number_format($product['total_profit'], 2); ?>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span><?php echo number_format($product['profit_margin'], 1); ?>%</span>
                            <div class="progress" style="width: 60px;">
                                <div class="progress-bar" style="width: <?php echo min($product['profit_margin'], 100); ?>%; background: <?php
                                    echo $product['profit_margin'] >= 30 ? '#059669' : ($product['profit_margin'] >= 15 ? '#f39c12' : '#b91c1c');
                                ?>;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr><td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8;">No product data available for selected filters</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const year = document.getElementById('year-select').value;
    const month = document.getElementById('month-select').value;
    const category = document.getElementById('category-select').value;
    const sort = '<?php echo $sort_by; ?>';
    window.location.href = `top_products.php?year=${year}&month=${month}&category=${category}&sort=${sort}`;
}

function sortBy(sort) {
    const year = document.getElementById('year-select').value;
    const month = document.getElementById('month-select').value;
    const category = document.getElementById('category-select').value;
    window.location.href = `top_products.php?year=${year}&month=${month}&category=${category}&sort=${sort}`;
}

function exportToCSV() {
    const rows = [
        ['Top Products Report - <?php echo $selected_year; ?>' + (<?php echo $selected_month; ?> > 0 ? ' - Month <?php echo $selected_month; ?>' : '')],
        [],
        ['Summary'],
        ['Unique Products', '<?php echo number_format($summary['unique_products']); ?>'],
        ['Items Sold', '<?php echo number_format($summary['total_items_sold']); ?>'],
        ['Total Revenue', 'KES <?php echo number_format($summary['total_revenue'], 2); ?>'],
        ['Total Profit', 'KES <?php echo number_format($summary['total_profit'], 2); ?>'],
        ['Profit Margin', '<?php echo $summary['total_revenue'] > 0 ? number_format(($summary['total_profit'] / $summary['total_revenue'] * 100), 1) : 0; ?>%'],
        [],
        ['Product Performance'],
        ['Product', 'Category', 'Quantity', 'Transactions', 'Avg Price', 'Revenue', 'Profit', 'Margin %']
    ];

    <?php foreach ($products as $product): ?>
    rows.push([
        '<?php echo addslashes($product['brandname']); ?>',
        '<?php echo addslashes($product['category_name'] ?: 'Uncategorized'); ?>',
        '<?php echo $product['total_quantity']; ?>',
        '<?php echo $product['transaction_count']; ?>',
        'KES <?php echo number_format($product['avg_selling_price'], 2); ?>',
        'KES <?php echo number_format($product['total_revenue'], 2); ?>',
        'KES <?php echo number_format($product['total_profit'], 2); ?>',
        '<?php echo number_format($product['profit_margin'], 1); ?>%'
    ]);
    <?php endforeach; ?>

    let csv = rows.map(row => row.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'top_products_<?php echo $selected_year; ?>_<?php echo $selected_month; ?>.csv';
    a.click();
}

$(document).ready(function() {
    // Top Products Chart
    const topProducts = <?php
        $top10 = array_slice($products, 0, 10);
        $labels = array_column($top10, 'brandname');
        $revenues = array_column($top10, 'total_revenue');
        echo json_encode(['labels' => $labels, 'data' => $revenues]);
    ?>;

    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: topProducts.labels.map(l => l.length > 20 ? l.substring(0, 20) + '...' : l),
            datasets: [{
                label: 'Revenue',
                data: topProducts.data,
                backgroundColor: '#4361ee',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'KES ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Category Chart
    const categories = <?php
        $cat_labels = array_column($categories_perf, 'name');
        $cat_revenues = array_column($categories_perf, 'revenue');
        echo json_encode(['labels' => $cat_labels, 'data' => $cat_revenues]);
    ?>;

    if (categories.labels.length > 0) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categories.labels,
                datasets: [{
                    data: categories.data,
                    backgroundColor: ['#4361ee', '#f72585', '#4cc9f0', '#f8961e', '#7209b7'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': KES ' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    } else {
        document.getElementById('categoryChart').parentElement.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 50px;">No category data available</div>';
    }
});
</script>
</body>
</html>
<?php $conn->close(); ?>