<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "wbms_db"; // Siguroha nga husto ang spelling sa database name

$conn = new mysqli($host, $user, $pass, $dbname);

// I-check kung naay error sa connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>