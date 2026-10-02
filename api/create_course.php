<?php
/**
 * create_course.php - POST: create a new category/project space
 * Body: { name, color_code?, type? }
 * PostgreSQL-compatible: uses RETURNING id instead of lastInsertId().
 */
require_once __DIR__ . '/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

if (!isset($_SESSION['user_id'])) jsonError('Unauthorized', 401);

$body  = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$name  = trim($body['name']       ?? '');
$color = trim($body['color_code'] ?? '#6366f1');
$type  = trim($body['type']       ?? 'other');

$validTypes = ['personal', 'work', 'freelance', 'study', 'other'];
if (!in_array($type, $validTypes)) $type = 'other';
if (empty($name)) jsonError('Category name is required');

$pdo  = getDB();

// RETURNING id is the PostgreSQL equivalent of lastInsertId()
$stmt = $pdo->prepare(
    "INSERT INTO courses (user_id, name, color_code, type) VALUES (:uid, :name, :color, :type) RETURNING id"
);
$stmt->execute([':uid' => $_SESSION['user_id'], ':name' => $name, ':color' => $color, ':type' => $type]);
$newId = $stmt->fetchColumn();

$fetch = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$fetch->execute([$newId]);
$course = $fetch->fetch();

jsonResponse(['success' => true, 'course' => $course], 201);
