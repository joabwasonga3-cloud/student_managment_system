<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "lecturer") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* Get lecturer */
$stmt = $conn->prepare("
    SELECT lecturer_id, full_name, department
    FROM lecturers
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$lecturer = $stmt->get_result()->fetch_assoc();

if (!$lecturer) {
    die("Lecturer profile not found.");
}

$lecturer_id = $lecturer["lecturer_id"];

/* Get students enrolled in lecturer's courses */
$stmt = $conn->prepare("
    SELECT DISTINCT
        s.student_id,
        s.student_number,
        s.full_name,
        s.gender,
        s.program,
        s.year_of_study,
        s.status
    FROM students s
    INNER JOIN enrollments e
        ON s.student_id = e.student_id
    INNER JOIN courses c
        ON e.course_id = c.course_id
    WHERE c.lecturer_id = ?
    ORDER BY s.full_name ASC
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$students = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Students | Lecturer Portal</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
}

.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    background: #0f172a;
    padding: 25px 15px;
}

.sidebar h2 {
    color: white;
    text-align: center;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 13px 15px;
    border-radius: 7px;
    margin-bottom: 8px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #1e40af;
    color: white;
}

.main {
    margin-left: 240px;
    padding: 30px;
}

.header,
.table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
}

.header {
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 8px;
}

.header p {
    margin: 0;
    color: #64748b;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 14px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

th {
    background: #f8fafc;
}

.badge {
    padding: 5px 10px;
    border-radius: 15px;
    background: #dcfce7;
    color: #166534;
    font-size: 12px;
}

.logout {
    color: #fecaca !important;
}

@media(max-width: 650px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <h2>Lecturer Portal</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="courses.php">My Courses</a>
    <a href="students.php" class="active">My Students</a>
    <a href="attendance.php">Attendance</a>
    <a href="results.php">Manage Results</a>
    <a href="../auth/logout.php" class="logout">Logout</a>

</div>

<div class="main">

    <div class="header">
        <h1>My Students</h1>
        <p>
            Students enrolled in your assigned courses.
        </p>
    </div>

    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Program</th>
                    <th>Year</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($students->num_rows > 0): ?>

                <?php while ($student = $students->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo e($student["student_number"]); ?>
                        </td>

                        <td>
                            <?php echo e($student["full_name"]); ?>
                        </td>

                        <td>
                            <?php echo e($student["gender"]); ?>
                        </td>

                        <td>
                            <?php echo e($student["program"]); ?>
                        </td>

                        <td>
                            <?php echo e($student["year_of_study"]); ?>
                        </td>

                        <td>
                            <span class="badge">
                                <?php echo e($student["status"]); ?>
                            </span>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="6">
                        No students are currently enrolled in your courses.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>