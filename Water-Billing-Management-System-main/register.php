<?php
include 'db.php'; // Siguroha nga husto ang sulod sa db.php (wbms_db)

$message = "Create your account";
$msg_color = "#3182bd";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $password = md5($_POST['password']); // MD5 hashing para parehas sa C#

    // 1. I-check kung existing na ang username
    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $message = "Error: Username already taken!";
        $msg_color = "red";
    } else {
        // 2. I-insert sa 'users' table. 
        // Siguroha nga ang imong columns sa database kay: username, password, firstname, type
        $stmt = $conn->prepare("INSERT INTO users (username, password, firstname, type) VALUES (?, ?, ?, '1')");
        $stmt->bind_param("sss", $username, $password, $fullname);
        
        if ($stmt->execute()) {
            echo "<script>alert('Account Created Successfully!'); window.location='login.php';</script>";
            exit();
        } else {
            $message = "Error: " . $conn->error;
            $msg_color = "red";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Water Billing System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body, html {
            margin: 0; padding: 0; height: 100%;
            font-family: 'Segoe UI', sans-serif;
            display: flex; justify-content: center; align-items: center;
            background-color: #f0f2f5;
        }
        .login-card {
            display: flex; width: 850px; height: 500px;
            background-color: #fff; box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .brand-panel {
            flex: 1; background-color: #256391; color: white;
            display: flex; flex-direction: column; justify-content: center;
            align-items: center; padding: 40px; text-align: center;
        }
        .brand-panel img { width: 150px; margin-bottom: 20px; background: white; padding: 10px; border-radius: 5px; }
        .form-panel {
            flex: 1.2; padding: 50px; display: flex;
            flex-direction: column; justify-content: center;
        }
        .form-panel h2 { color: #3182bd; margin: 0 0 10px 0; font-size: 28px; }
        .message-text { color: <?php echo $msg_color; ?>; font-size: 14px; margin-bottom: 5px; font-weight: bold; }
        .input-group {
            display: flex; align-items: center; border-bottom: 2px solid #eee;
            margin: 20px 0; padding: 5px 0;
        }
        .input-group i { color: #256391; font-size: 18px; width: 30px; }
        .input-group input { border: none; outline: none; width: 100%; padding: 10px; font-size: 16px; }
        .actions { display: flex; align-items: center; justify-content: space-between; margin-top: 20px; }
        .btn-login {
            background-color: #3182bd; color: white; border: none;
            padding: 12px 30px; font-size: 16px; cursor: pointer; transition: 0.3s;
        }
        .btn-login:hover { background-color: #256391; }
        .forgot-pass { font-size: 14px; color: #3182bd; text-decoration: none; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-panel">
        <img src="logo.png" alt="Logo"> 
        <h1>Start your Registration</h1>
    </div>
    <div class="form-panel">
        <p class="message-text"><?php echo $message; ?></p>
        <h2>Register</h2>
        <form method="POST">
            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="fullname" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="actions">
                <button type="submit" class="btn-login">REGISTER</button>
                <a href="login.php" class="forgot-pass">Back to Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>