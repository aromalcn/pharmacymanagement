<?php
/**
 * config/database.php
 * Database connection configuration using PDO (PHP Data Objects).
 * Now using SQLite for easier local development without MAMP/MySQL.
 */

// Define the path to our SQLite database file
define('DB_PATH', __DIR__ . '/../pharmacy.sqlite');

/**
 * Returns a singleton PDO connection.
 * Using a singleton means we create only one connection per request.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // SQLite DSN format: sqlite:path/to/database.sqlite
        $dsn = "sqlite:" . DB_PATH;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            /**
     * Note: Emulate prepares is not needed for SQLite,
     * but keeping consistent allows for easier switching later.
     */
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, null, null, $options);
            // Enable foreign key constraints in SQLite
            $pdo->exec("PRAGMA foreign_keys = ON;");
        }
        catch (PDOException $e) {
            error_log("SQLite connection failed: " . $e->getMessage());
            die("Database connection error. Please ensure the pharmacy.sqlite file exists and is writable.");
        }
    }
    return $pdo;
}