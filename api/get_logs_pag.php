<?php
// maccad/health-app/health-app-71be168511a39a96b9fec27cd763f72a6b44df00/api/get_logs.php

// Adjust path if necessary
require_once '../db.php';
require_once '../auth.php';

// Ensure the user is logged in
require_login(); 

header('Content-Type: application/json');

$user_id = current_user_id();

// --- 1. Get Pagination and Search Parameters ---
$limit = (int)($_GET['limit'] ?? 10);
$offset = (int)($_GET['offset'] ?? 0);
$search = trim($_GET['search'] ?? '');

$limit = max(1, $limit); // Ensure limit is at least 1
$offset = max(0, $offset); // Ensure offset is not negative

// --- 2. Build WHERE Clause and Parameters ---
$sql_where = "WHERE user_id = :user_id";
$params = ['user_id' => $user_id];

if (!empty($search)) {
    // Search across note, symptom_locations, medication_log, diet_log
    $sql_where .= " AND (
        note LIKE :search OR
        symptom_locations LIKE :search OR
        medication_log LIKE :search OR
        diet_log LIKE :search
    )";
    $params['search'] = '%' . $search . '%';
}

// --- 3. Get total count of matching logs ---
try {
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM health_logs {$sql_where}");
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error during count.']);
    exit;
}

// --- 4. Get the paginated logs ---
$sql = "SELECT * FROM health_logs 
        {$sql_where}
        ORDER BY timestamp DESC
        LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);

    // Bind named parameters
    foreach ($params as $key => &$value) {
        // Use PARAM_INT for user_id to ensure proper type binding
        if ($key === 'user_id') {
            $stmt->bindParam($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
    }

    // Bind pagination parameters (must use bindParam for LIMIT/OFFSET placeholders)
    $stmt->bindParam('limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam('offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $logs = $stmt->fetchAll();

    // --- 5. Return Results ---
    echo json_encode([
        'logs' => $logs,
        'total_records' => $total_records
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error during log retrieval.']);
    exit;
}
?>