<?php
session_start();
header('Content-Type: application/json');

require_once  '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$employeeId = trim($_POST['employee_id'] ?? '');
$password = $_POST['password'] ?? '';

if ($employeeId === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Employee ID and password are required'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            midwife_id,
            employee_id,
            password_hash,
            full_name,
            email,
            phone,
            assigned_area,
            moh_office,
            status
        FROM midwives
        WHERE employee_id = ?
        LIMIT 1
    ");

    $stmt->execute([$employeeId]);
    $midwife = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$midwife) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Employee ID or password'
        ]);
        exit;
    }

    if ($midwife['status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'Your account is not active. Please contact admin.'
        ]);
        exit;
    }

    if (!password_verify($password, $midwife['password_hash'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid Employee ID or password'
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_type'] = 'midwife';

    
    $_SESSION['midwife_id'] = $midwife['midwife_id'];
    $_SESSION['employee_id'] = $midwife['employee_id'];
    $_SESSION['full_name'] = $midwife['full_name'];
    $_SESSION['email'] = $midwife['email'];
    $_SESSION['assigned_area'] = $midwife['assigned_area'];
    $_SESSION['moh_office'] = $midwife['moh_office'];
    $_SESSION['login_time'] = date('Y-m-d H:i:s');

    $updateStmt = $pdo->prepare("
        UPDATE midwives 
        SET last_login = NOW(), login_attempts = 0, locked_until = NULL
        WHERE midwife_id = ?
    ");
    $updateStmt->execute([$midwife['midwife_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'midwife_id' => $midwife['midwife_id'],
            'employee_id' => $midwife['employee_id'],
            'full_name' => $midwife['full_name'],
            'email' => $midwife['email'],
            'assigned_area' => $midwife['assigned_area'],
            'moh_office' => $midwife['moh_office'],
            'role' => 'midwife'
        ]
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}