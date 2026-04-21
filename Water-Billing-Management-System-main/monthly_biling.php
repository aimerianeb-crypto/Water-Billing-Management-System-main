<?php
include 'db.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Default to current month and year
$currentMonth = date('m');
$currentYear = date('Y');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentMonth = $_POST['month'];
    $currentYear = $_POST['year'];
}

// Fetch all active clients
$sql = "SELECT * FROM client_list WHERE status = 1";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Billing Report | WBMS</title>
    <link rel="icon" href="logo.png">
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <style>
        :root {
            --sidebar-color: #2c3e50;
            --primary-blue: #3182bd;
            --bg-light: #f4f7f6;
        }

        body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: var(--bg-light); display: flex; height: 100vh; }

        /* Sidebar Styling */
        .sidebar { width: 260px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 30px 20px; text-align: center; background: rgba(0,0,0,0.2); }
        .sidebar-header img { width: 60px; background: white; border-radius: 50%; padding: 5px; }
        .menu-item { padding: 15px 25px; display: flex; align-items: center; color: #bdc3c7; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .menu-item:hover, .menu-item.active { background: #34495e; color: white; border-left: 4px solid var(--primary-blue); }
        .menu-item i { margin-right: 15px; font-size: 20px; }

        /* Main Content */
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }
        .top-nav { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .content-body { padding: 30px; }

        .report-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px; }
        
        /* Filter Section */
        .filter-box { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 25px; display: flex; gap: 15px; align-items: flex-end; border: 1px solid #e0e0e0; }
        .filter-box select, .filter-box button { height: 40px; border-radius: 5px; }

        .client-section { border-left: 4px solid var(--primary-blue); padding-left: 15px; margin-bottom: 15px; margin-top: 30px; }
        .table thead { background-color: #f8f9fa; font-size: 13px; }
        .status-paid { color: #27ae60; font-weight: bold; background: #eafaf1; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
        .status-pending { color: #e74c3c; font-weight: bold; background: #fdedec; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
        
        .btn-print { background-color: #34495e; color: white; font-size: 11px; border: none; padding: 5px 12px; border-radius: 4px; transition: 0.3s; text-decoration: none; }
        .btn-print:hover { background-color: #2c3e50; color: white; }

        @media print {
            .sidebar, .top-nav, .filter-box, .btn-print-action { display: none !important; }
            .main-content { width: 100%; }
            .report-card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Logo">
            <h4 class="mt-2" style="font-size: 16px;">WBMS</h4>
        </div>
        <nav class="mt-3">
            <a href="index.php" class="menu-item"><i class='bx bxs-dashboard'></i> Dashboard</a>
            <a href="view_clients.php" class="menu-item"><i class='bx bxs-user-detail'></i> View Clients</a>
            <a href="view_billings.php" class="menu-item"><i class='bx bxs-receipt'></i> Billing Records</a>
            <a href="monthly_biling.php" class="menu-item active"><i class='bx bxs-report'></i> Monthly Report</a>
            <a href="category_list.php" class="menu-item"><i class='bx bxs-category'></i> List of Category</a>
            <a href="user_list.php" class="menu-item"><i class='bx bxs-group'></i> User List</a>
            <a href="settings_rate.php" class="menu-item"><i class='bx bxs-cog'></i> Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="top-nav">
            <h4 class="m-0">Monthly Billing Report</h4>
            <span>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
        </header>

        <div class="content-body">
            <form method="post" class="filter-box shadow-sm">
                <div>
                    <label class="form-label small fw-bold">Select Month</label>
                    <select name="month" class="form-select">
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            $monthName = date("F", mktime(0, 0, 0, $m, 1));
                            echo "<option value='$m' ".($m == $currentMonth ? 'selected' : '').">$monthName</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="form-label small fw-bold">Select Year</label>
                    <select name="year" class="form-select">
                        <?php
                        for ($y = date('Y')-2; $y <= date('Y')+2; $y++) {
                            echo "<option value='$y' ".($y == $currentYear ? 'selected' : '').">$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class='bx bx-filter-alt'></i> Filter</button>
                <button type="button" onclick="window.print()" class="btn btn-secondary btn-print-action"><i class='bx bx-printer'></i> Print All</button>
            </form>

            <div class="report-card">
                <div class="text-center mb-4">
                    <h5 class="m-0">Water Billing Summary</h5>
                    <p class="text-muted small">Period: <?php echo date("F", mktime(0, 0, 0, $currentMonth, 1)) . " " . $currentYear; ?></p>
                </div>

                <?php
                if ($result->num_rows > 0) {
                    while ($client = $result->fetch_assoc()) {
                        $clientId = $client['id'];
                        // Gi-update ang query aron makuha ang meter_code
                        $sqlBilling = "SELECT * FROM billing_list WHERE client_id = ? 
                                       AND MONTH(reading_date) = ? AND YEAR(reading_date) = ?";
                        $stmtBilling = $conn->prepare($sqlBilling);
                        $stmtBilling->bind_param("iii", $clientId, $currentMonth, $currentYear);
                        $stmtBilling->execute();
                        $resultBilling = $stmtBilling->get_result();

                        if ($resultBilling->num_rows > 0) {
                            echo "<div class='client-section'><h6>{$client['firstname']} {$client['lastname']}</h6></div>";
                            echo "<div class='table-responsive mb-4'>";
                            echo "<table class='table table-bordered align-middle'>";
                            echo "<thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Meter No.</th> <th>Reading (Prev → Curr)</th>
                                        <th>Rate</th>
                                        <th>Current Bill</th>
                                        <th>Arrears</th>
                                        <th>Grand Total</th>
                                        <th>Status</th>
                                        <th class='btn-print-action'>Action</th>
                                    </tr>
                                  </thead><tbody>";

                            while ($row = $resultBilling->fetch_assoc()) {
                                $curr_bill = ($row['reading'] - $row['previous']) * $row['rate'];
                                $arrears = $row['total'] - $curr_bill; 
                                $statusBadge = ($row['status'] == 1) ? 'status-paid' : 'status-pending';
                                $statusText = ($row['status'] == 1) ? 'PAID' : 'PENDING';
                                $formattedDate = date("M d, Y", strtotime($row['reading_date']));
                                
                                // Meter Display Logic
                                $m_code = (!empty($row['meter_code'])) ? $row['meter_code'] : "N/A";

                                echo "<tr>";
                                echo "<td>$formattedDate</td>";
                                echo "<td><span class='badge bg-light text-dark border'>$m_code</span></td>"; // METER CODE DIRI
                                echo "<td>" . number_format($row['previous'], 2) . " → " . number_format($row['reading'], 2) . "</td>";
                                echo "<td>₱" . number_format($row['rate'], 2) . "</td>";
                                echo "<td>₱" . number_format($curr_bill, 2) . "</td>";
                                echo "<td class='text-danger'>₱" . number_format(max(0, $arrears), 2) . "</td>";
                                echo "<td><strong>₱" . number_format($row['total'], 2) . "</strong></td>";
                                echo "<td><span class='{$statusBadge}'>$statusText</span></td>";
                                echo "<td class='btn-print-action'><a href='print_receipt.php?id={$row['id']}' class='btn-print' target='_blank'>Receipt</a></td>";
                                echo "</tr>";
                            }
                            echo "</tbody></table></div>";
                        }
                        $stmtBilling->close();
                    }
                } else {
                    echo "<div class='alert alert-info'>No active clients found.</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>