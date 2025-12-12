<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 1800);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

$database_url = getenv('DATABASE_URL');

if ($database_url) {
    $db_parts = parse_url($database_url);
    $host = $db_parts['host'] ?? 'localhost';
    $port = $db_parts['port'] ?? 5432;
    $user = $db_parts['user'] ?? '';
    $password = $db_parts['pass'] ?? '';
    $database = ltrim($db_parts['path'] ?? '', '/');
} else {
    $host = getenv('PGHOST') ?: 'localhost';
    $port = getenv('PGPORT') ?: 5432;
    $user = getenv('PGUSER') ?: '';
    $password = getenv('PGPASSWORD') ?: '';
    $database = getenv('PGDATABASE') ?: '';
}

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$database", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

function isLoggedIn() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
        return true;
    }
    elseif (isset($_COOKIE['user_id']) && isset($_COOKIE['username']) && isset($_COOKIE['role'])) {
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
