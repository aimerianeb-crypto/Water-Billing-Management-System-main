<?php
include 'db.php'; 

$message = ''; 
$messageClass = '';

// 1. FETCH CLIENTS para sa dropdown
$sql_clients = "SELECT id, code, firstname, middlename, lastname FROM client_list WHERE delete_flag = 0 AND status = 1";
$result_clients = $conn->query($sql_clients);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id    = $_POST['client_id'];
    $meter_code   = $_POST['meter_code'];
    $reading_date = $_POST['reading_date'];
    $due_date     = $_POST['due_date'];
    $reading      = $_POST['reading'];
    $status       = $_POST['status'];

    // 2. FETCH RATE base sa kategorya sa client
    $sql_rate = "SELECT c.rate FROM category_list c 
                 JOIN client_list cl ON cl.category_id = c.id 
                 WHERE cl.id = ?";
    $stmt_rate = $conn->prepare($sql_rate);
    $stmt_rate->bind_param("i", $client_id);
    $stmt_rate->execute();
    $stmt_rate->bind_result($rate);
    $stmt_rate->fetch();
    $stmt_rate->close();
// 3. FETCH PREVIOUS READING (Kini ang saktong sorting)
// Gi-prioritize ang 'id' DESC aron ang pinaka-ulahing nasave nga record gyud ang makuha.
$sql_prev_read = "SELECT reading FROM billing_list 
                  WHERE client_id = ? 
                  ORDER BY id DESC LIMIT 1"; 

$stmt_prev_read = $conn->prepare($sql_prev_read);
$stmt_prev_read->bind_param("i", $client_id);
$stmt_prev_read->execute();
$stmt_prev_read->bind_result($prev_val);

if ($stmt_prev_read->fetch()) {
    $previous = $prev_val;
} else {
    $previous = 0;
}
$stmt_prev_read->close();

    // VALIDATION
    if ($reading < $previous) {
        $message = "❌ Error: Current reading ($reading) cannot be less than previous ($previous)!";
        $messageClass = "error-message";
    } else {
        // COMPUTE CURRENT BILL
        $current_bill = ($reading - $previous) * $rate;

        // 4. FIX: FETCH LATEST ARREARS (Running Balance Logic)
        // Imbes SUM(total), mokuha lang ta sa pinaka-ulahing 'total' sa client nga wala pa nabayran.
        $sql_arrears = "SELECT total FROM billing_list 
                        WHERE client_id = ? AND status = 0 
                        ORDER BY id DESC LIMIT 1";
        $stmt_arr = $conn->prepare($sql_arrears);
        $stmt_arr->bind_param("i", $client_id);
        $stmt_arr->execute();
        $stmt_arr->bind_result($last_grand_total);
        $stmt_arr->fetch();
        $stmt_arr->close();

        $total_arrears = ($last_grand_total) ? $last_grand_total : 0;

        // Bag-ong Grand Total = Current Bill + Pinaka-ulahing Balance
        $grand_total = $current_bill + $total_arrears;

        // 5. INSERT RECORD
        $sql_insert = "INSERT INTO billing_list (client_id, meter_code, reading_date, due_date, reading, previous, rate, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isssddddi", $client_id, $meter_code, $reading_date, $due_date, $reading, $previous, $rate, $grand_total, $status);

        if ($stmt_insert->execute()) {
            $message = "✅ SUCCESS! <br> Meter Number: <strong>$meter_code</strong> <br> Arrears: ₱" . number_format($total_arrears, 2) . " <br> Grand Total: ₱" . number_format($grand_total, 2);
            $messageClass = "success-message";
        } else {
            $message = "❌ Error: " . $stmt_insert->error;
            $messageClass = "error-message";
        }
        $stmt_insert->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Billing Entry | WBMS</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        h3 { text-align: center; color: #256391; margin-bottom: 20px; }
        form { display: flex; flex-direction: column; gap: 12px; }
        label { font-weight: 600; font-size: 13px; color: #333; }
        select, input { padding: 10px; border: 1px solid #ddd; border-radius: 6px; outline: none; transition: 0.3s; }
        input:focus, select:focus { border-color: #256391; box-shadow: 0 0 5px rgba(37, 99, 145, 0.2); }
        input[type="submit"] { background: #256391; color: white; border: none; cursor: pointer; font-weight: bold; margin-top: 10px; padding: 12px; }
        input[type="submit"]:hover { background: #1a4566; }
        .message { padding: 15px; border-radius: 6px; text-align: center; margin-top: 20px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h3>New Billing Entry</h3>
        <form method="post">
            <label>Select Client:</label>
            <select name="client_id" required>
                <option value="" disabled selected>-- Choose Client --</option>
                <?php
                if ($result_clients->num_rows > 0) {
                    while ($row = $result_clients->fetch_assoc()) {
                        $name = $row['firstname'] . " " . $row['lastname'];
                        echo "<option value='{$row['id']}'>[{$row['code']}] - {$name}</option>";
                    }
                }
                ?>
            </select>

            <label for="meter_code">Meter Code / Number:</label>
            <input type="text" name="meter_code" id="meter_code" placeholder="Enter Meter No." required>
            
            <label>Reading Date:</label>
            <input type="date" name="reading_date" value="<?php echo date('Y-m-d'); ?>" required>
            
            <label>Due Date:</label>
            <input type="date" name="due_date" required>
            
            <label>Current Reading (m³):</label>
            <input type="number" step="0.01" name="reading" placeholder="0.00" required>
            
            <label>Status:</label>
            <select name="status">
                <option value="0">Unpaid / Pending</option>
                <option value="1">Paid Immediately</option>
            </select>
            
            <input type="submit" value="Save Billing Record">
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <a href="view_billings.php" class="back-link">← Back to History</a>
    </div>
</body>
</html>