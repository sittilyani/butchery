<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/config.php';
include '../includes/header.php';

$page_title = "Manage Staff Loans";

// Check for logged-in user
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to access this page.";
    header("Location: ../login.php");
    exit;
}

// Check user permissions
$is_approver = false;
$user_id = intval($_SESSION['user_id']);
$stmt = $conn->prepare("SELECT job_title FROM staff WHERE username = ? AND current_status = 'Active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $is_approver = in_array($row['job_title'], ['Admin', 'Manager', 'Supervisor']);
}
$stmt->close();

// Process loan request or update
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['request_loan']) || isset($_POST['update_loan']))) {
    $amount = floatval($_POST['amount']);
    $request_date = date('Y-m-d H:i:s');
    $loan_id = isset($_POST['loan_id']) ? intval($_POST['loan_id']) : 0;
    $is_update = isset($_POST['update_loan']);

    $errors = [];
    if ($amount <= 0) {
        $errors[] = "Loan amount must be greater than zero.";
    }

    // Verify user_id exists in staff table
    $stmt = $conn->prepare("SELECT username FROM staff WHERE username = ? AND current_status = 'Active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        $errors[] = "Invalid staff username or inactive staff.";
    }
    $stmt->close();

    if (empty($errors)) {
        try {
            if ($is_update) {
                $stmt = $conn->prepare("UPDATE loans SET amount = ?, status = 'Pending', request_date = ?, resubmitted = 1 WHERE loan_id = ? AND user_id = ? AND status IN ('Pending', 'Rejected')");
                $stmt->bind_param("dsii", $amount, $request_date, $loan_id, $user_id);
            } else {
                // Log input values for debugging
                error_log("Inserting loan: user_id=$user_id, amount=$amount, request_date=$request_date, status=Pending");
                $stmt = $conn->prepare("INSERT INTO loans (user_id, amount, request_date, status) VALUES (?, ?, ?, 'Pending')");
                $stmt->bind_param("ids", $user_id, $amount, $request_date);
            }
            if ($stmt->execute()) {
                $_SESSION['success_message'] = $is_update ? "Loan updated and resubmitted successfully." : "Loan request submitted successfully.";
            } else {
                $_SESSION['error_message'] = "Error " . ($is_update ? "updating" : "submitting") . " loan request: " . $stmt->error;
            }
            $stmt->close();
            header("Location: staff_loans.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            error_log("Loan insert/update failed: " . $e->getMessage());
            $stmt->close();
            ob_flush();
        }
    } else {
        $_SESSION['error_message'] = implode(" ", $errors);
    }
}

// Process loan approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_loan']) && $is_approver) {
    $loan_id = intval($_POST['loan_id']);
    $status = $_POST['status'];
    $approved_by = intval($_POST['approved_by']);
    $reason = trim($_POST['reason']);
    $approved_date = date('Y-m-d H:i:s');

    // Validate status against allowed values
    if (!in_array($status, ['Approved', 'Rejected'])) {
        $_SESSION['error_message'] = "Invalid status value.";
        header("url: staff_loans.php");
        close();
    }

    $stmt = $conn->prepare("UPDATE loans SET status = ?, approved_by = ?, approved_date = ?, reason = ? WHERE loan_id = ? AND status = 'Pending'");
    $stmt->bind_param("sissi", $status, $approved_by, $approved_date, $reason, $loan_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Loan " . ($status == 'Approved' ? 'approved' : 'rejected') . " successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating loan status: " . $stmt->error;
    }
    $stmt->close();
    header("Location: staff_loans.php");
    exit;
}

// Fetch approvers for dropdown
$approvers = [];
$stmt = $conn->prepare("SELECT username, first_name FROM staff WHERE job_title IN ('Admin', 'Manager', 'Supervisor') AND current_status = 'Active' ORDER BY first_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $approvers[] = ['id' => $row['username'], 'name' => $row['first_name']];
}
$stmt->close();

// Fetch user’s loans (for applicant)
$user_loans = [];
$stmt = $conn->prepare("
    SELECT l.loan_id, l.amount, l.request_date, l.status, l.approved_by, s.first_name AS approver_name, l.approved_date, l.reason, l.resubmitted
    FROM loans l
    LEFT JOIN staff s ON l.approved_by = s.username
    WHERE l.username = ?
    ORDER BY l.request_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['staff_name'] = $_SESSION['username']; // Add staff_name for consistency
    $user_loans[] = $row;
}
$stmt->close();

