<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Get current date and date ranges
$currentDate = date('Y-m-d');
$threeMonthsFromNow = date('Y-m-d', strtotime('+3 months'));
$sixMonthsFromNow = date('Y-m-d', strtotime('+6 months'));

// Determine which range to show
$range = isset($_GET['range']) ? $_GET['range'] : '0-3';
$title = "Creditors within 0-3 Months";

if ($range === '3-6') {
    $title = "Creditors within 3-6 Months";
    $dateCondition = "transDate BETWEEN '$threeMonthsFromNow' AND '$sixMonthsFromNow'";
} else {
    $dateCondition = "transDate BETWEEN '$currentDate' AND '$threeMonthsFromNow'";
}

// Get credit items
$sql = "SELECT * FROM credit_balances
        WHERE $dateCondition AND status NOT IN ('paid')
        ORDER BY transDate ASC";
$result = $conn->query($sql);


// Define receipts directory
$receiptsDir = realpath(dirname(__DIR__) . '/receipts');
if (!$receiptsDir || !is_dir($receiptsDir)) {
    $error = "Receipts directory not found.";
}


// Handle Excel export
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="credit_customers_' . $range . '_months.csv"');

    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, array('ID', 'Receipt Number', 'Customer Name', 'Balance Amount', 'Credit Date', 'Status'));

    // Re-execute query for export
    $exportResult = $conn->query($sql);

    // Data rows
    if ($exportResult->num_rows > 0) {
        while($row = $exportResult->fetch_assoc()) {
            // Determine status for export
            $transDate = new DateTime($row['transDate']);
            $today = new DateTime();
            $diff = $today->diff($transDate);
            $months = ($diff->y * 12) + $diff->m;

            if ($row['balance_amount'] == 0) {
                $statusText = 'Paid';
            } elseif ($months > 6) {
                $statusText = 'Deferred';
            } elseif ($months > 3) {
                $statusText = 'Defaulted';
            } else {
                $statusText = 'Expiring Soon';
            }

            fputcsv($output, array(
                $row['id'],
                $row['receipt_id'],
                $row['customer_name'],
                $row['balance_amount'],
                $row['transDate'],
                $statusText
            ));
        }
    }

    fclose($output);
    exit();
}

