<?php
/**
 * Authentication Handler for Login Form
 * 
 * This PHP script handles user authentication via POST requests from the Fetch API.
 * It validates credentials against a MySQL database using PDO,
 * creates sessions, and returns JSON responses.
 */

// --- Session Management ---
// TODO: Start a PHP session using session_start()
// This must be called before any output is sent to the browser
// Sessions allow us to store user data across multiple pages
session_start();

// --- Set Response Headers ---
// TODO: Set the Content-Type header to 'application/json'
// This tells the browser that we're sending JSON data back
header('Content-Type: application/json');

// TODO: (Optional) Set CORS headers if your frontend and backend are on different domains
// You'll need headers for Access-Control-Allow-Origin, Methods, and Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// --- Check Request Method ---
// TODO: Verify that the request method is POST
// Use the $_SERVER superglobal to check the REQUEST_METHOD
// If the request is not POST, return an error response and exit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// --- Get POST Data ---
// TODO: Retrieve the raw POST data
// The Fetch API sends JSON data in the request body
// Use file_get_contents with 'php://input' to read the raw request body
$input = file_get_contents('php://input');

// TODO: Decode the JSON data into a PHP associative array
// Use json_decode with the second parameter set to true
$data = json_decode($input, true);

// TODO: Extract the email and password from the decoded data
// Check if both 'email' and 'password' keys exist in the array
// If either is missing, return an error response and exit
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// TODO: Store the email and password in variables
// Trim any whitespace from the email
$email = trim($data['email']);
$password = $data['password'];

// --- Server-Side Validation (Optional but Recommended) ---
// TODO: Validate the email format on the server side
// Use the appropriate filter function for email validation
// If invalid, return an error response and exit
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// TODO: Validate the password length (minimum 8 characters)
// If invalid, return an error response and exit
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit;
}

// --- Database Connection ---
// Include the database connection from Config.php
require_once __DIR__ . '/../../config/Config.php';
$db = $conn;
// TODO: Wrap database operations in a try-catch block to handle PDO exceptions
// This ensures you can return a proper JSON error response if something goes wrong
try {

    // --- Prepare SQL Query ---
    $sql = "SELECT id, username, email, password_hash, role, student_id FROM users WHERE email = :email AND is_active = true";
    $stmt = $db->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // --- Verify User Exists and Password Matches ---
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    // --- Handle Successful Authentication ---
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;

    setcookie('user_id', $user['id'], time() + (86400 * 30), "/");
    setcookie('username', $user['username'], time() + (86400 * 30), "/");
    setcookie('role', $user['role'], time() + (86400 * 30), "/");
    
    $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    $updateStmt->execute([$user['id']]);

    $response = [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'redirect' => '/dashboard'
    ];

    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}
?>
<?php
// Unreachable code removed
if (false) {
    // --- Handle Failed Authentication ---
        // - 'success' => false
        // - 'message' => 'Invalid email or password'
        //
        // SECURITY NOTE: Don't specify whether email or password was wrong
        // This prevents attackers from enumerating valid email addresses
        $errorResponse = [
        'success' => false,
        'message' => 'Invalid email or password'
    ];

        // TODO: Encode the error response as JSON and echo it
        echo json_encode($errorResponse);
        
        // TODO: Exit the script
        exit;

    // TODO: Catch PDO exceptions in the catch block
    // Catch PDOException type

    } catch (PDOException $e) {
    // TODO: Log the error for debugging
    // Use error_log() to write the error message to the server error log
    error_log("Database error: " . $e->getMessage());
    
    // TODO: Return a generic error message to the client
    // DON'T expose database details to the user for security reasons
    // Return a JSON response with success false and a generic message
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);

    // TODO: Exit the script
    exit;
}

// --- End of Script ---

?>
