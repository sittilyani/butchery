<?php
include '../includes/config.php';

// Get today's date in Y-m-d format
$today = date('Y-m-d');

// Prepare and execute the SQL query
$sql = "
        SELECT SUM(total_amount) AS total_cash_sales
        FROM sales
        WHERE payment_method = 'cash'
            AND DATE(transDate) = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$totalCashSales = $row['total_cash_sales'] ?? 0;

// Output formatted result
echo number_format($totalCashSales, 2); // e.g., 5,280.00
?>
<div id="cashSalesToday"></div>

<script>
    function fetchCashSalesToday() {
        $.ajax({
            url: 'daily_cash_sales.php',
            method: 'GET',
            success: function (data) {
                $('#cashSalesToday').text('Today\'s Cash Sales: KES ' + data);
            },
            error: function (err) {
                console.error('Error fetching daily cash sales', err);
            }
        });
    }

    fetchCashSalesToday();
    setInterval(fetchCashSalesToday, 300000); // Refresh every 5 minutes
</script>
