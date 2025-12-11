<?php
/**
 * Assignment Management API
 * 
 * This is a RESTful API that handles all CRUD operations for course assignments
 * and their associated discussion comments.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: assignments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - title (VARCHAR(200))
 *   - description (TEXT)
 *   - due_date (DATE)
 *   - files (TEXT)
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - assignment_id (VARCHAR(50), FOREIGN KEY)
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve assignment(s) or comment(s)
 *   - POST: Create a new assignment or comment
 *   - PUT: Update an existing assignment
 *   - DELETE: Delete an assignment or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// HEADERS AND CORS CONFIGURATION
// ============================================================================

// TODO: Set Content-Type header to application/json
header("Content-Type: application/json");

// TODO: Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// TODO: Handle preflight OPTIONS request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS")
{
    http_response_code(200);
    exit();
}


// ============================================================================
// DATABASE CONNECTION
// ============================================================================

// TODO: Include the database connection class
require_once __DIR__ . '/config/Config.php';

// TODO: Create database connection
$host = "localhost";
$user = "admin"; 
$password = "password123"; 
$database = "course";
try 
{
    $db = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    // TODO: Set PDO to throw exceptions on errors
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) 
{
    die("Connection failed: " . $e->getMessage());
}



// ============================================================================
// REQUEST PARSING
// ============================================================================

// TODO: Get the HTTP request method
$method = $_SERVER["REQUEST_METHOD"];

// TODO: Get the request body for POST and PUT requests
$body = null;
if ($method === "POST" || $method === "PUT")
{
    $body = json_decode(file_get_contents("php://input"), true);
}

// TODO: Parse query parameters
$queryParams = $_GET;


// ============================================================================
// ASSIGNMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all assignments
 * Method: GET
 * Endpoint: ?resource=assignments
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, due_date, created_at)
 *   - order: Optional sort order (asc or desc, default: asc)
 * 
 * Response: JSON array of assignment objects
 */
function getAllAssignments($db)
{
    // TODO: Start building the SQL query
    $sql = "SELECT * FROM assignments";
    $params = [];
    
    // TODO: Check if 'search' query parameter exists in $_GET
    if (isset($_GET["search"]) && $_GET["search"] !== "")
    {
        $sql .= " WHERE title LIKE :search OR description LIKE :search";
        $params[":search"] = "%" . $_GET["search"] . "%";
    }
    
    // TODO: Check if 'sort' and 'order' query parameters exist
    $allowedSort = ["title", "due_date", "created_at"];
    $allowedOrder = ["asc", "desc"];
    if (isset($_GET["sort"]) && in_array($_GET["sort"], $allowedSort))
        {
        $order = "asc";
        if (isset($_GET["order"]) && in_array(strtolower($_GET["order"]), $allowedOrder))
            {
            $order = strtolower($_GET["order"]);
            }
        $sql .= " ORDER BY " . $_GET["sort"] . " " . strtoupper($order);
    }
    
    // TODO: Prepare the SQL statement using $db->prepare()
    $stmt = $db->prepare($sql);
    
    // TODO: Bind parameters if search is used
    foreach ($params as $key => $value)
    {
        $stmt->bindValue($key, $value);
    }
    
    // TODO: Execute the prepared statement
    $stmt->execute();
    
    // TODO: Fetch all results as associative array
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // TODO: For each assignment, decode the 'files' field from JSON to array
    foreach ($assignments as &$assignment)
    {
        if (isset($assignment["files"])) {
            $assignment["files"] = json_decode($assignment["files"], true);
        }
    }
    
    // TODO: Return JSON response
    echo json_encode($assignments);
}


/**
 * Function: Get a single assignment by ID
 * Method: GET
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: The assignment ID (required)
 * 
 * Response: JSON object with assignment details
 */
