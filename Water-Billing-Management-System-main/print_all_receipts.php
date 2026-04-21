<?php
include 'db.php'; // Include your database connection file

// Ensure client_id is provided and valid
if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
    die("Invalid client ID.");
}

$clientID = $_GET['client_id'];

// Fetch client information to display header
$sqlClient = "SELECT firstname, lastname FROM client_list WHERE id = ?";
$stmtClient = $conn->prepare($sqlClient);
$stmtClient->bind_param("i", $clientID);
$stmtClient->execute();
$resultClient = $stmtClient->get_result();

if ($resultClient->num_rows === 0) {
    die("Client not found.");
}

$client = $resultClient->fetch_assoc();
$clientName = $client['firstname'] . ' ' . $client['lastname'];

// Initialize variables for filtering by default to current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Handle form submission for month and year filter
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentMonth = $_POST['month'];
    $currentYear = $_POST['year'];
}

// Fetch billing records for the selected month and year
$sqlBilling = "SELECT * FROM billing_list WHERE client_id = ? 
               AND MONTH(reading_date) = ? AND YEAR(reading_date) = ?";
$stmtBilling = $conn->prepare($sqlBilling);
$stmtBilling->bind_param("iii", $clientID, $currentMonth, $currentYear);
$stmtBilling->execute();
$resultBilling = $stmtBilling->get_result();

// Function to calculate total based on reading and rate
function calculateTotal($reading, $rate) {
    return $reading * $rate;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Print Bill for <?php echo $clientName; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .print-button {
            display: block;
            width: 100px;
            padding: 10px;
            background-color: #4FCFC8;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px auto 0;
        }
        .print-button:hover {
            background-color: #007770;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Print Bill for <?php echo $clientName; ?></h2>

    <!-- Month and Year Filter Form -->
    <form method="post" class="filter-form">
        <label for="month">Select Month:</label>
        <select name="month" id="month">
            <?php
            // Generate options for months
            for ($m = 1; $m <= 12; $m++) {
                $monthName = date("F", mktime(0, 0, 0, $m, 1));
                $selected = ($m == $currentMonth) ? 'selected' : '';
                echo "<option value='$m' $selected>$monthName</option>";
            }
            ?>
        </select>
        <label for="year">Select Year:</label>
        <select name="year" id="year">
            <?php
            // Generate options for years (you may adjust the range as needed)
            $startYear = date('Y') - 5;
            $endYear = date('Y') + 5;
            for ($y = $startYear; $y <= $endYear; $y++) {
                $selected = ($y == $currentYear) ? 'selected' : '';
                echo "<option value='$y' $selected>$y</option>";
            }
            ?>
        </select>
        <button type="submit">Apply Filter</button>
    </form>

    <!-- Display billing records for the selected month and year -->
    <?php if ($resultBilling->num_rows > 0): ?>
        <table>
            <tr>
                <th>Billing ID</th>
                <th>Reading Date</th>
                <th>Due Date</th>
                <th>Reading</th>
                <th>Previous</th>
                <th>Rate</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
            <?php while ($rowBilling = $resultBilling->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $rowBilling['id']; ?></td>
                    <td><?php echo $rowBilling['reading_date']; ?></td>
                    <td><?php echo $rowBilling['due_date']; ?></td>
                    <td><?php echo $rowBilling['reading']; ?></td>
                    <td><?php echo $rowBilling['previous']; ?></td>
                    <td><?php echo $rowBilling['rate']; ?></td>
                    <td><?php echo calculateTotal($rowBilling['reading'], $rowBilling['rate']); ?></td>
                    <td><?php echo ($rowBilling['status'] == 1) ? 'Paid' : 'Pending'; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="#" class="print-button" onclick="window.print();">Print</a>
    <?php else: ?>
        <p>No billing records found for <?php echo $clientName; ?> in <?php echo date('F Y', mktime(0, 0, 0, $currentMonth, 1)); ?></p>
    <?php endif; ?>
    <a href="index.php">Back to dashboard</a>

</div>
</body>
</html>

<?php
// Clean up
$stmtClient->close();
$stmtBilling->close();
$conn->close();
?>
