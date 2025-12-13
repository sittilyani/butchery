<?php
include '../includes/config.php';

// Get today's date in Y-m-d format
$today = date('Y-m-d');

// Prepare and execute the SQL query
$sql = "
    SELECT
        COALESCE(SUM(
            CASE
                WHEN payment_status = 'Paid' THEN grand_total
                WHEN payment_status = 'Credit' THEN tendered_amount
                ELSE 0
            END
        ), 0) AS total_cash_received
    FROM sales
    WHERE payment_method = 'cash'
        AND payment_status IN ('Paid', 'Credit')
        AND DATE(transDate) = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$totalCashReceived = $row['total_cash_received'] ?? 0;

// Also get breakdown for debugging/display purposes (optional)
$breakdown_sql = "
    SELECT
        SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE 0 END) as paid_total,
        SUM(CASE WHEN payment_status = 'Credit' THEN tendered_amount ELSE 0 END) as credit_tendered,
        COUNT(CASE WHEN payment_status = 'Paid' THEN 1 END) as paid_count,
        COUNT(CASE WHEN payment_status = 'Credit' THEN 1 END) as credit_count
    FROM sales
    WHERE payment_method = 'cash'
        AND DATE(transDate) = ?
";

$breakdown_stmt = $conn->prepare($breakdown_sql);
$breakdown_stmt->bind_param("s", $today);
$breakdown_stmt->execute();
$breakdown_result = $breakdown_stmt->get_result();
$breakdown = $breakdown_result->fetch_assoc();

// Output formatted result
echo number_format($totalCashReceived, 2); // e.g., 5,280.00

// For debugging (optional - you can remove this)
// echo " (Paid: " . number_format($breakdown['paid_total'] ?? 0, 2) .
//       ", Credit tendered: " . number_format($breakdown['credit_tendered'] ?? 0, 2) .
//       " - " . ($breakdown['paid_count'] ?? 0) . " paid, " .
//       ($breakdown['credit_count'] ?? 0) . " credit sales)";
?>
<div id="cashSalesToday"></div>

<script>
    function fetchCashSalesToday() {
        $.ajax({
            url: 'daily_cash_sales.php',
            method: 'GET',
            success: function (data) {
                $('#cashSalesToday').text('Today\'s Cash Received: KES ' + data);
            },
            error: function (err) {
                console.error('Error fetching daily cash sales', err);
            }
        });
    }

    fetchCashSalesToday();
    setInterval(fetchCashSalesToday, 300000); // Refresh every 5 minutes
</script>