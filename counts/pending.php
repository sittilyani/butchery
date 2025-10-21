<?php

// Include the config file to access the $conn variable
include '../includes/config.php';

// Fetch the count of all pending from the database
$sql = "SELECT COUNT(*) as pendingCount FROM sales_drafts";
$stmt = $conn->query($sql); // Use $conn instead of $pdo
$result = $stmt->fetch_assoc(); // Use fetch_assoc to get an associative array

// Get the numeric count value
$pendingCount = $result['pendingCount'];

// Output the count as plain text
echo $pendingCount;
?>



    <script>
        // Function to update the count of pending pending
        function updatependingCount() {
            $.ajax({
                url: 'pending.php',
                type: 'GET',
                success: function (data) {
                    $('#pendingCount').text('pending: ' + data);
                },
                error: function (error) {
                    console.error('Error fetching pending count:', error);
                }
            });
        }

        // Call the function initially
        updatependingCount();

        // Set an interval to update the count every 5 minutes (300,000 milliseconds)
        setInterval(updatependingCount, 300000);
    </script>



