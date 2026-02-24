<?php
include '../includes/config.php';

// Fetch supplier data from the database
$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
$suppliers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers List</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #cc0000;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
        }
        .main-content {
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .suppliers-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 25px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }
        h2 i { color: var(--primary); margin-right: 10px; }

        .btn-add {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 25px;
            transition: all 0.3s;
        }
        .btn-add:hover {
            background: #a00000;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(204,0,0,0.2);
        }

        .table {
            margin: 0;
            font-size: 0.9rem;
        }
        .table thead { background: var(--primary); color: white; }
        .table th { padding: 15px; font-weight: 600; }
        .table td { padding: 12px 15px; vertical-align: middle; }
        .table tbody tr:hover { background: rgba(204,0,0,0.02); }

        .action-group {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .btn-action:hover { transform: translateY(-2px); color: white; }
        .btn-update { background: var(--warning); }
        .btn-delete { background: var(--danger); }
        .btn-view { background: var(--success); }

        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .suppliers-card { padding: 20px; }
            .table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="suppliers-card">
        <h2><i class="fas fa-truck"></i>Supplier Management</h2>

        <a href="../stocks/suppliers.php" class="btn-add">
            <i class="fas fa-plus-circle"></i> Add New Supplier
        </a>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Sup ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Mobile Phone</th>
                        <th>Address</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier) : ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['supplier_id']) ?></td>
                            <td><?= htmlspecialchars($supplier['name']) ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                            <td><?= htmlspecialchars($supplier['email']) ?></td>
                            <td><?= htmlspecialchars($supplier['phone']) ?></td>
                            <td><?= htmlspecialchars($supplier['address']) ?></td>
                            <td><?= date('M d, Y', strtotime($supplier['date_created'])) ?></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action btn-update" onclick="location.href='../views/update_supplier.php?supplier_id=<?= $supplier['supplier_id'] ?>'">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                    <button class="btn-action btn-delete" onclick="if(confirm('Are you sure?')) location.href='../views/delete_supplier.php?supplier_id=<?= $supplier['supplier_id'] ?>'">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                    <button class="btn-action btn-view" onclick="location.href='view_supplier.php?supplier_id=<?= $supplier['supplier_id'] ?>'">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>