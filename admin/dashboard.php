```php
<?php
session_start();

require_once "../config/database.php";

/* AUTHENTICATION AND AUTHORIZATION */
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$email = $_SESSION["email"] ?? "Administrator";

/* SAFE DATABASE STATISTICS */
function getStatistic($conn, $sql, $column)
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        error_log("Dashboard query error: " . mysqli_error($conn));
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    return $row[$column] ?? 0;
}

/* DASHBOARD COUNTS */
$total_students = getStatistic(
    $conn, "SELECT COUNT(*) AS total FROM students", "total"
);

$total_lecturers = getStatistic(
    $conn, "SELECT COUNT(*) AS total FROM lecturers", "total"
);

$total_courses = getStatistic(
    $conn, "SELECT COUNT(*) AS total FROM courses", "total"
);

$total_enrollments = getStatistic(
    $conn, "SELECT COUNT(*) AS total FROM enrollments", "total"
);

$total_results = getStatistic(
    $conn, "SELECT COUNT(*) AS total FROM results", "total"
);

$total_attendance = getStatistic(
    $conn, "SELECT COUNT(*) AS total FROM attendance", "total"
);

$active_users = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE status = 'active'",
    "total"
);

$total_payments = getStatistic(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total FROM payments",
    "total"
);

/* STUDENT STATUS STATISTICS */
$active_students = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM students WHERE status = 'active'",
    "total"
);

$inactive_students = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM students WHERE status = 'inactive'",
    "total"
);

$graduated_students = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM students WHERE status = 'graduated'",
    "total"
);

$suspended_students = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM students WHERE status = 'suspended'",
    "total"
);

/* RECENT STUDENTS */
$recent_students = mysqli_query(
    $conn,
    "SELECT student_number, full_name, program, status
     FROM students
     ORDER BY student_id DESC
     LIMIT 5"
);

if (!$recent_students) {
    error_log("Recent students query error: " . mysqli_error($conn));
}

/* RECENT COURSES */
$recent_courses = mysqli_query(
    $conn,
    "SELECT course_code, course_name
     FROM courses
     ORDER BY course_id DESC
     LIMIT 5"
);

if (!$recent_courses) {
    error_log("Recent courses query error: " . mysqli_error($conn));
}

/* HELPER FUNCTIONS */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function statusClass($status)
{
    switch (strtolower((string)$status)) {
        case "active":
            return "status-active";
        case "inactive":
            return "status-inactive";
        case "graduated":
            return "status-graduated";
        case "suspended":
            return "status-suspended";
        default:
            return "status-default";
    }
}

$student_status_total =
    (int)$active_students +
    (int)$inactive_students +
    (int)$graduated_students +
    (int)$suspended_students;

function percentage($value, $total)
{
    if ((float)$total <= 0) {
        return 0;
    }

    return round(((float)$value / (float)$total) * 100);
}

$active_percentage = percentage($active_students, $student_status_total);
$inactive_percentage = percentage($inactive_students, $student_status_total);
$graduated_percentage = percentage($graduated_students, $student_status_total);
$suspended_percentage = percentage($suspended_students, $student_status_total);

/* DATE AND GREETING */
$hour = (int)date("G");

if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 17) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Student Management System</title>

    <style>
        :root {
            --navy: #101b36;
            --navy-light: #192746;
            --blue: #356df3;
            --blue-dark: #2457d6;
            --background: #f5f7fc;
            --surface: #ffffff;
            --text: #192642;
            --muted: #7c879e;
            --border: #e8edf5;
            --green: #16a36a;
            --orange: #f39a36;
            --purple: #8458ee;
            --red: #e55369;
            --shadow: 0 8px 28px rgba(25, 43, 78, 0.055);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            background: var(--background);
            color: var(--text);
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button, input {
            font: inherit;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 250px;
            background: linear-gradient(180deg, #101b36, #142441);
            color: #e8edfa;
            padding: 25px 16px 18px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 9px 27px;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }

        .brand-logo {
            width: 43px;
            height: 43px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #4f83ff, #315ce2);
            border-radius: 13px;
            font-size: 23px;
            box-shadow: 0 7px 20px rgba(53,109,243,.3);
        }

        .brand h2 {
            font-size: 15px;
            line-height: 1.5;
            color: white;
            letter-spacing: -.2px;
        }

        .brand p {
            color: #94a4c4;
            font-size: 11px;
            margin-top: 2px;
        }

        .menu-heading {
            margin: 25px 11px 11px;
            color: #8090b0;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1.4px;
        }

        .menu {
            list-style: none;
        }

        .menu li {
            margin-bottom: 5px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 13px;
            border-radius: 10px;
            font-size: 13px;
            color: #c4cee2;
            transition: .2s ease;
        }

        .menu a:hover {
            color: #fff;
            background: rgba(255,255,255,.075);
        }

        .menu a.active {
            background: linear-gradient(100deg, #4678ff, #345fe6);
            color: #fff;
            box-shadow: 0 7px 18px rgba(44,91,230,.25);
        }

        .menu-icon {
            width: 23px;
            text-align: center;
            font-size: 17px;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 22px;
        }

        .sidebar-promo {
            padding: 17px 14px;
            border-radius: 13px;
            background: rgba(255,255,255,.065);
            border: 1px solid rgba(255,255,255,.07);
            margin-bottom: 17px;
        }

        .sidebar-promo strong {
            font-size: 12px;
            display: block;
            margin-bottom: 7px;
            color: #fff;
        }

        .sidebar-promo p {
            color: #a5b3cf;
            font-size: 11px;
            line-height: 1.6;
        }

        .logout-link {
            display: block;
            text-align: center;
            color: #ffb5c0;
            background: rgba(229,83,105,.10);
            border: 1px solid rgba(229,83,105,.17);
            border-radius: 10px;
            padding: 11px;
            font-size: 13px;
            transition: .2s;
        }

        .logout-link:hover {
            background: rgba(229,83,105,.2);
        }

        /* MAIN LAYOUT */
        .main {
            margin-left: 250px;
            min-height: 100vh;
            padding: 0 30px 35px;
        }

        /* TOPBAR */
        .topbar {
            min-height: 83px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 27px;
        }

        .mobile-brand {
            display: none;
        }

        .search-box {
            width: min(390px, 48%);
            position: relative;
        }

        .search-box span {
            position: absolute;
            left: 14px;
            top: 11px;
            color: #8a96ae;
        }

        .search-box input {
            width: 100%;
            height: 41px;
            border: 1px solid #e3e9f3;
            background: white;
            border-radius: 10px;
            padding: 0 14px 0 40px;
            outline: none;
            color: var(--text);
            font-size: 12px;
        }

        .search-box input:focus {
            border-color: #85a6ff;
            box-shadow: 0 0 0 3px rgba(53,109,243,.08);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .date-label {
            font-size: 12px;
            color: var(--muted);
            padding-right: 12px;
            border-right: 1px solid var(--border);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: #315fe0;
            background: #e3ebff;
            font-weight: 800;
            font-size: 16px;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #e0e8f8;
        }

        .admin-meta strong {
            display: block;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .admin-meta span {
            color: var(--muted);
            font-size: 11px;
        }

        /* WELCOME BANNER */
        .welcome-banner {
            min-height: 185px;
            border-radius: 18px;
            overflow: hidden;
            padding: 29px 32px;
            margin-bottom: 26px;
            color: white;
            position: relative;
            background:
                radial-gradient(ellipse at 90% 20%, rgba(115,154,255,.35), transparent 40%),
                linear-gradient(115deg, #2449a5, #376cf0 62%, #2546a2);
            box-shadow: 0 12px 28px rgba(47,91,206,.16);
        }

        .welcome-banner::after {
            content: "✦";
            position: absolute;
            right: 11%;
            top: 8px;
            font-size: 145px;
            line-height: 1;
            color: rgba(255,255,255,.07);
            transform: rotate(12deg);
            pointer-events: none;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .welcome-label {
            display: inline-flex;
            padding: 6px 10px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 30px;
            color: #e0e9ff;
            font-size: 10px;
            letter-spacing: .7px;
            text-transform: uppercase;
            margin-bottom: 15px;
            background: rgba(255,255,255,.08);
        }

        .welcome-banner h1 {
            font-size: clamp(23px, 2.3vw, 30px);
            letter-spacing: -.7px;
            margin-bottom: 9px;
        }

        .welcome-banner p {
            color: #e0e9ff;
            font-size: 13px;
            line-height: 1.7;
        }

        .welcome-date {
            margin-top: 17px;
            font-size: 11px;
            color: #e0e9ff;
        }

        /* SECTION HEADERS */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 27px 0 16px;
        }

        .section-header h2 {
            font-size: 17px;
            letter-spacing: -.3px;
        }

        .section-header p {
            color: var(--muted);
            font-size: 11px;
            margin-top: 5px;
        }

        .view-link {
            color: var(--blue);
            font-size: 12px;
            font-weight: 700;
        }

        .view-link:hover {
            color: var(--blue-dark);
        }

        /* STAT CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 17px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 19px;
            min-width: 0;
            box-shadow: var(--shadow);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(25,43,78,.09);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }

        .stat-icon {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            font-size: 21px;
        }

        .icon-blue { background: #e7eeff; color: #376df3; }
        .icon-green { background: #e1f7ec; color: #169b65; }
        .icon-purple { background: #efe7ff; color: #8155e8; }
        .icon-orange { background: #fff0df; color: #e78b28; }
        .icon-teal { background: #ddf7f5; color: #079b9b; }
        .icon-red { background: #ffe8ed; color: #df4c67; }

        .stat-tag {
            font-size: 10px;
            font-weight: 700;
            color: #8390a7;
            background: #f5f7fb;
            padding: 5px 7px;
            border-radius: 6px;
        }

        .stat-card h3 {
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 9px;
        }

        .stat-number {
            font-size: clamp(22px, 2vw, 29px);
            font-weight: 750;
            letter-spacing: -.8px;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .stat-note {
            font-size: 10px;
            color: #8792a7;
            margin-top: 9px;
        }

        .stat-note strong {
            color: #199f6a;
            margin-right: 4px;
        }

        /* LOWER GRID */
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(280px, .9fr);
            gap: 19px;
            margin-top: 24px;
        }

        .panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 22px;
            min-width: 0;
            box-shadow: var(--shadow);
        }

        .panel-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
        }

        .panel-heading h2 {
            font-size: 15px;
            margin-bottom: 5px;
        }

        .panel-heading p {
            color: var(--muted);
            font-size: 11px;
        }

        /* STUDENT STATUS */
        .status-overview {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .status-item {
            display: grid;
            grid-template-columns: 11px minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
        }

        .dot-green { background: #18a56b; }
        .dot-orange { background: #eea044; }
        .dot-purple { background: #8758ed; }
        .dot-red { background: #e55369; }

        .status-name {
            font-size: 12px;
            color: #5c6880;
        }

        .status-value {
            font-size: 12px;
            font-weight: 700;
        }

        .progress-track {
            height: 6px;
            background: #eef1f7;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
            grid-column: 2 / 4;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
        }

        .fill-green { background: #18a56b; }
        .fill-orange { background: #eea044; }
        .fill-purple { background: #8758ed; }
        .fill-red { background: #e55369; }

        .empty-status {
            color: var(--muted);
            background: #f8faff;
            padding: 15px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.6;
        }

        /* QUICK ACTIONS */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .action-card {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 13px;
            padding: 17px 12px;
            min-height: 115px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            transition: .2s ease;
        }

        .action-card:hover {
            border-color: #b8caff;
            background: #f8faff;
            transform: translateY(-2px);
        }

        .action-card .stat-icon {
            width: 39px;
            height: 39px;
            font-size: 19px;
        }

        .action-card strong {
            font-size: 11px;
            line-height: 1.5;
        }

        .action-arrow {
            color: #91a0b9;
            margin-left: 5px;
        }

        /* TABLES */
        .table-wrap {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        th {
            background: #f8faff;
            color: #8490a5;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-size: 9px;
            text-align: left;
            padding: 13px 12px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #f0f2f7;
            font-size: 11px;
            color: #596780;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafbff;
        }

        .student-cell strong {
            color: var(--text);
            display: block;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .student-cell span {
            color: #8995aa;
            font-size: 10px;
        }

        .status-badge {
            display: inline-block;
            border-radius: 30px;
            padding: 5px 9px;
            font-size: 10px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-active {
            color: #148451;
            background: #e3f8ed;
        }

        .status-inactive {
            color: #a76a18;
            background: #fff2dc;
        }

        .status-graduated {
            color: #6846c2;
            background: #efe8ff;
        }

        .status-suspended {
            color: #bd3850;
            background: #ffe8ed;
        }

        .status-default {
            color: #667085;
            background: #edf0f5;
        }

        .empty-row {
            text-align: center;
            padding: 28px 12px;
            color: var(--muted);
            white-space: normal;
            line-height: 1.6;
        }

        .course-list {
            list-style: none;
        }

        .course-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid #f0f2f7;
        }

        .course-list li:last-child {
            border-bottom: none;
        }

        .course-symbol {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            background: #eaf0ff;
            color: #356df3;
            font-size: 17px;
            flex-shrink: 0;
        }

        .course-info {
            min-width: 0;
        }

        .course-info strong {
            display: block;
            font-size: 12px;
            margin-bottom: 5px;
            overflow-wrap: anywhere;
        }

        .course-info span {
            color: var(--muted);
            font-size: 10px;
        }

        .course-code {
            margin-left: auto;
            font-size: 10px;
            color: #5475ca;
            background: #f0f4ff;
            border-radius: 6px;
            padding: 6px 7px;
            flex-shrink: 0;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            color: #97a1b3;
            font-size: 10px;
            padding: 28px 0 5px;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 1250px) {
            .main {
                padding-left: 22px;
                padding-right: 22px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .action-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .content-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 215px;
            }

            .main {
                margin-left: 215px;
                padding-left: 17px;
                padding-right: 17px;
            }

            .date-label {
                display: none;
            }

            .topbar {
                gap: 12px;
            }

            .search-box {
                width: 48%;
            }

            .welcome-banner {
                padding: 24px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 15px;
            }

            .brand {
                padding-bottom: 14px;
            }

            .menu-heading {
                margin-top: 16px;
            }

            .menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 3px 6px;
            }

            .menu li {
                margin: 0;
            }

            .menu a {
                padding: 10px 8px;
                font-size: 11px;
                gap: 7px;
            }

            .menu-icon {
                font-size: 15px;
                width: 19px;
            }

            .sidebar-footer {
                margin-top: 15px;
                padding-top: 0;
            }

            .sidebar-promo {
                display: none;
            }

            .main {
                margin-left: 0;
                padding: 0 14px 25px;
            }

            .topbar {
                min-height: 72px;
                flex-wrap: wrap;
                padding: 13px 0;
                margin-bottom: 17px;
            }

            .search-box {
                width: 100%;
                order: 2;
            }

            .topbar-right {
                margin-left: auto;
                gap: 9px;
            }

            .admin-avatar {
                width: 34px;
                height: 34px;
            }

            .welcome-banner {
                padding: 22px 19px;
                min-height: 175px;
                margin-bottom: 21px;
            }

            .welcome-banner h1 {
                font-size: 23px;
            }

            .stats-grid {
                gap: 11px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-icon {
                width: 37px;
                height: 37px;
                font-size: 18px;
            }

            .stat-number {
                font-size: 25px;
            }

            .section-header h2 {
                font-size: 16px;
            }

            .panel {
                padding: 16px 12px;
            }

            .action-grid {
                gap: 10px;
            }

            .action-card {
                min-height: 105px;
                padding: 13px 10px;
            }

            .footer {
                line-height: 1.8;
            }
        }

        @media (max-width: 350px) {
            .stats-grid,
            .action-grid {
                grid-template-columns: 1fr;
            }

            .menu {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="brand">
        <div class="brand-logo">🎓</div>
        <div>
            <h2>Student Management</h2>
            <p>System Administration</p>
        </div>
    </div>

    <p class="menu-heading">Main Menu</p>

    <ul class="menu">
        <li>
            <a href="dashboard.php" class="active">
                <span class="menu-icon">⌂</span>
                Dashboard
            </a>
        </li>
    </ul>

    <p class="menu-heading">Academic Management</p>

    <ul class="menu">
        <li><a href="students/index.php"><span class="menu-icon">♟</span>Students</a></li>
        <li><a href="lecturers/index.php"><span class="menu-icon">♙</span>Lecturers</a></li>
        <li><a href="courses/index.php"><span class="menu-icon">▣</span>Courses</a></li>
        <li><a href="enrollments/index.php"><span class="menu-icon">▤</span>Enrollments</a></li>
        <li><a href="results/index.php"><span class="menu-icon">◈</span>Results</a></li>
        <li><a href="attendance/index.php"><span class="menu-icon">◷</span>Attendance</a></li>
    </ul>

    <p class="menu-heading">Finance & Reports</p>

    <ul class="menu">
        <li><a href="payments/index.php"><span class="menu-icon">▣</span>Payments</a></li>
        <li><a href="reports/index.php"><span class="menu-icon">▥</span>Reports</a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-promo">
            <strong>🎓 Better Education</strong>
            <p>Manage academic records, track progress and support student success.</p>
        </div>

        <a href="../auth/logout.php" class="logout-link">
            ↪ &nbsp; Sign Out
        </a>
    </div>

</aside>

<!-- MAIN CONTENT -->
<main class="main">

    <!-- TOP BAR -->
    <header class="topbar">

        <div class="search-box">
            <span>⌕</span>
            <input
                type="search"
                id="dashboardSearch"
                placeholder="Search visible students or courses..."
                aria-label="Search visible dashboard records"
            >
        </div>

        <div class="topbar-right">
            <div class="date-label">
                <?= e(date("D, d M Y")) ?>
            </div>

            <div class="admin-avatar">
                <?= e(strtoupper(substr($email, 0, 1))) ?>
            </div>

            <div class="admin-meta">
                <strong>Administrator</strong>
                <span><?= e($email) ?></span>
            </div>
        </div>

    </header>

    <!-- WELCOME BANNER -->
    <section class="welcome-banner">
        <div class="welcome-content">
            <span class="welcome-label">Administration Overview</span>

            <h1>
                <?= e($greeting) ?>, Administrator! 👋
            </h1>

            <p>
                Welcome to your Student Management System.
                Manage academic activities and monitor your institution
                from one convenient dashboard.
            </p>

            <div class="welcome-date">
                📅 &nbsp; <?= e(date("l, d F Y")) ?>
            </div>
        </div>
    </section>

    <!-- SYSTEM STATISTICS -->
    <div class="section-header">
        <div>
            <h2>System Overview</h2>
            <p>A live summary of the records stored in your database.</p>
        </div>

        <a href="reports/index.php" class="view-link">View reports →</a>
    </div>

    <section class="stats-grid">

        <a href="students/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-blue">♟</div>
                <span class="stat-tag">Students</span>
            </div>
            <h3>Total Students</h3>
            <div class="stat-number">
                <?= number_format((int)$total_students) ?>
            </div>
            <p class="stat-note">Registered student records</p>
        </a>

        <a href="lecturers/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-green">♙</div>
                <span class="stat-tag">Staff</span>
            </div>
            <h3>Total Lecturers</h3>
            <div class="stat-number">
                <?= number_format((int)$total_lecturers) ?>
            </div>
            <p class="stat-note">Lecturer records</p>
        </a>

        <a href="courses/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-purple">▣</div>
                <span class="stat-tag">Academics</span>
            </div>
            <h3>Total Courses</h3>
            <div class="stat-number">
                <?= number_format((int)$total_courses) ?>
            </div>
            <p class="stat-note">Courses in the system</p>
        </a>

        <a href="enrollments/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-orange">▤</div>
                <span class="stat-tag">Registration</span>
            </div>
            <h3>Total Enrollments</h3>
            <div class="stat-number">
                <?= number_format((int)$total_enrollments) ?>
            </div>
            <p class="stat-note">Course enrollment records</p>
        </a>

        <a href="results/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-purple">◈</div>
                <span class="stat-tag">Assessment</span>
            </div>
            <h3>Results Recorded</h3>
            <div class="stat-number">
                <?= number_format((int)$total_results) ?>
            </div>
            <p class="stat-note">Available result records</p>
        </a>

        <a href="attendance/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-teal">◷</div>
                <span class="stat-tag">Attendance</span>
            </div>
            <h3>Attendance Records</h3>
            <div class="stat-number">
                <?= number_format((int)$total_attendance) ?>
            </div>
            <p class="stat-note">Recorded attendance entries</p>
        </a>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-green">✓</div>
                <span class="stat-tag">Accounts</span>
            </div>
            <h3>Active User Accounts</h3>
            <div class="stat-number">
                <?= number_format((int)$active_users) ?>
            </div>
            <p class="stat-note">Accounts with active status</p>
        </div>

        <a href="payments/index.php" class="stat-card">
            <div class="stat-top">
                <div class="stat-icon icon-orange">KSh</div>
                <span class="stat-tag">Finance</span>
            </div>
            <h3>Total Payments</h3>
            <div class="stat-number" style="font-size:clamp(19px,1.8vw,25px)">
                KSh <?= number_format((float)$total_payments, 2) ?>
            </div>
            <p class="stat-note">Total amount recorded</p>
        </a>

    </section>

    <!-- STUDENT STATUS AND QUICK ACTIONS -->
    <div class="content-grid">

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Student Status Overview</h2>
                    <p>Distribution of students by their current status.</p>
                </div>
                <span class="stat-tag">
                    <?= number_format((int)$student_status_total) ?> students
                </span>
            </div>

            <?php if ((int)$student_status_total === 0): ?>

                <div class="empty-status">
                    No student records are available yet. Add students to
                    see the status distribution here.
                </div>

            <?php else: ?>

                <div class="status-overview">

                    <div class="status-item">
                        <span class="status-dot dot-green"></span>
                        <span class="status-name">Active students</span>
                        <span class="status-value">
                            <?= number_format((int)$active_students) ?>
                            (<?= $active_percentage ?>%)
                        </span>
                        <div class="progress-track">
                            <div class="progress-fill fill-green"
                                 style="width:<?= $active_percentage ?>%"></div>
                        </div>
                    </div>

                    <div class="status-item">
                        <span class="status-dot dot-orange"></span>
                        <span class="status-name">Inactive students</span>
                        <span class="status-value">
                            <?= number_format((int)$inactive_students) ?>
                            (<?= $inactive_percentage ?>%)
                        </span>
                        <div class="progress-track">
                            <div class="progress-fill fill-orange"
                                 style="width:<?= $inactive_percentage ?>%"></div>
                        </div>
                    </div>

                    <div class="status-item">
                        <span class="status-dot dot-purple"></span>
                        <span class="status-name">Graduated students</span>
                        <span class="status-value">
                            <?= number_format((int)$graduated_students) ?>
                            (<?= $graduated_percentage ?>%)
                        </span>
                        <div class="progress-track">
                            <div class="progress-fill fill-purple"
                                 style="width:<?= $graduated_percentage ?>%"></div>
                        </div>
                    </div>

                    <div class="status-item">
                        <span class="status-dot dot-red"></span>
                        <span class="status-name">Suspended students</span>
                        <span class="status-value">
                            <?= number_format((int)$suspended_students) ?>
                            (<?= $suspended_percentage ?>%)
                        </span>
                        <div class="progress-track">
                            <div class="progress-fill fill-red"
                                 style="width:<?= $suspended_percentage ?>%"></div>
                        </div>
                    </div>

                </div>

            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Quick Actions</h2>
                    <p>Shortcuts to common administrative tasks.</p>
                </div>
            </div>

            <div class="action-grid">

                <a href="students/create.php" class="action-card">
                    <span class="stat-icon icon-blue">♟</span>
                    <strong>Add Student <span class="action-arrow">↗</span></strong>
                </a>

                <a href="lecturers/create.php" class="action-card">
                    <span class="stat-icon icon-green">♙</span>
                    <strong>Add Lecturer <span class="action-arrow">↗</span></strong>
                </a>

                <a href="courses/create.php" class="action-card">
                    <span class="stat-icon icon-purple">▣</span>
                    <strong>Add Course <span class="action-arrow">↗</span></strong>
                </a>

                <a href="enrollments/create.php" class="action-card">
                    <span class="stat-icon icon-orange">▤</span>
                    <strong>Enroll Student <span class="action-arrow">↗</span></strong>
                </a>

                <a href="results/create.php" class="action-card">
                    <span class="stat-icon icon-red">◈</span>
                    <strong>Enter Results <span class="action-arrow">↗</span></strong>
                </a>

                <a href="attendance/create.php" class="action-card">
                    <span class="stat-icon icon-teal">◷</span>
                    <strong>Record Attendance <span class="action-arrow">↗</span></strong>
                </a>

                <a href="payments/create.php" class="action-card">
                    <span class="stat-icon icon-orange">KSh</span>
                    <strong>Record Payment <span class="action-arrow">↗</span></strong>
                </a>

                <a href="reports/index.php" class="action-card">
                    <span class="stat-icon icon-blue">▥</span>
                    <strong>View Reports <span class="action-arrow">↗</span></strong>
                </a>

            </div>
        </section>

    </div>

    <!-- RECENT STUDENTS -->
    <div class="section-header">
        <div>
            <h2>Recently Added Students</h2>
            <p>The five most recently created student records.</p>
        </div>

        <a href="students/index.php" class="view-link">View all students →</a>
    </div>

    <section class="panel">
        <div class="table-wrap">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Student Number</th>
                        <th>Programme</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($recent_students && mysqli_num_rows($recent_students) > 0): ?>

                    <?php while ($student = mysqli_fetch_assoc($recent_students)): ?>
                        <tr>
                            <td class="student-cell">
                                <strong><?= e($student["full_name"]) ?></strong>
                                <span>Student profile</span>
                            </td>

                            <td><?= e($student["student_number"]) ?></td>

                            <td><?= e($student["program"]) ?></td>

                            <td>
                                <span class="status-badge <?= e(statusClass($student["status"])) ?>">
                                    <?= e($student["status"]) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="empty-row">
                            No students have been added yet.
                            <br>
                            <a href="students/create.php" class="view-link">
                                Add your first student →
                            </a>
                        </td>
                    </tr>

                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- RECENT COURSES -->
    <div class="section-header">
        <div>
            <h2>Recently Added Courses</h2>
            <p>A quick look at the latest courses in the system.</p>
        </div>

        <a href="courses/index.php" class="view-link">View all courses →</a>
    </div>

    <section class="panel">
        <ul class="course-list">
            <?php if ($recent_courses && mysqli_num_rows($recent_courses) > 0): ?>

                <?php while ($course = mysqli_fetch_assoc($recent_courses)): ?>
                    <li>
                        <div class="course-symbol">▣</div>

                        <div class="course-info">
                            <strong><?= e($course["course_name"]) ?></strong>
                            <span>Course in the academic catalogue</span>
                        </div>

                        <span class="course-code">
                            <?= e($course["course_code"]) ?>
                        </span>
                    </li>
                <?php endwhile; ?>

            <?php else: ?>

                <li>
                    <div class="course-info">
                        <strong>No courses available</strong>
                        <span>Add a course to populate this section.</span>
                    </div>
                </li>

            <?php endif; ?>
        </ul>
    </section>

    <footer class="footer">
        Student Management System &copy; <?= date("Y") ?>
        <br>
        Academic Management • Student Success • Better Education
    </footer>

</main>

<script>
/* Search the visible student and course records on this dashboard. */
document.getElementById("dashboardSearch").addEventListener("input", function () {
    const query = this.value.toLowerCase().trim();

    document.querySelectorAll("#studentsTable tbody tr").forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(query)
            ? ""
            : "none";
    });

    document.querySelectorAll(".course-list li").forEach(function (item) {
        item.style.display = item.innerText.toLowerCase().includes(query)
            ? ""
            : "none";
    });
});
</script>

</body>
</html>
```