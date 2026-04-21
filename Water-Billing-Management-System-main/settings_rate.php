<?php
include 'db.php'; // Include your database connection script

// Fetch current rates for residential and commercial categories
$residential_rate = getRateByCategory(1); // Assuming 1 is the category_id for residential
$commercial_rate = getRateByCategory(2); // Assuming 2 is the category_id for commercial

// Function to fetch rate by category_id
function getRateByCategory($category_id) {
    global $conn;
    $sql = "SELECT rate FROM category_list WHERE id = ? AND status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($rate);
    $stmt->fetch();
    $stmt->close();
    return $rate;
}

// Function to update rate by category_id
function updateRateByCategory($category_id, $new_rate) {
    global $conn;
    $sql = "UPDATE category_list SET rate = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $new_rate, $category_id);
    $stmt->execute();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $residential_rate_new = $_POST['residential_rate'];
    $commercial_rate_new = $_POST['commercial_rate'];

    // Update rates in the category_list table
    updateRateByCategory(1, $residential_rate_new); // Update residential rate
    updateRateByCategory(2, $commercial_rate_new); // Update commercial rate

    echo "Rates updated successfully";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Set Rates</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .container {
            width: 50%;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff7d;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        form input[type="number"] {
            width: calc(100% - 12px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form input[type="submit"] {
            background-color: #256391;;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        form input[type="submit"]:hover {
            background-color: #007770;
        }
        a {
            display: block;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Set Rates</h2>
    <form method="post" action="">
        <label for="residential_rate">Residential Rate:</label>
        <input type="number" id="residential_rate" name="residential_rate" value="<?php echo $residential_rate; ?>" step="0.01" required><br>
        <label for="commercial_rate">Commercial Rate:</label>
        <input type="number" id="commercial_rate" name="commercial_rate" value="<?php echo $commercial_rate; ?>" step="0.01" required><br>
        <input type="submit" value="Update Rates">
    </form>

    <a href="index.php">Back to Home</a>
</div>
</body>
</html>
