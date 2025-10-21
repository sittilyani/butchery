<?php

// Include the config file to access the $conn variable
include '../includes/config.php';

// Fetch the count of all category from the database
$sql = "SELECT COUNT(*) as categoryCount FROM categories";
$stmt = $conn->query($sql); // Use $conn instead of $pdo
$result = $stmt->fetch_assoc(); // Use fetch_assoc to get an associative array

// Get the numeric count value
$categoryCount = $result['categoryCount'];

// Output the count as plain text
echo $categoryCount;
?>



    <script>
        // Function to update the count of category category
        function updatecategoryCount() {
            $.ajax({
                url: 'category_count.php',
                type: 'GET',
                success: function (data) {
                    $('#categoryCount').text('category: ' + data);
                },
                error: function (error) {
                    console.error('Error fetching category count:', error);
                }
            });
        }

        // Call the function initially
        updatecategoryCount();

        // Set an interval to update the count every 5 minutes (300,000 milliseconds)
        setInterval(updatecategoryCount, 300000);
    </script>



