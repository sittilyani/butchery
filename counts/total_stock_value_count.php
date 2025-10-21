<?php
include '../includes/config.php';

// SQL Query to get the total value of all latest stock items
$sql = "
    SELECT SUM(s.stockBalance * p.price) AS totalValue
    FROM stocks s
    INNER JOIN (
        SELECT id, MAX(stockID) AS latest_ID
        FROM stocks
        GROUP BY id
    ) latest ON s.id = latest.id AND s.stockID = latest.latest_ID
    INNER JOIN products p ON s.id = p.id
";

$result = $conn->query($sql);

$totalValue = 0;

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalValue = floatval($row['totalValue']);
}

// Output the formatted total stock value
echo number_format($totalValue, 2);
?>


    <script>
        // Function to update the count of totalvalue totalvalue
        function updatetotalvalueCount() {
            $.ajax({
                url: 'totalvalue_count.php',
                type: 'GET',
                success: function (data) {
                    $('#totalvalueCount').text('totalvalue: ' + data);
                },
                error: function (error) {
                    console.error('Error fetching totalvalue count:', error);
                }
            });
        }

        // Call the function initially
        updatetotalvalueCount();

        // Set an interval to update the count every 5 minutes (300,000 milliseconds)
        setInterval(updatetotalvalueCount, 300000);
    </script>



