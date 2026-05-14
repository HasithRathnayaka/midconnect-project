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

$sessionDatetime = trim($_POST['session_datetime'] ?? '');
$durationMins = trim($_POST['duration_mins'] ?? '');
$clientRef = trim($_POST['client_ref'] ?? '');
$focus = trim($_POST['focus'] ?? '');
$locationType = trim($_POST['location_type'] ?? 'clinic');
$notes = trim($_POST['notes'] ?? '');
$followup = trim($_POST['followup'] ?? 'no');
$followupDate = trim($_POST['followup_date'] ?? '');
$referralDetails = trim($_POST['referral_details'] ?? '');

if (
    $sessionDatetime === '' ||
    $durationMins === '' ||
    $clientRef === '' ||
    $focus === '' ||
    $notes === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill all required fields.'
    ]);
    exit;
}

$allowedFocus = [
    'antenatal',
    'postnatal',
    'breastfeeding',
    'family_planning',
    'mental_health',
    'gbv',
    'other'
];

$allowedLocations = [
    'clinic',
    'home',
    'phone',
    'community'
];

$allowedFollowups = [
    'no',
    'yes',
    'referral'
];

if (!in_array($focus, $allowedFocus, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid session focus.'
    ]);
    exit;
}

if (!in_array($locationType, $allowedLocations, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid location type.'
    ]);
    exit;
}

if (!in_array($followup, $allowedFollowups, true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid follow-up value.'
    ]);
    exit;
}

$durationMins = (int) $durationMins;

if ($durationMins < 5 || $durationMins > 240) {
    echo json_encode([
        'success' => false,
        'message' => 'Duration must be between 5 and 240 minutes.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO counseling_sessions (
            midwife_id,
            session_datetime,
            duration_mins,
            client_ref,
            focus,
            location_type,
            notes,
            followup,
            followup_date,
            referral_details,
            status,
            created_at,
            updated_at
        ) VALUES (
            :midwife_id,
            :session_datetime,
            :duration_mins,
            :client_ref,
            :focus,
            :location_type,
            :notes,
            :followup,
            :followup_date,
            :referral_details,
            :status,
            NOW(),
            NOW()
        )
    ");

    $stmt->execute([
        ':midwife_id' => $midwifeId,
        ':session_datetime' => str_replace('T', ' ', $sessionDatetime),
        ':duration_mins' => $durationMins,
        ':client_ref' => $clientRef,
        ':focus' => $focus,
        ':location_type' => $locationType,
        ':notes' => $notes,
        ':followup' => $followup,
        ':followup_date' => $followupDate !== '' ? $followupDate : null,
        ':referral_details' => $referralDetails !== '' ? $referralDetails : null,
        ':status' => 'completed'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Counseling session saved successfully.',
        'counseling_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}