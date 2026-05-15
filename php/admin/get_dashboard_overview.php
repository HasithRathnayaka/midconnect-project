<?php
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$adminMohOffice = trim($_SESSION['moh_office'] ?? '');

if ($adminMohOffice === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Admin MOH office not found in session.'
    ]);
    exit;
}

try {
    $activeMidwives = getActiveMidwives($pdo, $adminMohOffice);
    $todayActivities = getTodayActivities($pdo, $adminMohOffice);
    $homeVisitsToday = getHomeVisitsToday($pdo, $adminMohOffice);
    $vaccinations = getVaccinationsCount($pdo, $adminMohOffice);
    $clinicSessions = getClinicSessionsCount($pdo, $adminMohOffice);
    $pendingTasks = getPendingTasksCount($pdo, $adminMohOffice);
    $coverageRate = calculateCoverageRate($pdo, $adminMohOffice);
    $areaCards = getAreaCards($pdo, $adminMohOffice);
    $recentActivities = getRecentActivities($pdo, $adminMohOffice);
    $topMidwives = getTopMidwives($pdo, $adminMohOffice);
    $weeklyOverview = getWeeklyOverview($pdo, $adminMohOffice);
    $performanceSummary = getPerformanceSummary($pdo, $adminMohOffice);

    echo json_encode([
        'success' => true,
        'admin' => [
            'full_name' => $_SESSION['full_name'] ?? 'Admin',
            'moh_office' => $adminMohOffice
        ],
        'summary' => [
            'active_midwives' => $activeMidwives,
            'today_activities' => $todayActivities,
            'coverage_rate' => $coverageRate,
            'home_visits_today' => $homeVisitsToday,
            'vaccinations' => $vaccinations,
            'clinic_sessions' => $clinicSessions,
            'pending_tasks' => $pendingTasks
        ],
        'areas' => $areaCards,
        'recent_activities' => $recentActivities,
        'top_midwives' => $topMidwives,
        'weekly_overview' => $weeklyOverview,
        'performance_summary' => $performanceSummary
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Dashboard overview loading error: ' . $e->getMessage()
    ]);
    exit;
}

function getActiveMidwives(PDO $pdo, string $mohOffice): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM midwives
        WHERE LOWER(TRIM(moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(status)) = 'active'
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice
    ]);

    return (int)$stmt->fetchColumn();
}

function getTodayActivities(PDO $pdo, string $mohOffice): int
{
    return countActivitiesByDate($pdo, $mohOffice, 'today');
}

function getHomeVisitsToday(PDO $pdo, string $mohOffice): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM home_visits hv
        INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND hv.visit_date = CURDATE()
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice
    ]);

    return (int)$stmt->fetchColumn();
}

function getVaccinationsCount(PDO $pdo, string $mohOffice): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice
    ]);

    return (int)$stmt->fetchColumn();
}

function getClinicSessionsCount(PDO $pdo, string $mohOffice): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON a.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(at.type_code)) = 'clinic_visit'
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice
    ]);

    $activityCount = (int)$stmt->fetchColumn();

    $scheduleStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        INNER JOIN activity_types at ON s.activity_type_id = at.type_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(at.type_code)) = 'clinic_visit'
    ");

    $scheduleStmt->execute([
        ':moh_office' => $mohOffice
    ]);

    return $activityCount + (int)$scheduleStmt->fetchColumn();
}

function getPendingTasksCount(PDO $pdo, string $mohOffice): int
{
    $activities = countStatusFromActivities($pdo, $mohOffice, 'pending');
    $schedules = countStatusFromSchedules($pdo, $mohOffice, 'pending');
    $homeVisits = countStatusFromHomeVisits($pdo, $mohOffice, 'pending');
    $vaccinations = countStatusFromVaccinations($pdo, $mohOffice, 'pending');
    $counseling = countStatusFromCounseling($pdo, $mohOffice, 'pending');
    $healthEducation = countStatusFromHealthEducation($pdo, $mohOffice, 'pending');

    return $activities + $schedules + $homeVisits + $vaccinations + $counseling + $healthEducation;
}

