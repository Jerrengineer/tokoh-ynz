<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tokoh_ynz');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Currency Formatter Helper (Rupiah)
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Security: Input Sanitization
function sanitize($data) {
    global $conn;
    return htmlspecialchars(stripslashes(trim($conn->real_escape_string($data))));
}

// Check if user is logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Check role authorization (admin only)
function require_admin() {
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: dashboard.php?error=unauthorized");
        exit;
    }
}

// Get system notifications (low stock)
function get_low_stock_count() {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM products WHERE stock <= min_stock";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}
?>
