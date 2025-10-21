<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Get date parameters or default to today
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Build the SQL query with date filtering
$sql = "SELECT * FROM sales WHERE payment_status = 'paid'";
$params = [];
$types = "";

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND DATE(transDate) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

$sql .= " ORDER BY transDate DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// For export functionality
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="paid_orders_' . $start_date . '_to_' . $end_date . '.xls"');

    echo "<html>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<style>td { border: 1px solid black; padding: 5px; } th { background-color: #00246B; color: #CADCFC; padding: 8px; }</style>";
    echo "</head>";
    echo "<body>";
    echo "<h2>Fully Paid Orders - {$start_date} to {$end_date}</h2>";
    echo "<table border='1'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Sales ID</th>";
    echo "<th>Receipt ID</th>";
    echo "<th>Items</th>";
    echo "<th>Total Amount</th>";
    echo "<th>Tax Amount</th>";
    echo "<th>Grand Total</th>";
    echo "<th>Tendered Amount</th>";
    echo "<th>Payment Method</th>";
    echo "<th>Status</th>";
    echo "<th>Transaction Date</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['sales_id'] . "</td>";
        echo "<td>" . $row['receipt_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['items']) . "</td>";
        echo "<td>" . $row['total_amount'] . "</td>";
        echo "<td>" . $row['tax_amount'] . "</td>";
        echo "<td>" . $row['grand_total'] . "</td>";
        echo "<td>" . $row['tendered_amount'] . "</td>";
        echo "<td>" . $row['payment_method'] . "</td>";
        echo "<td>" . $row['payment_status'] . "</td>";
        echo "<td>" . $row['transDate'] . "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</body>";
    echo "</html>";
    exit();
}

// Reset result pointer for display
$result->data_seek(0);
?>
<?php
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
    echo "<div class='alert alert-info'>" . htmlspecialchars($message) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Orders</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">
    <script src="../assets/js/bootstrap.bundle.js"></script>
    <style>
        .main-content{
            position: flex;
            z-index: -1;
        }
        thead{
            background-color: #00246B;
            color: #CADCFC;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .export-section {
            margin-bottom: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
<div class="main-content" style="min-width: 90%; margin-top: 10px;">
    <h2>Fully Paid Orders</h2>

    <!-- Date Filter Section -->
    <div class="filter-section">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date"
                       value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date"
                       value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <button type="button" class="btn btn-secondary" onclick="setToday()">Today</button>
                <button type="button" class="btn btn-outline-danger" onclick="clearFilter()">Clear Filter</button>
                <a href="?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>"
                   class="btn btn-info">View Today</a>
            </div>
        </form>
    </div>

    <!-- Export Section -->
    <div class="export-section">
        <!--<a href="?start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&export=excel"
           class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a>-->
        <button type="button" class="btn btn-success" onclick="exportToExcel()">
            <i class="fas fa-file-excel"></i> Export to Excel (Client-side)
        </button>
    </div>

    <!-- Results Count -->
    <div class="mb-3">
        <strong>Showing orders from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></strong>
        <?php
            $count_sql = "SELECT COUNT(*) as total FROM sales WHERE payment_status = 'paid'";
            if (!empty($start_date) && !empty($end_date)) {
                $count_sql .= " AND DATE(transDate) BETWEEN ? AND ?";
            }
            $count_stmt = $conn->prepare($count_sql);
            if (!empty($start_date) && !empty($end_date)) {
                $count_stmt->bind_param("ss", $start_date, $end_date);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_count = $count_result->fetch_assoc()['total'];
            echo "<span class='badge bg-primary'>Total: " . $total_count . " orders</span>";
        ?>
    </div>

    <table class="table table-bordered table-striped" style="width: 90%;">
        <thead>
            <tr>
                <th>Sales ID</th>
                <th>Receipt ID</th>
                <th>Items</th>
                <th>Total</th>
                <th>Tax</th>
                <th>Grand Total</th>
                <th>Tendered</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Transaction Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['sales_id'] ?></td>
                        <td><?= $row['receipt_id'] ?></td>
                        <td><?= htmlspecialchars($row['items']) ?></td>
                        <td>KES <?= number_format($row['total_amount'], 2) ?></td>
                        <td>KES <?= number_format($row['tax_amount'], 2) ?></td>
                        <td>KES <?= number_format($row['grand_total'], 2) ?></td>
                        <td>KES <?= number_format($row['tendered_amount'], 2) ?></td>
                        <td>
                            <span class="badge bg-<?php
                                echo $row['payment_method'] == 'Cash' ? 'success' :
                                     ($row['payment_method'] == 'Mpesa' ? 'primary' : 'warning');
                            ?>">
                                <?= $row['payment_method'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?= $row['payment_status'] ?></span>
                        </td>
                        <td><?= date('M j, Y g:i A', strtotime($row['transDate'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center text-muted">
                        No paid orders found for the selected date range.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function exportToExcel() {
    var table = document.getElementsByTagName("table")[0];
    var html = table.outerHTML;
    var uri = 'data:application/vnd.ms-excel,' + encodeURIComponent('<html><head><meta charset="UTF-8"><style>td { border: 1px solid black; padding: 5px; } th { background-color: #00246B; color: #CADCFC; padding: 8px; }</style></head><body><h2>Fully Paid Orders - <?php echo $start_date; ?> to <?php echo $end_date; ?></h2>' + html + '</body></html>');
    var link = document.createElement("a");
    link.href = uri;
    link.download = "paid_orders_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.xls";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function setToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    document.getElementById('end_date').value = today;
}

function clearFilter() {
    window.location.href = window.location.pathname;
}

function cancelSearch() {
    window.location.href = window.location.pathname;
}

// Set default dates to today on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('start_date') && !urlParams.has('end_date')) {
        setToday();
    }
});

$('.edit-btn').click(function () {
    const draftId = $(this).data('draft-id');
    if (!draftId) return alert("Draft ID missing.");
    window.location.href = 'edit_order.php?draft_id=' + draftId;
});

$('.mark-paid-btn').click(function () {
    const draftId = $(this).data('draft-id');
    if (!draftId) return alert("Draft ID missing.");
    if (!confirm("Mark this order as paid?")) return;
    window.location.href = 'mark_paid.php?draft_id=' + draftId;
});

$('.update-btn').click(function () {
    const draftId = $(this).data('draft-id');
    if (!draftId) return alert("Draft ID missing.");
    window.location.href = 'edit_order.php?draft_id=' + draftId;
});

$('.delete-btn').click(function () {
    const receiptId = $(this).data('receipt-id');
    if (!receiptId) return alert("Receipt ID missing.");
    if (!confirm("Are you sure you want to delete this draft?")) return;
    window.location.href = 'delete_order.php?receipt_id=' + receiptId;
});
</script>
</body>
</html>

<?php
// Close the statement and connection
if (isset($stmt)) {
    $stmt->close();
}
if (isset($count_stmt)) {
    $count_stmt->close();
}
$conn->close();
?>