function calculateCoverageRate(PDO $pdo, string $mohOffice): int
{
    $total = countAllOperationalRecords($pdo, $mohOffice);

    if ($total === 0) {
        return 0;
    }

    $completed =
        countStatusFromActivities($pdo, $mohOffice, 'completed') +
        countStatusFromSchedules($pdo, $mohOffice, 'completed') +
        countStatusFromHomeVisits($pdo, $mohOffice, 'completed') +
        countStatusFromVaccinations($pdo, $mohOffice, 'completed') +
        countStatusFromCounseling($pdo, $mohOffice, 'completed') +
        countStatusFromHealthEducation($pdo, $mohOffice, 'completed');

    return (int)round(($completed / $total) * 100);
}

function countAllOperationalRecords(PDO $pdo, string $mohOffice): int
{
    $count = 0;

    $queries = [
        "
        SELECT COUNT(*)
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ",
        "
        SELECT COUNT(*)
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ",
        "
        SELECT COUNT(*)
        FROM home_visits hv
        INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ",
        "
        SELECT COUNT(*)
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ",
        "
        SELECT COUNT(*)
        FROM counseling_sessions cs
        INNER JOIN midwives m ON cs.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ",
        "
        SELECT COUNT(*)
        FROM health_education_sessions hes
        INNER JOIN midwives m ON hes.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        "
    ];

    foreach ($queries as $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':moh_office' => $mohOffice
        ]);
        $count += (int)$stmt->fetchColumn();
    }

    return $count;
}

function countStatusFromActivities(PDO $pdo, string $mohOffice, string $status): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(a.status)) = LOWER(TRIM(:status))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':status' => $status
    ]);

    return (int)$stmt->fetchColumn();
}

function countStatusFromSchedules(PDO $pdo, string $mohOffice, string $status): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(s.status)) = LOWER(TRIM(:status))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':status' => $status
    ]);

    return (int)$stmt->fetchColumn();
}

function countStatusFromHomeVisits(PDO $pdo, string $mohOffice, string $status): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM home_visits hv
        INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(hv.status)) = LOWER(TRIM(:status))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':status' => $status
    ]);

    return (int)$stmt->fetchColumn();
}

function countStatusFromVaccinations(PDO $pdo, string $mohOffice, string $status): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(vr.status)) = LOWER(TRIM(:status))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':status' => $status
    ]);

    return (int)$stmt->fetchColumn();
}

function countStatusFromCounseling(PDO $pdo, string $mohOffice, string $status): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM counseling_sessions cs
        INNER JOIN midwives m ON cs.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(cs.status)) = LOWER(TRIM(:status))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':status' => $status
    ]);

    return (int)$stmt->fetchColumn();
}

function countStatusFromHealthEducation(PDO $pdo, string $mohOffice, string $status): int
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM health_education_sessions hes
        INNER JOIN midwives m ON hes.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(hes.status)) = LOWER(TRIM(:status))
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':status' => $status
    ]);

    return (int)$stmt->fetchColumn();
}

function countActivitiesByDate(PDO $pdo, string $mohOffice, string $mode): int
{
    $dateCondition = $mode === 'today' ? 'CURDATE()' : 'CURDATE()';

    $count = 0;

    $queries = [
        "
        SELECT COUNT(*)
        FROM activities a
        INNER JOIN midwives m ON a.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND a.activity_date = {$dateCondition}
        ",
        "
        SELECT COUNT(*)
        FROM schedules s
        INNER JOIN midwives m ON s.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND s.scheduled_date = {$dateCondition}
        ",
        "
        SELECT COUNT(*)
        FROM home_visits hv
        INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND hv.visit_date = {$dateCondition}
        ",
        "
        SELECT COUNT(*)
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND vr.vaccination_date = {$dateCondition}
        ",
        "
        SELECT COUNT(*)
        FROM counseling_sessions cs
        INNER JOIN midwives m ON cs.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND DATE(cs.session_datetime) = {$dateCondition}
        ",
        "
        SELECT COUNT(*)
        FROM health_education_sessions hes
        INNER JOIN midwives m ON hes.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND hes.session_date = {$dateCondition}
        "
    ];

    foreach ($queries as $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':moh_office' => $mohOffice
        ]);

        $count += (int)$stmt->fetchColumn();
    }

    return $count;
}

