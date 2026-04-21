<?php
include 'db.php'; // Include your database connection file

// Variable to hold the notification message
$notification = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve values from POST data
    $client_id = $_POST['client_id'];
    $code      = $_POST['code'];
    $firstname = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $contact   = $_POST['contact'];
    $address   = $_POST['address'];

    // UPDATE QUERY: Gi-remove ang category_id ug meter_code para match sa imong database table
    // Siguroha nga 6 ra kabuok ang "?" (placeholders)
    $stmt = $conn->prepare("UPDATE client_list SET code = ?, firstname = ?, lastname = ?, contact = ?, address = ? WHERE id = ?");
    
    // BIND PARAMETERS: "sssssi" nagpasabot og 5 ka strings ug 1 ka integer (para sa id)
    // Kinahanglan 6 ra sad ka variables ang naa diri para mag-match sa taas
    $stmt->bind_param("sssssi", $code, $firstname, $lastname, $contact, $address, $client_id);

    // Execute the statement
    if ($stmt->execute()) {
        $notification = '<p class="notification success">Client updated successfully.</p>';
    } else {
        $notification = '<p class="notification error">Error updating client: ' . $stmt->error . '</p>';
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Update Client</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; }
        .container { width: 50%; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .notification { padding: 10px; margin-bottom: 15px; border-radius: 5px; font-weight: bold; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; text-decoration: none; color: #fff; background-color: #007770; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Client Status</h2>
        <?php echo $notification; ?>
        <a href="view_clients.php" class="back-link">Back to Client List</a>
    </div>
</body>
</html>