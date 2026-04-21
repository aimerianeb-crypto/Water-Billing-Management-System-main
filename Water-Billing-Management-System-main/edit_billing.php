<?php
include 'db.php';

// Initialize variables
$id = $client_id = $meter_code = $reading_date = $due_date = $reading = $previous = $rate = $total = $status = '';

// 1. FETCH DATA: Mokuha sa karaan nga record base sa ID sa URL
if(isset($_GET['id'])) {
    $sql = "SELECT * FROM billing_list WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $client_id = $row['client_id'];
        $meter_code = $row['meter_code'] ?? ''; 
        $reading_date = $row['reading_date'];
        $due_date = $row['due_date'];
        $reading = $row['reading'];
        $previous = $row['previous'];
        $rate = $row['rate'];
        $total = $row['total'];
        $status = $row['status'];
    } else {
        echo "Billing record not found.";
        exit();
    }
    $stmt->close();
}

// 2. UPDATE LOGIC: Inig click sa Save/Paid
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id           = $_POST['id'];
    $client_id    = $_POST['client_id'];
    $status       = $_POST['status'];
    
    // 1. I-update ang status sa record nga gi-click (e.g. ID 12 nahimong PAID)
    $sqlUpdate = "UPDATE billing_list SET status = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $status, $id);
    
    if ($stmtUpdate->execute()) {
        
        // TRIGGER: Kon ang record gi-mark nimo og PAID (1)
        if ($status == 1) {
            
            /* STEP A: BULK PAYMENT
               Kon nibayad sa tibuok, i-mark as PAID ang tanang karaan records.
            */
            $sqlBulkPaid = "UPDATE billing_list SET status = 1 WHERE client_id = ? AND id < ? AND status = 0";
            $stmtBulk = $conn->prepare($sqlBulkPaid);
            $stmtBulk->bind_param("ii", $client_id, $id);
            $stmtBulk->execute();
            $stmtBulk->close();

            /* STEP B: RECALCULATE ALL SUBSEQUENT PENDING BILLS
               Imbes usa ra ka record, i-update nato TANANG pending bills 
               sa client para sigurado nga hapsay ang arrears hangtod sa pinaka-ulahi.
            */
            $sql_pendings = "SELECT id, reading, previous, rate FROM billing_list WHERE client_id = ? AND status = 0 ORDER BY id ASC";
            $stmt_p = $conn->prepare($sql_pendings);
            $stmt_p->bind_param("i", $client_id);
            $stmt_p->execute();
            $res_p = $stmt_p->get_result();

            while ($row = $res_p->fetch_assoc()) {
                $p_id = $row['id'];
                $current_bill = ($row['reading'] - $row['previous']) * $row['rate'];

                // I-compute ang arrears (Sum sa tanang PENDING bills before ani nga record)
                $sql_arr = "SELECT COALESCE(SUM((reading - previous) * rate), 0) FROM billing_list WHERE client_id = ? AND status = 0 AND id < ?";
                $stmt_arr = $conn->prepare($sql_arr);
                $stmt_arr->bind_param("ii", $client_id, $p_id);
                $stmt_arr->execute();
                $stmt_arr->bind_result($computed_arrears);
                $stmt_arr->fetch();
                $stmt_arr->close();

                $new_total = $current_bill + $computed_arrears;

                // I-update ang total sa database para ani nga pending record
                $sql_fix = "UPDATE billing_list SET total = ? WHERE id = ?";
                $stmt_f = $conn->prepare($sql_fix);
                $stmt_f->bind_param("di", $new_total, $p_id);
                $stmt_f->execute();
                $stmt_f->close();
            }
        }

        header("Location: view_billings.php?msg=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Edit Billing Record | WBMS</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 550px; margin: 20px auto; background: #fff; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 10px; }
        h2 { text-align: center; color: #256391; margin-bottom: 20px; }
        label { display: block; margin: 12px 0 5px; font-weight: 600; font-size: 14px; color: #444; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: #256391; outline: none; box-shadow: 0 0 5px rgba(37, 99, 145, 0.2); }
        input[disabled] { background-color: #e9ecef; color: #6c757d; }
        input[type=submit] { background-color: #256391; color: white; border: none; cursor: pointer; margin-top: 25px; font-weight: bold; padding: 12px; transition: 0.3s; }
        input[type=submit]:hover { background-color: #1a4566; }
        .error-message { padding: 10px; background: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #256391; text-decoration: none; font-size: 13px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Billing Record</h2>
        
        <?php if(isset($error)) echo "<div class='error-message'>$error</div>"; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <label>Client ID:</label>
            <input type="text" name="client_id" value="<?php echo $client_id; ?>" required>

            <label>Meter Number:</label>
            <input type="text" name="meter_code" value="<?php echo $meter_code; ?>" placeholder="Enter Meter No." required>
            
            <label>Reading Date:</label>
            <input type="date" name="reading_date" value="<?php echo $reading_date; ?>" required>
            
            <label>Due Date:</label>
            <input type="date" name="due_date" value="<?php echo $due_date; ?>" required>
            
            <label>Current Reading (m³):</label>
            <input type="number" step="0.01" name="reading" value="<?php echo $reading; ?>" required>
            
            <label>Previous Reading (m³):</label>
            <input type="number" step="0.01" name="previous" value="<?php echo $previous; ?>" required>
            
            <label>Rate (Read-only):</label>
            <input type="number" value="<?php echo $rate; ?>" disabled>
            
            <label>Status:</label>
            <select name="status">
                <option value="0" <?php if ($status == 0) echo 'selected'; ?>>Pending</option>
                <option value="1" <?php if ($status == 1) echo 'selected'; ?>>Paid</option>
            </select>
            
            <input type="submit" value="Update Billing Record">
        </form>
        <a class="back-link" href="view_billings.php">← Back to List of Billings</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>