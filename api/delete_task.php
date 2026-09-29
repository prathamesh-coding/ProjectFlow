<?php
/**
 * delete_task.php
 * POST /api/delete_task.php
 * Body (JSON): { id }
 */
require_once __DIR__ . '/db_connect.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) jsonError('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$id = (int)($body['id'] ?? 0);
if (!$id) jsonError('Task ID is required');

$pdo  = getDB();
$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = 1");
$stmt->execute([$id]);

if ($stmt->rowCount() === 0) jsonError('Task not found or already deleted', 404);

jsonResponse(['success' => true, 'message' => 'Task deleted.', 'id' => $id]);
