<?php
/**
 * Royal Stay - Database Connection Configuration
 * PDO database connection provider
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '3306');
    define('DB_NAME', 'royal_stay');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

/**
 * Get the singleton PDO instance
 *
 * @return PDO
 */
function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Fail gracefully without exposing database password
            error_log("Database connection error: " . $e->getMessage());
            die("
                <div style='font-family: sans-serif; padding: 30px; max-width: 600px; margin: 40px auto; border: 1px solid #DC3545; border-radius: 8px; background: #FFF5F5;'>
                    <h2 style='color: #DC3545; margin-top: 0;'>Database Connection Error</h2>
                    <p>Unable to connect to the MySQL database. Please verify your database configuration in <code>config/database.php</code> and ensure the MySQL server is running.</p>
                    <p style='color: #666; font-size: 13px;'>Error detail logged securely.</p>
                </div>
            ");
        }
    }

    return $pdo;
}

// Convenient global PDO instance for scripts
$pdo = get_db();
