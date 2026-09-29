<?php
/**
 * get_tasks.php
 * GET /api/get_tasks.php
 * Query params: ?course_id=1 | ?status=todo | ?today_only=1 | ?search=keyword
 */
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

$pdo = getDB();

$sql    = "SELECT t.*, c.name AS course_name, c.color_code AS course_color, c.type AS course_type
           FROM tasks t
           LEFT JOIN courses c ON t.course_id = c.id
           WHERE t.user_id = :uid";
$params = [':uid' => 1]; // demo user

// Optional filters
if (!empty($_GET['course_id'])) {
    $sql .= " AND t.course_id = :course_id";
    $params[':course_id'] = (int)$_GET['course_id'];
}
if (!empty($_GET['status'])) {
    $sql .= " AND t.status = :status";
    $params[':status'] = $_GET['status'];
}
if (!empty($_GET['today_only']) && $_GET['today_only'] == '1') {
    $sql .= " AND t.due_date IS NOT NULL AND t.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)";
}
if (!empty($_GET['search'])) {
    $sql .= " AND t.title LIKE :search";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$sql .= " ORDER BY
    CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
    t.due_date ASC,
    t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Compute countdown for each task
foreach ($tasks as &$task) {
    if ($task['due_date']) {
        $diff = (strtotime($task['due_date']) - time());
        $task['hours_until_due'] = round($diff / 3600, 1);
    } else {
        $task['hours_until_due'] = null;
    }
}
unset($task);

jsonResponse(['success' => true, 'tasks' => $tasks, 'count' => count($tasks)]);
