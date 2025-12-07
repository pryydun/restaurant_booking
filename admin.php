<?php
session_start();
include "db.php";

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Table Configuration</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-page {
            min-height: 100vh;
            background:
                linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                url("img/bg.jpg") center / cover no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 120px 20px 40px;
        }
        
        .admin-container {
            max-width: 600px;
            width: 100%;
            background: rgba(0,0,0,0.85);
            padding: 40px;
            border-radius: 10px;
            color: #fff;
        }
        
        .admin-container h1 {
            color: #f5a623;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .login-form {
            margin-bottom: 20px;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 14px;
        }
        
        .login-form input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        
        .login-form button {
            width: 100%;
            padding: 12px;
            background: #1e90ff;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .login-form button:hover {
            background: #0f6fd6;
        }
        
        .error-message {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #ff6b6b;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .success-message {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .table-config-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255,255,255,0.9);
            font-weight: bold;
        }
        
        .form-group .description {
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1e90ff;
            background: rgba(255,255,255,0.15);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            background: #1e90ff;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: inline-block;
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
        
        .logout-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .logout-link a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-link a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
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
</body>
</html>

