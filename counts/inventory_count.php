<?php

// Include the config file to access the $conn variable
include '../includes/config.php';

// Fetch the count of all inventory from the database
$sql = "SELECT COUNT(*) as inventoryCount FROM products";
$stmt = $conn->query($sql); // Use $conn instead of $pdo
$result = $stmt->fetch_assoc(); // Use fetch_assoc to get an associative array

// Get the numeric count value
$inventoryCount = $result['inventoryCount'];

// Output the count as plain text
echo $inventoryCount;
?>



    <script>
        // Function to update the count of inventory inventory
        function updateinventoryCount() {
            $.ajax({
                url: 'inventory_count.php',
                type: 'GET',
                success: function (data) {
                    $('#inventoryCount').text('inventory: ' + data);
                },
                error: function (error) {
                    console.error('Error fetching inventory count:', error);
                }
            });
        }

        // Call the function initially
        updateinventoryCount();

        // Set an interval to update the count every 5 minutes (300,000 milliseconds)
        setInterval(updateinventoryCount, 300000);
    </script>



