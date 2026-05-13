<?php
session_start();

// 1. I-initialize ang variable para mawala ang "Undefined variable" error
$message = "";

// Check kon naay error gikan sa URL (e.g., login.php?error=no_token)
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'no_token') {
        $message = "Please login first!";
    } elseif ($_GET['error'] == 'invalid_credentials') {
        $message = "Invalid username or password!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Request to Node.js Bridge
    $url = 'http://localhost:3000/api/login';
    $data = json_encode(['username' => $username, 'password' => $password]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['status']) && $result['status'] == 'success') {
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['username'] = $result['user']['username'];
        $_SESSION['token'] = $result['token']; 

        header("Location: index.php");
        exit();
    } else {
        // I-set ang message kon mapakyas ang login
        $message = "Invalid Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Record Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f2f5;
        }

        .login-card {
            display: flex;
            width: 850px;
            height: 500px;
            background-color: #fff;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            position: relative;
        }

        /* Left Side (Blue Branding) */
        .brand-panel {
            flex: 1;
            background-color: #256391;; 
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
        }

        .brand-panel img {
            width: 150px;
            margin-bottom: 20px;
        }

        .brand-panel h1 {
            font-size: 24px;
            font-weight: 400;
            line-height: 1.4;
        }

        /* Right Side (Form) */
        .form-panel {
            flex: 1.2;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: #3182bd;
            text-decoration: none;
            font-weight: bold;
        }

        .form-panel h2 {
            color: #3182bd;
            margin: 0;
            font-size: 28px;
        }

        .message-text {
            color: red;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .input-group {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #eee;
            margin: 25px 0;
            padding: 5px 0;
        }

        .input-group i {
            color: #3e4eb8;
            font-size: 20px;
            width: 30px;
        }

        .input-group input {
            border: none;
            outline: none;
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-login {
            background-color: #3182bd;
            color: white;
            border: none;
            padding: 12px 40px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #256391;
        }

        .forgot-pass {
            font-size: 14px;
            color: #3182bd;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-card">
    <a href="#" class="close-btn">X</a>
    
    <div class="brand-panel">
        <img src="logo.png" alt="Logo"> 
        <h1>Welcome to the<br>Water Billing Management System</h1>
    </div>

    <div class="form-panel">
        <p class="message-text"><?php echo $message; ?></p>
        
        <h2>Login to your account</h2>

        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

           <div class="actions">
    <button type="submit" class="btn-login">LOGIN</button>
    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
        <a href="#" class="forgot-pass">Forget Password?</a>
    </div>
</div>
</div>
        </form>
    </div>
</div>

</body>
</html>