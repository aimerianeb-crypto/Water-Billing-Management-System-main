<?php
// Module for deleting clients via Bridge - HARD DELETE INTEGRATION
session_start();

// 1. SECURITY CHECK
$token = $_SESSION['token'] ?? ''; 
if (empty($token)) {
    header("Location: login.php");
    exit();
}

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $client_id = $_GET['id'];

    // 2. TAWGON ANG BRIDGE PARA SA DELETE (DELETE Method)
    $url = 'http://localhost:3000/api/clients/' . $client_id; 
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Importante: DELETE method ang gamiton
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 3. CHECK KUNG NAGMALAMPUSON BA
    if ($httpCode == 200) {
        $message = "Client permanently deleted from integrated database!";
        $status = "success";
    } else {
        $message = "Error deleting client via Bridge. Code: " . $httpCode;
        $status = "error";
    }
} else {
    $message = "Invalid request.";
    $status = "error";
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
