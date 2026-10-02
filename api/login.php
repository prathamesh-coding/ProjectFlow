<?php
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$email = trim($body['email'] ?? '');
$password = trim($body['password'] ?? '');

if (empty($email) || empty($password)) {
    jsonError('Email and password are required');
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT id, full_name, role, password_hash FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    jsonResponse([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ]);
} else {
    jsonError('Invalid email or password', 401);
}
