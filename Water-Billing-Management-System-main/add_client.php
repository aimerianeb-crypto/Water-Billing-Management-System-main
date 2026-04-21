<?php
include 'db.php'; // Include your database connection file

$message = ''; // Initialize message variable

// Fetch all active categories from the database
$category_sql = "SELECT id, name FROM category_list WHERE status = 1 AND delete_flag = 0";
$category_result = $conn->query($category_sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $category_id = $_POST['category_id'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender']; // Nadugang
    $birthdate = $_POST['birthdate']; // Nadugang
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $purok = $_POST['purok']; // Nadugang
    $status = 1;
    $delete_flag = 0;

    // Prepare SQL statement - Gi-update ang INSERT para maapil ang bag-ong columns
    $stmt = $conn->prepare("INSERT INTO client_list (code, category_id, firstname, middlename, lastname, gender, birthdate, contact, address, purok, status, delete_flag) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Ang "sisssssssssii" nagrepresentar sa tipo sa data (s=string, i=int)
    $stmt->bind_param("sisssssssssi", $code, $category_id, $firstname, $middlename, $lastname, $gender, $birthdate, $contact, $address, $purok, $status, $delete_flag);

    if ($stmt->execute()) {
        $message = "New client added successfully";
        $messageClass = "success-message";
    } else {
        $message = "Error: " . $stmt->error;
        $messageClass = "error-message";
    }

    $stmt->close();
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Add Client</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
        .container { width: 60%; margin: 50px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        form { max-width: 600px; margin: 0 auto; }
        form label { display: block; margin-bottom: 8px; font-weight: bold; }
        form input[type="text"], form input[type="date"], form select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        form input[type="submit"] { background-color: #256391; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        form input[type="submit"]:hover { background-color: #007770; }
        .message { padding: 10px; margin-top: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .success-message { background-color: #4CAF50; color: white; }
        .error-message { background-color: #f44336; color: white; }
        .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #256391; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="text-align: center;">Add New Client</h2>
        <form method="post" action="">
            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <?php
                if ($category_result->num_rows > 0) {
                    while ($row = $category_result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                }
                ?>
            </select>

            <label for="code">Code:</label>
            <input type="text" id="code" name="code" required>

            <label for="firstname">Firstname:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="middlename">Middlename:</label>
            <input type="text" id="middlename" name="middlename">

            <label for="lastname">Lastname:</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label for="birthdate">Birthdate:</label>
            <input type="date" id="birthdate" name="birthdate" required>

            <label for="contact">Contact:</label>
            <input type="text" id="contact" name="contact" required>

            <div class="form-group">
    <label>Address :</label>
    <input type="text" name="address" value="Himos-onan, Saint Bernard, Southern Leyte" class="form-control" readonly>
</div>

            <label for="purok">Purok:</label>
            <input type="text" id="purok" name="purok" required placeholder="e.g. Purok 1">

            <input type="submit" value="Add Client">
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <a href="view_clients.php" class="back-link">Back to Client List</a>
    </div>
</body>
</html>