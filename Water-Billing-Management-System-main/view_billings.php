<?php
// Core logic for water consumption and billing history tracking
include 'db.php'; 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Gi-apil ang b.meter_code sa SELECT statement aron makuha ang data gikan sa DB
$sql = "SELECT b.id, b.client_id, b.meter_code, b.reading_date, b.due_date, b.reading, b.previous, b.rate, b.total, b.status, c.firstname, c.lastname
        FROM billing_list b
        INNER JOIN client_list c ON b.client_id = c.id
        ORDER BY b.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Records | WBMS</title>
    <link rel="icon" href="logo.png">
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">

    <style>
        :root {
            --sidebar-color: #2c3e50;
            --primary-blue: #3182bd;
            --bg-light: #f4f7f6;
            --paid-green: #27ae60;
            --pending-red: #e74c3c;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-light);
            display: flex;
            height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-color);
            color: white;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            background: rgba(0,0,0,0.2);
        }

        .sidebar-header img { width: 60px; background: white; border-radius: 50%; padding: 5px; }

        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: #bdc3c7;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background: #34495e;
            color: white;
            border-left: 4px solid var(--primary-blue);
        }

        .menu-item i { margin-right: 15px; font-size: 20px; }

        /* Content Area */
        .main-content { flex: 1; overflow-y: auto; display: flex; flex-direction: column; }

        .top-nav {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .content-body { padding: 30px; }

        .data-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .btn-add {
            background-color: var(--primary-blue);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Status Badges */
        .badge-paid { background: #eafaf1; color: var(--paid-green); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #fdedec; color: var(--pending-red); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }

        /* Unified CRUD Action Buttons Style */
        .action-btn {
            padding: 7px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-view { background: #f3e5f5; color: #7b1fa2; margin-right: 5px; }
        .btn-view:hover { background: #e1bee7; color: #4a148c; }

        .btn-edit { background: #e3f2fd; color: #1976d2; margin-right: 5px; }
        .btn-edit:hover { background: #bbdefb; color: #0d47a1; }

        .btn-delete { background: #ffebee; color: #d32f2f; }
        .btn-delete:hover { background: #ffcdd2; color: #b71c1c; }

        .table th { font-size: 11px; text-transform: uppercase; color: #7f8c8d; border-top: none; }
        .table td { vertical-align: middle; }
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
            <a href="view_billings.php" class="menu-item active"><i class='bx bxs-receipt'></i> Billing Records</a>
            <a href="monthly_biling.php" class="menu-item"><i class='bx bxs-report'></i> Monthly Report</a>
            <a href="category_list.php" class="menu-item"><i class='bx bxs-category'></i> List of Category</a>
            <a href="user_list.php" class="menu-item"><i class='bx bxs-group'></i> User List</a>
            <a href="settings_rate.php" class="menu-item"><i class='bx bxs-cog'></i> Settings</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="top-nav">
            <h4 class="m-0">Billing & Collections</h4>
            <div class="user-meta">
                <span>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
            </div>
        </header>

        <div class="content-body">
            <div class="data-card">
                <div class="card-header">
                    <h5 class="m-0">Billing History</h5>
                    <a href="add_billing.php" class="btn-add"><i class='bx bx-plus-circle'></i> Add New Billing</a>
                </div>

                <div class="table-responsive">
                    <table id="billingTable" class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client Name</th>
                                <th>Meter</th> 
                                <th>Reading (m³)</th>
                                <th>Current</th>
                                <th>Arrears</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while($row = $result->fetch_assoc()) {
                                // Kalkulasyon para sa Current Month Bill
                                $consumption = $row['reading'] - $row['previous'];
                                $current_month_bill = $consumption * $row['rate'];
                                
                                // Ang Arrears mao ang Grand Total minus ang Current Bill
                                $unpaid_arrears = $row['total'] - $current_month_bill;
                                
                                // Badge Logic para sa Status
                                $statusBadge = $row['status'] == 1 ? 'badge-paid' : 'badge-pending';
                                $statusText = $row['status'] == 1 ? 'PAID' : 'PENDING';

                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                
                                // 1. Client Name
                                echo "<td><strong>{$row['firstname']} {$row['lastname']}</strong></td>";
                                
                                // 2. Meter Code (Display gikan sa DB)
                                $m_code = (!empty($row['meter_code'])) ? $row['meter_code'] : "N/A"; 
                                echo "<td><span class='badge bg-light text-dark border'>{$m_code}</span></td>";
                                
                                // 3. Reading (m³): Previous -> Current
                                echo "<td><small class='text-muted'>" . number_format($row['previous'], 1) . " → " . number_format($row['reading'], 1) . "</small></td>";
                                
                                // 4. Current Month Bill
                                echo "<td class='text-primary'>₱" . number_format($current_month_bill, 2) . "</td>";
                                
                                // 5. Arrears
                                echo "<td class='text-danger'>₱" . number_format($unpaid_arrears, 2) . "</td>";
                                
                                // 6. Grand Total (Current + Arrears)
                                echo "<td><strong>₱" . number_format($row['total'], 2) . "</strong></td>";
                                
                                // 7. Status Badge
                                echo "<td><span class='{$statusBadge}'>{$statusText}</span></td>";
                                
                                // 8. Actions
                                echo "<td class='text-center'>";
                                echo "<a href='view_billing.php?id={$row['id']}' class='action-btn btn-view' title='View'><i class='bx bxs-show'></i></a>";
                                echo "<a href='edit_billing.php?id={$row['id']}' class='action-btn btn-edit' title='Edit'><i class='bx bxs-edit-alt'></i></a>";
                                echo "<a href='delete_billing.php?id={$row['id']}' class='action-btn btn-delete' onclick='return confirm(\"Delete record?\")' title='Delete'><i class='bx bxs-trash-alt'></i></a>";
                                echo "</td>";
                                echo "</tr>";
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
            $('#billingTable').DataTable({
                "pageLength": 10,
                "pagingType": "simple",
                "info": false,
                "order": [[0, "desc"]], // I-sort by ID descending
                "language": {
                    "paginate": {
                        "previous": "<",
                        "next": ">"
                    }
                }
            });
        });
    </script>
</body>
</html>