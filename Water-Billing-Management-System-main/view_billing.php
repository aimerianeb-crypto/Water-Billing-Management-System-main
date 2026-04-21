<?php
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $billing_id = $_GET['id'];

    // Fetch billing information based on the billing_id
    $stmt = $conn->prepare("SELECT b.id, b.client_id, b.reading_date, b.due_date, b.reading, b.previous, b.rate, b.total, b.status, c.firstname, c.lastname, c.address
                            FROM billing_list b
                            INNER JOIN client_list c ON b.client_id = c.id
                            WHERE b.id = ?");
    $stmt->bind_param("i", $billing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Display billing details
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <link rel="icon" href="logo.png">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>View Billing</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f2f2f2;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #fff;
                    padding: 20px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                }
                h1 {
                    text-align: center;
                    color: #333;
                }
                p {
                    font-size: 16px;
                    color: #555;
                    margin: 10px 0;
                }
                p strong {
                    color: #000;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    text-decoration: none;
                    color: #007bff;
                    font-weight: bold;
                }
                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Billing Details</h1>
                <p><strong>ID:</strong> <?php echo $row['id']; ?></p>
                <p><strong>Client Name:</strong> <?php echo $row['firstname'] . ' ' . $row['lastname']; ?></p>
                <p><strong>Address:</strong> <?php echo $row['address']; ?></p>
                <p><strong>Reading Date:</strong> <?php echo $row['reading_date']; ?></p>
                <p><strong>Due Date:</strong> <?php echo $row['due_date']; ?></p>
                <p><strong>Reading:</strong> <?php echo $row['reading']; ?></p>
                <p><strong>Previous:</strong> <?php echo $row['previous']; ?></p>
                <p><strong>Rate:</strong> <?php echo $row['rate']; ?></p>
                <p><strong>Total:</strong> <?php echo $row['total']; ?></p>
                <p><strong>Status:</strong> <?php echo $row['status'] == 1 ? 'Paid' : 'Pending'; ?></p>
                <a href="view_billings.php">Back to Billing List</a>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Billing record not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
