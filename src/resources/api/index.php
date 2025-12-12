<?php
/**
 * Course Resources API
 * 
 * RESTful API for managing course resources and comments.
 * Uses PDO + MySQL database.
 */

// ============================================================================
// HEADERS AND INITIALIZATION
// ============================================================================

// Set headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Include database file
require_once __DIR__ . "/../../../config/Config.php";
$db = $conn;

// Method + body + query
$method = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents("php://input"), true);
$query = $_GET;


// ============================================================================
// RESOURCE FUNCTIONS
// ============================================================================

function getAllResources($db) {
    $sql = "SELECT id, title, description, external_url, created_at 
            FROM course_resources WHERE is_active = TRUE";

    // Search
    if (isset($_GET['search'])) {
        $sql .= " AND (title LIKE :search OR description LIKE :search)";
    }

    // Sorting
    $allowedSort = ["title", "created_at"];
    $sort = $_GET["sort"] ?? "created_at";
    $order = strtoupper($_GET["order"] ?? "DESC");

    if (!in_array($sort, $allowedSort)) $sort = "created_at";
    if (!in_array($order, ["ASC", "DESC"])) $order = "DESC";

    $sql .= " ORDER BY $sort $order";

    $stmt = $db->prepare($sql);

    if (isset($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $stmt->bindParam(":search", $search);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(["success" => true, "data" => $rows]);
}

function getResourceById($db, $id) {
    if (!is_numeric($id)) sendResponse(["success" => false, "message" => "Invalid ID"], 400);

    $stmt = $db->prepare("SELECT * FROM course_resources WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) sendResponse(["success" => false, "message" => "Resource not found"], 404);

    sendResponse(["success" => true, "data" => $row]);
}

function createResource($db, $body) {
    if (empty($body['title']) || empty($body['link'])) {
        sendResponse(["success" => false, "message" => "Title and link required"], 400);
    }

    if (!filter_var($body['link'], FILTER_VALIDATE_URL)) {
        sendResponse(["success" => false, "message" => "Invalid URL"], 400);
    }

    $title = sanitizeInput($body['title']);
    $desc = sanitizeInput($body['description'] ?? "");
    $link = $body['link'];

    $stmt = $db->prepare("
        INSERT INTO course_resources (title, description, resource_type, external_url, created_by)
        VALUES (?, ?, 'web_link', ?, 1)
    ");

    $stmt->execute([$title, $desc, $link]);

    sendResponse([
        "success" => true,
        "message" => "Resource created",
        "id" => $db->lastInsertId()
    ], 201);
}

function updateResource($db, $body) {
    if (empty($body['id'])) sendResponse(["success" => false, "message" => "ID required"], 400);

    $id = $body['id'];

    // Check exists
    $check = $db->prepare("SELECT id FROM course_resources WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) sendResponse(["success" => false, "message" => "Not found"], 404);

    $fields = [];
    $values = [];

    if (isset($body['title'])) {
        $fields[] = "title = ?";
        $values[] = sanitizeInput($body['title']);
    }

    if (isset($body['description'])) {
        $fields[] = "description = ?";
        $values[] = sanitizeInput($body['description']);
    }

    if (isset($body['link'])) {
        if (!filter_var($body['link'], FILTER_VALIDATE_URL))
            sendResponse(["success" => false, "message" => "Invalid URL"], 400);

        $fields[] = "external_url = ?";
        $values[] = $body['link'];
    }

    if (empty($fields)) {
        sendResponse(["success" => false, "message" => "No fields to update"], 400);
    }

    $sql = "UPDATE course_resources SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    $stmt = $db->prepare($sql);
    $stmt->execute($values);

    sendResponse(["success" => true, "message" => "Updated"]);
}

function deleteResource($db, $id) {
    if (!is_numeric($id)) sendResponse(["success" => false, "message" => "Invalid ID"], 400);

    // Check exists
    $check = $db->prepare("SELECT id FROM course_resources WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) sendResponse(["success" => false, "message" => "Not found"], 404);

    $db->beginTransaction();

    try {
        // Delete comments
        $delC = $db->prepare("DELETE FROM resource_comments WHERE resource_id = ?");
        $delC->execute([$id]);

        // Delete resource
        $delR = $db->prepare("DELETE FROM course_resources WHERE id = ?");
        $delR->execute([$id]);

        $db->commit();
        sendResponse(["success" => true, "message" => "Resource deleted"]);
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(["success" => false, "message" => "Error deleting"], 500);
    }
}


// ============================================================================
// COMMENT FUNCTIONS
// ============================================================================

function getCommentsByResourceId($db, $resourceId) {
    if (!is_numeric($resourceId)) sendResponse(["success" => false, "message" => "Invalid ID"], 400);

    $stmt = $db->prepare("SELECT * FROM resource_comments WHERE resource_id = ?");
    $stmt->execute([$resourceId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(["success" => true, "data" => $rows]);
}

function createComment($db, $body) {
    if (empty($body['resource_id']) || empty($body['text'])) {
        sendResponse(["success" => false, "message" => "resource_id and text required"], 400);
    }

    if (!is_numeric($body['resource_id']))
        sendResponse(["success" => false, "message" => "Invalid resource_id"], 400);

    $stmt = $db->prepare("INSERT INTO resource_comments (resource_id, user_id, comment_text)
                         VALUES (?, 1, ?)");

    $stmt->execute([
        $body['resource_id'],
        sanitizeInput($body['text'])
    ]);

    sendResponse(["success" => true, "message" => "Comment added"]);
}

function deleteComment($db, $commentId) {
    if (!is_numeric($commentId)) sendResponse(["success" => false, "message" => "Invalid ID"], 400);

    $stmt = $db->prepare("DELETE FROM resource_comments WHERE id = ?");
    $stmt->execute([$commentId]);

    sendResponse(["success" => true, "message" => "Comment deleted"]);
}


// ============================================================================
// MAIN ROUTER
// ============================================================================

if ($method === "GET") {
    if (isset($query['action']) && $query['action'] === "comments") {
        getCommentsByResourceId($db, $query['resource_id'] ?? null);
    } elseif (isset($query['id'])) {
        getResourceById($db, $query['id']);
    } else {
        getAllResources($db);
    }

} elseif ($method === "POST") {
    if (isset($query['action']) && $query['action'] === "comment") {
        createComment($db, $body);
    } else {
        createResource($db, $body);
    }

} elseif ($method === "PUT") {
    updateResource($db, $body);

} elseif ($method === "DELETE") {
    if (isset($query['action']) && $query['action'] === "delete_comment") {
        deleteComment($db, $query['comment_id'] ?? null);
    } else {
        deleteResource($db, $query['id'] ?? null);
    }

} else {
    sendResponse(["success" => false, "message" => "Method not allowed"], 405);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function sanitizeInput($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

?>
