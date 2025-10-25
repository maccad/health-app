<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Past Logs • Health Logger</title>
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
  <h1 class="display-6 fw-bold mb-3">Past Logs</h1>
  <div id="logsList"></div>
  <div id="loading" class="text-muted">Loading...</div>
</div>
<script>
async function load() {
  const list = document.getElementById('logsList');
  const loading = document.getElementById('loading');
  try{
    const res = await fetch('api/get_logs.php');
    if(res.status===401){ window.location.href='login.php'; return; }
    const rows = await res.json();
    loading.style.display='none';
    if(!rows.length){ list.innerHTML = '<div class="p-4 bg-white rounded-3 shadow-sm text-center text-muted">No logs yet.</div>'; return; }
    rows.forEach(l=>{
      const d = new Date(l.timestamp).toLocaleString();
      const card = document.createElement('div');
      card.className='p-3 bg-white rounded-3 shadow-sm mb-3';
      card.innerHTML = `<div class="d-flex justify-content-between">
        <div><strong>Mood:</strong> ${l.mood}/5</div><div class="text-muted small">${d}</div></div>
        ${l.note?`<div class="mt-2"><strong>Note:</strong> ${l.note}</div>`:''}
        ${l.symptom_locations?`<div class="mt-2"><strong>Locations:</strong> ${l.symptom_locations}</div>`:''}
        ${l.medication_log?`<div class="mt-2"><strong>Medication:</strong> ${l.medication_log}</div>`:''}
        ${l.diet_log?`<div class="mt-2"><strong>Diet:</strong> ${l.diet_log}</div>`:''}
        ${l.water_intake_oz?`<div class="mt-2"><strong>Water:</strong> ${l.water_intake_oz} oz</div>`:''}
        ${l.exercise_type && l.exercise_duration?`<div class="mt-2"><strong>Exercise:</strong> ${l.exercise_type} (${l.exercise_duration} min)</div>`:''}
      `;
      list.appendChild(card);
    });
  }catch(e){ loading.innerText='Failed to load'; }
}
load();
</script>

  </main>
</div>
</body></html>