<?php
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $rate = $_POST['rate']; // Gi-add nato ang rate
    $status = $_POST['status']; 
    $delete_flag = 0; 

    // Gi-update ang INSERT query aron maapil ang rate column
    $stmt = $conn->prepare("INSERT INTO category_list (name, rate, status, delete_flag, date_created, date_updated) VALUES (?, ?, ?, ?, current_timestamp(), current_timestamp())");
    $stmt->bind_param("sdii", $name, $rate, $status, $delete_flag); // "sdii" kay s=string, d=double/float, i=integer

    if ($stmt->execute()) {
        echo "<script>alert('New category added successfully!'); window.location.href='category_list.php';</script>";
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
    <title>Add Category</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; overflow: hidden; }
        .bg-img { position: fixed; z-index: -1; width: 100%; height: 100%; object-fit: cover; }
        .container {
            margin: 0 auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
            width: 320px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 16px;
            text-align: center;
        }
        input, select { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }
        .btn-submit { background-color: #256391; color: white; border: none; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background-color: #007770; }
        .back-link { display: block; margin-top: 15px; color: #007770; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>


<div class="container">
    <h2 style="color: #256391;">Add New Category</h2>
    <form method="post" action="">
        <label style="float: left;">Category Name:</label>
        <input type="text" name="name" placeholder="e.g. Residential" required>
        
        <label style="float: left;">Rate (per m³):</label>
        <input type="number" step="0.01" name="rate" placeholder="e.g. 15.00" required>
        
        <label style="float: left;">Status:</label>
        <select name="status" required>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>

        <input type="submit" value="Add Category" class="btn-submit">
    </form>
    
    <a href="category_list.php" class="back-link">← Back to Category List</a>
</div>
</body>
</html>