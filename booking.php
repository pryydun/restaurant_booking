<?php
session_start();
require_once "includes/db.php";
require_once "includes/functions.php";

// Get table counts
$table_counts = get_table_counts($conn);

// Determine current step
$step = $_GET['step'] ?? '1';
$selected_table = intval($_GET['table'] ?? 0);
$selected_date = $_GET['date'] ?? '';

// Get form data from session if available (for error display)
$form_data = $_SESSION['booking_form_data'] ?? [];
$errors = $_SESSION['booking_errors'] ?? [];
unset($_SESSION['booking_form_data']);
unset($_SESSION['booking_errors']);

// Generate table IDs
$tables = [];
$table_id = 1;

// 2-seat tables
for ($i = 0; $i < $table_counts['2_seats']; $i++) {
    $tables[] = [
        'id' => $table_id++,
        'type' => '2_seats',
        'label' => '2'
    ];
}

// More than 2 seats tables
for ($i = 0; $i < $table_counts['more_than_2']; $i++) {
    $tables[] = [
        'id' => $table_id++,
        'type' => 'more_than_2',
        'label' => '4+'
    ];
}

// Bar seats
for ($i = 0; $i < $table_counts['bar']; $i++) {
    $tables[] = [
        'id' => $table_id++,
        'type' => 'bar',
        'label' => 'BAR'
    ];
}

// Get reserved dates for selected table (Step 2)
$reserved_dates = [];
if ($selected_table > 0 && $step == '2') {
    $check_sql = "SELECT DISTINCT booking_date FROM bookings WHERE table_number = ? ORDER BY booking_date";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "i", $selected_table);
        mysqli_stmt_execute($check_stmt);
        $res = mysqli_stmt_get_result($check_stmt);

        while ($row = mysqli_fetch_assoc($res)) {
            $reserved_dates[] = $row['booking_date'];
        }
        mysqli_stmt_close($check_stmt);
    }
}

// Get selected table info
$selected_table_info = null;
if ($selected_table > 0) {
    foreach ($tables as $table) {
        if ($table['id'] == $selected_table) {
            $selected_table_info = $table;
            break;
        }
    }
}

$page_title = "Book a Table";
$additional_css = ['booking.css'];
$body_attributes = 'data-current-step="' . htmlspecialchars($step) . '"';
include "includes/header.php";
?>

