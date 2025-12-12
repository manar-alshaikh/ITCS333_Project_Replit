<?php
/*For testing:
ini_set('display_errors', 1);
error_reporting(E_ALL);*/
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
//JSON.stringify() -> ?
// ============================================================================
// HEADERS AND CORS CONFIGURATION
// ============================================================================

//session_start();
// TODO: Set Content-Type header to application/json
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

// TODO: Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");

// TODO: Handle preflight OPTIONS request
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
    {
    // Return 200 OK status to the browser's security check:http_response_code(200);
    // Stop execution here for preflight: exit();
        http_response_code(200);
        exit();
    }


// ============================================================================
// DATABASE CONNECTION
// ============================================================================

// Include the database connection
require_once __DIR__ . '/../../../config/Config.php';

// Use the connection from Config.php
$db = $conn;

// ============================================================================
// REQUEST PARSING
// ============================================================================

// TODO: Get the HTTP request method 
    $method = $_SERVER['REQUEST_METHOD'];
/*Get the Resource:
    $resource = $_GET['resource'] ?? '';*/
//Get the id:
    $id = $_GET['id'] ?? null;
// TODO: Get the request body for POST and PUT requests
    $data = json_decode(file_get_contents("php://input"), true);  
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
     //Read optional query parameters(search, order, and sort):
       $search = trim($_REQUEST['search']?? '');
     //order default ACS, and DES  another option:
     //strtoupper protects the SQL string (Case-sensitivity):
       $order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC') ? 'DESC' : 'ASC';
     //sort default by title or others if exists:
     //Allowed sort fields (prevent SQL injection) using a white list:
        //$allowedSortedFields = ['title','due_date', 'created_at'];
       $allowedValues = ['title','due_date', 'created_at'];
       //$sort = in_array($_GET['sort'] ?? '', $allowedValues) ? $_GET['sort'] : 'title';
        $value = in_array($_GET['sort'] ?? '', $allowedValues) ? $_GET['sort'] : 'title';
     //flag to track if search is used:
       $flag = false;
    // TODO: Check if 'search' query parameter exists in $_GET
     if(!empty($search)&&validateAllowedValue($value,$allowedValues)&&$order)
        {
            $sql = "SELECT * FROM assignments WHERE title LIKE :search OR description LIKE :search ORDER BY $value $order";
            $flag = true;
        }
    else if(!empty($search))
        {
            $sql = "SELECT * FROM assignments WHERE title LIKE :search OR description LIKE :search";
            $flag = true;
        }

    // TODO: Check if 'sort' and 'order' query parameters exist
    else if(validateAllowedValue($value,$allowedValues) && $order)
        {
            $sql = "SELECT * FROM assignments ORDER BY $value $order";
        }
    else if(validateAllowedValue($value,$allowedValues))
        {
            $sql = "SELECT * FROM assignments ORDER BY $value $order";
        }
    else if($order)
        {
            $sql = "SELECT * FROM assignments ORDER BY $value $order";
        }
    else
        {
            $sql  ="SELECT * FROM assignments";
        }

    // TODO: Prepare the SQL statement using $db->prepare()
      $stmt = $db->prepare($sql);

    // TODO: Bind parameters if search is used
      if($flag===true)
        {
           $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

    // TODO: Execute the prepared statement
      $stmt->execute();

    // TODO: Fetch all results as associative array
      $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: For each assignment, decode the 'files' field from JSON to array
     foreach($assignments as &$assignment)
        {
           //Decode(convert string to json object):
           if (isset($assignment['files']))
            {
               $assignment['files'] = json_decode($assignment['files'], true);
            } 
        }
    // TODO: Return JSON response
        return $assignments;
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
     if(empty($assignmentId))
        {
            http_response_code(400);
            return ["error" => "Assignment ID is required!"];
        };

    // TODO: Prepare SQL query to select assignment by id
    $sql = "SELECT * FROM assignments WHERE id = :id";
    $stmt = $db->prepare($sql);

    // TODO: Bind the :id parameter
    $stmt->bindValue(':id', $assignmentId, PDO::PARAM_INT);

    // TODO: Execute the statement
    $stmt->execute();

    // TODO: Fetch the result as associative array
    $assignmentWithThatId = $stmt->fetch(PDO::FETCH_ASSOC);

    // TODO: Check if assignment was found
    if (!$assignmentWithThatId)
      {
        http_response_code(404);
        return (["error" => "No assignment found with that ID."]);
      }

    // TODO: Decode the 'files' field from JSON to array
      //Decode(convert string to json object):
           if (isset($assignmentWithThatId['files']))
            {
               $assignmentWithThatId['files'] = json_decode($assignmentWithThatId['files'], true);
            } 

    // TODO: Return success response with assignment data
     return $assignmentWithThatId;
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
      if(empty($data)||empty($data['title']) || empty($data['description']) || empty($data['due_date']))
        {
            http_response_code(400);
            return(["error" => " Assignment title, description, and due date are required!"]);
        };

    // TODO: Sanitize input data
     //Prevent XSS and remove extra space:$title = htmlspecialchars(trim($data['title']));
     //Remove extra space:$due_date = trim($data['due_date']);
     //Prevent XSS and remove extra space:$description = htmlspecialchars(trim($data['description']));
     //Split by newline, prevent XSS, clean spaces, and remove empty lines:
      $title = sanitizeInput($data['title']);
      $due_date = sanitizeInput($data['due_date']);
      $description = sanitizeInput($data['description']);
      $files = array_filter(array_map('trim', array_map('strip_tags', explode("\n", $data['files'] ?? ''))));

    // TODO: Validate due_date format
      //using regex: $regex = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
      //matching:if(!preg_match($regex, $due_date)){return (["error" => "Invalid date format!"]);}
      //using helper function validateDate:
      if(!validateDate($due_date)){return (["error" => "Invalid date format!"]); };

    // TODO: Generate a unique assignment ID
     //combine time built-in function to get the time and mt_rand to generate random numbers betwen 100 and 999
       //$id = time() . mt_rand(100, 999);

    // TODO: Handle the 'files' field
      //Decode(convert string to json object):
      $newFiles = json_encode(array_values($files));

    // TODO: Prepare INSERT query
      //$sql = "INSERT INTO assignments (id, title, description, due_date, files) VALUES (:id, :title, :description, :due_date, :files)";
       $sql = "INSERT INTO assignments (title, description, due_date, files) VALUES (:title, :description, :due_date, :files)";
       $stmt = $db->prepare($sql);

    // TODO: Bind all parameters
      //$stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->bindValue(':title', $title, PDO::PARAM_STR);
      $stmt->bindValue(':description', $description, PDO::PARAM_STR);
      $stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
      $stmt->bindValue(':files', $newFiles, PDO::PARAM_STR);

    // TODO: Execute the statement
    // TODO: Check if insert was successful
     if($stmt->execute())
        {
            $newId = $db->lastInsertId();
            $newAssignment = getAssignmentById($db, $newId);
            http_response_code(201);
            return([
                "success" => true,
                "message" => "Assignment created successfully!",
                //"id" => $id, 
                   "id" => $newId,
                "assignment" => $newAssignment
            ]);
        }

    // TODO: If insert failed, return 500 error
     else
        {
            http_response_code(500);
            return([
                "success" => false,
                "message" => "Assignment not created successfully!",
                "id" => null
            ]);
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
     if(empty($data)||empty($data['id']))
         {
             http_response_code(400);
            return (["error" => " Assignment ID is required!"]);
         };

    // TODO: Store assignment ID in variable
    $updatedAssignmentId = $data['id'];

    // TODO: Check if assignment exists
     $checkStmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE id = :id");
     $checkStmt->execute([':id' => $updatedAssignmentId]);
     if ($checkStmt->fetchColumn() == 0) 
         {
             http_response_code(404);
             return (["error" => "Assignment not found!"]);
         }
    $requiredFields = ['title', 'description', 'due_date'];
    $missingFields = [];
    // Check that all required fields are present and not empty after basic sanitization:
    foreach ($requiredFields as $field)
    {
        // Run sanitizeInput to ensure the field isn't just whitespace or tags
        if (empty($data[$field]) || empty(sanitizeInput($data[$field])))
        {
            $missingFields[] = $field;
        }
    }

    // Check if date format is valid:
    if (empty($missingFields) && !validateDate($data['due_date']))
    {
        http_response_code(400);
        return ["error" => "Invalid date format for due_date."];
    }

    if (!empty($missingFields))
    {
        http_response_code(400);
        return ["error" => "The following required fields are missing or empty: " . implode(', ', $missingFields) . "."];
    }
    // TODO: Build UPDATE query dynamically based on provided fields
    // TODO: Check which fields are provided and add to SET clause
     //fields array to complete UPDATE:
      $fields = [];
     //params array pass it for execution:
      $params = [];

    // Required Fields:

     // Title
     $fields[] = "title = :title";
     $params[':title'] = sanitizeInput($data['title']);

     // Description
     $fields[] = "description = :description";
     $params[':description'] = sanitizeInput($data['description']);

     // Due Date
     $fields[] = "due_date = :due_date";
     $params[':due_date'] = sanitizeInput($data['due_date']);


    //files
    if (isset($data['files']))
    {
        // Ensure we treat the input as a string (it might be null if the JSON was malformed)
        $filesString = (string)($data['files'] ?? '');

        // Process the newline-separated string into a clean array
        // This matches the original intent (to store an array/JSON in the DB)
        $filesArray = array_filter(array_map('trim', array_map('strip_tags', explode("\n", $filesString))));

        $fields[] = "files = :files";
        // Encode the resulting array (which is [] if the textarea was empty)
        $params[':files'] = json_encode(array_values($filesArray));
    }

    // TODO: If no fields to update (besides updated_at), return 400 error
    if (empty($fields))
         {
              http_response_code(400);
              return ["error" => "No valid fields provided for update."];
         }
    // TODO: Complete the UPDATE query
      $params[':id'] = (int)$updatedAssignmentId;
      $sql = "UPDATE assignments SET " . implode(', ', $fields) . " WHERE id = :id";
    // TODO: Prepare the statement
      try {
         $stmt = $db->prepare($sql);

          // TODO: Bind all parameters dynamically
         $stmt->execute($params);

          // TODO: Execute the statement
         if ($stmt->rowCount() > 0)
           {
              // TODO: Check if update was successful
               http_response_code(200);
               return ["success" => true, "message" => "Assignment updated successfully."];
           } 
         else 
           {
              // TODO: If no rows affected, return appropriate message
              return ["message" => "No changes were made or assignment not found."];
             }

      } catch (PDOException $error)
     {
         http_response_code(500);
         return ["error" => "Faild: " . $error->getMessage()];
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
     if(empty($assignmentId))
        {
            http_response_code(400);
            return(["error" => "Assignment ID is required!"]);
        };

    // TODO: Check if assignment exists
      $checkStmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE id = :id");
      $checkStmt->execute([':id' => $assignmentId]);
     if ($checkStmt->fetchColumn() == 0) 
        {
            http_response_code(404);
            return(["error" => "Assignment not found!"]);
        }
    try{
    $db->beginTransaction();
    // TODO: Delete associated comments first (due to foreign key constraint)
    $sqlDeleteComments = "DELETE FROM assignment_comments WHERE assignment_id = :id";
    $stmtDeleteComments = $db->prepare($sqlDeleteComments);
    $stmtDeleteComments->bindValue(':id', $assignmentId, PDO::PARAM_INT);
    $stmtDeleteComments->execute();

    // TODO: Prepare DELETE query for assignment
      $sql = "DELETE FROM assignments WHERE id = :id";
      $stmt = $db->prepare($sql);

    // TODO: Bind the :id parameter
      $stmt->bindValue(':id', $assignmentId, PDO::PARAM_INT);
    // TODO: Execute the statement
      $stmt->execute();
    // TODO: Check if delete was successful
      /*if($stmt->execute())
        {
           return ["success" => true, "message" => "Assignment and associated comments deleted successfully."];
        }*/
        $db->commit();
    if($stmt->rowCount() > 0)
    {
        http_response_code(200);
        return ["success" => true, "message" => "Assignment and associated comments deleted successfully."];
    }
    else
    {
            
         return ["message" => "No changes were made or assignment not found."];
    }

    // TODO: If delete failed, return 500 error
      /*else
        {
            http_response_code(500);
            return ["error" => "Failed!"];
        }*/
    } catch (PDOException $e) {
        // If anything fails (including foreign key errors if not caught by explicit delete)
        $db->rollBack(); 
        http_response_code(500);
        return ["error" => "Failed to delete assignment due to database error: " . $e->getMessage()];
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
    try
    {
    // TODO: Validate that $assignmentId is provided and not empty
      if(empty($assignmentId))
        {
            http_response_code(400);
            return (["error" => "Assignment ID is required!"]);
        };

    // TODO: Prepare SQL query to select all comments for the assignment
      $sql = "SELECT * FROM assignment_comments WHERE assignment_id = :id";
      $stmt = $db->prepare($sql);

    // TODO: Bind the :assignment_id parameter
      $stmt->bindValue(':id', $assignmentId, PDO::PARAM_INT);

    // TODO: Execute the statement
      $stmt->execute();

    // TODO: Fetch the result as associative array
    // TODO: Fetch all results as associative array
      $allCommentsOfThatAssignment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Return success response with comments data
          http_response_code(200);
          return [
          "success" => true,
          "comments" =>$allCommentsOfThatAssignment];
    }
    catch(PDOException $error)
    {
        http_response_code(500);
        return ["error" => "Failed: " . $error->getMessage()];
    }
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
      if(empty($data)||empty($data['assignment_id']) || empty($data['author']) || empty($data['text']))
        {
            http_response_code(400);
            return (["error" => " Assignmet ID, author, and comment content are required!"]);
        };

    // TODO: Sanitize input data
      //Prevent XSS and remove extra space:$author = htmlspecialchars(trim($data['author'])); and $text = htmlspecialchars(trim($data['text']));
       $author = sanitizeInput($data['author']);
       $text = sanitizeInput($data['text']);
    // TODO: Validate that text is not empty after trimming
      if($text==="")
        {
            http_response_code(400);
            return(["error" => " Empty text!"]);
        }

    // TODO: Verify that the assignment exists
      $checkStmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE id = :id");
      $assignmentId = $data['assignment_id'];
      $checkStmt->execute([':id' => $assignmentId]);
     if ($checkStmt->fetchColumn() == 0) 
        {
            http_response_code(404);
            return(["error" => "Assignment not found!"]);
        }

    // TODO: Prepare INSERT query for comment
      $sql = "INSERT INTO assignment_comments (assignment_id, author, text) VALUES (:assignment_id, :author, :text)";
      $stmt = $db->prepare($sql);

    // TODO: Bind all parameters
      $stmt->bindValue(':assignment_id', $assignmentId, PDO::PARAM_INT);
      $stmt->bindValue(':author', $author, PDO::PARAM_STR);
      $stmt->bindValue(':text', $text, PDO::PARAM_STR);

    // TODO: Execute the statement
     $stmt->execute();

    // TODO: Get the ID of the inserted comment
     $lastId = $db->lastInsertId();

    // TODO: Return success response with created comment data
       /*http_response_code(201);
          return 
          [
            "success" => true,
            "comments" =>[$lastId,
            "author" => $author,
             "text" => $text]
          ];*/
    http_response_code(201);
    return
    [
           "success" => true,
            "message" => "Comment created successfully!",
            "comment" => [  
                           "id" => $lastId,
                           "assignment_id" => $assignmentId,
                           "author" => $author,
                           "text" => $text]
    ];
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
      if(empty($commentId))
        {
            http_response_code(400);
            return(["error" => "Comment ID is required!"]);
        };

    // TODO: Check if comment exists
      $checkStmt = $db->prepare("SELECT COUNT(*) FROM assignment_comments WHERE id = :id");
      $checkStmt->execute([':id' => $commentId]);
     if ($checkStmt->fetchColumn() == 0) 
        {
            http_response_code(404);
            return(["error" => "Comment not found!"]);
        }
    try 
    {
    // TODO: Prepare DELETE query
    $sql = "DELETE FROM assignment_comments WHERE id = :id";
    $stmt = $db->prepare($sql);

    // TODO: Bind the :id parameter
      $stmt->bindValue(':id', $commentId, PDO::PARAM_INT);

    // TODO: Execute the statement
        $stmt->execute();
    // TODO: Check if delete was successful
      /*if($stmt->execute())
        {
           return ["success" => true, "message" => "Comment deleted successfully."];
        }*/
        if ($stmt->rowCount() > 0)
        {
            http_response_code(200);    
            return ["success" => true, "message" => "Comment deleted successfully."];
        } 
        else 
        {
                http_response_code(404);
                return ["error" => "Comment not found or already deleted."];
        
        }
    }
    // TODO: If delete failed, return 500 error
      /*else
        {
            http_response_code(500);
            return ["error" => "Failed!"];
        }*/
catch (PDOException $e)
    {   
        http_response_code(500);
       return ["error" => "Failed!"];
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

/*if ($method === 'POST' || $method === 'PUT')
{
    // Read raw JSON body for POST/PUT requests
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Check for JSON decoding errors
    if ($data === null && $input !== '')
    {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON payload received."]);
        exit;
    }
}*/
/*Basic auth:
 <?php
session_start();
require_once __DIR__ . '/auth.php'; // provided by Task 1

require_login(); // blocks if not logged in

// For admin-only endpoints:
require_role('teacher'); // blocks if current user is not teacher
 */
try {
    // TODO: Get the 'resource' query parameter to determine which resource to access
    $resource = $_GET['resource'] ?? '';
    $res = null;

    // TODO: Route based on HTTP method and resource type
    if ($method === 'GET')
    {
        // TODO: Handle GET requests
        if ($resource === 'assignments')
        {
            // TODO: Check if 'id' query parameter exists
            if (isset($_GET['id']))
            {
                $assignmentId = $_GET['id'];
                $res = getAssignmentById($db, $assignmentId);
            }
            else
            {
                $res = getAllAssignments($db);
            }
        }
        elseif ($resource === 'comments') 
        {
           if (isset($_GET['id'])) 
            {
               $assignmentId = $_GET['id'];
               $res = getCommentsByAssignment($db, $assignmentId);
            } 
            else 
            {
               http_response_code(400);
               $res = ["error" => "Assignment ID is required!"];
            }
        } 
        else
        {
            // TODO: Invalid resource, return 400 error
             http_response_code(400);
             $res = ["error" => "Invalid resource: " . $resource];
        }
    } 
    elseif ($method === 'POST')
    {
        // TODO: Handle POST requests (create operations)
        if ($resource === 'assignments')
        {
            // TODO: Call createAssignment($db, $data)
             $res = createAssignment($db, $data);
        } 
        elseif ($resource === 'comments')
        {
        // TODO: Call createComment($db, $data)
           $res = createComment($db, $data);
        }
       else
        {
        // TODO: Invalid resource, return 400 error
        http_response_code(400);
        $res = ["error" => "Invalid resource: " . $resource];
        }   
    }
    elseif ($method === 'PUT') 
    {
        // TODO: Handle PUT requests (update operations)
        if ($resource === 'assignments')
        {
            // TODO: Call updateAssignment($db, $data)
             $res = updateAssignment($db, $data);
        }
        else
        {
            // TODO: PUT not supported for other resources
             http_response_code(405);
             $res = ["error" => "Method not supported for resource: " . $resource];
        }
    }
    elseif ($method === 'DELETE')
    {
        // TODO: Handle DELETE requests
        if ($resource === 'assignments')
        {
            // TODO: Get 'id' from query parameter or request body
            $deleteIdAssignment = $_GET['id'] ?? null;
            if (!$deleteIdAssignment)
            {
                http_response_code(400);
                $res = ["error" => "ID is required."];
            }
            else
            {
                $res = deleteAssignment($db, $deleteIdAssignment);
            }
        }
        elseif ($resource === 'comments')
        {
            // TODO: Get comment 'id' from query parameter
            $deleteIdComment = $_GET['id'] ?? null;
            if (!$deleteIdComment)
            {
                http_response_code(400);
                $res = ["error" => "ID is required."];
            }
            else
            {
                $res = deleteComment($db, $deleteIdComment);
            }
        } 
        else
        {
            // TODO: Invalid resource, return 400 error
            http_response_code(400);
            $res = ["error" => "Invalid resource: " . $resource];
        }
    }
    else
    {
        // TODO: Method not supported
        http_response_code(405);
        $res = ["error" => "Unsupported method: " . $method];
    }
} catch (PDOException $e)
{
    // TODO: Handle database errors
      http_response_code(500);
      $res = ["error" => "Database failure: " . $e->getMessage()];
} catch (Exception $e)
{
    // TODO: Handle general errors
    http_response_code(500);
    $res = ["error" => "An unexpected error occurred: " . $e->getMessage()];
}
//ob_clean();
echo json_encode($res);

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
     if($data!==null && $statusCode!==204)
        {
            // TODO: Echo JSON encoded data
             echo json_encode($data);
        }
    // TODO: Exit to prevent further execution
     exit();
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
     $newData = trim($data);

    // TODO: Remove HTML and PHP tags
     $newData = strip_tags($newData);

    // TODO: Convert special characters to HTML entities
     $newData = htmlentities($newData);

    // TODO: Return the sanitized data
     return $newData;
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
      $dateTime = DateTime::createFromFormat('Y-m-d', $date);

    // TODO: Return true if valid, false otherwise
       return $dateTime !== false && $dateTime->format('Y-m-d') === $date;
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