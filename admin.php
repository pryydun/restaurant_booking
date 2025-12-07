<?php
session_start();
require_once "includes/db.php";
require_once "includes/functions.php";

// Simple authentication (in production, use proper authentication)
$admin_password = 'admin123'; // Change this to a secure password
$is_authenticated = isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $password = $_POST['password'] ?? '';
    if ($password === $admin_password) {
        $_SESSION['admin_authenticated'] = true;
        $is_authenticated = true;
    } else {
        $login_error = "Incorrect password";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_authenticated']);
    $is_authenticated = false;
}

// Handle table count updates
if ($is_authenticated && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_tables'])) {
    $updates = [];
    $errors = [];
    
    foreach (['2_seats', 'more_than_2', 'bar'] as $type) {
        $count = intval($_POST[$type] ?? 0);
        if ($count < 0 || $count > 50) {
            $errors[] = "Invalid count for {$type}";
        } else {
            $updates[$type] = $count;
        }
    }
    
    if (empty($errors)) {
        foreach ($updates as $type => $count) {
            $sql = "UPDATE tables_config SET table_count = ? WHERE table_type = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "is", $count, $type);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $success_message = "Table counts updated successfully!";
    } else {
        $error_message = implode(", ", $errors);
    }
}

// Get current table configuration
$table_configs = [];
$config_query = "SELECT table_type, table_count, description FROM tables_config";
$config_result = mysqli_query($conn, $config_query);
if ($config_result) {
    while ($row = mysqli_fetch_assoc($config_result)) {
        $table_configs[$row['table_type']] = [
            'count' => intval($row['table_count']),
            'description' => $row['description']
        ];
    }
}

// Initialize default values if table doesn't exist or is empty
if (empty($table_configs)) {
    $default_configs = [
        ['2_seats', 6, 'Tables for 2 persons'],
        ['more_than_2', 4, 'Tables for more than 2 persons'],
        ['bar', 5, 'Seats near the bar']
    ];
    
    foreach ($default_configs as $config) {
        $init_sql = "INSERT IGNORE INTO tables_config (table_type, table_count, description) VALUES (?, ?, ?)";
        $init_stmt = mysqli_prepare($conn, $init_sql);
        if ($init_stmt) {
            mysqli_stmt_bind_param($init_stmt, "sis", $config[0], $config[1], $config[2]);
            mysqli_stmt_execute($init_stmt);
            mysqli_stmt_close($init_stmt);
            $table_configs[$config[0]] = [
                'count' => $config[1],
                'description' => $config[2]
            ];
        }
    }
}

// Default values
$current_counts = [
    '2_seats' => $table_configs['2_seats']['count'] ?? 6,
    'more_than_2' => $table_configs['more_than_2']['count'] ?? 4,
    'bar' => $table_configs['bar']['count'] ?? 5
];

$descriptions = [
    '2_seats' => $table_configs['2_seats']['description'] ?? 'Tables for 2 persons',
    'more_than_2' => $table_configs['more_than_2']['description'] ?? 'Tables for more than 2 persons',
    'bar' => $table_configs['bar']['description'] ?? 'Seats near the bar'
];

$page_title = "Admin - Table Configuration";
$additional_css = ['admin.css'];
include "includes/header.php";
?>

<div class="admin-page">
    <div class="admin-container">
        <h1>Admin Panel</h1>
        
        <?php if (!$is_authenticated): ?>
            <!-- Login Form -->
            <form class="login-form" method="post">
                <?php if (isset($login_error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Enter admin password" 
                    required
                    autofocus
                >
                <button type="submit" name="login">Login</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn btn-secondary">Return to Home</a>
            </div>
        <?php else: ?>
            <!-- Table Configuration Form -->
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <form class="table-config-form" method="post">
                <div class="form-group">
                    <label for="2_seats">2-Seat Tables</label>
                    <div class="description"><?php echo htmlspecialchars($descriptions['2_seats']); ?></div>
                    <input 
                        type="number" 
                        id="2_seats" 
                        name="2_seats" 
                        value="<?php echo $current_counts['2_seats']; ?>"
                        min="0"
                        max="50"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="more_than_2">More Than 2-Seat Tables</label>
                    <div class="description"><?php echo htmlspecialchars($descriptions['more_than_2']); ?></div>
                    <input 
                        type="number" 
                        id="more_than_2" 
                        name="more_than_2" 
                        value="<?php echo $current_counts['more_than_2']; ?>"
                        min="0"
                        max="50"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="bar">Bar Seats</label>
                    <div class="description"><?php echo htmlspecialchars($descriptions['bar']); ?></div>
                    <input 
                        type="number" 
                        id="bar" 
                        name="bar" 
                        value="<?php echo $current_counts['bar']; ?>"
                        min="0"
                        max="50"
                        required
                    >
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_tables" class="btn">Update Table Counts</button>
                    <a href="index.php" class="btn btn-secondary">Return to Home</a>
                </div>
            </form>
            
            <div class="logout-link">
                <a href="?logout=1">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/footer.php"; ?>
