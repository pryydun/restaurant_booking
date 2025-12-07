-- Restaurant Booking System Database Schema

CREATE DATABASE IF NOT EXISTS restaurant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant;

-- Table configuration for managing table types and counts
CREATE TABLE IF NOT EXISTS tables_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    table_type VARCHAR(50) NOT NULL UNIQUE,
    table_count INT NOT NULL DEFAULT 0,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default table configuration
INSERT INTO tables_config (table_type, table_count, description) VALUES
('2_seats', 6, 'Tables for 2 persons'),
('more_than_2', 4, 'Tables for more than 2 persons'),
('bar', 5, 'Seats near the bar')
ON DUPLICATE KEY UPDATE table_count=VALUES(table_count);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    guests INT NOT NULL,
    table_number INT NOT NULL,
    table_type VARCHAR(50) NOT NULL,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date_time (booking_date, booking_time),
    INDEX idx_table_date_time (table_number, booking_date, booking_time),
    INDEX idx_booking_code (booking_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

