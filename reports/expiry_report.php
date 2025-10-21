<?php
// Include necessary files
include "../includes/header.php";
include "../includes/config.php";

// Set a custom page title
$page_title = "Expired Stocks Report";

// Set default date values to the current date if not provided in the URL
$today = date('Y-m-d');
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : $today;
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : $today;

// Construct the SQL query to fetch the latest stock entry for each brand name within the date range
// We use a subquery to find the maximum transDate for each brandname
// and then join back to the stocks table to get all the data for that specific row.
$sql = "SELECT t1.*
        FROM stocks t1
        JOIN (
            SELECT brandname, MAX(transDate) AS maxTransDate
            FROM stocks
            GROUP BY brandname
        ) t2 ON t1.brandname = t2.brandname AND t1.transDate = t2.maxTransDate
        WHERE t1.expiryDate BETWEEN ? AND ?
        ORDER BY t1.expiryDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dateFrom, $dateTo);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- Include Bootstrap for basic styling -->
    <link rel="stylesheet" href="../assets/css/bootstrap.css" type="text/css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" type="text/css">

    <style>
        /* General body and container styling */

        .main-container {
            width: 95%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        /* Heading styling */
        h3 {
            color: #1a5276;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        /* Filter form container styling */
        .filter-form-container {
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            background: #000099;
            color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }

        .filter-form-container label {
            font-size: 1.1rem;
            margin-right: 10px;
            font-weight: 500;
        }

        .filter-form-container input[type="date"] {
            border: none;
            border-radius: 5px;
            padding: 8px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
        }

        .filter-form-container input[type="submit"],
        .filter-form-container .action-btn {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            margin-left: 15px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-weight: bold;
        }

        .filter-form-container input[type="submit"]:hover,
        .filter-form-container .action-btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        /* Table styling */
        .table-container {
            overflow-x: auto;
        }

        .table-striped th, .table-striped td {
            text-align: center;
            white-space: wrap;
        }

        .table-striped th {
            background-color: #1a5276;
            color: white;
            padding: 12px;
            vertical-align: middle;
            font-weight: bold;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }

        .table-striped tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Export buttons styling */
        #print-pdf {
            background-color: #888;
        }

        #export-excel {
            background-color: #2ecc71;
        }

        @media (max-width: 768px) {
            .filter-form-container {
                flex-direction: column;
            }
            .filter-form-container label,
            .filter-form-container input {
                margin: 5px 0;
            }
            .filter-form-container input[type="submit"],
            .filter-form-container .action-btn {
                margin-top: 15px;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="main-container">

    <h3 style="font-family: Times New Roman;">Drug Expiries By Date</h3>

    <div class="filter-form-container">
        <form method="get" class="d-flex align-items-center flex-wrap">
            <div class="form-group mb-0 mr-3">
                <label for="dateFrom">Date From:</label>
                <input type="date" name="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            <div class="form-group mb-0 mr-3">
                <label for="dateTo">Date To:</label>
                <input type="date" name="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            <input type="submit" value="Filter" class="action-btn">
        </form>
        <button id="print-pdf" class="action-btn" onclick="window.print()">Print PDF</button>
        <button id="export-excel" class="action-btn" onclick="exportToExcel()">Export to Excel</button>
    </div>

    <div class="table-container">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Stock ID</th>
                    <th>Brand Name</th>
                    <th style='text-align: left; width: 400px;'>Generic Name</th>
                    <th>Batch Number</th>
                    <th>Supplier</th>
                    <th>Last Transaction Date</th>
                    <th>Expiry Date</th>
                    <th>Stock Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $rowCounter = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($rowCounter) . "</td>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td style='text-align: left; width: 300px;'>" . htmlspecialchars($row['brandname']) . "</td>";
                        echo "<td style='text-align: left; width: 400px;'>" . htmlspecialchars($row['productname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['batch']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['receivedFrom']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['transDate']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['expiryDate']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['stockBalance']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                        $rowCounter++;
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>No records found for the selected date range.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>
<script src="../assets/js/bootstrap.bundle.js"></script>
<script>
    // Function to export table data to Excel
    function exportToExcel() {
        var table = document.querySelector("table");
        var html = table.outerHTML;

        // Format HTML for Excel
        var uri = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);

        // Create temporary link element and trigger download
        var link = document.createElement("a");
        link.href = uri;
        link.style = "visibility:hidden";
        link.download = "expired_stocks_report.xls";

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php
// Include the footer file if need be

$stmt->close();
$conn->close();
?>

</body>
</html>