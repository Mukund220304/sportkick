<?php
/**
 * SportKick - Database Configuration & PDO Connection Manager
 * Uses MySQL PDO with Prepared Statements and Auto-Bootstrap support
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', getenv('SPORTKICK_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('SPORTKICK_DB_PORT') ?: '3306');
define('DB_NAME', getenv('SPORTKICK_DB_NAME') ?: 'sportkick_db');
define('DB_USER', getenv('SPORTKICK_DB_USER') ?: 'root');
define('DB_PASS', getenv('SPORTKICK_DB_PASS') ?: '');

/**
 * Returns a singleton PDO database connection instance.
 *
 * @return PDO
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Attempt direct connection to sportkick_db via TCP or Unix socket
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        try {
            // Fallback to localhost unix socket if 127.0.0.1 is not bound
            $dsnSocket = sprintf('mysql:host=localhost;dbname=%s;charset=utf8mb4', DB_NAME);
            $pdo = new PDO($dsnSocket, DB_USER, DB_PASS, $options);
        } catch (PDOException $innerException) {
            // Auto-bootstrap database from database/sportkick.sql if DB does not exist yet
            try {
                $rootPdo = new PDO(sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT), DB_USER, DB_PASS, $options);
                $sqlFile = dirname(__DIR__) . '/database/sportkick.sql';
                if (file_exists($sqlFile)) {
                    $sqlContent = file_get_contents($sqlFile);
                    $rootPdo->exec($sqlContent);
                    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
                    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                } else {
                    throw $innerException;
                }
            } catch (PDOException $finalException) {
                die('Database connection error: ' . htmlspecialchars($finalException->getMessage()));
            }
        }
    }

    return $pdo;
}

$pdo = getDBConnection();