function getAssignmentById($db, $assignmentId)
{
    // TODO: Validate that $assignmentId is provided and not empty
    if (empty($assignmentId))
    {
        echo json_encode(["error" => "Assignment ID is required"]);
        return;
    }
    
    // TODO: Prepare SQL query to select assignment by id
    $sql = "SELECT * FROM assignments WHERE id = :id";
    
    // TODO: Bind the :id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":id", $assignmentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Fetch the result as associative array
     $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // TODO: Check if assignment was found
    if (!$assignment)
    {
        echo json_encode(["error" => "Assignment not found"]);
        return;
    }
    
    // TODO: Decode the 'files' field from JSON to array
    if (isset($assignment["files"]))
    {
        $assignment["files"] = json_decode($assignment["files"], true);
    }
    
    // TODO: Return success response with assignment data
    echo json_encode($assignment);
}


/**
 * Function: Create a new assignment
 * Method: POST
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - title: Assignment title (required)
 *   - description: Assignment description (required)
 *   - due_date: Due date in YYYY-MM-DD format (required)
 *   - files: Array of file URLs/paths (optional)
 * 
 * Response: JSON object with created assignment data
 */
function createAssignment($db, $data)
{
    // TODO: Validate required fields
    if (empty($data["title"]) || empty($data["description"]) || empty($data["due_date"]))
    {
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }
    
    // TODO: Sanitize input data
    $title = htmlspecialchars(trim($data["title"]));
    $description = htmlspecialchars(trim($data["description"]));
    $due_date = trim($data["due_date"]);
    
    // TODO: Validate due_date format
    $dateRegex = "/^\d{4}-\d{2}-\d{2}$/";
    if (!preg_match($dateRegex, $due_date))
    {
        echo json_encode(["error" => "Invalid due_date format. Use YYYY-MM-DD"]);
        return;
    }
    
    // TODO: Generate a unique assignment ID
    //$id = uniqid("assign_", true);
    
    // TODO: Handle the 'files' field
    $files = isset($data["files"]) ? json_encode($data["files"]) : json_encode([]);
    
    // TODO: Prepare INSERT query
    $sql = "INSERT INTO assignments (title, description, due_date, files, created_at) 
            VALUES (:title, :description, :due_date, :files, NOW())";
    
    // TODO: Bind all parameters
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":title", $title);
    $stmt->bindValue(":description", $description);
    $stmt->bindValue(":due_date", $due_date);
    $stmt->bindValue(":files", $files);
    
    // TODO: Execute the statement
    $success = $stmt->execute();
    
    // TODO: Check if insert was successful
    if ($success)
    {
        $id = $db->lastInsertId();
        $createdAssignment = [
            "id" => $id,
            "title" => $title,
            "description" => $description,
            "due_date" => $due_date,
            "files" => json_decode($files, true),
            "created_at" => date("Y-m-d H:i:s")
        ];
        echo json_encode($createdAssignment);
    } else {
    
    // TODO: If insert failed, return 500 error
    http_response_code(500);
        echo json_encode(["error" => "Failed to create assignment"]);
    }
}


/**
 * Function: Update an existing assignment
 * Method: PUT
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - id: Assignment ID (required, to identify which assignment to update)
 *   - title: Updated title (optional)
 *   - description: Updated description (optional)
 *   - due_date: Updated due date (optional)
 *   - files: Updated files array (optional)
 * 
 * Response: JSON object with success status
 */
