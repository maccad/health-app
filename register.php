<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';
  if ($u===''){ $error='Username required'; }
  else {
    $check = $pdo->prepare("SELECT id FROM users WHERE username=?");
    $check->execute([$u]);
    if ($check->fetch()) $error='Username already exists';
    else {
      $hash = password_hash($p, PASSWORD_BCRYPT);
      $stmt=$pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");
      $stmt->execute([$u,$hash]);
      header("Location: login.php"); exit;
    }
  }
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register • Health Logger</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f8fafc;height:100vh;display:flex;align-items:center;justify-content:center}.card{max-width:420px;width:100%}</style>
</head><body>
<div class="card shadow border-0 p-4">
  <h3 class="text-center fw-bold mb-3">Create Account</h3>
  <?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
    <button class="btn btn-success w-100">Register</button>
  </form>
  <hr><p class="text-center small mb-0">Have an account? <a href="login.php">Login</a></p>
</div>
</body></html>
