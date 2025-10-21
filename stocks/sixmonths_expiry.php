<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Get current date and date ranges
$currentDate = date('Y-m-d');
$sixMonthsFromNow = date('Y-m-d', strtotime('+6 months'));
$twelveMonthsFromNow = date('Y-m-d', strtotime('+12 months'));

// Determine which range to show
$range = isset($_GET['range']) ? $_GET['range'] : '0-6';
$title = "Products Expiring in 0-6 Months";

if ($range === '6-12') {
    $title = "Products Expiring in 6-12 Months";
    $dateCondition = "expiryDate BETWEEN '$sixMonthsFromNow' AND '$twelveMonthsFromNow'";
} else {
    $dateCondition = "expiryDate BETWEEN '$currentDate' AND '$sixMonthsFromNow'";
}

// Get expiry items
$sql = "SELECT s.*, p.productname
        FROM stocks s
        LEFT JOIN products p ON s.id = p.id
        WHERE $dateCondition AND s.stockBalance > 0
        ORDER BY s.expiryDate ASC";
$result = $conn->query($sql);

// Handle Excel export
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="expiry_items_' . $range . '_months.csv"');

    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, array('ID', 'Product Name', 'Brand Name', 'Batch', 'Expiry Date', 'Stock Balance', 'Status'));

    // Data rows
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['id'],
                $row['productname'],
                $row['brandname'],
                $row['batch'],
                $row['expiryDate'],
                $row['stockBalance'],
                $row['status']
            ));
        }
    }

    fclose($output);
    exit();
}

// Re-execute query for display (since pointer was moved during export)
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">

    <style>
        .container {
            max-width: 90%;
            margin: 20px auto;
            padding: 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        #export-excel {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        #export-excel:hover {
            background-color: #99CCFF;
            color: #000000;
        }
        .table-responsive {
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .badge-expiring-soon {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-expired {
            background-color: #dc3545;
            color: white;
        }
        .badge-safe {
            background-color: #28a745;
            color: white;
        }
        .range-selector {
            margin-bottom: 20px;
        }
        .range-btn {
            margin-right: 10px;
            padding: 8px 15px;
            border: 1px solid #007bff;
            background: white;
            color: #007bff;
            border-radius: 5px;
            cursor: pointer;
        }
        .range-btn.active {
            background: #007bff;
            color: white;
        }
        .range-btn:hover {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2><?php echo $title; ?></h2>
            <form method="post">
                <button type="submit" name="export_excel" id="export-excel" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i>Export to Excel
                </button>
            </form>
        </div>

        <div class="range-selector">
            <a href="?range=0-6" class="range-btn <?php echo $range === '0-6' ? 'active' : ''; ?>">0-6 Months</a>
            <a href="?range=6-12" class="range-btn <?php echo $range === '6-12' ? 'active' : ''; ?>">6-12 Months</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th style="width: 40%;">Product Name</th>
                        <th style="width: 25%;">Brand Name</th>
                        <th style="width: 5%;">Batch</th>
                        <th>Expiry Date</th>
                        <th>Stock Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()):
                            $expiryStatus = '';
                            $expiryDate = new DateTime($row['expiryDate']);
                            $today = new DateTime();
                            $diff = $today->diff($expiryDate);
                            $months = ($diff->y * 12) + $diff->m;

                            if ($expiryDate < $today) {
                                $expiryStatus = 'badge-expired';
                                $statusText = 'Expired';
                            } elseif ($months <= 3) {
                                $expiryStatus = 'badge-expiring-soon';
                                $statusText = 'Expiring Soon';
                            } else {
                                $expiryStatus = 'badge-safe';
                                $statusText = 'OK';
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['productname']); ?></td>
                                <td><?php echo htmlspecialchars($row['brandname']); ?></td>
                                <td><?php echo htmlspecialchars($row['batch']); ?></td>
                                <td><?php echo htmlspecialchars($row['expiryDate']); ?></td>
                                <td><?php echo htmlspecialchars($row['stockBalance']); ?></td>
                                <td><span class="badge <?php echo $expiryStatus; ?>"><?php echo $statusText; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No products found in this expiry range.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
   <script>
        function exportToExcel() {
            var table = document.getElementsByTagName("table")[0];
            var html = table.outerHTML;
            var uri = 'data:application/vnd.ms-excel,' + encodeURIComponent('<html><head><meta charset="UTF-8"><style>td { border: 1px solid black; }</style></head><body>' + html + '</body></html>');
            var link = document.createElement("a");
            link.href = uri;
            link.download = "data.xls";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function cancelSearch() {
            window.location.href = window.location.pathname;
        }

    </script>
    
</body>
</html>

<?php
$conn->close();
ob_end_flush();
?>