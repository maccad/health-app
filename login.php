<?php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $u = $_POST['username'] ?? '';
  $p = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
  $stmt->execute([$u]); $user = $stmt->fetch();
  if ($user && password_verify($p, $user['password_hash'])) {
    $_SESSION['user_id']=$user['id']; $_SESSION['username']=$user['username']; $_SESSION['role']=$user['role'];
    header("Location: dashboard.php"); exit;
  } else $error = "Invalid username or password";
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login • Health Logger</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f8fafc;height:100vh;display:flex;align-items:center;justify-content:center}.card{max-width:420px;width:100%}</style>
</head><body>
<div class="card shadow border-0 p-4">
  <h3 class="text-center fw-bold mb-3">Sign In</h3>
  <?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
    <button class="btn btn-primary w-100">Login</button>
  </form>
  <hr><p class="text-center small mb-0">No account? <a href="register.php">Register</a></p>
</div>
</body></html>
