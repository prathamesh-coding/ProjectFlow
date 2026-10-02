<?php
require_once __DIR__ . '/db_connect.php';

if (isset($_SESSION['user_id'])) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, full_name, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        jsonResponse([
            'authenticated' => true,
            'user' => $user
        ]);
    }
}

jsonResponse(['authenticated' => false]);