function getAreaCards(PDO $pdo, string $mohOffice): array
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT assigned_area
        FROM midwives
        WHERE LOWER(TRIM(moh_office)) = LOWER(TRIM(:moh_office))
          AND assigned_area IS NOT NULL
          AND TRIM(assigned_area) != ''
        ORDER BY assigned_area ASC
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice
    ]);

    $areas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $result = [];

    foreach ($areas as $area) {
        $midwifeStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM midwives
            WHERE LOWER(TRIM(moh_office)) = LOWER(TRIM(:moh_office))
              AND LOWER(TRIM(assigned_area)) = LOWER(TRIM(:area))
              AND LOWER(TRIM(status)) = 'active'
        ");

        $midwifeStmt->execute([
            ':moh_office' => $mohOffice,
            ':area' => $area
        ]);

        $todayStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM home_visits hv
            INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
            WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
              AND LOWER(TRIM(m.assigned_area)) = LOWER(TRIM(:area))
              AND hv.visit_date = CURDATE()
        ");

        $todayStmt->execute([
            ':moh_office' => $mohOffice,
            ':area' => $area
        ]);

        $result[] = [
            'area' => $area,
            'active_midwives' => (int)$midwifeStmt->fetchColumn(),
            'appointments_today' => (int)$todayStmt->fetchColumn()
        ];
    }

    return $result;
}

