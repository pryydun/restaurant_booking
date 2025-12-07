<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name   = $_POST['name'];
    $phone  = $_POST['phone'];
    $date   = $_POST['date'];
    $time   = $_POST['time'];
    $guests = $_POST['guests'];
    $table  = $_POST['table_number'];

    // Генерація унікального коду бронювання
    $booking_code = "BK-" . strtoupper(substr(md5(uniqid()), 0, 6));

    $sql = "INSERT INTO bookings 
        (name, phone, booking_date, booking_time, guests, table_number, booking_code)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssiis",
        $name,
        $phone,
        $date,
        $time,
        $guests,
        $table,
        $booking_code
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<h2>✅ Booking confirmed!</h2>";
        echo "<p><b>Your booking code:</b> $booking_code</p>";
        echo "<p><b>Table:</b> $table</p>";
        echo "<br><a href='check.php'>Check booking status</a>";
    } else {
        echo "❌ Error saving booking";
    }

    mysqli_stmt_close($stmt);
}
?>


