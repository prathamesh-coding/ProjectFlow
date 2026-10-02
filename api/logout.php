<?php
require_once __DIR__ . '/db_connect.php';
session_destroy();
jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
