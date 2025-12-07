<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "restaurant";
$port = 3307;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for proper character encoding
mysqli_set_charset($conn, "utf8mb4");
?>

