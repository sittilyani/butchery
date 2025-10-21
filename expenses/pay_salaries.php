<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/config.php';
include '../includes/header.php';

$page_title = "Pay Salaries";

// Check for logged-in user
if (!isset($_SESSION['username'])) {
    $_SESSION['error_message'] = "Please log in to access this page.";
    header("Location: ../login.php");
    exit;
}

// Process salary payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_salary'])) {
    $staff_id = intval($_POST['staff_id']);
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];
    $payment_date = date('Y-m-d H:i:s');

    $errors = [];
    if ($staff_id <= 0) {
        $errors[] = "Invalid staff selected.";
    }
    if ($amount <= 0) {
        $errors[] = "Salary amount must be greater than zero.";
    }
    if (!in_array($payment_method, ['Cash', 'Mpesa', 'Bank'])) {
        $errors[] = "Invalid payment method.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO salary_payments (staff_id, amount, payment_date, payment_method, status) VALUES (?, ?, ?, ?, 'Paid')");
        $stmt->bind_param("idss", $staff_id, $amount, $payment_date, $payment_method);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Salary paid successfully.";
        } else {
            $_SESSION['error_message'] = "Error paying salary: " . $conn->error;
        }
        $stmt->close();
        header("Location: pay_salaries.php");
        exit;
    } else {
        $_SESSION['error_message'] = implode(" ", $errors);
    }
}

// Fetch staff for dropdown
$staff_list = [];
$stmt = $conn->prepare("SELECT staff_id, first_name FROM staff WHERE current_status = 'Active' ORDER BY first_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $staff_list[] = $row;
}
$stmt->close();

// Fetch salary payments
$payments = [];
$stmt = $conn->prepare("
    SELECT sp.payment_id, sp.staff_id, s.first_name AS staff_name, sp.amount, sp.payment_date, sp.payment_method, sp.status
    FROM salary_payments sp
    JOIN staff s ON sp.staff_id = s.staff_id
    ORDER BY sp.payment_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        /* CSS Variables for easy theme changes */
        :root {
            --primary-color: #0056b3; /* Darker blue for primary actions */
            --secondary-color: #6c757d; /* Grey for secondary elements */
            --background-light: #f8f9fa; /* Light background for overall page */
            --card-background: #ffffff; /* White for form background */
            --border-color: #dee2e6;
            --success-color: #28a745;
            --success-bg-color: #d4edda;
            --text-color: #343a40;
            --input-border: #ced4da;
            --input-focus-border: #80bdff;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --font-family: 'Arial', sans-serif; /* Changed from Times New Roman for a modern look */
        }

        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto; /* Center the main content */
            background-color: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 4px 10px var(--shadow-light);
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8em;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        #success-message {
            background-color: var(--success-bg-color);
            color: var(--success-color);
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid var(--success-color);
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        #success-message .fas {
            font-size: 1.2em;
        }


        form {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Three equal columns */
            gap: 25px; /* Spacing between columns and rows */
            padding: 20px;
            background-color: #FFFFE0; /* Original light yellow background */
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 2px 5px var(--shadow-light);
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding in width */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .readonly-input {
            background-color: #e9ecef; /* Light gray for readonly fields */
            cursor: not-allowed;
        }

        .custom-submit-btn {
            grid-column: 1 / -1; /* Make the button span all three columns */
            padding: 15px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-submit-btn:hover {
            background-color: #004085; /* Darker shade on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        .custom-submit-btn:active {
            transform: translateY(0);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            form {
                grid-template-columns: repeat(2, 1fr); /* Two columns on medium screens */
            }
            .custom-submit-btn {
                grid-column: 1 / -1; /* Still span full width */
            }
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
        }

        .main-content{
            width: 50%; margin-left: auto; margin-right:
            auto; padding: 20px;
        }

    </style>
</head>
<body>
<div class="main-content">
    
        <h2 class="text-center mb-4"><?php echo htmlspecialchars($page_title); ?></h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <h4>Pay Salary</h4>
        <form action="pay_salaries.php" method="post" class="mb-4">
            <div class="form-grid">
                <div>
                    <label for="staff_id" class="form-label">Staff</label>
                    <select id="staff_id" name="staff_id" class="form-select" required>
                        <option value="">Select Staff</option>
                        <?php foreach ($staff_list as $staff): ?>
                            <option value="<?php echo $staff['staff_id']; ?>">
                                <?php echo htmlspecialchars($staff['first_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="amount" class="form-label">Salary Amount (KES)</label>
                    <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                </div>
                <div>
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-select" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash">Cash</option>
                        <option value="Mpesa">Mpesa</option>
                        <option value="Bank">Bank</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button type="submit" name="pay_salary" class="btn btn-primary">Pay Salary</button>
            </div>
        </form>

        <h4>Salary Payments</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff</th>
                        <th>Amount (KES)</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $index => $payment): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($payment['staff_name']); ?></td>
                            <td><?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo $payment['payment_date']; ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($payment['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>