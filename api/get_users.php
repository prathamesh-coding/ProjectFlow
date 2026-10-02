<?php
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonError('Method not allowed', 405);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    jsonError('Unauthorized', 403);
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

jsonResponse(['success' => true, 'users' => $users]);
