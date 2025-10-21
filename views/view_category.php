<?php
ob_start();
include '../includes/config.php';
include '../includes/header.php';

// Initialize $user to an empty array to avoid warnings if no user is found.
$user = []; // Important: Initialize $user

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) { // Check if any rows were returned
        $categorie = $result->fetch_assoc(); // Now $categorie is populated
    } else {
        // Handle the case where the categorie is not found.  You can redirect or display a message.
        header("Location: view_categories.php?error=category_not_found"); // Redirect with an error message
        exit(); // Important: Stop execution after redirecting
    }
    $stmt->close(); // Close the statement after use
} else {
    // Handle the case where id is not set.  You can redirect or display a message.
    header("Location: view_categories.php?error=id_missing"); // Redirect with an error message
    exit(); // Important: Stop execution after redirecting
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $name, $description, $id);

    $stmt->execute();
    $stmt->close(); // Close the statement

    header("Location: view_categories.php?success=categories_updated"); // Redirect with a success message
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    </head>
<body>
    <div class="views">
        <h2>View categorie</h2>

        <?php if (!empty($categories)): ?>  <p>categorie ID: <?php echo $categories['id']; ?></p>
            <p>Category Name: <?php echo $categories['name']; ?></p>
            <p>Description: <?php echo $categories['last_name']; ?></p>
        <?php else: ?>
            <p>category not found.</p>  <?php endif; ?>

        <a href="view_categories.php">Back to categories list</a>
    </div>
</body>
</html>