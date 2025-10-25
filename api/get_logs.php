<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode([]); exit; }
$uid = (int)$_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM health_logs WHERE user_id=? ORDER BY timestamp DESC");
$stmt->execute([$uid]);
echo json_encode($stmt->fetchAll());
