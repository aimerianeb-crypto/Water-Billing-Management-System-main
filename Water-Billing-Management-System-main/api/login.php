<?php
header('Content-Type: application/json');
include 'db.php'; 

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Importante: I-MD5 ang password para mo-match sa wbms_db
$hashed_password = md5($password); 

$sql = "SELECT * FROM users WHERE username = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $hashed_password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "user_id" => $user['id'], 
        "role" => $user['type'] ?? '1', // 'type' ang column sa wbms_db
        "username" => $user['username']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Username or Password!"
    ]);
}
?>