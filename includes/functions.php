<?php
/**
 * Helper function to sanitize input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Helper function to validate email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Helper function to validate phone
 */
function validate_phone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it has at least 10 digits
    return strlen($phone) >= 10;
}

/**
 * Initialize table configuration if not exists
 */
function initialize_table_config($conn) {
    $table_configs = [];
    $config_query = "SELECT table_type, table_count FROM tables_config";
    $config_result = mysqli_query($conn, $config_query);
    if ($config_result) {
        while ($row = mysqli_fetch_assoc($config_result)) {
            $table_configs[$row['table_type']] = intval($row['table_count']);
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
                $table_configs[$config[0]] = $config[1];
            }
        }
    }
    
    return $table_configs;
}

/**
 * Get table counts from database
 */
function get_table_counts($conn) {
    $table_configs = initialize_table_config($conn);
    
    return [
        '2_seats' => $table_configs['2_seats'] ?? 6,
        'more_than_2' => $table_configs['more_than_2'] ?? 4,
        'bar' => $table_configs['bar'] ?? 5
    ];
}
?>

