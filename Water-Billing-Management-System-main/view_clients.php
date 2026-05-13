<?php
// Module for resident profiling and client data management - INTEGRATED BRIDGE VERSION
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 1. KUHAON ANG TOKEN GIKAN SA SESSION
$token = $_SESSION['token'] ?? ''; 

if (empty($token)) {
    // Kung walay token, pabalikon sa login
    header("Location: login.php?error=no_token");
    exit();
}

// 2. TAWGON ANG NODE.JS BRIDGE API PARA SA MGA CLIENTS
$url = 'http://localhost:3000/api/clients'; // Ang imong Bridge URL
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token, // I-pasa ang gate pass
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// I-convert ang JSON response gikan sa Node.js ngadto sa PHP array
$clients = ($httpCode == 200) ? json_decode($response, true) : [];

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
    // Usba gikan sa $result ngadto sa $clients
    if (!empty($clients)) { 
        foreach ($clients as $row) {
            $middle = (!empty($row['middlename'])) ? $row['middlename'] : '';
            
            echo "<tr>";
            echo "<td><span class='badge bg-light text-dark'>" . htmlspecialchars($row['code']) . "</span></td>";
            echo "<td>" . htmlspecialchars($row['firstname'] . " " . $middle . " " . $row['lastname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gender'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['birthdate'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['purok'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['contact'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['address'] ?? 'N/A') . "</td>";
            echo "<td class='text-center'>";
            echo "<a href='view_client.php?id={$row['id']}' class='action-btn btn-view' title='View'><i class='bx bxs-show'></i></a>";
            echo "<a href='edit_client.php?id={$row['id']}' class='action-btn btn-edit' title='Edit'><i class='bx bxs-edit-alt'></i></a>";
            echo "<a href='delete_client.php?id={$row['id']}' class='action-btn btn-delete' title='Delete' onclick='return confirm(\"Are you sure?\")'><i class='bx bxs-trash-alt'></i></a>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8' class='text-center text-muted'>No clients found in the integrated database.</td></tr>";
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