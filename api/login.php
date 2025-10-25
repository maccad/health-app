<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['username']) || empty($input['password'])) {
    echo json_encode(['error' => 'Missing credentials']); exit;
}
$username = trim($input['username']);
$password = $input['password'];
$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();
if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    echo json_encode(['success' => true]);
} else echo json_encode(['error' => 'Invalid username or password']);
