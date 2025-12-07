<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "restaurant";
$port = 3307;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die(" Помилка підключення до бази даних");
}
?>

