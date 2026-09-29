<?php
/**
 * get_courses.php - GET all categories for the current user (with task counts & type)
 */
require_once __DIR__ . '/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

$pdo  = getDB();
$stmt = $pdo->prepare(
    "SELECT c.*,
            COUNT(t.id)               AS total_tasks,
            SUM(t.status = 'done')    AS done_tasks,
            SUM(t.status = 'in_progress') AS inprogress_tasks
     FROM courses c
     LEFT JOIN tasks t ON t.course_id = c.id AND t.user_id = 1
     WHERE c.user_id = 1
     GROUP BY c.id
     ORDER BY c.type ASC, c.name ASC"
);
$stmt->execute();
$courses = $stmt->fetchAll();
jsonResponse(['success' => true, 'courses' => $courses]);
