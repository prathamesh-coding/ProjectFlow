<?php
/**
 * update_course.php - POST: update a category's name, color, or type
 * Body: { id, name?, color_code?, type? }
 * PostgreSQL-compatible: double-quoted identifiers instead of MySQL backticks.
 */
require_once __DIR__ . '/db_connect.php';
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT'])) jsonError('Method not allowed', 405);
if (!isset($_SESSION['user_id'])) jsonError('Unauthorized', 401);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$id = (int)($body['id'] ?? 0);
if (!$id) jsonError('Category ID is required');

$pdo = getDB();
$allowed    = ['name', 'color_code', 'type'];
$validTypes = ['personal', 'work', 'freelance', 'study', 'other'];
$sets   = [];
$params = [':id' => $id];

foreach ($allowed as $field) {
    if (!array_key_exists($field, $body)) continue;
    $value = trim($body[$field]);
    if ($field === 'type' && !in_array($value, $validTypes)) continue;
    if ($field === 'name' && empty($value)) jsonError('Name cannot be empty');
    // PostgreSQL uses double-quoted identifiers (not MySQL backticks)
    $sets[]            = "\"$field\" = :$field";
    $params[":$field"] = $value;
}
if (empty($sets)) jsonError('No fields to update');

$params[':uid'] = $_SESSION['user_id'];
$stmt = $pdo->prepare("UPDATE courses SET " . implode(', ', $sets) . " WHERE id = :id AND user_id = :uid");
$stmt->execute($params);

$fetch = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$fetch->execute([$id]);
$course = $fetch->fetch();
if (!$course) jsonError('Category not found', 404);

jsonResponse(['success' => true, 'course' => $course]);