<div class="booking-page">
    <div class="booking-container">
        <!-- Progress Steps -->
        <div class="booking-steps">
            <div class="step-item <?php echo $step == '1' ? 'active' : ($step > '1' ? 'completed' : ''); ?>">
                <div class="step-number">1</div>
                <div class="step-label">Select Table</div>
            </div>
            <div class="step-item <?php echo $step == '2' ? 'active' : ($step > '2' ? 'completed' : ''); ?>">
                <div class="step-number">2</div>
                <div class="step-label">Choose Date</div>
            </div>
            <div class="step-item <?php echo $step == '3' ? 'active' : ($step > '3' ? 'completed' : ''); ?>">
                <div class="step-number">3</div>
                <div class="step-label">Complete Booking</div>
            </div>
        </div>

        <?php if ($step == '1'): ?>
            <!-- STEP 1: Table Selection -->
            <div class="step-content">
                <h2>Select a Table</h2>
                <p class="step-description">Choose a table from the restaurant layout</p>

                <div class="hall-wrapper">
                    <div class="hall" id="hall">
                        <?php foreach ($tables as $table): ?>
                            <div class="table-wrapper">
                                <div
                                    class="table"
                                    data-table="<?php echo $table['id']; ?>"
                                    data-type="<?php echo $table['type']; ?>"
                                    onclick="selectTable(<?php echo $table['id']; ?>, '<?php echo $table['type']; ?>')">
                                    <?php
                                    if ($table['type'] == '2_seats') {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13h16" /><path d="M12 13v8" /><path d="M8 21h8" /><path d="M5 13V8a2 2 0 0 0-2-2H2" /><path d="M2 13v8" /><path d="M5 13v8" /><path d="M19 13V8a2 2 0 0 1 2-2h1" /><path d="M22 13v8" /><path d="M19 13v8" /></svg>';
                                    } elseif ($table['type'] == 'more_than_2') {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="6" /><path d="M10 3a2 2 0 0 1 4 0" /><path d="M10 3v3" /><path d="M14 3v3" /><path d="M10 21a2 2 0 0 0 4 0" /><path d="M10 18v3" /><path d="M14 18v3" /><path d="M3 10a2 2 0 0 0 0 4" /><path d="M3 10h3" /><path d="M3 14h3" /><path d="M21 10a2 2 0 0 1 0 4" /><path d="M18 10h3" /><path d="M18 14h3" /></svg>';
                                    } else {
                                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12" /><path d="M12 8v13" /><path d="M9 21h6" /><path d="M16 12h4" /><path d="M16 12v9" /><path d="M20 12v9" /><path d="M16 17h4" /><rect x="9" y="5" width="6" height="3" rx="0.5" /><polyline points="10.5 6.5 11.5 7.5 13.5 5.5" /></svg>';
                                    }
                                    ?>
                                </div>
                                <div class="table-label">Table <?php echo $table['id']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="legend">
                        <div class="legend-item legend-available">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="6" />
                            </svg>
                            <span>Available</span>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($step == '2' && $selected_table > 0 && $selected_table_info): ?>
            <!-- STEP 2: Date Selection -->
            <div class="step-content">
                <h2>Choose a Date for Table <?php echo $selected_table; ?></h2>
                <p class="step-description">Select an available date. Reserved dates are marked in red.</p>

                <div class="calendar-wrapper">
                    <div
                        class="calendar"
                        id="calendar"
                        data-reserved-dates='<?php echo json_encode($reserved_dates); ?>'
                        data-selected-table="<?php echo $selected_table; ?>"></div>
                </div>

                <div class="step-actions">
                    <a href="booking.php?step=1" class="btn btn-secondary">← Back to Tables</a>
                </div>
            </div>

        <?php elseif ($step == '3' && $selected_table > 0 && $selected_date): ?>
            <!-- STEP 3: Booking Form -->
            <div class="step-content">
                <h2>Complete Your Booking</h2>
                <p class="step-description">
                    Table <?php echo $selected_table; ?> -
                    <?php echo date('F j, Y', strtotime($selected_date)); ?>
                </p>

                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form class="booking-form" action="book.php" method="post" id="bookingForm">
                    <input type="hidden" name="table_number" value="<?php echo $selected_table; ?>">
                    <input type="hidden" name="table_type" value="<?php echo htmlspecialchars($selected_table_info['type']); ?>">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">

                    <input
                        type="text"
                        name="name"
                        placeholder="Your full name *"
                        required
                        value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                        minlength="2">

                    <input
                        type="tel"
                        name="phone"
                        placeholder="Phone number *"
                        required
                        value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                        pattern="[0-9+\-\s()]+">

                    <input
                        type="email"
                        name="email"
                        placeholder="Email (optional)"
                        value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">

                    <input
                        type="time"
                        name="time"
                        id="booking_time"
                        required
                        value="<?php echo htmlspecialchars($form_data['time'] ?? ''); ?>">

                    <input
                        type="number"
                        name="guests"
                        placeholder="Number of guests *"
                        required
                        min="1"
                        max="20"
                        value="<?php echo htmlspecialchars($form_data['guests'] ?? ''); ?>">

                    <div class="step-actions">
                        <a href="booking.php?step=2&table=<?php echo $selected_table; ?>" class="btn btn-secondary">← Back</a>
                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <!-- Invalid step, redirect to step 1 -->
            <?php header("Location: booking.php?step=1");
            exit; ?>
        <?php endif; ?>
    </div>
</div>

<script src="js/booking.js"></script>

<?php include "includes/footer.php"; ?>