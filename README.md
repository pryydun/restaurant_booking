# Restaurant Booking System

A PHP-based restaurant table booking system with an interactive seat layout and admin panel.

## Features

- **Home Page**: Welcome page with navigation to booking
- **Booking Form**: Interactive restaurant hall layout with 3 table types:
  - 2-seat tables (default: 6 tables)
  - More than 2-seat tables (default: 4 tables)
  - Bar seats (default: 5 seats)
- **Real-time Availability**: Shows reserved tables in red (not clickable)
- **Form Validation**: Client-side and server-side validation
- **Success/Error Pages**: Proper feedback after booking attempts
- **Admin Panel**: Manage table counts for each table type

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Web server (Apache/Nginx)

## Installation

1. **Database Setup**:
   - Create a MySQL database named `restaurant`
   - Import the schema: `mysql -u root -p restaurant < schema.sql`
   - Or run the SQL commands from `schema.sql` in your database

2. **Configuration**:
   - Update database credentials in `db.php`:
     ```php
     $host = "127.0.0.1";
     $user = "root";
     $pass = "your_password";
     $db   = "restaurant";
     $port = 3307; // Change if needed
     ```

3. **Admin Access**:
   - Default admin password: `admin123`
   - Change the password in `admin.php` (line 7) for security:
     ```php
     $admin_password = 'your_secure_password';
     ```

4. **File Permissions**:
   - Ensure web server has read access to all files
   - Ensure PHP can write to session directory

## Usage

1. **Home Page** (`index.php`):
   - Navigate to the booking form or admin panel

2. **Booking** (`booking.php`):
   - Select date and time
   - View available tables in the hall layout
   - Click on an available table to select it
   - Fill in booking details (name, phone, email, guests)
   - Submit the form

3. **Admin Panel** (`admin.php`):
   - Login with admin password
   - Update table counts for each table type
   - Changes take effect immediately

## Database Schema

### `bookings` table
- Stores all booking information
- Includes booking code for reference
- Tracks table number, type, date, time, and guest information

### `tables_config` table
- Stores configuration for each table type
- Admin can update table counts
- Default values are set in the schema

## Security Notes

- **Change the admin password** in production
- Consider implementing proper authentication (sessions, password hashing)
- Add CSRF protection for forms
- Sanitize all user inputs (already implemented)
- Use prepared statements (already implemented)
- Consider rate limiting for booking submissions

## File Structure

```
restaurant_booking/
├── index.php              # Home page
├── booking.php            # Booking form with hall layout
├── book.php               # Booking submission handler
├── booking_success.php    # Success page after booking
├── booking_error.php      # Error page if table is taken
├── admin.php              # Admin panel for table management
├── db.php                 # Database connection
├── schema.sql             # Database schema
├── css/
│   └── style.css         # Main stylesheet
└── img/
    ├── table_2seats.svg
    ├── table_more_then2.svg
    ├── table_bar.svg
    └── table_reserved.svg
```

## Best Practices Implemented

- ✅ Prepared statements to prevent SQL injection
- ✅ Input sanitization and validation
- ✅ Session management for error/success messages
- ✅ Proper error handling
- ✅ Responsive design considerations
- ✅ Semantic HTML
- ✅ Separation of concerns (database, presentation, logic)

## Future Enhancements

- User authentication system
- Email notifications
- Booking cancellation
- Booking history for users
- Advanced admin features (view bookings, cancel bookings)
- Time slot management
- Multiple restaurant locations

