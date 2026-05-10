<?php
session_start();

require_once  '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/dashboard.html');
    exit;
}

$fullName = trim($_POST['fullname'] ?? '');
$employeeId = trim($_POST['employee_id'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$area = trim($_POST['area'] ?? '');
$startDate = trim($_POST['start_date'] ?? '');

if (
    $fullName === '' ||
    $employeeId === '' ||
    $email === '' ||
    $phone === '' ||
    $password === '' ||
    $confirmPassword === '' ||
    $area === '' ||
    $startDate === ''
) {
    die('All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email address.');
}

if (strlen($password) < 6) {
    die('Password must be at least 6 characters.');
}

if ($password !== $confirmPassword) {
    die('Password and confirm password do not match.');
}

$areaMap = [
    'area1' => 'Colombo Central',
    'area2' => 'Colombo North',
    'area3' => 'Colombo South'
];

$assignedArea = $areaMap[$area] ?? $area;
$mohOffice = $_SESSION['moh_office'] ?? 'MOH Colombo 01';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $checkStmt = $pdo->prepare("
        SELECT midwife_id 
        FROM midwives 
        WHERE employee_id = ? OR email = ?
        LIMIT 1
    ");
    $checkStmt->execute([$employeeId, $email]);

    if ($checkStmt->fetch()) {
        die('Employee ID or Email already exists.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO midwives (
            employee_id,
            password_hash,
            full_name,
            email,
            phone,
            assigned_area,
            moh_office,
            hire_date,
            status,
            experience_years,
            login_attempts
        ) VALUES (
            :employee_id,
            :password_hash,
            :full_name,
            :email,
            :phone,
            :assigned_area,
            :moh_office,
            :hire_date,
            'active',
            0,
            0
        )
    ");

    $stmt->execute([
        ':employee_id' => $employeeId,
        ':password_hash' => $passwordHash,
        ':full_name' => $fullName,
        ':email' => $email,
        ':phone' => $phone,
        ':assigned_area' => $assignedArea,
        ':moh_office' => $mohOffice,
        ':hire_date' => $startDate
    ]);

    header('Location: ../../admin/dashboard.html?midwife_added=1');
    exit;

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}