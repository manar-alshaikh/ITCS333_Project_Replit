<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

$path = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");

// --------------------------------------------------
// STATIC HOME REDIRECT
// --------------------------------------------------
if ($path === "") {
    header("Location: /login");
    exit();
}

switch ($path) {

    // ----------------------------------------------
    // AUTH
    // ----------------------------------------------

    case "login":
        include __DIR__ . "/src/auth/login.php";
        break;

    case "logout":
        include __DIR__ . "/src/auth/logout.php";
        break;

    case "dashboard":
        include __DIR__ . "/src/auth/dashboard.php";
        break;

    case "admin":
        include __DIR__ . "/src/auth/AdminPortal.php";
        break;


    // ----------------------------------------------
    // WEEKLY PAGES
    // ----------------------------------------------

    case "weekly/list":
        include __DIR__ . "/src/weekly/list.php";
        break;

    case "weekly/details":
        include __DIR__ . "/src/weekly/details.php";
        break;

    case "weekly/admin":
        include __DIR__ . "/src/weekly/admin.php";
        break;


    // ----------------------------------------------
    // RESOURCES PAGES
    // ----------------------------------------------

    case "resources/list":
        include __DIR__ . "/src/resources/list.php";
        break;

    case "resources/details":
        include __DIR__ . "/src/resources/details.php";
        break;

    case "resources/admin":
        include __DIR__ . "/src/resources/admin.php";
        break;


    // ----------------------------------------------
    // ASSIGNMENTS PAGES
    // ----------------------------------------------

    case "assignments/list":
        include __DIR__ . "/src/assignments/list.html";
        break;

    case "assignments/details":
        include __DIR__ . "/src/assignments/details.html";
        break;

    case "assignments/admin":
        include __DIR__ . "/src/assignments/admin.html";
        break;


    // ----------------------------------------------
    // ADMIN USERS PAGE
    // ----------------------------------------------

    case "admin/users":
        include __DIR__ . "/src/admin/manage_users.html";
        break;


    // ----------------------------------------------
    // API ROUTES
    // ----------------------------------------------

    case "api/auth":
        include __DIR__ . "/src/auth/api/index.php";
        break;

    case "api/weekly":
        include __DIR__ . "/src/weekly/api/index.php";
        break;

    case "api/resources":
        include __DIR__ . "/src/resources/api/index.php";
        break;

    case "api/assignments":
        include __DIR__ . "/src/assignments/api/index.php";
        break;

    case "api/admin/users":
        include __DIR__ . "/src/admin/api/index.php";
        break;


    // ----------------------------------------------
    // 404 ERROR
    // ----------------------------------------------

    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>No route matches: {$path}</p>";
        break;
}
