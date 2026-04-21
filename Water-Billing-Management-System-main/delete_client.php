<?php
include 'db.php'; // Include your database connection file

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $client_id = $_GET['id'];

    // Delete client record from the database
    $stmt = $conn->prepare("DELETE FROM client_list WHERE id = ?");
    $stmt->bind_param("i", $client_id);

    if ($stmt->execute()) {
        $message = "Client deleted successfully.";
        $messageClass = "success-message";
    } else {
        $message = "Error deleting client: " . $stmt->error;
        $messageClass = "error-message";
    }

    $stmt->close();
    $conn->close();
} else {
    $message = "Invalid request.";
    $messageClass = "error-message";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Delete Client Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .success-message {
            background-color: #4CAF50; /* Green */
            color: white;
        }
        .error-message {
            background-color: #f44336; /* Red */
            color: white;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff; /* Blue */
            color: white;
            border-radius: 5px;
        }
        .back-link:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
    </style>
</head>
<body>
    <div class="message <?php echo $messageClass; ?>">
        <?php echo $message; ?>
    </div>
    <a href="view_clients.php" class="back-link">Back to View Clients</a>
</body>
</html>
