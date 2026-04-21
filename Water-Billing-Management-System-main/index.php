<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php'; 

// Fetch the total number of categories
$sqlCategories = "SELECT COUNT(*) as totalCategories FROM category_list";
$resultCategories = $conn->query($sqlCategories);
$totalCategories = $resultCategories->fetch_assoc()['totalCategories'];

// Fetch the total number of clients
$sqlClients = "SELECT COUNT(*) as totalClients FROM client_list WHERE status = 1";
$resultClients = $conn->query($sqlClients);
$totalClients = $resultClients->fetch_assoc()['totalClients'];

// Fetch the total number of pending bills
$sqlPendingBills = "SELECT COUNT(*) as totalPendingBills FROM billing_list WHERE status = 0";
$resultPendingBills = $conn->query($sqlPendingBills);
$totalPendingBills = $resultPendingBills->fetch_assoc()['totalPendingBills'];

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Water Billing Management System</title>
    <link rel="icon" href="logo.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        :root {
            --sidebar-color: #2c3e50;
            --primary-blue: #3182bd;
            --bg-light: #f4f7f6;
            --text-dark: #333;
            --white: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-color);
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            background: rgba(0,0,0,0.2);
        }

        .sidebar-header img {
            width: 70px;
            background: white;
            padding: 5px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .menu {
            flex: 1;
            padding-top: 20px;
        }

        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: #bdc3c7;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .menu-item i { margin-right: 15px; font-size: 20px; }

        .menu-item:hover, .menu-item.active {
            background: #34495e;
            color: white;
            border-left: 4px solid var(--primary-blue);
        }

        /* Main Content Styling */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .top-nav {
            background: var(--white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 18px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
        }

        .logout-btn:hover { background: #c0392b; }

        .content-body { padding: 40px; }

        /* Dashboard Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-bottom: 5px solid var(--primary-blue);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: #ebf5ff;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 30px;
            margin-right: 20px;
        }

        .stat-info h3 { 
            margin: 0; 
            font-size: 14px; 
            color: #7f8c8d; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p { 
            margin: 5px 0 0; 
            font-size: 32px; 
            font-weight: bold; 
            color: var(--text-dark); 
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Logo">
            <h4>WBMS</h4>
        </div>
        <nav class="menu">
            <a href="index.php" class="menu-item active"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="view_clients.php" class="menu-item"><i class='bx bxs-user-detail'></i> View Clients</a>
            <a href="view_billings.php" class="menu-item"><i class='bx bxs-receipt'></i> Billing Records</a>
            <a href="monthly_biling.php" class="menu-item"><i class='bx bxs-report'></i> Monthly Report</a>
            <a href="category_list.php" class="menu-item"><i class='bx bxs-category'></i> List of Category</a>
            <a href="user_list.php" class="menu-item"><i class='bx bxs-group'></i> User List</a>
            <a href="settings_rate.php" class="menu-item"><i class='bx bxs-cog'></i> Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="top-nav">
            <h2 style="font-weight: 600; font-size: 22px; color: #2c3e50;">Water Billing Management System</h2>
            <div class="user-info">
                <span>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            </div>
        </header>

        <div class="content-body">
            <div class="stats-grid">
                
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bxs-layer'></i></div>
                    <div class="stat-info">
                        <h3>Total Categories</h3>
                        <p><?php echo $totalCategories; ?></p>
                    </div>
                </div>

                <div class="stat-card" style="border-color: #27ae60;">
                    <div class="stat-icon" style="background: #eafaf1; color: #27ae60;"><i class='bx bxs-user-group'></i></div>
                    <div class="stat-info">
                        <h3>Total Clients</h3>
                        <p><?php echo $totalClients; ?></p>
                    </div>
                </div>

                <div class="stat-card" style="border-color: #f39c12;">
                    <div class="stat-icon" style="background: #fef5e7; color: #f39c12;"><i class='bx bxs-time-five'></i></div>
                    <div class="stat-info">
                        <h3>Pending Bills</h3>
                        <p><?php echo $totalPendingBills; ?></p>
                    </div>
                </div>

            </div>

            </div>
    </div>

</body>
</html>