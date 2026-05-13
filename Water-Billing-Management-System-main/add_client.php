<?php
session_start();

// 1. SECURITY CHECK: Kinahanglan naay token gikan sa login
$token = $_SESSION['token'] ?? ''; 
if (empty($token)) {
    header("Location: login.php");
    exit();
}

$message = ''; 
$messageClass = '';

/**
 * 2. KUHAON ANG CATEGORIES GIKAN SA BRIDGE
 */
$cat_url = 'http://localhost:3000/api/categories'; 
$cat_ch = curl_init($cat_url);
curl_setopt($cat_ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cat_ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);

$cat_res = curl_exec($cat_ch);
$categories = json_decode($cat_res, true) ?? [];
curl_close($cat_ch);

/**
 * 3. HANDLE FORM SUBMISSION
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postData = [
        "code" => $_POST['code'],
        "category_id" => (int)$_POST['category_id'], // Gi-ensure nato nga number ni
        "firstname" => $_POST['firstname'],
        "middlename" => $_POST['middlename'],
        "lastname" => $_POST['lastname'],
        "gender" => $_POST['gender'],
        "birthdate" => $_POST['birthdate'],
        "contact" => $_POST['contact'],
        "address" => "Himos-onan, Saint Bernard, Southern Leyte", // I-hardcode nalang nato para sigurado
        "purok" => $_POST['purok']
    ];

    $url = 'http://localhost:3000/api/clients/add';
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch); // Mao ni ang tig-bantay kon nganong "Failed"
    curl_close($ch);

    if ($curlError) {
        $message = "Connection Error: " . $curlError; // Makita nimo kon wala ba ka-connect sa Port 3000
        $messageClass = "error-message";
    } elseif ($httpCode == 200 || $httpCode == 201) {
        $message = "Success! Client added via Bridge.";
        $messageClass = "success-message";
    } else {
        $resData = json_decode($response, true);
        $message = "Bridge Error: " . ($resData['message'] ?? "Check Node.js Terminal");
        $messageClass = "error-message";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.png">
    <title>Add Client | Water Billing</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .container { width: 60%; margin: 50px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        form { max-width: 600px; margin: 0 auto; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        form input[type="text"], form input[type="date"], form select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        form input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        form input[type="submit"] { background-color: #256391; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 10px; }
        form input[type="submit"]:hover { background-color: #1a4566; }
        .message { padding: 15px; margin-top: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .success-message { background-color: #4CAF50; color: white; }
        .error-message { background-color: #f44336; color: white; }
        .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; transition: 0.3s; }
        .back-link:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="text-align: center; color: #256391;">Add New Client</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
           <label for="category_id">Category:</label>
<select id="category_id" name="category_id" required>
    <option value="">-- Select Category --</option>
    <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['id']; ?>">
            <?php echo htmlspecialchars($cat['name']); ?> 
        </option>
    <?php endforeach; ?>
</select>

            <label for="code">Client Code:</label>
            <input type="text" id="code" name="code" required placeholder="e.g. 2024-001">

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

            <label for="contact">Contact Number:</label>
            <input type="text" id="contact" name="contact" required>

            <label>Address (Default):</label>
            <input type="text" name="address" value="Himos-onan, Saint Bernard, Southern Leyte" readonly>

            <label for="purok">Purok:</label>
            <input type="text" id="purok" name="purok" required placeholder="e.g. Purok 1">

            <input type="submit" value="Add Client to System">
        </form>

        <a href="view_clients.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Client List
        </a>
    </div>
</body>
</html>