<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration MUST be before session_start()
    ini_set('session.cookie_lifetime', 0); // Session ends when browser closes
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    ini_set('session.cookie_httponly', 1); // Prevent JS access
    ini_set('session.use_strict_mode', 1); // Security enhancement
    
    // Enable if using HTTPS:
    // ini_set('session.cookie_secure', 1);
    
    session_start();
}

// Database configuration
$host = "localhost";
$user = "root"; 
$password = ""; // ⚠️ Change this in production!
$database = "course_page";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Important for security
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Authentication functions
function isLoggedIn() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
        return true;
    }
    elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['username']) && isset($_COOKIE['role'])) {
        // Validate cookie data before using
        if (!is_numeric($_COOKIE['user_id']) || empty($_COOKIE['username']) || empty($_COOKIE['role'])) {
            return false;
        }
        
        $_SESSION['user_id'] = (int)$_COOKIE['user_id'];
        $_SESSION['username'] = htmlspecialchars($_COOKIE['username'], ENT_QUOTES, 'UTF-8');
        $_SESSION['role'] = htmlspecialchars($_COOKIE['role'], ENT_QUOTES, 'UTF-8');
        return true;
    }
    return false;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isInstructor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'instructor';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Authorization helpers
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: unauthorized.php");
        exit();
    }
}

function requireInstructor() {
    if (!isLoggedIn() || !isInstructor()) {
        header("Location: unauthorized.php");
        exit();
    }
}

function requireStudent() {
    if (!isLoggedIn() || !isStudent()) {
        header("Location: unauthorized.php");
        exit();
    }
}

// CSRF protection function (recommended)
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>