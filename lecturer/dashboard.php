<?php
session_start();

require_once "../config/database.php";

// Check whether user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Ensure only lecturers can access this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "lecturer") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch lecturer information
$stmt = $conn->prepare("
    SELECT lecturer_id, full_name, department
    FROM lecturers
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$lecturer = $stmt->get_result()->fetch_assoc();

if (!$lecturer) {
    die("Lecturer profile not found. Please contact the administrator.");
}

$lecturer_id = $lecturer["lecturer_id"];
$full_name = $lecturer["full_name"];
$department = $lecturer["department"];

// Count assigned courses
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM courses
    WHERE lecturer_id = ?
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$total_courses = $stmt->get_result()->fetch_assoc()["total"];

// Count distinct students enrolled in lecturer's courses
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT e.student_id) AS total
    FROM enrollments e
    INNER JOIN courses c ON e.course_id = c.course_id
    WHERE c.lecturer_id = ?
    AND e.status = 'active'
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$total_students = $stmt->get_result()->fetch_assoc()["total"];

// Count attendance records
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM attendance a
    INNER JOIN enrollments e ON a.enrollment_id = e.enrollment_id
    INNER JOIN courses c ON e.course_id = c.course_id
    WHERE c.lecturer_id = ?
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$total_attendance = $stmt->get_result()->fetch_assoc()["total"];

// Count recorded results
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM results r
    INNER JOIN enrollments e ON r.enrollment_id = e.enrollment_id
    INNER JOIN courses c ON e.course_id = c.course_id
    WHERE c.lecturer_id = ?
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$total_results = $stmt->get_result()->fetch_assoc()["total"];

// Fetch lecturer's courses
$stmt = $conn->prepare("
    SELECT
        course_id,
        course_code,
        course_name,
        credit_hours
    FROM courses
    WHERE lecturer_id = ?
    ORDER BY course_code ASC
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$courses = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lecturer Dashboard</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    color: #333;
}

.sidebar {
    width: 240px;
    height: 100vh;
    background: #1e293b;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    padding: 25px 15px;
    overflow-y: auto;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 21px;
}

.sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 13px 15px;
    border-radius: 6px;
    margin-bottom: 8px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #334155;
    color: white;
}

.main {
    margin-left: 240px;
    padding: 30px;
}

.header {
    background: white;
    padding: 22px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.header h1 {
    margin: 0 0 10px;
    font-size: 25px;
}

.header p {
    margin: 0;
    color: #64748b;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-card h3 {
    margin: 0 0 15px;
    font-size: 15px;
    color: #64748b;
}

.stat-card p {
    margin: 0;
    font-size: 30px;
    font-weight: bold;
    color: #2563eb;
}

.section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section h2 {
    margin-top: 0;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

th {
    background: #f8fafc;
    color: #475569;
}

.empty {
    color: #64748b;
    padding: 20px 0;
}

.logout {
    color: #fecaca !important;
}

@media (max-width: 1000px) {
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 650px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .section {
        overflow-x: auto;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <h2>Lecturer Portal</h2>

    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="courses.php">My Courses</a>
    <a href="students.php">My Students</a>
    <a href="attendance.php">Attendance</a>
    <a href="results.php">Manage Results</a>
    <a href="../auth/logout.php" class="logout">Logout</a>

</div>

<div class="main">

    <div class="header">
        <h1>Welcome, <?php echo e($full_name); ?>!</h1>
        <p>
            Department: <?php echo e($department); ?>
            | Lecturer Dashboard
        </p>
    </div>

    <div class="stats">

        <div class="stat-card">
            <h3>My Courses</h3>
            <p><?php echo $total_courses; ?></p>
        </div>

        <div class="stat-card">
            <h3>My Students</h3>
            <p><?php echo $total_students; ?></p>
        </div>

        <div class="stat-card">
            <h3>Attendance Records</h3>
            <p><?php echo $total_attendance; ?></p>
        </div>

        <div class="stat-card">
            <h3>Results Recorded</h3>
            <p><?php echo $total_results; ?></p>
        </div>

    </div>

    <div class="section">

        <h2>My Assigned Courses</h2>

        <table>

            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credit Hours</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($courses->num_rows > 0): ?>

                <?php while ($course = $courses->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo e($course["course_code"]); ?></td>
                        <td><?php echo e($course["course_name"]); ?></td>
                        <td><?php echo e($course["credit_hours"]); ?></td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="3" class="empty">
                        No courses have been assigned to you yet.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>