function getRecentActivities(PDO $pdo, string $mohOffice): array
{
    $records = [];

    $homeStmt = $pdo->prepare("
        SELECT
            'Home Visit' AS activity_type,
            hv.patient_name AS title,
            hv.created_at,
            m.full_name AS midwife_name,
            m.employee_id,
            'home' AS icon,
            'success' AS icon_class
        FROM home_visits hv
        INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ORDER BY hv.created_at DESC
        LIMIT 5
    ");

    $homeStmt->execute([
        ':moh_office' => $mohOffice
    ]);

    $records = array_merge($records, $homeStmt->fetchAll(PDO::FETCH_ASSOC));

    $vaccStmt = $pdo->prepare("
        SELECT
            'Vaccination Session' AS activity_type,
            vr.patient_name AS title,
            vr.created_at,
            m.full_name AS midwife_name,
            m.employee_id,
            'syringe' AS icon,
            'info' AS icon_class
        FROM vaccination_records vr
        INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ORDER BY vr.created_at DESC
        LIMIT 5
    ");

    $vaccStmt->execute([
        ':moh_office' => $mohOffice
    ]);

    $records = array_merge($records, $vaccStmt->fetchAll(PDO::FETCH_ASSOC));

    $counselStmt = $pdo->prepare("
        SELECT
            'Counseling Session' AS activity_type,
            cs.client_ref AS title,
            cs.created_at,
            m.full_name AS midwife_name,
            m.employee_id,
            'comments' AS icon,
            'warning' AS icon_class
        FROM counseling_sessions cs
        INNER JOIN midwives m ON cs.midwife_id = m.midwife_id
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
        ORDER BY cs.created_at DESC
        LIMIT 5
    ");

    $counselStmt->execute([
        ':moh_office' => $mohOffice
    ]);

    $records = array_merge($records, $counselStmt->fetchAll(PDO::FETCH_ASSOC));

    usort($records, function ($a, $b) {
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
    });

    return array_slice($records, 0, 6);
}

function getTopMidwives(PDO $pdo, string $mohOffice): array
{
    $stmt = $pdo->prepare("
        SELECT
            m.midwife_id,
            m.full_name,
            m.employee_id,
            m.assigned_area,

            (
                SELECT COUNT(*)
                FROM home_visits hv
                WHERE hv.midwife_id = m.midwife_id
            ) +
            (
                SELECT COUNT(*)
                FROM vaccination_records vr
                WHERE vr.midwife_id = m.midwife_id
            ) +
            (
                SELECT COUNT(*)
                FROM counseling_sessions cs
                WHERE cs.midwife_id = m.midwife_id
            ) +
            (
                SELECT COUNT(*)
                FROM health_education_sessions hes
                WHERE hes.midwife_id = m.midwife_id
            ) AS total_records,

            (
                SELECT COUNT(*)
                FROM home_visits hv
                WHERE hv.midwife_id = m.midwife_id
                  AND LOWER(hv.status) = 'completed'
            ) +
            (
                SELECT COUNT(*)
                FROM vaccination_records vr
                WHERE vr.midwife_id = m.midwife_id
                  AND LOWER(vr.status) = 'completed'
            ) +
            (
                SELECT COUNT(*)
                FROM counseling_sessions cs
                WHERE cs.midwife_id = m.midwife_id
                  AND LOWER(cs.status) = 'completed'
            ) +
            (
                SELECT COUNT(*)
                FROM health_education_sessions hes
                WHERE hes.midwife_id = m.midwife_id
                  AND LOWER(hes.status) = 'completed'
            ) AS completed_records

        FROM midwives m
        WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
          AND LOWER(TRIM(m.status)) = 'active'
        ORDER BY total_records DESC
        LIMIT 5
    ");

    $stmt->execute([
        ':moh_office' => $mohOffice
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(function ($row) {
        $total = (int)$row['total_records'];
        $completed = (int)$row['completed_records'];

        $rate = $total > 0 ? (int)round(($completed / $total) * 100) : 0;

        return [
            'midwife_id' => (int)$row['midwife_id'],
            'full_name' => $row['full_name'],
            'employee_id' => $row['employee_id'],
            'assigned_area' => $row['assigned_area'],
            'completion_rate' => $rate,
            'total_records' => $total
        ];
    }, $rows);
}

function getWeeklyOverview(PDO $pdo, string $mohOffice): array
{
    $days = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));

        $days[] = [
            'date' => $date,
            'label' => date('D', strtotime($date)),
            'home_visits' => getDateCount($pdo, $mohOffice, 'home_visits', $date),
            'vaccinations' => getDateCount($pdo, $mohOffice, 'vaccinations', $date),
            'clinic_sessions' => getDateCount($pdo, $mohOffice, 'clinics', $date)
        ];
    }

    return $days;
}

function getDateCount(PDO $pdo, string $mohOffice, string $type, string $date): int
{
    if ($type === 'home_visits') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM home_visits hv
            INNER JOIN midwives m ON hv.midwife_id = m.midwife_id
            WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
              AND hv.visit_date = :record_date
        ");
    } elseif ($type === 'vaccinations') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM vaccination_records vr
            INNER JOIN midwives m ON vr.midwife_id = m.midwife_id
            WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
              AND vr.vaccination_date = :record_date
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM activities a
            INNER JOIN midwives m ON a.midwife_id = m.midwife_id
            INNER JOIN activity_types at ON a.activity_type_id = at.type_id
            WHERE LOWER(TRIM(m.moh_office)) = LOWER(TRIM(:moh_office))
              AND LOWER(TRIM(at.type_code)) = 'clinic_visit'
              AND a.activity_date = :record_date
        ");
    }

    $stmt->execute([
        ':moh_office' => $mohOffice,
        ':record_date' => $date
    ]);

    return (int)$stmt->fetchColumn();
}

function getPerformanceSummary(PDO $pdo, string $mohOffice): array
{
    return [
        'completed' => calculatePerformanceStatus($pdo, $mohOffice, 'completed'),
        'in_progress' => calculatePerformanceStatus($pdo, $mohOffice, 'in_progress'),
        'pending' => calculatePerformanceStatus($pdo, $mohOffice, 'pending'),
        'scheduled' => calculatePerformanceStatus($pdo, $mohOffice, 'scheduled')
    ];
}

function calculatePerformanceStatus(PDO $pdo, string $mohOffice, string $status): int
{
    return
        countStatusFromActivities($pdo, $mohOffice, $status) +
        countStatusFromSchedules($pdo, $mohOffice, $status) +
        countStatusFromHomeVisits($pdo, $mohOffice, $status) +
        countStatusFromVaccinations($pdo, $mohOffice, $status) +
        countStatusFromCounseling($pdo, $mohOffice, $status) +
        countStatusFromHealthEducation($pdo, $mohOffice, $status);
}