<?php
session_start();
require_once "includes/db.php";
require_once "includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = [];

    // Sanitize and validate inputs
    $name = sanitize_input($_POST['name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $guests = intval($_POST['guests'] ?? 0);
    $table_number = intval($_POST['table_number'] ?? 0);
    $table_type = sanitize_input($_POST['table_type'] ?? '');

    // Validation
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long";
    }

    if (empty($phone) || !validate_phone($phone)) {
        $errors[] = "Please enter a valid phone number";
    }

    if (!empty($email) && !validate_email($email)) {
        $errors[] = "Please enter a valid email address";
    }

    if (empty($date)) {
        $errors[] = "Please select a date";
    } else {
        // Check if date is not in the past
        $selected_date = strtotime($date);
        $today = strtotime(date('Y-m-d'));
        if ($selected_date < $today) {
            $errors[] = "Cannot book for past dates";
        }
    }

    if (empty($time)) {
        $errors[] = "Please select a time";
    }

    if ($guests < 1 || $guests > 20) {
        $errors[] = "Number of guests must be between 1 and 20";
    }

    if ($table_number < 1) {
        $errors[] = "Please select a table";
    }

    if (empty($table_type)) {
        $errors[] = "Table type is required";
    }

    // If there are validation errors, redirect back with errors
    if (!empty($errors)) {
        $_SESSION['booking_errors'] = $errors;
        $_SESSION['booking_form_data'] = $_POST;
        header("Location: booking.php?" . http_build_query([
            'date' => $date,
            'time' => $time
        ]));
        exit;
    }

    // Check if table is already booked for this date and time
    // Use a transaction to prevent race conditions
    mysqli_begin_transaction($conn);

    $check_sql = "SELECT id FROM bookings 
                  WHERE table_number = ? 
                  AND booking_date = ? 
                  AND booking_time = ? 
                  FOR UPDATE";

    $check_stmt = mysqli_prepare($conn, $check_sql);
    if (!$check_stmt) {
        mysqli_rollback($conn);
        $_SESSION['booking_error'] = "Database error. Please try again.";
        header("Location: booking.php?" . http_build_query([
            'date' => $date,
            'time' => $time
        ]));
        exit;
    }

    mysqli_stmt_bind_param($check_stmt, "iss", $table_number, $date, $time);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) > 0) {
        // Table is already booked
        mysqli_stmt_close($check_stmt);
        mysqli_rollback($conn);
        $_SESSION['booking_error'] = "This table is already reserved for the selected date and time.";
        $_SESSION['booking_form_data'] = $_POST;
        header("Location: booking_error.php?" . http_build_query([
            'date' => $date,
            'time' => $time,
            'table' => $table_number
        ]));
        exit;
    }
    mysqli_stmt_close($check_stmt);

    // Generate unique booking code
    $booking_code = "BK-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

    // Ensure booking code is unique
    $code_check = true;
    while ($code_check) {
        $check_code_sql = "SELECT id FROM bookings WHERE booking_code = ?";
        $check_code_stmt = mysqli_prepare($conn, $check_code_sql);
        mysqli_stmt_bind_param($check_code_stmt, "s", $booking_code);
        mysqli_stmt_execute($check_code_stmt);
        $code_result = mysqli_stmt_get_result($check_code_stmt);

        if (mysqli_num_rows($code_result) == 0) {
            $code_check = false;
        } else {
            $booking_code = "BK-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        }
        mysqli_stmt_close($check_code_stmt);
    }

    // Insert booking
    $sql = "INSERT INTO bookings 
            (name, phone, email, booking_date, booking_time, guests, table_number, table_type, booking_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        $_SESSION['booking_error'] = "Database error. Please try again.";
        header("Location: booking.php?" . http_build_query([
            'date' => $date,
            'time' => $time
        ]));
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssiiss",
        $name,
        $phone,
        $email,
        $date,
        $time,
        $guests,
        $table_number,
        $table_type,
        $booking_code
    );

    if (mysqli_stmt_execute($stmt)) {
        // Success - commit transaction and redirect to success page
        mysqli_commit($conn);
        $booking_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        $_SESSION['booking_success'] = [
            'booking_code' => $booking_code,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
            'table_number' => $table_number,
            'table_type' => $table_type
        ];

        header("Location: booking_success.php");
        exit;
    } else {
        mysqli_rollback($conn);
        mysqli_stmt_close($stmt);
        $_SESSION['booking_error'] = "Error saving booking. Please try again.";
        header("Location: booking.php?" . http_build_query([
            'date' => $date,
            'time' => $time
        ]));
        exit;
    }
} else {
    // Not a POST request, redirect to booking page
    header("Location: booking.php");
    exit;
}
