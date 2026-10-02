<?php
/**
 * update_task.php
 * POST /api/update_task.php
 * Body (JSON): { id, title?, course_id?, status?, priority?, due_date?, notes_body? }
 * Updates only the fields provided in the payload.
 * PostgreSQL-compatible: double-quoted identifiers instead of MySQL backticks.
 */
require_once __DIR__ . '/db_connect.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) jsonError('Method not allowed', 405);
if (!isset($_SESSION['user_id'])) jsonError('Unauthorized', 401);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$id = (int)($body['id'] ?? 0);
if (!$id) jsonError('Task ID is required');

$pdo = getDB();

// Dynamically build SET clause for only provided fields
$allowed = ['title', 'course_id', 'status', 'priority', 'due_date', 'notes_body'];
$enums   = [
    'status'   => ['todo', 'in_progress', 'blocked', 'done'],
    'priority' => ['low', 'medium', 'high'],
];
$sets   = [];
$params = [':id' => $id];

foreach ($allowed as $field) {
    if (!array_key_exists($field, $body)) continue;

    $value = $body[$field];

    // Validate ENUMs
    if (isset($enums[$field]) && !in_array($value, $enums[$field])) {
        jsonError("Invalid value for $field");
    }

    // PostgreSQL uses double-quoted identifiers (not MySQL backticks)
    $sets[]            = "\"$field\" = :$field";
    $params[":$field"] = ($value === '' || $value === 'null') ? null : $value;
}

if (empty($sets)) jsonError('No fields to update');

$params[':uid'] = $_SESSION['user_id'];
// updated_at is kept current via trigger; we also set it explicitly as fallback
$sql  = "UPDATE tasks SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id AND user_id = :uid";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Return updated task with course join
$fetch = $pdo->prepare(
    "SELECT t.*, c.name AS course_name, c.color_code AS course_color
     FROM tasks t
     LEFT JOIN courses c ON t.course_id = c.id
     WHERE t.id = ?"
);
$fetch->execute([$id]);
$task = $fetch->fetch();

if (!$task) jsonError('Task not found', 404);

jsonResponse(['success' => true, 'task' => $task]);
