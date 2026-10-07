<?php
session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get student profile
$stmt = $conn->prepare(
    "SELECT student_id, full_name, student_number
     FROM students
     WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student profile not found. Please contact the administrator.");
}

$student_id = $student["student_id"];

// Attendance summary
$stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_classes,
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
        SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) AS late_count
     FROM attendance a
     INNER JOIN enrollments e ON a.enrollment_id = e.enrollment_id
     WHERE e.student_id = ?"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$summary = $stmt->get_result()->fetch_assoc();

$total_classes = (int)($summary["total_classes"] ?? 0);
$present = (int)($summary["present_count"] ?? 0);
$absent = (int)($summary["absent_count"] ?? 0);
$late = (int)($summary["late_count"] ?? 0);

$attendance_percentage = $total_classes > 0
    ? round(($present / $total_classes) * 100, 1)
    : 0;

// Fetch attendance records
$stmt = $conn->prepare(
    "SELECT
        c.course_code,
        c.course_name,
        a.date,
        a.status,
        a.remarks
     FROM attendance a
     INNER JOIN enrollments e ON a.enrollment_id = e.enrollment_id
     INNER JOIN courses c ON e.course_id = c.course_id
     WHERE e.student_id = ?
     ORDER BY a.date DESC, a.attendance_id DESC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$attendance_records = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Attendance | Student Portal</title>

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
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
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
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 22px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.card h3 {
    color: #64748b;
    font-size: 14px;
    margin-top: 0;
}

.card p {
    font-size: 27px;
    font-weight: bold;
    margin-bottom: 0;
}

.table-container {
    background: white;
    padding: 22px;
    border-radius: 10px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    padding: 13px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

th {
    background: #f8fafc;
}

.status {
    padding: 6px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}

.Present {
    background: #dcfce7;
    color: #166534;
}

.Absent {
    background: #fee2e2;
    color: #991b1b;
}

.Late {
    background: #fef3c7;
    color: #92400e;
}

.back-link {
    display: inline-block;
    margin-bottom: 18px;
    color: #2563eb;
    text-decoration: none;
}

@media (max-width: 900px) {
    .cards {
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

    .cards {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>Student Portal</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="courses.php">My Courses</a>
    <a href="results.php">My Results</a>
    <a href="attendance.php" class="active">My Attendance</a>
    <a href="payments.php">My Payments</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <div class="header">
        <h2>My Attendance</h2>
        <p>
            Student: <?php echo e($student["full_name"]); ?>
            |
            Student Number: <?php echo e($student["student_number"]); ?>
        </p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>Total Classes Recorded</h3>
            <p><?php echo $total_classes; ?></p>
        </div>

        <div class="card">
            <h3>Present</h3>
            <p><?php echo $present; ?></p>
        </div>

        <div class="card">
            <h3>Absent</h3>
            <p><?php echo $absent; ?></p>
        </div>

        <div class="card">
            <h3>Attendance Percentage</h3>
            <p><?php echo $attendance_percentage; ?>%</p>
        </div>

    </div>

    <div class="table-container">

        <h3>Attendance History</h3>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($attendance_records->num_rows > 0): ?>

                <?php $count = 1; ?>

                <?php while ($row = $attendance_records->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo e($row["course_code"]); ?></td>
                        <td><?php echo e($row["course_name"]); ?></td>
                        <td><?php echo e($row["date"]); ?></td>
                        <td>
                            <span class="status <?php echo e($row["status"]); ?>">
                                <?php echo e($row["status"]); ?>
                            </span>
                        </td>
                        <td><?php echo e($row["remarks"] ?: "—"); ?></td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="6" style="text-align:center;">
                        No attendance records available yet.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>