<?php
include 'db.php'; 
session_start();

// Siguroha nga naka-login ang user
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch users (type 1 for Admin, type 2 for Staff)
$sql = "SELECT id, firstname, middlename, lastname, username, type, date_added, date_updated FROM users WHERE type IN (1, 2)";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List | WBMS</title>
    <link rel="icon" href="logo.png">
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <style>
        :root {
            --sidebar-color: #2c3e50;
            --primary-blue: #3182bd;
            --bg-light: #f8fafc;
            --accent-teal: #4FCFC8;
        }

        body { 
            margin: 0; 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--bg-light); 
            display: flex; 
            height: 100vh; 
            overflow: hidden;
        }

        /* Sidebar Customization */
        .sidebar { 
            width: 260px; 
            background-color: var(--sidebar-color); 
            color: white; 
            display: flex; 
            flex-direction: column; 
            flex-shrink: 0; 
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header { 
            padding: 30px 20px; 
            text-align: center; 
            background: rgba(0,0,0,0.1); 
        }
        .sidebar-header img { 
            width: 65px; 
            background: white; 
            border-radius: 50%; 
            padding: 5px; 
            margin-bottom: 10px;
        }
        .menu-item { 
            padding: 14px 25px; 
            display: flex; 
            align-items: center; 
            color: #bdc3c7; 
            text-decoration: none; 
            transition: all 0.3s ease; 
            border-left: 4px solid transparent;
            font-size: 14px;
        }
        .menu-item:hover, .menu-item.active { 
            background: rgba(255,255,255,0.05); 
            color: white; 
            border-left: 4px solid var(--accent-teal); 
        }
        .menu-item i { margin-right: 15px; font-size: 20px; }

        /* Main Content Area */
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
        
        .top-nav { 
            background: white; 
            padding: 15px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .content-body { padding: 40px; }

        /* Modern Table Card */
        .data-card { 
            background: white; 
            padding: 0; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); 
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .table { margin-bottom: 0; }
        .table thead { 
            background-color: #f8fafc; 
            border-bottom: 2px solid #edf2f7;
        }
        .table thead th { 
            padding: 18px 25px; 
            font-weight: 600; 
            color: #64748b; 
            text-transform: uppercase; 
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .table tbody td { 
            padding: 18px 25px; 
            vertical-align: middle;
            color: #334155;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Status Badges */
        .badge-role {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 11px;
        }
        .badge-admin { background-color: #e2e8f0; color: #475569; }
        .badge-staff { background-color: #dcfce7; color: #166534; }

        .btn-add { 
            background: #256391;
            color: white; 
            border: none; 
            padding: 12px 24px; 
            border-radius: 10px; 
            transition: 0.3s ease; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(79, 207, 200, 0.3);
        }
        .btn-add:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 207, 200, 0.4);
            color: white;
            
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--primary-blue);
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Logo">
            <h4 style="font-size: 18px; font-weight: 600; margin: 0;">WBMS</h4>
        </div>
        <nav class="mt-4">
            <a href="index.php" class="menu-item"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="view_clients.php" class="menu-item"><i class='bx bxs-user-detail'></i> View Clients</a>
            <a href="view_billings.php" class="menu-item"><i class='bx bxs-receipt'></i> Billing Records</a>
            <a href="monthly_biling.php" class="menu-item"><i class='bx bxs-report'></i> Monthly Report</a>
            <a href="category_list.php" class="menu-item"><i class='bx bxs-category'></i> List of Category</a>
            <a href="user_list.php" class="menu-item active"><i class='bx bxs-group'></i> User List</a>
            <a href="settings_rate.php" class="menu-item"><i class='bx bxs-cog'></i> Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="top-nav">
            <div class="d-flex align-items-center">
                <i class='bx bx-menu-alt-left me-3 text-muted' style="font-size: 24px;"></i>
                <h5 class="m-0" style="font-weight: 600;">System User Management</h5>
            </div>
            <div class="d-flex align-items-center text-muted">
                <i class='bx bx-bell me-3' style="font-size: 20px;"></i>
                <span class="small">Administrator: <strong><?php echo $_SESSION['username']; ?></strong></span>
            </div>
        </header>

        <div class="content-body">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="m-0" style="font-weight: 700; color: #1e293b;">Active Users</h3>
            <p class="text-muted small">Manage administrative and staff access levels.</p>
        </div>
        
        <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 1): ?>
            <a href="add_user.php" class="btn-add">
                <i class='bx bx-plus-circle me-2'></i> Register New User
            </a>
        <?php endif; ?>
    </div>

            <div class="data-card">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>User Information</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <?php 
                                        $fullName = $row['firstname'] . " " . ($row['middlename'] ? $row['middlename'][0] . ". " : "") . $row['lastname'];
                                        $roleClass = ($row['type'] == 1) ? "badge-admin" : "badge-staff";
                                        $roleLabel = ($row['type'] == 1) ? "ADMIN" : "STAFF";
                                    ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $row['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar">
                                                    <i class='bx bxs-user'></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; color: #1e293b;"><?php echo $fullName; ?></div>
                                                    <div class="text-muted" style="font-size: 11px;">System Access User</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-monospace text-primary" style="font-size: 13px;"><?php echo $row['username']; ?></td>
                                        <td><span class="badge-role <?php echo $roleClass; ?>"><?php echo $roleLabel; ?></span></td>
                                        <td class="small text-muted">
                                            <i class='bx bx-calendar-event me-1'></i> <?php echo date("M d, Y", strtotime($row['date_added'])); ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success small"><i class='bx bxs-circle me-1' style="font-size: 8px;"></i> Online</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center">
                        <img src="no-data.svg" alt="No data" style="width: 150px; opacity: 0.5;">
                        <p class="mt-3 text-muted">No system users found in the database.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>