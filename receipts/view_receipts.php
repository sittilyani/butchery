<?php
session_start();
include '../includes/config.php';

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
            $fileDate = null;
            $displayDateTime = 'Unknown';
            $displayDateOnly = 'Unknown';

            if (preg_match('/ORD(\d{4})(\d{2})(\d{2})\d+/', $receipt_id, $matches)) {
                // Extract year, month, day from receipt_id
                $year = $matches[1];
                $month = $matches[2];
                $day = $matches[3];
                $fileDate = "$year-$month-$day";
                // Format with time from file modification time
                $fileTime = filemtime($fullPath);
                $displayDateTime = date('Y-m-d h:i A', $fileTime); // Shows 05:44 PM format
                $displayDateOnly = date('Y-m-d', strtotime($fileDate));
            } else {
                // Fallback to file modification time
                $fileTime = filemtime($fullPath);
                $fileDate = date('Y-m-d', $fileTime);
                $displayDateTime = date('Y-m-d h:i A', $fileTime);
                $displayDateOnly = date('Y-m-d', $fileTime);
            }

            // Apply date filter using only the date part (not time)
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
                    'display_date_time' => $displayDateTime,
                    'display_date_only' => $displayDateOnly,
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
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .main-content {
            padding: 30px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .receipts-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid #000099;
            padding-bottom: 15px;
        }
        h2 i { color: #000099; margin-right: 10px; }

        /* Filter Section */
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        .filter-title {
            font-weight: 600;
            color: #000099;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .filter-title i { margin-right: 8px; }

        /* Table Styles */
        .table {
            margin: 0;
            font-size: 0.95rem;
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table th {
            padding: 15px;
            font-weight: 600;
            border: none;
        }
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-color: #e9ecef;
        }
        .table tbody tr:hover {
            background-color: rgba(0,0,153,0.02);
            transition: all 0.2s;
        }

        /* Badge for date */
        .date-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #e9ecef;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #495057;
        }
        .date-badge i {
            color: #000099;
            margin-right: 5px;
        }

        /* Button Styles */
        .btn-group-sm > .btn, .btn-sm {
            padding: 5px 12px;
            margin: 0 3px;
            border-radius: 6px;
        }
        .btn-outline-primary {
            border-color: #000099;
            color: #000099;
        }
        .btn-outline-primary:hover {
            background: #000099;
            color: white;
        }
        .btn-outline-success {
            border-color: #28a745;
            color: #28a745;
        }
        .btn-outline-success:hover {
            background: #28a745;
            color: white;
        }

        /* Filter Buttons */
        .btn-primary {
            background: #000099;
            border-color: #000099;
        }
        .btn-primary:hover {
            background: #000080;
            border-color: #000080;
        }
        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }
        .alert-info {
            background: #e1f5fe;
            color: #01579b;
        }
        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .receipts-card { padding: 20px; }
            .table { display: block; overflow-x: auto; }
            .btn-group-sm > .btn, .btn-sm {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }

        /* Time display */
        .time-display {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 3px;
        }
        .time-display i {
            color: #000099;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="receipts-card">
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
                </div>
            <?php endif; ?>

            <h2>
                <i class="bi bi-receipt"></i>
                Receipts Management
            </h2>

            <!-- Date Range Filter -->
            <div class="filter-section">
                <div class="filter-title">
                    <i class="bi bi-funnel"></i> Filter by Date Range
                </div>
                <form method="GET" action="">
                    <div class="row g-3">
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
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter"></i> Apply Filter
                                </button>
                                <a href="?" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif (empty($receipts)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-inbox me-2"></i>
                    <?php if ($filterApplied): ?>
                        No receipts found for the selected date range.
                    <?php else: ?>
                        No receipts found.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
                                <th>Date of Transaction</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $receipt): ?>
                                <tr>
                                    <td>
                                        <span class="date-badge">
                                            <i class="bi bi-receipt"></i>
                                            <?php echo $receipt['receipt_id']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 me-2 text-primary"></i>
                                        <?php echo $receipt['display_date_only']; ?>
                                    </td>
                                    <td>
                                        <span class="time-display">
                                            <i class="bi bi-clock"></i>
                                            <?php
                                            // Extract time from display_date_time
                                            $timePart = explode(' ', $receipt['display_date_time']);
                                            echo end($timePart) . ' ' . prev($timePart);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo $receipt['path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View Receipt">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a href="<?php echo $receipt['path']; ?>" class="btn btn-sm btn-outline-success" download title="Download Receipt">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Summary -->
                    <div class="text-muted mt-3 text-end">
                        <i class="bi bi-files"></i>
                        Total Receipts: <strong><?php echo count($receipts); ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update end_date min date when start_date changes
        document.addEventListener("DOMContentLoaded", function() {
            const startDateInput = document.getElementById("start_date");
            const endDateInput = document.getElementById("end_date");

            if (startDateInput && endDateInput) {
                startDateInput.addEventListener("change", function() {
                    if (this.value) {
                        endDateInput.min = this.value;

                        // If end date is less than start date, update it
                        if (endDateInput.value && endDateInput.value < this.value) {
                            endDateInput.value = this.value;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>