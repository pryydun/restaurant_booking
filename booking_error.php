<?php
session_start();

$error_message = $_SESSION['booking_error'] ?? "This table is already reserved for the selected date and time.";
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$table = $_GET['table'] ?? '';

unset($_SESSION['booking_error']);

// Format date and time if available
$date_formatted = $date ? date('F j, Y', strtotime($date)) : '';
$time_formatted = $time ? date('g:i A', strtotime($time)) : '';

$page_title = "Booking Error";
$additional_css = ['error.css'];
include "includes/header.php";
?>

<div class="error-page">
    <div class="error-container">
        <div class="error-icon">✗</div>
        <h1>Booking Unavailable</h1>
        
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        
        <?php if ($date || $time || $table): ?>
        <div class="booking-info">
            <h3>Selected Details:</h3>
            <?php if ($date_formatted): ?>
                <div class="info-row"><strong>Date:</strong> <?php echo $date_formatted; ?></div>
            <?php endif; ?>
            <?php if ($time_formatted): ?>
                <div class="info-row"><strong>Time:</strong> <?php echo $time_formatted; ?></div>
            <?php endif; ?>
            <?php if ($table): ?>
                <div class="info-row"><strong>Table:</strong> #<?php echo htmlspecialchars($table); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <p style="color: rgba(255,255,255,0.7); margin: 20px 0;">
            Please select a different table or choose another date/time for your reservation.
        </p>
        
        <div class="actions">
            <?php if ($date && $time): ?>
                <a href="booking.php?date=<?php echo urlencode($date); ?>&time=<?php echo urlencode($time); ?>" class="btn">Return to Booking Form</a>
            <?php else: ?>
                <a href="booking.php" class="btn">Return to Booking Form</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Return to Home</a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
