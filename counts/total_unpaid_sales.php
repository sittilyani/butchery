<?php
include '../includes/config.php';

// Query to get all-time unpaid credit sales
$sql = "
    SELECT SUM(total_amount) AS total_credit_sales
    FROM sales_drafts";

$result = $conn->query($sql);
$row = $result->fetch_assoc();
$totalcreditSales = $row['total_credit_sales'] ?? 0;

// Return the value only (for AJAX use)
echo number_format($totalcreditSales, 2); // e.g., 5,280.00
?>
<div id="creditSalesContainer">
   <p><a href="../views/view_credit_sales.php" target="_blank" style="margin-left:10px; font-size: 18px; text-decoration:none; color:blue;">
        View Creditors
    </a></p>
</div>

<script>
    function fetchCreditSales() {
        $.ajax({
            url: 'credit_sales_total.php',
            method: 'GET',
            success: function (data) {
                $('#creditSalesAmount').text('Total Credit Sales (Unpaid): KES ' + data);
            },
            error: function (err) {
                console.error('Error fetching credit sales', err);
            }
        });
    }

    fetchCreditSales();
    setInterval(fetchCreditSales, 300000); // Update every 5 minutes
</script>
