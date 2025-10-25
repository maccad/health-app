<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['username']) || empty($data['password'])) { echo json_encode(['error'=>'Missing fields']); exit; }
$username = trim($data['username']);
$password = password_hash($data['password'], PASSWORD_BCRYPT);

$check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);
if ($check->fetch()) { echo json_encode(['error'=>'Username already exists']); exit; }

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");
$stmt->execute([$username, $password]);
echo json_encode(['success'=>true]);
