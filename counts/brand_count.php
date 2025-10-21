<?php

// Include the config file to access the $conn variable
include '../includes/config.php';

// Fetch the count of all brand from the database
$sql = "SELECT COUNT(*) as productCount FROM products";
$stmt = $conn->query($sql); // Use $conn instead of $pdo
$result = $stmt->fetch_assoc(); // Use fetch_assoc to get an associative array

// Get the numeric count value
$productCount = $result['productCount'];

// Output the count as plain text
echo $productCount;
?>



    <script>
        // Function to update the count of product product
        function updateproductCount() {
            $.ajax({
                url: 'product_count.php',
                type: 'GET',
                success: function (data) {
                    $('#productCount').text('product: ' + data);
                },
                error: function (error) {
                    console.error('Error fetching product count:', error);
                }
            });
        }

        // Call the function initially
        updateproductCount();

        // Set an interval to update the count every 5 minutes (300,000 milliseconds)
        setInterval(updateproductCount, 300000);
    </script>



