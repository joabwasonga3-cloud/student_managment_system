

<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION AND AUTHORIZATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$email = $_SESSION["email"];

/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
| Each query returns a single aggregate value.
| If a query fails, the dashboard remains accessible and displays 0.
*/

function getStatistic($conn, $sql, $column)
{
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        error_log("Dashboard statistics error: " . mysqli_error($conn));
        return 0;
    }

    $data = mysqli_fetch_assoc($result);

    return $data[$column] ?? 0;
}

// Students
$total_students = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM students",
    "total"
);

// Lecturers
$total_lecturers = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM lecturers",
    "total"
);

// Courses
$total_courses = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM courses",
    "total"
);

// Enrollments
$total_enrollments = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM enrollments",
    "total"
);

// Results
$total_results = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM results",
    "total"
);

// Attendance records
$total_attendance = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM attendance",
    "total"
);

// Active users
$active_users = getStatistic(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE status = 'active'",
    "total"
);

// Total payments recorded
$total_payments = getStatistic(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total FROM payments",
    "total"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Student Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 245px;
            height: 100vh;
            overflow-y: auto;
            background-color: #1f2937;
            color: white;
            padding: 22px 16px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 8px;
            font-size: 22px;
        }

        .sidebar .system-name {
            text-align: center;
            font-size: 12px;
            color: #cbd5e1;
            margin-bottom: 28px;
        }

        .sidebar .menu-heading {
            color: #9ca3af;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 10px 10px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 5px;
        }

        .sidebar ul li a {
            display: block;
            padding: 11px 12px;
            color: #e5e7eb;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .sidebar ul li a:hover {
            background-color: #374151;
            color: white;
        }

        .sidebar ul li a.active {
            background-color: #2563eb;
            color: white;
        }

        .logout {
            margin-top: 28px;
            padding-bottom: 10px;
        }

        .logout a {
            display: block;
            background-color: #dc3545;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 6px;
            text-decoration: none;
        }

        .logout a:hover {
            background-color: #b02a37;
        }

        /* MAIN CONTENT */

        .main-content {
            margin-left: 245px;
            padding: 25px;
            min-height: 100vh;
        }

        /* TOP BAR */

        .topbar {
            background-color: white;
            padding: 20px 24px;
            border-radius: 9px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .topbar h1 {
            font-size: 25px;
            margin-bottom: 5px;
        }

        .topbar p {
            color: #6b7280;
            font-size: 13px;
        }

        .admin-info {
            color: #555;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        /* SECTION HEADINGS */

        .section-heading {
            margin-bottom: 17px;
            font-size: 20px;
        }

        /* STATISTICS CARDS */

        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 32px;
        }

        .card {
            background-color: white;
            padding: 22px;
            border-radius: 9px;
            border-left: 4px solid #2563eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            min-width: 0;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.10);
        }

        .card h3 {
            color: #6b7280;
            font-size: 13px;
            font-weight: normal;
            margin-bottom: 12px;
        }

        .card .number {
            color: #2563eb;
            font-size: 28px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .card .description {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 9px;
        }

        .card.green {
            border-left-color: #16a34a;
        }

        .card.green .number {
            color: #16a34a;
        }

        .card.orange {
            border-left-color: #ea580c;
        }

        .card.orange .number {
            color: #ea580c;
        }

        .card.purple {
            border-left-color: #7c3aed;
        }

        .card.purple .number {
            color: #7c3aed;
        }

        /* QUICK ACTIONS */

        .quick-actions {
            background-color: white;
            padding: 24px;
            border-radius: 9px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .quick-actions p {
            color: #6b7280;
            font-size: 14px;
            margin-top: -7px;
            margin-bottom: 20px;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
        }

        .action-btn {
            display: block;
            padding: 15px 10px;
            background-color: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 7px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .action-btn:hover {
            background-color: #e5e7eb;
        }

        .action-btn.primary {
            background-color: #2563eb;
            color: white;
        }

        .action-btn.primary:hover {
            background-color: #1d4ed8;
        }

        /* WELCOME SECTION */

        .welcome {
            background-color: white;
            padding: 26px;
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 25px;
        }

        .welcome h2 {
            margin-bottom: 13px;
            font-size: 21px;
        }

        .welcome p {
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 8px;
        }

        /* RESPONSIVE DESIGN */

        @media (max-width: 1250px) {
            .cards {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .action-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .main-content {
                margin-left: 210px;
                padding: 18px;
            }

            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                overflow-y: visible;
            }

            .sidebar h2 {
                margin-top: 5px;
            }

            .main-content {
                margin-left: 0;
                padding: 14px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
                padding: 18px;
            }

            .topbar h1 {
                font-size: 22px;
            }

            .cards {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions,
            .welcome {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

    <h2>Admin Panel</h2>

    <p class="system-name">Student Management System</p>

    <ul>
        <li>
            <a href="dashboard.php" class="active">
                Dashboard
            </a>
        </li>
    </ul>

    <p class="menu-heading">Academic Management</p>

    <ul>
        <li>
            <a href="students/index.php">Students</a>
        </li>

        <li>
            <a href="lecturers/index.php">Lecturers</a>
        </li>

        <li>
            <a href="courses/index.php">Courses</a>
        </li>

        <li>
            <a href="enrollments/index.php">Enrollments</a>
        </li>

        <li>
            <a href="results/index.php">Results</a>
        </li>

        <li>
            <a href="attendance/index.php">Attendance</a>
        </li>
    </ul>

    <p class="menu-heading">Finance & Administration</p>

    <ul>
        <li>
            <a href="payments/index.php">Payments</a>
        </li>

        <li>
            <a href="reports/index.php">Reports</a>
        </li>
    </ul>

    <div class="logout">
        <a href="../auth/logout.php">Logout</a>
    </div>

</aside>

<!-- MAIN CONTENT -->

<main class="main-content">

    <!-- TOP BAR -->

    <header class="topbar">

        <div>
            <h1>Admin Dashboard</h1>
            <p>Overview of your Student Management System</p>
        </div>

        <div class="admin-info">
            Logged in as:
            <strong>
                <?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>
            </strong>
        </div>

    </header>

    <!-- DASHBOARD STATISTICS -->

    <h2 class="section-heading">System Overview</h2>

    <section class="cards">

        <div class="card">
            <h3>Total Students</h3>
            <div class="number">
                <?= number_format((int) $total_students) ?>
            </div>
            <p class="description">Student records</p>
        </div>

        <div class="card green">
            <h3>Total Lecturers</h3>
            <div class="number">
                <?= number_format((int) $total_lecturers) ?>
            </div>
            <p class="description">Lecturer records</p>
        </div>

        <div class="card purple">
            <h3>Total Courses</h3>
            <div class="number">
                <?= number_format((int) $total_courses) ?>
            </div>
            <p class="description">Available courses</p>
        </div>

        <div class="card orange">
            <h3>Total Enrollments</h3>
            <div class="number">
                <?= number_format((int) $total_enrollments) ?>
            </div>
            <p class="description">Course registrations</p>
        </div>

        <div class="card">
            <h3>Results Recorded</h3>
            <div class="number">
                <?= number_format((int) $total_results) ?>
            </div>
            <p class="description">Result records entered</p>
        </div>

        <div class="card green">
            <h3>Attendance Records</h3>
            <div class="number">
                <?= number_format((int) $total_attendance) ?>
            </div>
            <p class="description">Attendance entries</p>
        </div>

        <div class="card purple">
            <h3>Active Users</h3>
            <div class="number">
                <?= number_format((int) $active_users) ?>
            </div>
            <p class="description">Active system accounts</p>
        </div>

        <div class="card orange">
            <h3>Total Payments</h3>
            <div class="number">
                KSh <?= number_format((float) $total_payments, 2) ?>
            </div>
            <p class="description">Amount recorded in payments</p>
        </div>

    </section>

    <!-- QUICK ACTIONS -->

    <section class="quick-actions">

        <h2 class="section-heading">Quick Actions</h2>

        <p>Use these shortcuts to perform common administrative tasks.</p>

        <div class="action-grid">

            <a href="students/create.php"
               class="action-btn primary">
                + Add Student
            </a>

            <a href="lecturers/create.php"
               class="action-btn">
                + Add Lecturer
            </a>

            <a href="courses/create.php"
               class="action-btn">
                + Add Course
            </a>

            <a href="enrollments/create.php"
               class="action-btn">
                + Enroll Student
            </a>

            <a href="results/create.php"
               class="action-btn">
                + Record Results
            </a>

            <a href="attendance/create.php"
               class="action-btn">
                + Record Attendance
            </a>

            <a href="payments/create.php"
               class="action-btn">
                + Record Payment
            </a>

            <a href="reports/index.php"
               class="action-btn">
                View Reports
            </a>

        </div>

    </section>

    <!-- WELCOME SECTION -->

    <section class="welcome">

        <h2>Welcome, Administrator!</h2>

        <p>
            You have successfully logged into the
            Student Management System.
        </p>

        <p>
            Use the Academic Management section to manage students,
            lecturers, courses, enrollments, results and attendance.
        </p>

        <p>
            The Finance and Administration section provides access
            to student payment records and system reports.
        </p>

        <p>
            The statistics above summarize the records currently
            stored in your database. They update when the page is
            refreshed after records are added, edited or deleted.
        </p>

    </section>

</main>

</body>
</html>