// Re-execute query for display (since pointer might be moved during export)
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Sales Details</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.css" type="text/css">

    <style>
        .container {
            max-width: 1200px;
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
        .export-btn {
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
        .export-btn:hover {
            background-color: #218838;
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
        .badge-paid {
            background-color: #28a745;
            color: white;
        }
        .badge-expiring-soon {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-defaulted {
            background-color: #fd7e14;
            color: white;
        }
        .badge-waived {
            background-color: #6c757d;
            color: white;
        }
        .badge-overdue {
            background-color: #dc3545;
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
            text-decoration: none;
            display: inline-block;
        }
        .range-btn.active {
            background: #007bff;
            color: white;
        }
        .range-btn:hover {
            background: #007bff;
            color: white;
            text-decoration: none;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Modal Styles */
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .customer-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .customer-info h5 {
            margin-bottom: 15px;
            color: #495057;
        }
        .items-table {
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #e9ecef;
        }
        .summary-section {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #dee2e6;
            padding-top: 8px;
            margin-top: 8px;
        }
        .loading-spinner {
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h2><?php echo $title; ?></h2>
            <form method="post">
                <button type="submit" name="export_excel" class="export-btn">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </form>
        </div>

        <div class="range-selector">
            <a href="?range=0-3" class="range-btn <?php echo $range === '0-3' ? 'active' : ''; ?>">0-3 Months</a>
            <a href="?range=3-6" class="range-btn <?php echo $range === '3-6' ? 'active' : ''; ?>">3-6 Months</a>
            <a href="?range=overdue" class="range-btn <?php echo $range === '6-12' ? 'active' : ''; ?>">Overdue (>6 Months)</a>
            <a href="?range=all" class="range-btn <?php echo $range === 'all' ? 'active' : ''; ?>">All Credits</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Receipt Number</th>
                        <th>Customer Name</th>
                        <th>Customer Phone</th>
                        <th>Balance Amount</th>
                        <th>Credit Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()):
                            $transDate = new DateTime($row['transDate']);
                            $today = new DateTime();
                            $diff = $today->diff($transDate);
                            $months = ($diff->y * 12) + $diff->m;

                            // Determine status based on your requirements
                            if ($row['balance_amount'] == 0) {
                                $creditStatus = 'badge-paid';
                                $statusText = 'Paid';
                            } elseif ($months > 6) {
                                $creditStatus = 'badge-waived';
                                $statusText = 'Waived';
                            } elseif ($months > 3) {
                                $creditStatus = 'badge-defaulted';
                                $statusText = 'Defaulted';
                            } else {
                                $creditStatus = 'badge-expiring-soon';
                                $statusText = 'Expiring Soon';
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['receipt_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_phone']); ?></td>
                                <td><?php echo number_format($row['balance_amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['transDate']); ?></td>
                                <td><span class="badge <?php echo $creditStatus; ?>"><?php echo $statusText; ?></span></td>
                                <td class="action-buttons">
                                    <button class="btn btn-sm btn-success view-btn"
                                            data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>"
                                            data-customer-name="<?php echo htmlspecialchars($row['customer_name']); ?>"
                                            data-customer-phone="<?php echo htmlspecialchars($row['customer_phone']); ?>"
                                            data-balance-amount="<?php echo $row['balance_amount']; ?>"
                                            data-credit-date="<?php echo htmlspecialchars($row['transDate']); ?>">
                                        View
                                    </button>
                                    <button class="btn btn-sm btn-warning update-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Update</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-receipt-id="<?php echo htmlspecialchars($row['receipt_id']); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No creditors found in this credit range.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for displaying credit sales details -->
    <div class="modal fade" id="creditDetailsModal" tabindex="-1" aria-labelledby="creditDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="creditDetailsModalLabel">Credit Sale Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="loadingSpinner" class="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading credit details...</p>
                    </div>
                    <div id="creditDetailsContent" style="display: none;">
                        <!-- Customer Information -->
                        <div class="customer-info">
                            <h5>Customer Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Receipt ID:</strong> <span id="modalReceiptId"></span></p>
                                    <p><strong>Customer Name:</strong> <span id="modalCustomerName"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Customer Phone:</strong> <span id="modalCustomerPhone"></span></p>
                                    <p><strong>Credit Date:</strong> <span id="modalCreditDate"></span></p>
                                </div>
                            </div>
                            <p><strong>Balance Amount:</strong> <span id="modalBalanceAmount" class="font-weight-bold"></span></p>
                        </div>

                        <!-- Items Table -->
                        <h5>Items Purchased</h5>
                        <div class="table-responsive items-table">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Brand Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Discount</th>
                                        <th>Tax Amount</th>
                                        <th>Total</th>
                                        <th>Sold By</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be populated here by JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Section -->
                        <div class="summary-section">
                            <h5>Transaction Summary</h5>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="summarySubtotal">0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax Amount:</span>
                                <span id="summaryTax">0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Discount:</span>
                                <span id="summaryDiscount">0.00</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Grand Total:</span>
                                <span id="summaryGrandTotal">0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Amount Paid:</span>
                                <span id="summaryPaid">0.00</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Balance Due:</span>
                                <span id="summaryBalance">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printReceiptBtn">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
    // JavaScript for handling view, update and delete actions
    document.addEventListener('DOMContentLoaded', function() {
        // View button handler
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const receiptId = this.getAttribute('data-receipt-id');
                const customerName = this.getAttribute('data-customer-name');
                const customerPhone = this.getAttribute('data-customer-phone');
                const balanceAmount = this.getAttribute('data-balance-amount');
                const creditDate = this.getAttribute('data-credit-date');

                // Show modal with basic info
                document.getElementById('modalReceiptId').textContent = receiptId;
                document.getElementById('modalCustomerName').textContent = customerName;
                document.getElementById('modalCustomerPhone').textContent = customerPhone;
                document.getElementById('modalBalanceAmount').textContent = 'KSh ' + parseFloat(balanceAmount).toFixed(2);
                document.getElementById('modalCreditDate').textContent = creditDate;

                // Show loading spinner and hide content
                document.getElementById('loadingSpinner').style.display = 'block';
                document.getElementById('creditDetailsContent').style.display = 'none';

                // Show modal
                $('#creditDetailsModal').modal('show');

                // Fetch detailed item information via AJAX
                fetchCreditDetails(receiptId);
            });
        });

        // Update button handler
        document.querySelectorAll('.update-btn').forEach(button => {
            button.addEventListener('click', function() {
                const receiptId = this.getAttribute('data-receipt-id');
                if (confirm('Are you sure you want to update credit record ' + receiptId + '?')) {
                    // Redirect to update page or show modal
                    window.location.href = 'update_credit.php?receipt_id=' + receiptId;
                }
            });
        });

        // Delete button handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const receiptId = this.getAttribute('data-receipt-id');
                if (confirm('Are you sure you want to delete credit record ' + receiptId + '? This action cannot be undone.')) {
                    // Redirect to delete script
                    window.location.href = 'delete_credit.php?receipt_id=' + receiptId;
                }
            });
        });

        // Print receipt button handler
        document.getElementById('printReceiptBtn').addEventListener('click', function() {
            // Create a print-friendly version of the modal content
            const printContent = document.getElementById('creditDetailsContent').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = `
                <div class="container">
                    <h3 class="text-center mb-4">Credit Sale Receipt</h3>
                    ${printContent}
                </div>
            `;

            window.print();
            document.body.innerHTML = originalContent;
            // Re-attach event listeners after printing
            window.location.reload();
        });

        // Function to fetch credit details via AJAX
        function fetchCreditDetails(receiptId) {
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_credit_details.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            // Populate items table
                            const itemsTableBody = document.getElementById('itemsTableBody');
                            itemsTableBody.innerHTML = '';

                            let subtotal = 0;
                            let totalTax = 0;
                            let totalDiscount = 0;

                            response.items.forEach(item => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${item.brandname}</td>
                                    <td>${item.quantity}</td>
                                    <td>KSh ${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td>KSh ${parseFloat(item.discount).toFixed(2)}</td>
                                    <td>KSh ${parseFloat(item.tax_amount).toFixed(2)}</td>
                                    <td>KSh ${parseFloat(item.total_amount).toFixed(2)}</td>
                                    <td>${item.transBy}</td>
                                `;
                                itemsTableBody.appendChild(row);

                                // Calculate totals
                                subtotal += parseFloat(item.total_amount);
                                totalTax += parseFloat(item.tax_amount);
                                totalDiscount += parseFloat(item.discount);
                            });

                            // Calculate grand total
                            const grandTotal = subtotal - totalDiscount;

                            // Get amount paid (grand total - balance amount)
                            const balanceAmount = parseFloat(document.getElementById('modalBalanceAmount').textContent.replace('KSh ', ''));
                            const amountPaid = grandTotal - balanceAmount;

                            // Update summary section
                            document.getElementById('summarySubtotal').textContent = 'KSh ' + subtotal.toFixed(2);
                            document.getElementById('summaryTax').textContent = 'KSh ' + totalTax.toFixed(2);
                            document.getElementById('summaryDiscount').textContent = 'KSh ' + totalDiscount.toFixed(2);
                            document.getElementById('summaryGrandTotal').textContent = 'KSh ' + grandTotal.toFixed(2);
                            document.getElementById('summaryPaid').textContent = 'KSh ' + amountPaid.toFixed(2);
                            document.getElementById('summaryBalance').textContent = 'KSh ' + balanceAmount.toFixed(2);

                            // Hide loading spinner and show content
                            document.getElementById('loadingSpinner').style.display = 'none';
                            document.getElementById('creditDetailsContent').style.display = 'block';
                        } else {
                            // Show error message
                            document.getElementById('loadingSpinner').innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> ${response.message}
                                </div>
                            `;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        document.getElementById('loadingSpinner').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Error loading credit details.
                            </div>
                        `;
                    }
                } else {
                    document.getElementById('loadingSpinner').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Server error. Please try again.
                        </div>
                    `;
                }
            };

            xhr.onerror = function() {
                document.getElementById('loadingSpinner').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Network error. Please check your connection.
                    </div>
                `;
            };

            // Send the request with receipt_id
            xhr.send('receipt_id=' + encodeURIComponent(receiptId));
        }
    });
    </script>
</body>
</html>