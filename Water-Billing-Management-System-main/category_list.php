<?php
include 'db.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories and rates
$sql = "SELECT * FROM category_list WHERE delete_flag = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Water Rates | WBMS</title>
    <link rel="icon" href="logo.png">
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <style>
        :root {
            --sidebar-color: #1e293b; 
            --primary-accent: #4FCFC8;
            --bg-light: #f8fafc;
            --text-main: #334155;
        }

        body { margin: 0; font-family: 'Inter', sans-serif; background-color: var(--bg-light); display: flex; height: 100vh; color: var(--text-main); }

        /* Sidebar - Simple Style */
        .sidebar { width: 280px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 40px 20px; text-align: center; }
        .sidebar-header img { width: 70px; background: white; border-radius: 12px; padding: 5px; }
        
        .menu-item { 
            padding: 14px 25px; 
            display: flex; 
            align-items: center; 
            color: #94a3b8; 
            text-decoration: none; 
            transition: 0.2s; 
            margin: 4px 15px; 
            border-radius: 8px; 
        }

        /* Simple Hover */
        .menu-item:hover { 
            background: rgba(255,255,255,0.08); 
            color: white; 
        }

        /* Simple Active State - Walay Teal Color */
        .menu-item.active { 
            background: rgba(255,255,255,0.15); 
            color: white; 
            font-weight: 600;
            pointer-events: none; /* Tangtang hover effect */
        }

        .menu-item i { margin-right: 15px; font-size: 22px; }

        /* Main Content */
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
        .top-nav { background: white; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .content-body { padding: 40px; max-width: 1200px; }

        .data-card { 
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            overflow: hidden;
            border: 1px solid #edf2f7;
        }
        
        /* Table Styling */
        .table thead { background-color: #f1f5f9; }
        .table thead th { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; padding: 15px 25px; border: none; }
        .table tbody td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; }

        .rate-text { color: #10b981; font-weight: 700; }
        
        /* Action Buttons */
        .btn-add { background-color: #256391; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-add:hover { background-color: #1e4a6d; color: white; transform: translateY(-2px); }
        
        .action-btn { padding: 7px 10px; border-radius: 6px; text-decoration: none; font-size: 18px; display: inline-flex; align-items: center; transition: 0.2s; }
        .btn-edit { background: #e3f2fd; color: #1976d2; margin-right: 5px; }
        .btn-delete { background: #ffebee; color: #d32f2f; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Logo">
            <h5 class="mt-3 font-weight-bold">WBMS</h5>
        </div>
        <nav>
            <a href="index.php" class="menu-item"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="view_clients.php" class="menu-item"><i class='bx bxs-user-detail'></i> View Clients</a>
            <a href="view_billings.php" class="menu-item"><i class='bx bxs-receipt'></i> Billing Records</a>
            <a href="monthly_biling.php" class="menu-item"><i class='bx bxs-report'></i> Monthly Report</a>
            <a href="category_list.php" class="menu-item active"><i class='bx bxs-category'></i> List of Category</a>
            <a href="user_list.php" class="menu-item"><i class='bx bxs-group'></i> User List</a>
            <a href="settings_rate.php" class="menu-item"><i class='bx bxs-cog'></i> Settings</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <h4 class="m-0 font-weight-bold">Water Category & Rates</h4>
            <div class="user-profile">
                <small class="text-muted">Logged in as:</small>
                <strong class="ms-1"><?php echo $_SESSION['username']; ?></strong>
            </div>
        </header>

        <div class="content-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted m-0">Define and manage water usage rates for different consumer types.</p>
                <a href="add_category.php" class="btn-add"><i class='bx bx-plus-circle'></i> Add New Category</a>
            </div>

            <div class="data-card">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th width="80px">ID</th>
                                <th>Category Name</th>
                                <th>Rate (per m³)</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $row['id']; ?></td>
                                        <td><strong><?php echo $row['name']; ?></strong></td>
                                        <td><span class="rate-text">₱ <?php echo number_format($row['rate'], 2); ?></span></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo ($row['status'] == 1) ? 'bg-success' : 'bg-danger'; ?> bg-opacity-10 <?php echo ($row['status'] == 1) ? 'text-success' : 'text-danger'; ?> px-3">
                                                <?php echo ($row['status'] == 1) ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="edit_category.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit">
                                                <i class='bx bxs-edit-alt'></i>
                                            </a>
                                            <a href="delete_category.php?id=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Sigurado ka?')">
                                                <i class='bx bxs-trash-alt'></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center p-4 text-muted">No data found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>