function updateAssignment($db, $data)
{
    // TODO: Validate that 'id' is provided in $data
    if (empty($data["id"]))
    {
        http_response_code(400);
        echo json_encode(["error" => "Assignment ID is required"]);
        return;
    }
    // TODO: Store assignment ID in variable
    $id = $data["id"];
    
    // TODO: Check if assignment exists
    $checkStmt = $db->prepare("SELECT * FROM assignments WHERE id = :id");
    $checkStmt->bindValue(":id", $id, PDO::PARAM_INT);
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing)
    {
        http_response_code(404);
        echo json_encode(["error" => "Assignment not found"]);
        return;
    }
    
    // TODO: Build UPDATE query dynamically based on provided fields
    $fields = [];
    $params = [];
    
    // TODO: Check which fields are provided and add to SET clause
    if (!empty($data["title"])) {
        $fields[] = "title = :title";
        $params[":title"] = htmlspecialchars(trim($data["title"]));
    }
    if (!empty($data["description"]))
    {
        $fields[] = "description = :description";
        $params[":description"] = htmlspecialchars(trim($data["description"]));
    }
    if (!empty($data["due_date"]))
    {
        $fields[] = "due_date = :due_date";
        $params[":due_date"] = trim($data["due_date"]);
    }
    if (isset($data["files"]))
    {
        $fields[] = "files = :files";
        $params[":files"] = json_encode($data["files"]);
    }
    
    // TODO: If no fields to update (besides updated_at), return 400 error
    if (empty($fields))
    {
        http_response_code(400);
        echo json_encode(["error" => "No fields provided to update"]);
        return;
    }
    
    // TODO: Complete the UPDATE query
    $sql = "UPDATE assignments SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
    
    // TODO: Prepare the statement
    $stmt = $db->prepare($sql);
    
    // TODO: Bind all parameters dynamically
    foreach ($params as $key => $value)
    {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Check if update was successful
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Assignment updated successfully"]);
    } else {
    
    // TODO: If no rows affected, return appropriate message
    echo json_encode(["success" => false, "message" => "No changes made to the assignment"]);
}
}


/**
 * Function: Delete an assignment
 * Method: DELETE
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: Assignment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteAssignment($db, $assignmentId)
{
    // TODO: Validate that $assignmentId is provided and not empty
     if (empty($assignmentId))
    {
        http_response_code(400);
        echo json_encode(["error" => "Assignment ID is required"]);
        return;
    }
    
    // TODO: Check if assignment exists
    $checkStmt = $db->prepare("SELECT * FROM assignments WHERE id = :id");
    $checkStmt->bindValue(":id", $assignmentId, PDO::PARAM_INT);
    $checkStmt->execute();
    $assignment = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$assignment)
    {
        http_response_code(404);
        echo json_encode(["error" => "Assignment not found"]);
        return;
    }
    
    // TODO: Delete associated comments first (due to foreign key constraint)
    $deleteComments = $db->prepare("DELETE FROM comments WHERE assignment_id = :id");
    $deleteComments->bindValue(":id", $assignmentId, PDO::PARAM_INT);
    $deleteComments->execute();
    
    // TODO: Prepare DELETE query for assignment
    $sql = "DELETE FROM assignments WHERE id = :id";
    
    // TODO: Bind the :id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":id", $assignmentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $success = $stmt->execute();
    
    // TODO: Check if delete was successful
    if ($success && $stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Assignment deleted successfully"]);
    } else {
    
    // TODO: If delete failed, return 500 error
    http_response_code(500);
        echo json_encode(["error" => "Failed to delete assignment"]);
    }
}


// ============================================================================
// COMMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all comments for a specific assignment
 * Method: GET
 * Endpoint: ?resource=comments&assignment_id={assignment_id}
 * 
 * Query Parameters:
 *   - assignment_id: The assignment ID (required)
 * 
 * Response: JSON array of comment objects
 */
function getCommentsByAssignment($db, $assignmentId)
{
    // TODO: Validate that $assignmentId is provided and not empty
    if (empty($assignmentId))
    {
        http_response_code(400);
        echo json_encode(["error" => "Assignment ID is required"]);
        return;
    }
    
    // TODO: Prepare SQL query to select all comments for the assignment
    $sql = "SELECT * FROM comments WHERE assignment_id = :assignment_id ORDER BY created_at ASC";
    
    // TODO: Bind the :assignment_id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":assignment_id", $assignmentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();
    
    // TODO: Fetch all results as associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // TODO: Return success response with comments data
    echo json_encode($comments);
}


