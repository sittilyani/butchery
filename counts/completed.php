<?php

// Include the config file to access the $conn variable
include '../includes/config.php';

// Fetch the count of all completed from the database
$sql = "SELECT COUNT(*) as completedCount FROM sales";
$stmt = $conn->query($sql); // Use $conn instead of $pdo
$result = $stmt->fetch_assoc(); // Use fetch_assoc to get an associative array

// Get the numeric count value
$completedCount = $result['completedCount'];

// Output the count as plain text
echo $completedCount;
?>



    <script>
        // Function to update the count of completed completed
        function updatecompletedCount() {
            $.ajax({
                url: 'completed.php',
                type: 'GET',
                success: function (data) {
                    $('#completedCount').text('completed: ' + data);
                },
                error: function (error) {
                    console.error('Error fetching completed count:', error);
                }
            });
        }

        // Call the function initially
        updatecompletedCount();

        // Set an interval to update the count every 5 minutes (300,000 milliseconds)
        setInterval(updatecompletedCount, 300000);
    </script>



