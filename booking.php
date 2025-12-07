<?php
include "db.php";

$busyTables = [];

if (isset($_GET['date']) && isset($_GET['time'])) {
    $date = $_GET['date'];
    $time = $_GET['time'];

    $res = mysqli_query(
        $conn,
        "SELECT table_number FROM bookings
         WHERE booking_date='$date'
         AND booking_time='$time'"
    );

    while ($row = mysqli_fetch_assoc($res)) {
        $busyTables[] = intval($row['table_number']);
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Book a Table</title>
<link rel="stylesheet" href="css/style.css">

<style>
/* ===== SAME BACKGROUND AS HOME ===== */
.booking-page {
    min-height: 100vh;
    background:
        linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
        url("img/bg.jpg") center / cover no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* ===== CONTAINER ===== */
.booking-container {
    width: 900px;
    background: rgba(0,0,0,0.75);
    display: flex;
    gap: 40px;
    padding: 40px;
    border-radius: 10px;
    color: #fff;
}

/* ===== LEFT (FORM) ===== */
.booking-form {
    width: 45%;
}

.booking-form h2 {
    margin-bottom: 20px;
}

.booking-form input,
.booking-form button {
    width: 100%;
    padding: 12px;
    margin-bottom: 12px;
    border-radius: 4px;
    border: none;
}

.booking-form button {
    background: #1e90ff;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

/* ===== RIGHT (HALL) ===== */
.hall-wrapper {
    width: 55%;
}

.hall-wrapper h3 {
    margin-bottom: 15px;
}

/* HALL */
.hall {
    position: relative;
    width: 100%;
    height: 320px;
    background: rgba(255,255,255,0.12);
    border-radius: 10px;
}

/* TABLE */
.table {
    position: absolute;
    width: 70px;
    height: 70px;
    background: #2ecc71;
    color: #fff;
    font-weight: bold;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    cursor: pointer;
}

/* STATES */
.table.selected { background: #3498db; }
.table.busy {
    background: #e74c3c;
    cursor: not-allowed;
    opacity: 0.8;
}

/* POSITIONS */
.t1 { top: 40px; left: 60px; }
.t2 { top: 40px; right: 60px; }
.t3 { top: 120px; left: 200px; }
.t4 { bottom: 40px; left: 60px; }
.t5 { bottom: 40px; right: 60px; }
.t6 { bottom: 120px; right: 200px; }
</style>

<script>
const busyTables = <?= json_encode($busyTables); ?>;
</script>
</head>

<body>

<div class="booking-page">

<div class="booking-container">

<!-- LEFT -->
<form class="booking-form" action="book.php" method="post">

<h2>Book a Table</h2>

<input name="name" placeholder="Your name" required>
<input name="phone" placeholder="Phone" required>

<input type="date" name="date" required onchange="reloadPage()"
value="<?= $_GET['date'] ?? '' ?>">

<input type="time" name="time" required onchange="reloadPage()"
value="<?= $_GET['time'] ?? '' ?>">

<input type="number" name="guests" placeholder="Guests" required>

<input type="hidden" name="table_number" id="table_number" required>

<button type="submit">CONFIRM BOOKING</button>

</form>

<!-- RIGHT -->
<div class="hall-wrapper">

<h3>Restaurant Hall</h3>

<div class="hall">
    <div class="table t1" data-table="1">1</div>
    <div class="table t2" data-table="2">2</div>
    <div class="table t3" data-table="3">3</div>
    <div class="table t4" data-table="4">4</div>
    <div class="table t5" data-table="5">5</div>
    <div class="table t6" data-table="6">6</div>
</div>

</div>

</div>
</div>

<script>
function reloadPage() {
    const d = document.querySelector('input[name="date"]').value;
    const t = document.querySelector('input[name="time"]').value;
    if (d && t) {
        window.location.href = `booking.php?date=${d}&time=${t}`;
    }
}

/* TABLE LOGIC */
const tables = document.querySelectorAll('.table');
const input = document.getElementById('table_number');

tables.forEach(t => {
    const num = parseInt(t.dataset.table);

    if (busyTables.includes(num)) {
        t.classList.add('busy');
        t.innerText = "X";
    }

    t.onclick = () => {
        if (t.classList.contains('busy')) return;
        tables.forEach(el => el.classList.remove('selected'));
        t.classList.add('selected');
        input.value = num;
    };
});
</script>

</body>
</html>


