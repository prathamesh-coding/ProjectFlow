<?php
require_once __DIR__ . '/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonError('Invalid JSON body');

$full_name = trim($body['full_name'] ?? '');
$email = trim($body['email'] ?? '');
$password = trim($body['password'] ?? '');

if (empty($full_name) || empty($email) || empty($password)) {
    jsonError('All fields are required');
}

$pdo = getDB();
// check if email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonError('Email already registered');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
if ($stmt->execute([$full_name, $email, $hash])) {
    jsonResponse(['success' => true, 'message' => 'Registration successful']);
} else {
    jsonError('Failed to register user');
}
