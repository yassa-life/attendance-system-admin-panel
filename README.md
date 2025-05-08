# Edu Authorities Student Management System

A web-based attendance and employee management system featuring face recognition, RFID, leave management, and detailed work analysis. Built with PHP and MySQL, it provides a modern dashboard for admins and employees.

## Features

- **User Authentication**: Secure login for admins and employees.
- **Dashboard**: Overview of pending leave requests, total employees, daily check-ins, and late arrivals.
- **Employee Management**: Add, update, activate/deactivate employees.
- **Attendance Tracking**:
  - **Face Recognition**: Register and manage face images for attendance.
  - **RFID**: Assign and manage RFID cards for employees.
- **Leave Management**:
  - Employees can apply for various leave types.
  - Admins can approve or reject leave requests.
- **Work Time Analysis**: Visual and tabular reports of employee work hours, leave hours, and net work time.
- **Password Reset**: Secure password reset via email with one-time code.
- **Responsive UI**: Modern, mobile-friendly interface using Bootstrap and custom CSS.
- **Email Notifications**: Password reset codes sent via email (PHPMailer).

## Screenshots

> You can add screenshots from `assets/images/dashboard/` or `assets/images/auth/` here.

## Getting Started

### Prerequisites

- PHP 7.x or higher
- MySQL/MariaDB
- Web server (Apache recommended)
- Composer (for PHP dependencies)
- SMTP credentials for email (Gmail SMTP is used by default)

### Installation

1. **Clone the repository** to your web server directory.

2. **Database Setup**:
   - Create a MySQL database named `attendance`.
   - Import the required tables and data (not included here; create tables for `users`, `attendance`, `leave_application`, `rfid`, `user_lo`, `administration`, etc.).

3. **Configure Database Connection**:
   - Edit `includes/dbconnection.php` if your MySQL credentials differ:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "your_password";
     $dbname = "attendance";
     ```

4. **Email Setup**:
   - Edit `vendor/mail.php` with your SMTP credentials:
     ```php
     $mail->Username = 'your_email@gmail.com';
     $mail->Password = 'your_app_password';
     ```

5. **Dependencies**:
   - PHPMailer is included in `vendor/PHPMailer/`. If you need to update or reinstall, use Composer:
     ```
     composer require phpmailer/phpmailer
     ```

6. **Assets**:
   - All CSS, JS, and image assets are in the `assets/` directory.

### Running the Application

- Open your browser and navigate to the project directory (e.g., `http://localhost/DB/attendace/`).
- Login with your admin or user credentials.

## File Structure

- `index.php` - Login page
- `dashboard.php` - Main dashboard
- `manage.php` - Employee management
- `face.php` - Face image management
- `id.php` - RFID card management
- `leave.php` - Leave application (employee)
- `approve.php` - Leave approval (admin)
- `work.php` - Work time analysis
- `reset.php` - Password reset request
- `change_pwd.php` - Password change/reset
- `process/` - Backend scripts (login, logout)
- `includes/` - Header, footer, sidebar, DB connection
- `assets/` - CSS, JS, images
- `vendor/` - PHPMailer and mail scripts

## Security Notes

- Passwords are stored in plain text in the current implementation. **It is strongly recommended to use password hashing (e.g., `password_hash()` in PHP) in production.**
- Update SMTP credentials and database passwords before deploying.
- Restrict access to sensitive files and directories.

## License

<<<<<<< HEAD
This project is for educational purposes. Please review and adapt for production use. 
=======
This project is for educational purposes. Please review and adapt for production use. 
>>>>>>> 61dd073d36e3915021be49bbeedffc2fcd8771f9
=======
# attendance-system-admin-panel
>>>>>>> 6faa9673a3e56ee237096e90d1b59330031880bd
