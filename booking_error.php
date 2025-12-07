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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Error</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error-page {
            min-height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                url("img/bg.jpg") center / cover no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 40px;
        }
        
        .error-container {
            max-width: 600px;
            width: 100%;
            background: rgba(0,0,0,0.85);
            padding: 40px;
            border-radius: 10px;
            color: #fff;
            text-align: center;
        }
        
        .error-icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .error-container h1 {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 32px;
        }
        
        .error-message {
            background: rgba(231, 76, 60, 0.2);
            border: 2px solid #e74c3c;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .booking-info {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .booking-info h3 {
            color: #f5a623;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .info-row {
            padding: 8px 0;
            color: rgba(255,255,255,0.8);
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
</body>
</html>

