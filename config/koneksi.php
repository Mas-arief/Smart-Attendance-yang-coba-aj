<?php
/**
 * ==========================================
 * SMART ATTENDANCE - DATABASE CONNECTION
 * Face Recognition System
 * Created: 2025-12-30
 * ==========================================
 */

// Error Reporting (Development Mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_attendance_db');
define('DB_CHARSET', 'utf8mb4');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// ==========================================
// CONNECTION MYSQLI
// ==========================================
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, DB_CHARSET);

// ==========================================
// FUNCTION HELPER
// ==========================================

/**
 * Escape string untuk mencegah SQL Injection
 */
function escapeString($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}

/**
 * Execute query dan return result
 */
function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("❌ Query Error: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Get single row as associative array
 */
function getSingle($sql) {
    $result = query($sql);
    return mysqli_fetch_assoc($result);
}

/**
 * Get all rows as associative array
 */
function getAll($sql) {
    $result = query($sql);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

/**
 * Insert data dan return last insert ID
 */
function insert($table, $data) {
    global $conn;

    $columns = implode(', ', array_keys($data));
    $values = "'" . implode("', '", array_map('escapeString', array_values($data))) . "'";

    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    query($sql);

    return mysqli_insert_id($conn);
}

/**
 * Update data
 */
function update($table, $data, $where) {
    $set = [];

    foreach ($data as $key => $value) {
        $set[] = "$key = '" . escapeString($value) . "'";
    }

    $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
    return query($sql);
}

/**
 * Delete data
 */
function delete($table, $where) {
    $sql = "DELETE FROM $table WHERE $where";
    return query($sql);
}

/**
 * Hitung jumlah row
 */
function countRows($table, $where = '1=1') {
    $result = getSingle("SELECT COUNT(*) as total FROM $table WHERE $where");
    return $result['total'];
}

/**
 * Get setting value by key
 */
function getSetting($key) {
    $result = getSingle("SELECT setting_value FROM settings WHERE setting_key = '$key'");
    return $result ? $result['setting_value'] : null;
}

/**
 * Update setting value
 */
function updateSetting($key, $value) {
    return query("UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
}

/**
 * Log sistem activity
 */
function logActivity($user_id, $user_type, $action, $description = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    insert('system_logs', [
        'user_id' => $user_id,
        'user_type' => $user_type,
        'action' => $action,
        'description' => $description,
        'ip_address' => $ip
    ]);
}

/**
 * Return JSON Response
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Return Success JSON
 */
function jsonSuccess($message, $data = []) {
    jsonResponse([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ], 200);
}

/**
 * Return Error JSON
 */
function jsonError($message, $code = 400) {
    jsonResponse([
        'status' => 'error',
        'message' => $message
    ], $code);
}

// ==========================================
// SESSION MANAGEMENT
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUser() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is dosen
 */
function isDosen() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'dosen';
}

/**
 * Require login (redirect jika belum login)
 */
function requireLogin($redirect = '../login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin($redirect = '../index.php') {
    if (!isAdmin()) {
        header("Location: $redirect");
        exit;
    }
}

// ==========================================
// DEBUG MODE (Matikan di Production!)
// ==========================================
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    echo "✅ Database Connected: " . DB_NAME . "<br>";
    echo "📅 Timezone: " . date_default_timezone_get() . "<br>";
    echo "🕒 Current Time: " . date('Y-m-d H:i:s') . "<br>";
}

?>
