<?php
/**
 * config/database.php
 * Database connection configuration using PDO (PHP Data Objects).
 * PDO gives us prepared statement support – protecting against SQL injection.
 */

// --- Database settings (update these for your XAMPP / server setup) ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // XAMPP default user
define('DB_PASS', '');           // XAMPP default password (empty)
define('DB_NAME', 'pharmacy_db');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO connection.
 * Using a singleton means we create only one connection per request.
 */
function getDB(): PDO {
    static $pdo = null; // static keeps the value between function calls

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // return rows as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                   // use real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, never show error details to end users
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error. Please contact support.");
        }
    }

    return $pdo;
}
