<?php
/**
 * delete_course.php - POST: delete a category (tasks' course_id becomes NULL)
 * Body: { id }
 */
require_once __DIR__ . '/db_connect.php';
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','DELETE'])) jsonError('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$id = (int)($body['id'] ?? 0);
if (!$id) jsonError('Category ID is required');

$pdo  = getDB();
$stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND user_id = 1");
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) jsonError('Category not found or already deleted', 404);
jsonResponse(['success' => true, 'message' => 'Category deleted. Affected tasks moved to uncategorized.', 'id' => $id]);
