<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Restaurant Booking'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link rel="stylesheet" href="css/<?php echo htmlspecialchars($css_file); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body <?php echo isset($body_attributes) ? $body_attributes : ''; ?>>
    <header>
        <div class="logo">
            <img src="img/44.png" alt="SMOKED logo">
            <span>SMOKED</span>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="booking.php">Book a Table</a>
            <a href="admin.php">Admin</a>
        </nav>
    </header>