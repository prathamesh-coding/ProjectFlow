<?php
/**
 * get_courses.php - GET all categories for the current user (with task counts & type)
 * PostgreSQL-compatible: uses COUNT(*) FILTER instead of MySQL's SUM(condition).
 */
require_once __DIR__ . '/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

if (!isset($_SESSION['user_id'])) jsonError('Unauthorized', 401);

$pdo  = getDB();
$stmt = $pdo->prepare(
    "SELECT c.*,
            COUNT(t.id)                                              AS total_tasks,
            COUNT(*) FILTER (WHERE t.status = 'done')              AS done_tasks,
            COUNT(*) FILTER (WHERE t.status = 'in_progress')       AS inprogress_tasks
     FROM courses c
     LEFT JOIN tasks t ON t.course_id = c.id AND t.user_id = :uid1
     WHERE c.user_id = :uid2
     GROUP BY c.id
     ORDER BY c.type ASC, c.name ASC"
);
$stmt->execute([':uid1' => $_SESSION['user_id'], ':uid2' => $_SESSION['user_id']]);
$courses = $stmt->fetchAll();
jsonResponse(['success' => true, 'courses' => $courses]);
