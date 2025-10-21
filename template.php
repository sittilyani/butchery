<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/favicons/favicon.png">
    <link rel="apple-touch-icon" href="../assets/favicons/apple-touch-icon.png">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="content-main">
        <h2>Page Title</h2>
        <p>Add your page-specific content here.</p>
        
        <button type="submit">Submit</button>
    </div>

    <footer>
        <p>� 2025 Your Website. All rights reserved.</p>
    </footer>

    <script>
        document.querySelector('.menu-toggle')?.addEventListener('click', () => {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    </script>
</body>
</html>