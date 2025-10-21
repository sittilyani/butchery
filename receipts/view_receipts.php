<?php
session_start();
include '../includes/config.php';
include '../includes/header.php';

// Define receipts directory
$receiptsDir = realpath(dirname(__DIR__) . '/receipts');
if (!$receiptsDir || !is_dir($receiptsDir)) {
    $error = "Receipts directory not found.";
}

// Process date filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filterApplied = !empty($startDate) || !empty($endDate);

// Collect PDF files
$receipts = [];
if (!isset($error)) {
    $files = scandir($receiptsDir);
    foreach ($files as $file) {
        // Filter for PDF files and prevent directory traversal
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf' && strpos($file, '..') === false) {
            $fullPath = $receiptsDir . '/' . $file;

            // Extract receipt_id from filename (remove .pdf extension)
            $receipt_id = pathinfo($file, PATHINFO_FILENAME);

            // Try to extract date from receipt_id format: ORD202510172678
            // Format appears to be: ORD + YYYYMMDD + random numbers
            $fileDate = null;
            $displayDate = 'Unknown';

            if (preg_match('/ORD(\d{4})(\d{2})(\d{2})\d+/', $receipt_id, $matches)) {
                // Extract year, month, day from receipt_id
                $year = $matches[1];
                $month = $matches[2];
                $day = $matches[3];
                $fileDate = "$year-$month-$day";
                $displayDate = date('Y-m-d H:i:s', strtotime($fileDate));
            } else {
                // Fallback to file modification time if pattern doesn't match
                $fileDate = date('Y-m-d', filemtime($fullPath));
                $displayDate = date('Y-m-d H:i:s', filemtime($fullPath));
            }

            // Apply date filter if set
            $includeFile = true;
            if ($filterApplied && $fileDate) {
                if (!empty($startDate) && $fileDate < $startDate) {
                    $includeFile = false;
                }
                if (!empty($endDate) && $fileDate > $endDate) {
                    $includeFile = false;
                }
            }

            if ($includeFile) {
                $receipts[] = [
                    'filename' => $file,
                    'receipt_id' => htmlspecialchars($receipt_id),
                    'date' => $fileDate,
                    'display_date' => $displayDate,
                    'path' => '../receipts/' . rawurlencode($file),
                    'sort_key' => $fileDate ? strtotime($fileDate) : filemtime($fullPath)
                ];
            }
        }
    }

    // Sort receipts by date (newest first)
    usort($receipts, function($a, $b) {
        return $b['sort_key'] - $a['sort_key'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Receipts</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">

    <style>
        .main-content {margin: 10px 20px;}
        .receipts{background: #000099; padding: 20px; width: 80%; max-width: 900px; border-radius: 5px; margin-left: auto; margin-right: auto; margin-top: 20px;}
        .receipts h2 {color: white;}
        .receipt-table {margin: 0 auto; width: 100%;}
        .no-receipts-message {margin-top: 20px; text-align: center; font-style: italic; color: #6c757d;}
        .filter-container {background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;}
        .filter-title {font-weight: bold; margin-bottom: 10px; color: #000099;}
        .reset-btn {margin-top: 32px;}
        @media (max-width: 768px) {
            .receipts {width: 95%; padding: 15px;}
            .filter-container .row > div {margin-bottom: 10px;}
            .reset-btn {margin-top: 0;}
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="receipts">
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars(urldecode($_GET['message'])); ?></div>
        <?php endif; ?>

        <center><h2>Receipts</h2></center>

        <!-- Date Range Filter -->
        <div class="filter-container">
            <div class="filter-title"><i class="bi bi-funnel"></i> Filter Receipts by Date Range</div>
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="<?php echo htmlspecialchars($startDate); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="<?php echo htmlspecialchars($endDate); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-filter"></i> Apply Filter</button>
                            <a href="?" class="btn btn-secondary reset-btn"><i class="bi bi-x-circle"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (empty($receipts)): ?>
            <div class="alert alert-info">
                <?php if ($filterApplied): ?>
                    No receipts found for the selected date range.
                <?php else: ?>
                    No receipts found.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered receipt-table">
                    <thead>
                        <tr>
                            <th>Receipt ID</th>
                            <th>Date of Sales</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $receipt): ?>
                            <tr>
                                <td><?php echo $receipt['receipt_id']; ?></td>
                                <td><?php echo htmlspecialchars($receipt['display_date']); ?></td>
                                <td>
                                    <a href="<?php echo $receipt['path']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?php echo $receipt['path']; ?>" class="btn btn-sm btn-success" download>
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>

    <script>
        // Update end_date min date when start_date changes
        document.addEventListener("DOMContentLoaded", function() {
            const startDateInput = document.getElementById("start_date");
            const endDateInput = document.getElementById("end_date");

            if (startDateInput && endDateInput) {
                startDateInput.addEventListener("change", function() {
                    if (this.value) {
                        endDateInput.min = this.value;
                    }
                });
            }
        });
    </script>
</body>
</html>