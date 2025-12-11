<?php
/**
 * Weekly Course Breakdown API
 * Matches NEW schema exactly (no is_active, uses week_id + links JSON)
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/../../../config/Config.php";

$method = $_SERVER['REQUEST_METHOD'];
$query  = $_GET;
$input  = json_decode(file_get_contents('php://input'), true);

/* ===========================================================
   HELPER FUNCTIONS
=========================================================== */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function sendError($msg, $statusCode = 400) {
    sendResponse(["success" => false, "error" => $msg], $statusCode);
}

function sanitizeInput($d) {
    return is_array($d)
        ? array_map('sanitizeInput', $d)
        : htmlspecialchars(strip_tags(trim((string)$d)), ENT_QUOTES, "UTF-8");
}

/* ===========================================================
   GET USERS (NEEDED FOR COMMENT IMPORT)
=========================================================== */
if ($method === "GET" && ($query["resource"] ?? "") === "users") {
    try {
        $stmt = $conn->prepare("SELECT id, username FROM users");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(["success" => true, "data" => $rows]);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   GET ALL WEEKS
=========================================================== */
function getAllWeeks($conn) {
    $sql = "
        SELECT wb.id, wb.week_id, wb.title, wb.description, wb.links,
               wb.start_date, wb.created_by, u.username AS created_by_name,
               wb.created_at, wb.updated_at
        FROM weekly_breakdown wb
        LEFT JOIN users u ON wb.created_by = u.id
        ORDER BY wb.week_id ASC
    ";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($weeks as &$w) {
            $w["links"] = json_decode($w["links"], true) ?? [];
        }

        sendResponse(["success" => true, "data" => $weeks]);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   GET WEEK BY ID OR week_id
=========================================================== */
function getWeekById($conn, $id) {
    if (!$id || !is_numeric($id)) sendError("Valid ID required");

    $sql = "SELECT * FROM weekly_breakdown WHERE id = ? OR week_id = ? LIMIT 1";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) sendError("Week not found", 404);

        $row["links"] = json_decode($row["links"], true) ?? [];
        sendResponse(["success" => true, "data" => $row]);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   CREATE WEEK
=========================================================== */
function createWeek($conn, $d) {
    foreach (["week_id","title","description","start_date"] as $f) {
        if (!isset($d[$f])) sendError("Missing field: $f");
    }

    $week_id = sanitizeInput($d["week_id"]);
    $title   = sanitizeInput($d["title"]);
    $desc    = sanitizeInput($d["description"]);
    $date    = sanitizeInput($d["start_date"]);
    $created = 1; // static for now

    // Ensure week_id is unique
    $check = $conn->prepare("SELECT id FROM weekly_breakdown WHERE week_id = ?");
    $check->execute([$week_id]);
    if ($check->fetch()) sendError("Week ID already exists", 409);

    $links = isset($d["links"]) && is_array($d["links"])
        ? json_encode($d["links"])
        : "[]";

    $sql = "
        INSERT INTO weekly_breakdown (week_id, title, description, links, start_date, created_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$week_id, $title, $desc, $links, $date, $created]);

        getWeekById($conn, $conn->lastInsertId());
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   UPDATE WEEK
=========================================================== */
function updateWeek($conn, $d) {
    if (!isset($d["id"])) sendError("Week ID required");

    $id = $d["id"];

    $check = $conn->prepare("SELECT id FROM weekly_breakdown WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) sendError("Week not found", 404);

    $set = [];
    $params = [];

    foreach (["week_id","title","description","start_date"] as $f) {
        if (isset($d[$f])) {
            $set[] = "$f = ?";
            $params[] = sanitizeInput($d[$f]);
        }
    }

    if (isset($d["links"])) {
        $set[] = "links = ?";
        $params[] = json_encode($d["links"]);
    }

    if (!$set) sendError("No fields to update");

    $sql = "UPDATE weekly_breakdown SET ".implode(", ", $set).", updated_at = NOW() WHERE id = ?";
    $params[] = $id;

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        getWeekById($conn, $id);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   DELETE WEEK
=========================================================== */
function deleteWeek($conn, $id) {
    if (!$id) sendError("ID required");

    try {
        $stmt = $conn->prepare("DELETE FROM weekly_breakdown WHERE id = ?");
        $stmt->execute([$id]);

        sendResponse(["success" => true, "message" => "Week deleted"]);
    } catch (PDOException $e) {
        sendError("Database error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   GET COMMENTS FOR A WEEK
=========================================================== */
function getCommentsForWeek($conn, $week_id) {
    if (!$week_id) sendError("Missing week_id");

    try {
        $stmt = $conn->prepare("
            SELECT wc.*, u.username 
            FROM weekly_comments wc
            JOIN users u ON wc.user_id = u.id
            WHERE wc.week_id = ?
            ORDER BY wc.created_at ASC
        ");

        $stmt->execute([$week_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(["success" => true, "data" => $rows]);
    } catch (PDOException $e) {
        sendError("DB error: " . $e->getMessage(), 500);
    }
}

/* ===========================================================
   ROUTER
=========================================================== */

// ---------- GET ----------
if ($method === "GET") {

    // Get comments for a week
    if (($query["resource"] ?? "") === "weekly_comments") {
        getCommentsForWeek($conn, $query["week_id"] ?? null);
    }
    // Get a single week by id or week_id
    elseif (isset($query["id"])) {
        getWeekById($conn, $query["id"]);
    }
    elseif (isset($query["week_id"])) {
        getWeekById($conn, $query["week_id"]);
    }
    // Get all weeks
    else {
        getAllWeeks($conn);
    }
} else // ===============================
// DELETE COMMENT (AND REPLIES)
// ===============================
if ($method === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'delete_comment') {

    $commentId = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM weekly_comments WHERE id = ?");
    $success = $stmt->execute([$commentId]);

    echo json_encode([
        "success" => $success,
        "message" => $success ? "Comment deleted" : "Delete failed"
    ]);
    exit;
}


// ---------- POST: ADD COMMENT ----------
elseif ($method === "POST" && ($query["action"] ?? "") === "weekly_comments") {

    if (!is_array($input)) {
        sendError("Invalid JSON body");
    }

    foreach (["week_id", "user_id", "comment_text"] as $field) {
        if (!isset($input[$field])) {
            sendError("Missing field: $field", 422);
        }
    }

    $week_id  = (int)$input["week_id"];
    $user_id  = (int)$input["user_id"];
    $text     = sanitizeInput($input["comment_text"]);
    $parentId = isset($input["parent_comment_id"]) ? (int)$input["parent_comment_id"] : null;

    try {
        $stmt = $conn->prepare("
            INSERT INTO weekly_comments (week_id, user_id, comment_text, parent_comment_id)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$week_id, $user_id, $text, $parentId]);

        sendResponse(["success" => true, "id" => $conn->lastInsertId()]);
    } catch (PDOException $e) {
        sendError("DB error inserting comment: " . $e->getMessage(), 500);
    }
}


// ---------- POST: CREATE WEEK ----------
elseif ($method === "POST") {
    if (!is_array($input)) {
        sendError("Invalid JSON body", 400);
    }
    createWeek($conn, $input);
}

// ---------- PUT: UPDATE WEEK ----------
elseif ($method === "PUT") {
    if (!is_array($input)) {
        sendError("Invalid JSON body", 400);
    }
    updateWeek($conn, $input);
}

// ---------- DELETE: DELETE WEEK ----------
elseif ($method === "DELETE") {
    deleteWeek($conn, $query["id"] ?? 0);
}

// ---------- INVALID METHOD ----------
else {
    sendError("Method not allowed", 405);
}
