<?php
// maccad/health-app/health-app-71be168511a39a96b9fec27cd763f72a6b44df00/api/get_insights.php

require_once '../db.php';
require_once '../auth.php';

// Function to calculate sleep duration in minutes
function calculate_sleep_duration_minutes($bed_time, $wake_time) {
    if (!$bed_time || !$wake_time) return 0;

    $bed = strtotime($bed_time);
    $wake = strtotime($wake_time);

    // If wake is earlier than bed (slept past midnight)
    if ($wake < $bed) {
        // Add 24 hours to wake time to account for crossing midnight
        $wake += 24 * 3600; 
    }
    
    $diff = $wake - $bed;
    return round($diff / 60); // Difference in minutes
}

// Ensure the user is logged in
require_login(); 

header('Content-Type: application/json');

$user_id = current_user_id();

try {
    $results = [];

    // --- 1. Mood Trend (Last 30 Logs) & Base Data for Trends ---
    $sql_trends = "SELECT id, timestamp, mood, water_intake_oz, exercise_duration, exercise_type, sleep_bed_time, sleep_wake_time
                   FROM health_logs 
                   WHERE user_id = :user_id 
                   ORDER BY timestamp DESC LIMIT 30";
    $stmt_trends = $pdo->prepare($sql_trends);
    $stmt_trends->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_trends->execute();
    $trends_data = $stmt_trends->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Trend Data on the PHP side to simplify frontend charting
    $mood_data = [];
    $sleep_data = [];
    $activity_data = [];
    $water_data = [];

    foreach ($trends_data as $log) {
        // Mood
        $mood_data[] = ['timestamp' => $log['timestamp'], 'mood' => (int)$log['mood']];
        
        // Sleep (Duration in Hours)
        $duration_min = calculate_sleep_duration_minutes($log['sleep_bed_time'], $log['sleep_wake_time']);
        $sleep_data[] = ['timestamp' => $log['timestamp'], 'duration_h' => round($duration_min / 60, 1)];

        // Water
        $water_data[] = ['timestamp' => $log['timestamp'], 'oz' => (int)$log['water_intake_oz']];

        // Activity
        $activity_data[] = ['timestamp' => $log['timestamp'], 'duration_min' => (int)$log['exercise_duration']];
    }

    $results['mood_data'] = array_reverse($mood_data);
    $results['sleep_data'] = array_reverse($sleep_data);
    $results['water_data'] = array_reverse($water_data);
    $results['activity_trend_data'] = array_reverse($activity_data);

    // --- 2. Pain Intensity Distribution (Bar Chart) ---
    $sql_pain = "SELECT symptom_intensity, COUNT(*) as count 
                 FROM health_logs 
                 WHERE user_id = :user_id AND symptom_intensity > 0
                 GROUP BY symptom_intensity 
                 ORDER BY symptom_intensity ASC";
    $stmt_pain = $pdo->prepare($sql_pain);
    $stmt_pain->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_pain->execute();
    $results['pain_data'] = $stmt_pain->fetchAll(PDO::FETCH_ASSOC);
    
    // --- 3. Exercise Type Breakdown (Doughnut Chart) ---
    $sql_exercise = "SELECT exercise_type, COUNT(*) as count 
                     FROM health_logs 
                     WHERE user_id = :user_id AND exercise_type != 'None' AND exercise_type IS NOT NULL
                     GROUP BY exercise_type 
                     ORDER BY count DESC";
    $stmt_exercise = $pdo->prepare($sql_exercise);
    $stmt_exercise->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_exercise->execute();
    $results['exercise_data'] = $stmt_exercise->fetchAll(PDO::FETCH_ASSOC);

    // Return all data
    echo json_encode(array_merge($results, ['success' => true]));

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve insights data.', 'detail' => $e->getMessage()]);
    exit;
}
?>