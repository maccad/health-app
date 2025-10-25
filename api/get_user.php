<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
echo json_encode([
  'logged_in' => isset($_SESSION['user_id']),
  'user' => isset($_SESSION['user_id']) ? [
      'id' => (int)$_SESSION['user_id'],
      'username' => $_SESSION['username'] ?? 'user',
      'role' => $_SESSION['role'] ?? 'user'
  ] : null
]);
