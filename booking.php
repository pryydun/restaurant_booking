<?php
session_start();
require_once "includes/db.php";
require_once "includes/functions.php";

// Get table counts
$table_counts = get_table_counts($conn);

// Get busy tables for selected date/time
$busyTables = [];
$selected_date = $_GET['date'] ?? '';
$selected_time = $_GET['time'] ?? '';

// If date is selected, check for reservations on that date
if ($selected_date) {
    if ($selected_time) {
        // Check for exact date and time match using prepared statement
        $check_sql = "SELECT table_number, table_type FROM bookings
                      WHERE booking_date = ? AND booking_time = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "ss", $selected_date, $selected_time);
            mysqli_stmt_execute($check_stmt);
            $res = mysqli_stmt_get_result($check_stmt);
            
            while ($row = mysqli_fetch_assoc($res)) {
                $busyTables[] = [
                    'number' => intval($row['table_number']),
                    'type' => $row['table_type']
                ];
            }
            mysqli_stmt_close($check_stmt);
        }
    } else {
        // If only date is selected, show all tables reserved for that date (any time)
        $check_sql = "SELECT table_number, table_type FROM bookings
                      WHERE booking_date = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "s", $selected_date);
            mysqli_stmt_execute($check_stmt);
            $res = mysqli_stmt_get_result($check_stmt);
            
            while ($row = mysqli_fetch_assoc($res)) {
                $busyTables[] = [
                    'number' => intval($row['table_number']),
                    'type' => $row['table_type']
                ];
            }
            mysqli_stmt_close($check_stmt);
        }
    }
}

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

// Check which tables are busy
$busy_table_numbers = array_column($busyTables, 'number');

$page_title = "Book a Table";
$additional_css = ['booking.css'];
include "includes/header.php";
?>

<div class="booking-page">
    <div class="booking-container">
        <!-- LEFT FORM -->
        <form class="booking-form" action="book.php" method="post" id="bookingForm">
            <h2>Book a Table</h2>
            
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
            
            <input 
                type="text" 
                name="name" 
                placeholder="Your full name *" 
                required
                value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                minlength="2"
            >
            
            <input 
                type="tel" 
                name="phone" 
                placeholder="Phone number *" 
                required
                value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                pattern="[0-9+\-\s()]+"
            >
            
            <input 
                type="email" 
                name="email" 
                placeholder="Email (optional)"
                value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
            >
            
            <input 
                type="date" 
                name="date" 
                id="booking_date"
                required 
                onchange="reloadPage()"
                value="<?php echo htmlspecialchars($selected_date ?: ($form_data['date'] ?? '')); ?>"
                min="<?php echo date('Y-m-d'); ?>"
            >
            
            <input 
                type="time" 
                name="time" 
                id="booking_time"
                required 
                onchange="reloadPage()"
                value="<?php echo htmlspecialchars($selected_time ?: ($form_data['time'] ?? '')); ?>"
            >
            
            <?php if ($selected_date && !$selected_time): ?>
                <p style="color: #f5a623; font-size: 12px; margin-top: -10px; margin-bottom: 10px;">
                    ⚠️ Please select a time to see table availability for specific time slots.
                </p>
            <?php endif; ?>
            
            <input 
                type="number" 
                name="guests" 
                placeholder="Number of guests *" 
                required
                min="1"
                max="20"
                value="<?php echo htmlspecialchars($form_data['guests'] ?? ''); ?>"
            >
            
            <input type="hidden" name="table_number" id="table_number" required>
            <input type="hidden" name="table_type" id="table_type" required>
            
            <button type="submit" id="submitBtn" disabled>CONFIRM BOOKING</button>
            
            <p style="margin-top: 15px; font-size: 12px; color: rgba(255,255,255,0.6);">
                * Required fields
            </p>
        </form>
        
        <!-- RIGHT HALL -->
        <div class="hall-wrapper">
            <h3>Restaurant Hall</h3>
            
            <div class="hall" id="hall">
                <?php 
                $table_index = 0;
                foreach ($tables as $table): 
                    $is_busy = in_array($table['id'], $busy_table_numbers);
                    $table_index++;
                ?>
                    <div class="table-wrapper">
                        <div 
                            class="table <?php echo $is_busy ? 'busy' : ''; ?>" 
                            data-table="<?php echo $table['id']; ?>"
                            data-type="<?php echo $table['type']; ?>"
                            <?php if (!$is_busy): ?>onclick="selectTable(this)"<?php endif; ?>
                        >
                            <?php if ($is_busy): ?>
                                <!-- Reserved overlay -->
                                <div class="table-reserved-overlay">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19L12 5l8 14H4z" />
                                        <polyline points="9 14 11 16 15 11" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
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
                        <div class="table-label"><?php echo $table['id']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="legend">
                <div class="legend-item legend-available">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="6"/>
                    </svg>
                    <span>Available</span>
                </div>
                <div class="legend-item legend-selected">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="6"/>
                    </svg>
                    <span>Selected</span>
                </div>
                <div class="legend-item legend-busy">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="6"/>
                    </svg>
                    <span>Reserved</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const busyTables = <?php echo json_encode($busy_table_numbers); ?>;
const selectedDate = <?php echo json_encode($selected_date); ?>;
const selectedTime = <?php echo json_encode($selected_time); ?>;

function reloadPage() {
    const d = document.querySelector('input[name="date"]').value;
    const t = document.querySelector('input[name="time"]').value;
    // Reload when date is selected (even without time)
    if (d) {
        if (t) {
            window.location.href = `booking.php?date=${d}&time=${t}`;
        } else {
            window.location.href = `booking.php?date=${d}`;
        }
    }
}

function selectTable(element) {
    // Remove previous selection
    document.querySelectorAll('.table').forEach(t => {
        if (!t.classList.contains('busy')) {
            t.classList.remove('selected');
        }
    });
    
    // Add selection to clicked table
    element.classList.add('selected');
    
    // Set form values
    document.getElementById('table_number').value = element.dataset.table;
    document.getElementById('table_type').value = element.dataset.type;
    
    // Enable submit button
    document.getElementById('submitBtn').disabled = false;
}

// Form validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const tableNumber = document.getElementById('table_number').value;
    const tableType = document.getElementById('table_type').value;
    const bookingDate = document.getElementById('booking_date').value;
    const bookingTime = document.getElementById('booking_time').value;
    
    if (!tableNumber || !tableType) {
        e.preventDefault();
        alert('Please select a table from the hall layout.');
        return false;
    }
    
    if (!bookingDate || !bookingTime) {
        e.preventDefault();
        alert('Please select both date and time for your booking.');
        return false;
    }
    
    // Check if selected table is busy
    if (busyTables.includes(parseInt(tableNumber))) {
        e.preventDefault();
        alert('This table is already reserved for the selected date and time. Please select another table.');
        return false;
    }
});

// Disable busy tables on click
document.querySelectorAll('.table.busy').forEach(table => {
    table.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        alert('This table is already reserved for the selected date and time.');
    });
});
</script>

<?php include "includes/footer.php"; ?>
