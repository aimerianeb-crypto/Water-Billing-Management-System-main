<?php
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Assuming passwords are stored as md5 hashes
    $type = $_POST['type'];

    $stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, username, password, type, date_added, date_updated) VALUES (?, ?, ?, ?, ?, ?, current_timestamp(), current_timestamp())");
    $stmt->bind_param("sssssi", $firstname, $middlename, $lastname, $username, $password, $type);

    if ($stmt->execute()) {
        echo "New user added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Add User</title>
    <style>
        body {
            font-family: Arial, sans-serif; 
            background-color: #f0f0f0; 
        }
        .container {
            width: 50%; 
            margin: 10% auto; 
            padding: 20px;
            background-color: #fff9; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
            text-align: center;
        }
        form {
            text-align: left; 
        }
        form input[type="text"],
        form input[type="password"],
        form select {
            width: calc(100% - 12px); 
            padding: 8px; 
            margin-bottom: 10px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        form select {
            width: 99%; 
            background-color: #256391;
            color: white;
        }
        form input[type="submit"] {
            background-color: #256391;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px; 
        }
        form input[type="submit"]:hover {
            background-color: #256391;
        }
        a {
            display: block; 
            margin-top: 10px; 
            text-align: center;
            text-decoration: none;
            color: #333; 
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Add New User</h1>
    <form method="post" action="">
        First Name: <input type="text" name="firstname" required><br>
        Middle Name: <input type="text" name="middlename"><br>
        Last Name: <input type="text" name="lastname" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Type: 
        <select name="type" required>
            <option value="1">Administrator</option>
            <option value="2">Staff</option>
        </select><br>
        <input type="submit" value="Add User">
    </form>
    
    <a href="user_list.php" style="background-color: ##256391;;color: white;padding: 10px 15px;border: none;border-radius: 4px;cursor: pointer;margin-top: 10px;width: 19%; transition: background-color 0.3s, color 0.3s;"
   onmouseover="this.style.backgroundColor='#256391'; this.style.color='#fff';" onmouseout="this.style.backgroundColor='#256391;; this.style.color='#fff';">Back to User List</a> <!-- Link to list_users.php -->
</div>
</body>
</html>

