<?php
include '../includes/config.php';

// Get current date in YYYY-MM-DD format
$currentDate = date('Y-m-d');

// Query to count unique sales for today
$sql = "
    SELECT COUNT(DISTINCT receipt_id) AS daily_sales_count
    FROM sales
    WHERE DATE(created_at) = '$currentDate'
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $dailySalesCount = $row['daily_sales_count'] ?? 0;
} else {
    $dailySalesCount = 0;
    error_log("Error in sales count query: " . $conn->error);
}

// Close connection
$conn->close();
?>

<div id="SalesContainer">
    <p id="SalesAmount" style="margin-left:10px;">
        <?php echo $dailySalesCount; ?>
    </p>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function fetchSalesCount() {
        $.ajax({
            url: window.location.href, // Refresh from same file
            method: 'GET',
            success: function(response) {
                // Extract just the number from the response
                const salesCount = $(response).filter('#SalesAmount').text().match(/\d+/)[0];
                $('#SalesAmount').html("Today's Sales Count: <strong>" + salesCount + "</strong>");

                // Update the time of last refresh
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                $('#lastUpdate').text("Last updated: " + timeString);
            },
            error: function(err) {
                console.error('Error fetching sales count', err);
                $('#SalesAmount').html("Today's Sales Count: <span style='color:red'>Error refreshing</span>");
            }
        });
    }

    // Initial fetch
    fetchSalesCount();

    // Refresh every 5 minutes (300000ms)
    setInterval(fetchSalesCount, 300000);

    // Add last update time display
    $(document).ready(function() {
        $('#SalesContainer').append('<p id="lastUpdate" style="margin-left:10px; color:#666;"></p>');
    });
</script>
