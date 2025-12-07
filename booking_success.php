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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-page {
            min-height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                url("img/bg.jpg") center / cover no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 40px;
        }
        
        .success-container {
            max-width: 600px;
            width: 100%;
            background: rgba(0,0,0,0.85);
            padding: 40px;
            border-radius: 10px;
            color: #fff;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .success-container h1 {
            color: #2ecc71;
            margin-bottom: 30px;
            font-size: 32px;
        }
        
        .booking-details {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }
        
        .booking-details h2 {
            color: #f5a623;
            margin-bottom: 20px;
            font-size: 20px;
            text-align: center;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: rgba(255,255,255,0.7);
            font-weight: normal;
        }
        
        .detail-value {
            color: #fff;
            font-weight: bold;
        }
        
        .booking-code {
            background: rgba(46, 204, 113, 0.2);
            border: 2px solid #2ecc71;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            color: #2ecc71;
            letter-spacing: 2px;
        }
        
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #1e90ff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #0f6fd6;
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
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
</body>
</html>

