<?php
include '../includes/config.php';

// Get today's date in Y-m-d format
$today = date('Y-m-d');

// Prepare and execute the SQL query
$sql = "
        SELECT SUM(total_amount) AS total_mpesa_sales
        FROM sales
        WHERE payment_method = 'mpesa'
            AND DATE(transDate) = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$totalmpesaSales = $row['total_mpesa_sales'] ?? 0;

// Output formatted result
echo number_format($totalmpesaSales, 2); // e.g., 5,280.00
?>
<div id="mpesaSalesToday"></div>

<script>
    function fetchCashSalesToday() {
        $.ajax({
            url: 'daily_mpesa_sales.php',
            method: 'GET',
            success: function (data) {
                $('#mpesaSalesToday').text('Today\'s mpesa Sales: KES ' + data);
            },
            error: function (err) {
                console.error('Error fetching daily mpesa sales', err);
            }
        });
    }

    fetchCashSalesToday();
    setInterval(fetchmpesaSalesToday, 300000); // Refresh every 5 minutes
</script>
