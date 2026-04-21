<?php
include 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $client_id = $_GET['id'];

    // Fetch client information based on the client_id
    $stmt = $conn->prepare("SELECT * FROM client_list WHERE id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Display client details
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" href="logo.png">
            <title>Client Details</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f2f2f2;
                }
                .container {
                    width: 60%;
                    margin: 50px auto;
                    padding: 20px;
                    background-color: #ffffff94;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    border-radius: 10px;
                    position: relative;
                }
                .container img {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    z-index: -1;
                    border-radius: 10px;
                }
                .container h1 {
                    text-align: center;
                    color: #333;
                }
                .details {
                    margin: 20px 0;
                }
                .details p {
                    font-size: 18px;
                    color: #555;
                }
                .details p span {
                    font-weight: bold;
                    color: #000;
                }
                .back-link {
                    display: block;
                    text-align: center;
                    margin-top: 20px;
                    padding: 10px 20px;
                    text-decoration: none;
                    color: #fff;
                    background-color: #007770;
                    border-radius: 5px;
                    transition: background-color 0.3s;
                }
                .back-link:hover {
                    background-color: #005f5f;
                }
            </style>
        </head>
        <body>
            <div class="container">
                
                <h1>Client Details</h1>
                <div class="details">
                    <p><span>ID:</span> <?php echo $row['id']; ?></p>
                    <p><span>Code:</span> <?php echo $row['code']; ?></p>
                    <p><span>Category ID:</span> <?php echo $row['category_id']; ?></p>
                    <p><span>Firstname:</span> <?php echo $row['firstname']; ?></p>
                    <p><span>Lastname:</span> <?php echo $row['lastname']; ?></p>
                    <p><span>Contact:</span> <?php echo $row['contact']; ?></p>
                    <p><span>Address:</span> <?php echo $row['address']; ?></p>
                    <p><span>Status:</span> <?php echo $row['status'] == 1 ? 'Active' : 'Inactive'; ?></p>
                </div>
                <a href="view_clients.php" class="back-link">Back to Client List</a>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Client not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
