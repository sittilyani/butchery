<?php
include '../includes/config.php';

// Get today's date in Y-m-d format
$today = date('Y-m-d');

// Prepare and execute the SQL query
$sql = "
        SELECT SUM(total_amount) AS total_credit_sales
        FROM sales
        WHERE payment_method = 'credit'
            AND DATE(transDate) = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$totalCreditSales = $row['total_credit_sales'] ?? 0;

// Output formatted result
echo number_format($totalCreditSales, 2); // e.g., 5,280.00
?>
<div id="creditSalesToday"></div>

<script>
    function fetchCashSalesToday() {
        $.ajax({
            url: 'daily_credit_sales.php',
            method: 'GET',
            success: function (data) {
                $('#creditSalesToday').text('Today\'s Credit Sales: KES ' + data);
            },
            error: function (err) {
                console.error('Error fetching daily credit sales', err);
            }
        });
    }

    fetchCashSalesToday();
    setInterval(fetchCreditSalesToday, 300000); // Refresh every 5 minutes
</script>
