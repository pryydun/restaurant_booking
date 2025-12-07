<?php
session_start();

if (!isset($_SESSION['booking_success'])) {
    header("Location: booking.php");
    exit;
}

$booking = $_SESSION['booking_success'];
unset($_SESSION['booking_success']);

// Format date and time
$date_formatted = date('F j, Y', strtotime($booking['date']));
$time_formatted = date('g:i A', strtotime($booking['time']));

// Get table type label
$table_type_labels = [
    '2_seats' => 'Table for 2 persons',
    'more_than_2' => 'Table for more than 2 persons',
    'bar' => 'Bar seat'
];
$table_type_label = $table_type_labels[$booking['table_type']] ?? $booking['table_type'];

$page_title = "Booking Confirmed";
$additional_css = ['success.css'];
include "includes/header.php";
?>

<div class="success-page">
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Booking Confirmed!</h1>
        
        <div class="booking-code">
            <?php echo htmlspecialchars($booking['booking_code']); ?>
        </div>
        
        <div class="booking-details">
            <h2>Booking Details</h2>
            
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['name']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></span>
            </div>
            
            <?php if (!empty($booking['email'])): ?>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value"><?php echo $date_formatted; ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span class="detail-value"><?php echo $time_formatted; ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Number of Guests:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['guests']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Table Type:</span>
                <span class="detail-value"><?php echo htmlspecialchars($table_type_label); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Table Number:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['table_number']); ?></span>
            </div>
        </div>
        
        <p style="color: rgba(255,255,255,0.7); margin: 20px 0;">
            Please save your booking code for future reference. You can use it to check your booking status.
        </p>
        
        <div class="actions">
            <a href="index.php" class="btn">Return to Home</a>
            <a href="booking.php" class="btn btn-secondary">Book Another Table</a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
