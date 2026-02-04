# PW Projekt: cząstkowy 1 PHP (User Registration System)

Simple user registration system built with PHP and MySQL.

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Web server (Apache/XAMPP)

## Installation

1. Clone the repository
2. Create MySQL database: `projekt1_rejestracja`
3. Copy `config.example.php` to `config.php`
4. Edit `config.php` with your database credentials
5. Run on your web server

## Files

- `index.php` - Registration form
- `register.php` - Registration logic
- `config.example.php` - Configuration template
- `style.css` - Styles

## Security

- `config.php` contains sensitive data and is excluded from Git
- Passwords are hashed using PHP's `password_hash()`