// Fetch pending and approved loans (for approvers)
$pending_loans = [];
$approved_loans = [];
if ($is_approver) {
    $stmt = $conn->prepare("
        SELECT l.loan_id, l.user_id, s.first_name AS staff_name, l.amount, l.request_date, l.status, l.approved_by, s2.first_name AS approver_name, l.approved_date, l.reason, l.resubmitted
        FROM loans l
        JOIN staff s ON l.user_id = s.user_id
        LEFT JOIN staff s2 ON l.approved_by = s2.user_id
        WHERE l.status = 'Pending'
        ORDER BY l.request_date DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pending_loans[] = $row;
    }
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT l.loan_id, l.user_id, s.first_name AS staff_name, l.amount, l.request_date, l.status, l.approved_by, s2.first_name AS approver_name, l.approved_date, l.reason, l.resubmitted
        FROM loans l
        JOIN staff s ON l.user_id = s.user_id
        LEFT JOIN staff s2 ON l.approved_by = s2.user_id
        WHERE l.status IN ('Approved', 'Rejected')
        ORDER BY l.approved_date DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $approved_loans[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #6c757d;
            --background-light: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --success-bg-color: #d4edda;
            --text-color: #343a40;
            --input-border: #ced4da;
            --input-focus-border: #80bdff;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --font-family: 'Arial', sans-serif;
        }

        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
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

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .alert-success {
            background-color: var(--success-bg-color);
            color: var(--success-color);
            border-color: var(--success-color);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #dc3545;
            border-color: #f5c6cb;
        }

        form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 20px;
            background-color: #FFFFE0;
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
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--input-border);
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .readonly-input {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .custom-submit-btn {
            grid-column: 1 / -1;
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
            background-color: #004085;
            transform: translateY(-2px);
        }

        .custom-submit-btn:active {
            transform: translateY(0);
        }

        .action-form .btn {
            margin: 5px;
        }

        @media (max-width: 992px) {
            form {
                grid-template-columns: repeat(2, 1fr);
            }
            .custom-submit-btn {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2><?php echo htmlspecialchars($page_title); ?></h2>

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

    <?php if (!$is_approver): ?>
        <h4>Request Loan</h4>
        <form action="staff_loans.php" method="post" class="mb-4">
            <div class="form-group">
                <label for="amount" class="form-label">Loan Amount (KES)</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <button type="submit" name="request_loan" class="custom-submit-btn">Request Loan</button>
        </form>
    <?php endif; ?>

    <h4><?php echo $is_approver ? 'All Loans' : 'Your Loans'; ?></h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Staff</th>
                    <th>Amount (KES)</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Approver</th>
                    <th>Approved Date</th>
                    <th>Reason</th>
                    <th>Resubmitted</th>
                    <?php if (!$is_approver): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_loans as $index => $loan): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($loan['staff_name'] ?? 'You'); ?></td>
                        <td><?php echo number_format($loan['amount'], 2); ?></td>
                        <td><?php echo $loan['request_date']; ?></td>
                        <td><?php echo htmlspecialchars($loan['status']); ?></td>
                        <td><?php echo htmlspecialchars($loan['approver_name'] ?? 'N/A'); ?></td>
                        <td><?php echo $loan['approved_date'] ?? 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($loan['reason'] ?? 'N/A'); ?></td>
                        <td><?php echo $loan['resubmitted'] ? 'Yes' : 'No'; ?></td>
                        <?php if (!$is_approver && in_array($loan['status'], ['Pending', 'Rejected'])): ?>
                            <td>
                                <form action="staff_loans.php" method="post" class="mb-0">
                                    <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                    <div class="form-group">
                                        <label for="amount_<?php echo $loan['loan_id']; ?>" class="form-label">Loan Amount (KES)</label>
                                        <input type="number" id="amount_<?php echo $loan['loan_id']; ?>" name="amount" class="form-control" step="0.01" min="0.01" value="<?php echo $loan['amount']; ?>" required>
                                    </div>
                                    <button type="submit" name="update_loan" class="custom-submit-btn btn-sm mt-2">Update & Resubmit</button>
                                </form>
                            </td>
                        <?php else: ?>
                            <td>-</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($is_approver): ?>
        <h4>Pending Loans for Approval</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff</th>
                        <th>Amount (KES)</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Resubmitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_loans as $index => $loan): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($loan['staff_name']); ?></td>
                            <td><?php echo number_format($loan['amount'], 2); ?></td>
                            <td><?php echo $loan['request_date']; ?></td>
                            <td><?php echo htmlspecialchars($loan['status']); ?></td>
                            <td><?php echo $loan['resubmitted'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <form action="staff_loans.php" method="post" class="action-form">
                                    <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                    <select name="approved_by" class="form-control w-auto" required>
                                        <option value="">Select Approver</option>
                                        <?php foreach ($approvers as $approver): ?>
                                            <option value="<?php echo $approver['id']; ?>" <?php echo $approver['id'] == $user_id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($approver['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <textarea name="reason" class="form-control w-auto" placeholder="Reason for approval/rejection" rows="2"></textarea>
                                    <button type="submit" name="approve_loan" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="approve_loan" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h4>Approved/Rejected Loans</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff</th>
                        <th>Amount (KES)</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Approver</th>
                        <th>Approved Date</th>
                        <th>Reason</th>
                        <th>Resubmitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approved_loans as $index => $loan): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($loan['staff_name']); ?></td>
                            <td><?php echo number_format($loan['amount'], 2); ?></td>
                            <td><?php echo $loan['request_date']; ?></td>
                            <td><?php echo htmlspecialchars($loan['status']); ?></td>
                            <td><?php echo htmlspecialchars($loan['approver_name'] ?? 'N/A'); ?></td>
                            <td><?php echo $loan['approved_date'] ?? 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($loan['reason'] ?? 'N/A'); ?></td>
                            <td><?php echo $loan['resubmitted'] ? 'Yes' : 'No'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>