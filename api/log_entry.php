<?php
require_once __DIR__ . '/../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not logged in']);
  exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid JSON']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO health_logs (
      user_id,
      mood,
      note,
      symptom_intensity,
      symptom_duration,
      symptom_locations,
      sleep_bed_time,
      sleep_wake_time,
      medication_log,
      diet_log,
      water_intake_oz,
      exercise_type,
      exercise_duration,
      timestamp
    ) VALUES (
      :user_id, :mood, :note,
      :symptom_intensity, :symptom_duration, :symptom_locations,
      :sleep_bed_time, :sleep_wake_time,
      :medication_log, :diet_log,
      :water_intake_oz, :exercise_type, :exercise_duration,
      NOW()
    )
  ");

  $sleep = $data['sleep'] ?? null;
  $symptoms = $data['symptoms'] ?? null;
  $exercise = $data['exercise'] ?? null;

  $stmt->execute([
    ':user_id' => $_SESSION['user_id'],
    ':mood' => $data['mood'] ?? null,
    ':note' => $data['note'] ?? null,
    ':symptom_intensity' => $symptoms['intensity'] ?? null,
    ':symptom_duration' => $symptoms['duration'] ?? null,
    ':symptom_locations' => isset($symptoms['locations']) ? implode(',', $symptoms['locations']) : null,
    ':sleep_bed_time' => $sleep['bedTime'] ?? null,
    ':sleep_wake_time' => $sleep['wakeUpTime'] ?? null,
    ':medication_log' => $data['medication'] ?? null,
    ':diet_log' => $data['meals'] ?? null,
    ':water_intake_oz' => $data['waterIntake'] ?? 0,
    ':exercise_type' => $exercise['type'] ?? null,
    ':exercise_duration' => $exercise['duration'] ?? 0,
  ]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
