<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'thamerex');
define('DB_PASS', 'Tham3r99??!!??');
define('DB_NAME', 'cp1234_lettersmax');

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get a database connection
 */
function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_error) {
            die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $db->connect_error]));
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}

/**
 * Handle JSON Response
 */
function sendResponse($success, $data = null, $error = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error
    ]);
    exit;
}
?>
