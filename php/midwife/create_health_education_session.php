<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'midwife') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login again.'
    ]);
    exit;
}

$midwifeId = $_SESSION['midwife_id'] ?? null;

if (!$midwifeId) {
    echo json_encode([
        'success' => false,
        'message' => 'Midwife session not found.'
    ]);
    exit;
}

$sessionDate = trim($_POST['session_date'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$topic = trim($_POST['topic'] ?? '');
$audience = trim($_POST['audience'] ?? 'mixed');
$attendees = trim($_POST['attendees'] ?? '');
$durationMins = trim($_POST['duration_mins'] ?? '');
$materials = trim($_POST['materials'] ?? '');
$outcomes = trim($_POST['outcomes'] ?? '');
$status = trim($_POST['status'] ?? 'completed');

if (
    $sessionDate === '' ||
    $venue === '' ||
    $topic === '' ||
    $attendees === '' ||
    $durationMins === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

$allowedTopics = [
    'nutrition',
    'danger_signs',
    'newborn',
    'immunization',
    'fp',
    'dengue',
    'mental',
    'other'
];

$allowedAudiences = [
    'antenatal',
    'postnatal',
    'mixed',
    'adolescent'
];

$allowedStatuses = [
    'completed',
    'upcoming',
    'planned',
    'cancelled'
];

if (!in_array($topic, $allowedTopics, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid topic selected.'
    ]);
    exit;
}

if (!in_array($audience, $allowedAudiences, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid audience selected.'
    ]);
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status selected.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO health_education_sessions (
            midwife_id,
            session_date,
            venue,
            topic,
            audience,
            attendees,
            duration_mins,
            materials,
            outcomes,
            status,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :session_date,
            :venue,
            :topic,
            :audience,
            :attendees,
            :duration_mins,
            :materials,
            :outcomes,
            :status,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':session_date' => $sessionDate,
        ':venue' => $venue,
        ':topic' => $topic,
        ':audience' => $audience,
        ':attendees' => (int) $attendees,
        ':duration_mins' => (int) $durationMins,
        ':materials' => $materials !== '' ? $materials : null,
        ':outcomes' => $outcomes !== '' ? $outcomes : null,
        ':status' => $status
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Health education session saved successfully.',
        'health_education_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}