/**
 * Function: Create a new comment
 * Method: POST
 * Endpoint: ?resource=comments
 * 
 * Required JSON Body:
 *   - assignment_id: Assignment ID (required)
 *   - author: Comment author name (required)
 *   - text: Comment content (required)
 * 
 * Response: JSON object with created comment data
 */
function createComment($db, $data)
{
    // TODO: Validate required fields
    if (empty($data["assignment_id"]) || empty($data["author"]) || empty($data["text"]))
    {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }
    
    // TODO: Sanitize input data
    $assignmentId = htmlspecialchars(trim($data["assignment_id"]));
    $author = htmlspecialchars(trim($data["author"]));
    $text = htmlspecialchars(trim($data["text"]));
    
    // TODO: Validate that text is not empty after trimming
    if ($text === "")
    {
        http_response_code(400);
        echo json_encode(["error" => "Comment text cannot be empty"]);
        return;
    }
    
    // TODO: Verify that the assignment exists
    $checkStmt = $db->prepare("SELECT id FROM assignments WHERE id = :id");
    $checkStmt->bindValue(":id", $assignmentId, PDO::PARAM_INT);
    $checkStmt->execute();
    if (!$checkStmt->fetch())
    {
        http_response_code(404);
        echo json_encode(["error" => "Assignment not found"]);
        return;
    }
    
    // TODO: Prepare INSERT query for comment
    $sql = "INSERT INTO comments (assignment_id, author, text, created_at) 
            VALUES (:assignment_id, :author, :text, NOW())";
    
    // TODO: Bind all parameters
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":assignment_id", $assignmentId, PDO::PARAM_INT);
    $stmt->bindValue(":author", $author, PDO::PARAM_STR);
    $stmt->bindValue(":text", $text, PDO::PARAM_STR);
    
    // TODO: Execute the statement
     $success = $stmt->execute();
    
    // TODO: Get the ID of the inserted comment
    $commentId = $db->lastInsertId();
    
    // TODO: Return success response with created comment data
    if ($success)
    {
        $createdComment = [
            "id" => $commentId,
            "assignment_id" => $assignmentId,
            "author" => $author,
            "text" => $text,
            "created_at" => date("Y-m-d H:i:s")
        ];
        echo json_encode($createdComment);
    } else
    {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create comment"]);
    }
}


