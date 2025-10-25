<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Data Insights • Health Logger</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
:root { --sidebar-width: 280px; }
body { background:#f8fafc }
.sidebar { width:var(--sidebar-width); min-height:100vh }
.content { margin-left:var(--sidebar-width) }
@media (max-width: 991px) { .content { margin-left:0 } }
</style></head><body>
<div class="d-flex">
  <aside class="sidebar bg-white border-end p-4 position-fixed top-0 start-0 d-none d-lg-block">
    <h3 class="fw-bold mb-3">Health <span class="text-primary">Logger</span></h3>
    <nav class="nav flex-column">
      <a class="nav-link mb-2" href="dashboard.php">📝 Log Entry</a>
      <a class="nav-link mb-2" href="logs.php">📒 Past Logs</a>
      <a class="nav-link mb-2" href="insights.php">📊 Data Insights</a>
      <hr>
      <a class="nav-link text-danger" href="logout.php">Logout</a>
    </nav>
  </aside>
  <main class="content container-fluid p-4">
    
<div class="container" style="max-width:1100px">
  <h1 class="display-6 fw-bold mb-3">Data Insights</h1>
  <p class="text-muted">Coming soon — charts and trends for mood, sleep, pain, and activity.</p>
</div>

  </main>
</div>
</body></html>