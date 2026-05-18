<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain');

/*
|--------------------------------------------------------------------------
| MOH Admin Details
| Change these values before running
|--------------------------------------------------------------------------
*/

$username = 'admin004';
$plainPassword = '12345678';

$fullName = 'Dr. Kamal Fernanndo';
$email = 'test.kamalfadmin@health.gov.lk';
$phone = '077123456';
$mohOffice = 'MOH Galle';
$position = 'MOH Officer';

/*
|--------------------------------------------------------------------------
| Do not edit below unless needed
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);

try {
    /*
     * Check duplicate username or email
     */
    $checkStmt = $pdo->prepare("
        SELECT admin_id
        FROM moh_admins
        WHERE username = :username
           OR email = :email
        LIMIT 1
    ");

    $checkStmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);

    if ($checkStmt->fetch()) {
        echo "Admin already exists with this username or email.\n";
        exit;
    }

    /*
     * Insert MOH admin
     */
    $stmt = $pdo->prepare("
        INSERT INTO moh_admins (
            username,
            password_hash,
            full_name,
            email,
            phone,
            moh_office,
            position,
            is_active,
            login_attempts,
            locked_until
        ) VALUES (
            :username,
            :password_hash,
            :full_name,
            :email,
            :phone,
            :moh_office,
            :position,
            1,
            0,
            NULL
        )
    ");

    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $passwordHash,
        ':full_name' => $fullName,
        ':email' => $email,
        ':phone' => $phone,
        ':moh_office' => $mohOffice,
        ':position' => $position
    ]);

    echo "MOH admin created successfully.\n";
    echo "Username: " . $username . "\n";
    echo "Email: " . $email . "\n";

} catch (PDOException $e) {
    echo "Database error:\n";
    echo $e->getMessage() . "\n";
}