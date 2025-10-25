<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard • Health Logger</title>
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
  <h1 class="display-6 fw-bold mb-1">New Health Entry</h1>
  <p class="text-muted mb-4">Fill out your mood, physical, and activity logs for today.</p>

  <div id="errorMessage" class="alert alert-danger d-none"></div>

  <div class="bg-white p-4 rounded-4 shadow-sm mb-5">
    <h2 class="fs-4 fw-semibold border-bottom pb-3">Today's Entry</h2>
    <h3 class="fs-6 text-secondary mb-3 mt-3">How is your mood? (1=Poor, 5=Excellent)</h3>
    <div id="moodButtons" class="d-flex gap-2 gap-md-4 mb-4 flex-wrap"></div>

    <h2 class="fs-4 fw-semibold border-bottom pb-3 pt-3">Sleep Details (Optional)</h2>
    <div class="p-3 border rounded-3 bg-light mb-4 text-center">
      <button id="openSleepModal" class="btn btn-outline-primary">Log Sleep Times</button>
      <p id="sleepSummary" class="small text-muted mt-2">Click to set times between 9pm and 12pm.</p>
    </div>

    <h2 class="fs-4 fw-semibold border-bottom pb-3 pt-3">Symptom Details (Optional)</h2>
    <div class="mb-3 p-3 border rounded-3 bg-light">
      <label class="form-label">Pain Intensity: <span id="painIntensityValue" class="fw-bold">0</span>/10</label>
      <input type="range" class="form-range" id="painIntensity" min="0" max="10" step="1" value="0">
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="p-3 border rounded-3 bg-light h-100">
          <label class="form-label">How long have you had this?</label>
          <select id="symptomDuration" class="form-select"></select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-3 border rounded-3 bg-light h-100 text-center">
          <label class="form-label">Location(s) of symptom:</label><br>
          <button id="openBodyMapModal" class="btn btn-outline-danger">Select Symptom Location</button>
          <p id="bodyMapSummary" class="small text-muted mt-2">No areas selected</p>
        </div>
      </div>
    </div>

    <h2 class="fs-4 fw-semibold border-bottom pb-3 pt-3">Medication Tracker (Optional)</h2>
    <div class="p-3 border rounded-3 bg-light mb-4 text-center">
      <button id="openMedicationModal" class="btn btn-outline-success">Select Medications & Add Notes</button>
      <p id="medicationSummary" class="small text-muted mt-2">No medication logged</p>
    </div>

    <h2 class="fs-4 fw-semibold border-bottom pb-3 pt-3">Diet Log (Optional)</h2>
    <div class="p-3 border rounded-3 bg-light mb-4 text-center">
      <button id="openDietModal" class="btn btn-outline-warning">Select Meals & Add Notes</button>
      <p id="mealSummary" class="small text-muted mt-2">No meals logged</p>
    </div>

    <h2 class="fs-4 fw-semibold border-bottom pb-3 pt-3">Activity & Hydration (Optional)</h2>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="p-3 border rounded-3 bg-light h-100 text-center">
          <h6>Exercise Today</h6>
          <select id="exerciseType" class="form-select mb-2"></select>
          <div class="d-flex justify-content-center align-items-center gap-2">
            <button id="decreaseDuration" class="btn btn-outline-secondary btn-sm">-</button>
            <div><span id="exerciseDurationValue" class="fw-bold">0</span> min</div>
            <button id="increaseDuration" class="btn btn-outline-secondary btn-sm">+</button>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-3 border rounded-3 bg-light h-100 text-center">
          <h6>Daily Hydration</h6>
          <div class="d-flex justify-content-center align-items-center gap-2">
            <button id="decreaseWater" class="btn btn-outline-secondary btn-sm">-</button>
            <div><span id="waterIntakeValue" class="fw-bold">0</span> oz</div>
            <button id="increaseWater" class="btn btn-outline-secondary btn-sm">+</button>
          </div>
          <p id="waterGoal" class="small mt-2">Goal: 64 oz (0 / 8 cups)</p>
        </div>
      </div>
    </div>

    <h2 class="fs-4 fw-semibold border-bottom pb-3 pt-3">General Notes</h2>
    <textarea id="generalNote" class="form-control mb-4" rows="3" placeholder="Add any general notes..."></textarea>
    <button id="logEntryButton" class="btn btn-primary w-100">Log My Health Entry</button>
  </div>

  <h2 class="fs-4 fw-bold mb-3">Past Logs</h2>
  <div id="historyLogs"></div>
  <div id="loadingMessage" class="text-center text-muted d-none">Loading...</div>
</div>
<script src="assets/js/app.js"></script>

  </main>
</div>
</body></html>