/**
 * Function: Delete a comment
 * Method: DELETE
 * Endpoint: ?resource=comments&id={comment_id}
 * 
 * Query Parameters:
 *   - id: Comment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteComment($db, $commentId)
{
    // TODO: Validate that $commentId is provided and not empty
    if (empty($commentId))
    {
        http_response_code(400);
        echo json_encode(["error" => "Comment ID is required"]);
        return;
    }
    
    // TODO: Check if comment exists
    $checkStmt = $db->prepare("SELECT * FROM comments WHERE id = :id");
    $checkStmt->bindValue(":id", $commentId, PDO::PARAM_INT);
    $checkStmt->execute();
    $comment = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$comment)
    {
        http_response_code(404);
        echo json_encode(["error" => "Comment not found"]);
        return;
    }
    
    // TODO: Prepare DELETE query
     $sql = "DELETE FROM comments WHERE id = :id";
    
    // TODO: Bind the :id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":id", $commentId, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $success = $stmt->execute();
    
    // TODO: Check if delete was successful
    if ($success && $stmt->rowCount() > 0)
    {
        echo json_encode(["success" => true, "message" => "Comment deleted successfully"]);
    } else {
    
    // TODO: If delete failed, return 500 error
    http_response_code(500);
        echo json_encode(["error" => "Failed to delete comment"]);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Get the 'resource' query parameter to determine which resource to access
     $resource = isset($_GET["resource"]) ? $_GET["resource"] : null;
    
    // TODO: Route based on HTTP method and resource type
    if ($method === 'GET')
    {
        // TODO: Handle GET requests
        if ($resource === 'assignments')
        {
            // TODO: Check if 'id' query parameter exists
            if (isset($_GET["id"])) {
                getAssignmentById($db, $_GET["id"]);
            } else {
                getAllAssignments($db);
            }
        } elseif ($resource === 'comments')
        {
            // TODO: Check if 'assignment_id' query parameter exists
            if (isset($_GET["assignment_id"]))
            {
                getCommentsByAssignment($db, $_GET["assignment_id"]);
            }
        } else
        {
            // TODO: Invalid resource, return 400 error
                        http_response_code(400);
            echo json_encode(["error" => "Invalid resource"]);
        }
    } elseif ($method === 'POST')
    {
        // TODO: Handle POST requests (create operations)
        
        if ($resource === 'assignments') {
            // TODO: Call createAssignment($db, $data)
            createAssignment($db, $body);
        } elseif ($resource === 'comments')
        {
            // TODO: Call createComment($db, $data)
            createComment($db, $body);
        } else
        {
            // TODO: Invalid resource, return 400 error
            http_response_code(400);
            echo json_encode(["error" => "Invalid resource"]);
        }
    } elseif ($method === 'PUT') {
        // TODO: Handle PUT requests (update operations)
        
        if ($resource === 'assignments')
        {
            // TODO: Call updateAssignment($db, $data)
            updateAssignment($db, $body);
        } else
        {
            // TODO: PUT not supported for other resources
            http_response_code(400);
            echo json_encode(["error" => "PUT not supported for this resource"]);
        }
        
    } elseif ($method === 'DELETE')
    {
        // TODO: Handle DELETE requests
        
        if ($resource === 'assignments')
        {
            // TODO: Get 'id' from query parameter or request body
            $id = isset($_GET["id"]) ? $_GET["id"] : (isset($body["id"]) ? $body["id"] : null);
            deleteAssignment($db, $id);
        } elseif ($resource === 'comments')
        {
            // TODO: Get comment 'id' from query parameter
            if (isset($_GET["id"])) {
                deleteComment($db, $_GET["id"]);
            } else 
            {
                http_response_code(400);
                echo json_encode(["error" => "Comment ID is required"]);
            }
        } else
        {
            // TODO: Invalid resource, return 400 error
            http_response_code(400);
            echo json_encode(["error" => "Invalid resource"]);
        }
    } else
    {
        // TODO: Method not supported
        http_response_code(405);
        echo json_encode(["error" => "Method not supported"]);
    }
    
} catch (PDOException $e)
{
    // TODO: Handle database errors
     http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);

} catch (Exception $e)
{
    // TODO: Handle general errors
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param array $data - Data to send as JSON
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200)
{
    // TODO: Set HTTP response code
    http_response_code($statusCode);
    
    // TODO: Ensure data is an array
    if (!is_array($data))
    {
        $data = ["message" => $data];
    }
    
    // TODO: Echo JSON encoded data
    echo json_encode($data);
    
    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data)
{
    // TODO: Trim whitespace from beginning and end
    $data = trim($data);
    
    // TODO: Remove HTML and PHP tags
    $data = strip_tags($data);
    
    // TODO: Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // TODO: Return the sanitized data
    return $data;
}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date)
{
    // TODO: Use DateTime::createFromFormat to validate
    $d = DateTime::createFromFormat('Y-m-d', $date);
    
    // TODO: Return true if valid, false otherwise
    return $d && $d->format('Y-m-d') === $date;
}


/**
 * Helper function to validate allowed values (for sort fields, order, etc.)
 * 
 * @param string $value - Value to validate
 * @param array $allowedValues - Array of allowed values
 * @return bool - True if valid, false otherwise
 */
function validateAllowedValue($value, $allowedValues) 
{
    // TODO: Check if $value exists in $allowedValues array
    $isValid = in_array($value, $allowedValues, true);
    
    // TODO: Return the result
    return $isValid;
}
?>