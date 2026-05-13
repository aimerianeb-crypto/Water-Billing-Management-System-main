<?php
session_start();
header
('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Provide username and password."]);
    exit;
}

// I-prepare ang data para sa Node.js Bridge
$data = ["username" => $username, "password" => $password];
$payload = json_encode($data);

$ch = curl_init('http://localhost:3000/api/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = json_decode($result, true);
curl_close($ch);

if ($httpCode == 200 && isset($res['token'])) {
    // KINI ANG IMPORTANTE: Gi-save ang token para sa view/add clients
    $_SESSION['token'] = $res['token']; 
    echo $result; 
} else {
    echo json_encode(["status" => "error", "message" => $res['message'] ?? "Login failed."]);
}
?>