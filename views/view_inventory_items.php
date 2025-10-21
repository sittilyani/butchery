<?php
// view_inventory_items.php
include '../includes/config.php';
include '../includes/header.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE inventory_name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Fetch inventory items
$sql = "SELECT * FROM inventory_items " . $search_condition;
$result = $conn->query($sql);

// Delete functionality
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_sql = "DELETE FROM inventory_items WHERE id = ?";
    if ($stmt = $conn->prepare($delete_sql)) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            echo "<script>alert('Item deleted successfully.'); window.location.href='view_inventory_items.php';</script>";
        } else {
            echo "<script>alert('Error deleting item.');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Inventory Items</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ... your existing styles ... */
    </style>
</head>
<body>
    <div class="container">
        <h2>Inventory Items</h2>
        <input type="text" id="searchInput" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Unit Price</th>
                    <th>Selling Price</th>
                    <th>Reorder Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['inventory_id'] . "</td>";
                        echo "<td>" . $row['inventory_name'] . "</td>";
                        echo "<td>" . $row['unit_price'] . "</td>";
                        echo "<td>" . $row['selling_price'] . "</td>";
                        echo "<td>" . $row['reorder_level'] . "</td>";
                        echo "<td><a href='edit_inventory_items.php?inventory_id=" . $row['inventory_id'] . "'><i class='fas fa-edit'></i></a> | <a href='view_inventory_items.php?delete=" . $row['inventory_id'] . "' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#searchInput').on('keyup', function() {
                const search = $(this).val();
                $.ajax({
                    url: 'view_inventory_items.php',
                    type: 'GET',
                    data: { search: search },
                    success: function(data) {
                        const tableBody = $(data).find('#inventoryTableBody').html();
                        $('#inventoryTableBody').html(tableBody);
                    }
                });
            });
        });
    </script>
</body>
</html>