<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Gi-select nato tanan gikan sa client_list
$sql = "SELECT * FROM client_list WHERE delete_flag = 0"; // I-filter nato ang wala na-delete
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Clients | WBMS</title>
    <link rel="icon" href="logo.png">
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <style>
        :root {
            --sidebar-color: #2c3e50;
            --primary-blue: #3182bd;
            --bg-light: #f4f7f6;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            display: flex;
            height: 100vh;
        }

        /* Sidebar & Layout */
        .sidebar { width: 260px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 30px 20px; text-align: center; background: rgba(0,0,0,0.2); }
        .sidebar-header img { width: 60px; background: white; border-radius: 50%; padding: 5px; }
        .menu-item { padding: 15px 25px; display: flex; align-items: center; color: #bdc3c7; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .menu-item:hover, .menu-item.active { background: #34495e; color: white; border-left: 4px solid var(--primary-blue); }
        .menu-item i { margin-right: 15px; font-size: 20px; }
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
        .top-nav { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .content-body { padding: 30px; }
        .data-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { background-color: var(--primary-blue); color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; }

        /* Action Buttons */
        .action-btn { padding: 7px 10px; border-radius: 6px; text-decoration: none; font-size: 18px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease; border: none; }
        .btn-view { background: #f3e5f5; color: #7b1fa2; margin-right: 5px; }
        .btn-edit { background: #e3f2fd; color: #1976d2; margin-right: 5px; }
        .btn-delete { background: #ffebee; color: #d32f2f; }

        /* Table Text Wrap */
        .table th { font-size: 11px; white-space: nowrap; }
        .table td { font-size: 13px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Logo">
            <h4 class="mt-2">WBMS</h4>
        </div>
        <nav class="mt-3">
            <a href="index.php" class="menu-item"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="view_clients.php" class="menu-item active"><i class='bx bxs-user-detail'></i> View Clients</a>
            <a href="view_billings.php" class="menu-item"><i class='bx bxs-receipt'></i> Billing Records</a>
            <a href="monthly_biling.php" class="menu-item"><i class='bx bxs-report'></i> Monthly Report</a>
            <a href="category_list.php" class="menu-item"><i class='bx bxs-category'></i> List of Category</a>
            <a href="user_list.php" class="menu-item"><i class='bx bxs-group'></i> User List</a>
            <a href="settings_rate.php" class="menu-item"><i class='bx bxs-cog'></i> Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="top-nav">
            <h4 class="m-0">Client Management</h4>
            <span>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
        </header>

        <div class="content-body">
            <div class="data-card">
                <div class="card-header">
                    <h5 class="m-0">List of Registered Clients</h5>
                    <a href="add_client.php" class="btn-add"><i class='bx bx-plus'></i> Add New Client</a>
                </div>

                <div class="table-responsive">
                    <table id="clientTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Birthdate</th>
                                <th>Purok</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Kuhaon ang middle initial o ang tibuok middle name
            $middle = (!empty($row['middlename'])) ? $row['middlename'] : '';
            
            echo "<tr>";
            echo "<td><span class='badge bg-light text-dark'>{$row['code']}</span></td>";
            echo "<td>{$row['firstname']} {$middle} {$row['lastname']}</td>"; // Gi-apil na ang middle name diri
            echo "<td>" . ($row['gender'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['birthdate'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['purok'] ?? 'N/A') . "</td>";
            echo "<td>{$row['contact']}</td>";
            echo "<td>{$row['address']}</td>";
            echo "<td class='text-center'>";
            echo "<a href='view_client.php?id={$row['id']}' class='action-btn btn-view' title='View'><i class='bx bxs-show'></i></a>";
            echo "<a href='edit_client.php?id={$row['id']}' class='action-btn btn-edit' title='Edit'><i class='bx bxs-edit-alt'></i></a>";
            echo "<a href='delete_client.php?id={$row['id']}' class='action-btn btn-delete' title='Delete' onclick='return confirm(\"Are you sure?\")'><i class='bx bxs-trash-alt'></i></a>";
            echo "</td>";
            echo "</tr>";
        }
    }
    ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#clientTable').DataTable({
                "pageLength": 10,
                "order": [[0, "desc"]],
                "info": false,
                "language": {
                    "paginate": { "previous": "<", "next": ">" }
                }
            });
        });
    </script>
</body>
</html>