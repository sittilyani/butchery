<?php
// dashboard.php
session_start();
// Assume user role is stored in session, e.g., $_SESSION['role'] = 'admin' or 'user'
// For demonstration, set a default if not set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'user'; // Change to 'admin' for testing
}

include '../includes/header.php'; // Include your header file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 1.2em;
            color: #333;
        }

        .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #007bff;
            cursor: pointer;
            transition: color 0.2s;
        }

        .number:hover {
            color: #0056b3;
        }

        .details {
            display: none;
            margin-top: 15px;
            text-align: left;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .details ul {
            list-style-type: disc;
            padding-left: 20px;
        }

        .details li {
            margin-bottom: 5px;
        }

        /* Admin-only card styling */
        .admin-only {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>

    <div class="grid">
        <!-- Card 1: Top 10 Products (accessible to all) -->
        <div class="card">
            <h2>Top 10 Products</h2>
            <div class="number" onclick="toggleDetails('top10-details')">10</div>
            <div id="top10-details" class="details">
                <ul>
                    <li>Product A - Sales: 500</li>
                    <li>Product B - Sales: 450</li>
                    <li>Product C - Sales: 400</li>
                    <li>Product D - Sales: 350</li>
                    <li>Product E - Sales: 300</li>
                    <li>Product F - Sales: 250</li>
                    <li>Product G - Sales: 200</li>
                    <li>Product H - Sales: 150</li>
                    <li>Product I - Sales: 100</li>
                    <li>Product J - Sales: 50</li>
                </ul>
            </div>
        </div>

        <!-- Card 2: Fast Moving Products (accessible to all) -->
        <div class="card">
            <h2>Fast Moving Products</h2>
            <div class="number" onclick="toggleDetails('fast-moving-details')">15</div>
            <div id="fast-moving-details" class="details">
                <ul>
                    <li>Item X - Velocity: High</li>
                    <li>Item Y - Velocity: High</li>
                    <li>Item Z - Velocity: Medium</li>
                    <!-- Add more mock items as needed -->
                </ul>
            </div>
        </div>

        <!-- Card 3: Near Expiry Products (accessible to all) -->
        <div class="card">
            <h2>Near Expiry Products</h2>
            <div class="number" onclick="toggleDetails('near-expiry-details')">8</div>
            <div id="near-expiry-details" class="details">
                <ul>
                    <li>Product P - Expires: 2025-09-01</li>
                    <li>Product Q - Expires: 2025-09-05</li>
                    <li>Product R - Expires: 2025-09-10</li>
                    <!-- Add more mock items as needed -->
                </ul>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- Card 4: Creditors (admin only) -->
        <div class="card admin-only">
            <h2>Creditors</h2>
            <div class="number" onclick="toggleDetails('creditors-details')">5</div>
            <div id="creditors-details" class="details">
                <ul>
                    <li>Creditor A - Amount: $10,000</li>
                    <li>Creditor B - Amount: $8,000</li>
                    <li>Creditor C - Amount: $5,000</li>
                    <!-- Add more mock items as needed -->
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add more cards as needed -->
    </div>

    <script>
        function toggleDetails(id) {
            var element = document.getElementById(id);
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>

</body>
</html>