<?php
/**
 * create_task.php
 * POST /api/create_task.php
 * Body (JSON): { title, course_id?, status?, priority?, due_date?, notes_body? }
 */
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$title     = trim($body['title'] ?? '');
$course_id = !empty($body['course_id']) ? (int)$body['course_id'] : null;
$status    = in_array($body['status'] ?? '', ['todo','in_progress','blocked','done']) ? $body['status'] : 'todo';
$priority  = in_array($body['priority'] ?? '', ['low','medium','high']) ? $body['priority'] : 'medium';
$due_date  = !empty($body['due_date']) ? $body['due_date'] : null;
$notes     = $body['notes_body'] ?? null;

if (empty($title)) jsonError('Title is required');

$pdo  = getDB();
$stmt = $pdo->prepare("INSERT INTO tasks (user_id, course_id, title, status, priority, due_date, notes_body)
                        VALUES (:uid, :cid, :title, :status, :priority, :due_date, :notes)");
$stmt->execute([
    ':uid'      => 1,
    ':cid'      => $course_id,
    ':title'    => $title,
    ':status'   => $status,
    ':priority' => $priority,
    ':due_date' => $due_date,
    ':notes'    => $notes,
]);

$newId = $pdo->lastInsertId();

// Fetch newly created task with course join
$fetch = $pdo->prepare("SELECT t.*, c.name AS course_name, c.color_code AS course_color
                         FROM tasks t
                         LEFT JOIN courses c ON t.course_id = c.id
                         WHERE t.id = ?");
$fetch->execute([$newId]);
$task = $fetch->fetch();

jsonResponse(['success' => true, 'task' => $task], 201);
