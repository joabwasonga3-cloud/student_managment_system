<?php

session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check student role
if ($_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get student information
$student_sql = "
    SELECT
        student_id,
        student_number,
        full_name,
        phone,
        gender,
        program,
        year_of_study,
        admission_date,
        status
    FROM students
    WHERE user_id = ?
";

$stmt = $conn->prepare($student_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$student_result = $stmt->get_result();

if ($student_result->num_rows === 0) {
    die("Student profile not found.");
}

$student = $student_result->fetch_assoc();
$student_id = $student["student_id"];

// Count active courses
$course_sql = "
    SELECT COUNT(*) AS total_courses
    FROM enrollments
    WHERE student_id = ?
    AND status = 'active'
";

$stmt = $conn->prepare($course_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$course_count = $stmt->get_result()->fetch_assoc()["total_courses"];

// Count results
$result_sql = "
    SELECT COUNT(*) AS total_results
    FROM results r
    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id
    WHERE e.student_id = ?
";

$stmt = $conn->prepare($result_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$result_count = $stmt->get_result()->fetch_assoc()["total_results"];

// Attendance summary
$attendance_sql = "
    SELECT
        COUNT(*) AS total_attendance,
        SUM(
            CASE WHEN a.status = 'Present'
            THEN 1 ELSE 0 END
        ) AS present_count
    FROM attendance a
    INNER JOIN enrollments e
        ON a.enrollment_id = e.enrollment_id
    WHERE e.student_id = ?
";

$stmt = $conn->prepare($attendance_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$attendance = $stmt->get_result()->fetch_assoc();

$total_attendance = $attendance["total_attendance"] ?? 0;
$present_count = $attendance["present_count"] ?? 0;

$attendance_percentage = $total_attendance > 0
    ? round(($present_count / $total_attendance) * 100, 1)
    : 0;

// Payment summary
$payment_sql = "
    SELECT COALESCE(SUM(amount), 0) AS total_paid
    FROM payments
    WHERE student_id = ?
";

$stmt = $conn->prepare($payment_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$total_paid = $stmt->get_result()->fetch_assoc()["total_paid"];

// Recent courses
$courses_sql = "
    SELECT
        c.course_code,
        c.course_name,
        e.academic_year,
        e.semester,
        e.status
    FROM enrollments e
    INNER JOIN courses c
        ON e.course_id = c.course_id
    WHERE e.student_id = ?
    ORDER BY e.enrollment_id DESC
    LIMIT 5
";

$stmt = $conn->prepare($courses_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$courses = $stmt->get_result();

// Recent results
$results_sql = "
    SELECT
        c.course_code,
        c.course_name,
        r.total_marks,
        r.grade,
        r.remarks
    FROM results r
    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id
    INNER JOIN courses c
        ON e.course_id = c.course_id
    WHERE e.student_id = ?
    ORDER BY r.result_id DESC
    LIMIT 5
";

$stmt = $conn->prepare($results_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$results = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    color: #222;
}

/* Sidebar */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    height: 100vh;
    background: #1f2937;
    color: white;
    padding: 20px;
    overflow-y: auto;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 12px;
    margin-bottom: 8px;
    border-radius: 5px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #374151;
}

.logout {
    margin-top: 30px;
    background: #dc2626;
}

/* Main */

.main {
    margin-left: 240px;
    padding: 30px;
}

.header {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.header h1 {
    margin-bottom: 10px;
}

.header p {
    color: #666;
}

/* Cards */

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.card h3 {
    color: #666;
    font-size: 15px;
    margin-bottom: 12px;
}

.card .number {
    font-size: 27px;
    font-weight: bold;
    color: #1f2937;
}

/* Sections */

.section {
    background: white;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    overflow-x: auto;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.section-header h2 {
    font-size: 20px;
}

.view-link {
    color: #2563eb;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.view-link:hover {
    text-decoration: underline;
}

/* Tables */

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 500px;
}

th, td {
    padding: 13px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #f3f4f6;
}

tr:hover {
    background: #f9fafb;
}

/* Badges */

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 13px;
}

.active {
    background: #dcfce7;
    color: #166534;
}

.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.completed {
    background: #dbeafe;
    color: #1e40af;
}

.dropped {
    background: #fee2e2;
    color: #991b1b;
}

.summary-text {
    line-height: 2;
    color: #444;
}

.empty {
    text-align: center;
    padding: 25px;
    color: #777;
}

/* Responsive */

@media (max-width: 1100px) {
    .cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .cards {
        grid-template-columns: 1fr;
    }

    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>

</head>

<body>

<!-- Sidebar -->

<div class="sidebar">

    <h2>Student Portal</h2>

    <a href="dashboard.php" class="active">Dashboard</a>

    <a href="profile.php">My Profile</a>

    <a href="courses.php">My Courses</a>

    <a href="results.php">My Results</a>

    <a href="attendance.php">My Attendance</a>

    <a href="payments.php">My Payments</a>

    <a href="../auth/logout.php" class="logout">Logout</a>

</div>

<!-- Main Content -->

<div class="main">

    <div class="header">

        <h1>
            Welcome, <?php echo htmlspecialchars($student["full_name"]); ?>!
        </h1>

        <p>
            Student Number:
            <strong>
                <?php echo htmlspecialchars($student["student_number"]); ?>
            </strong>
        </p>

        <p>
            Program:
            <strong>
                <?php echo htmlspecialchars($student["program"]); ?>
            </strong>
        </p>

    </div>

    <!-- Summary Cards -->

    <div class="cards">

        <div class="card">
            <h3>Enrolled Courses</h3>
            <div class="number">
                <?php echo $course_count; ?>
            </div>
        </div>

        <div class="card">
            <h3>Published Results</h3>
            <div class="number">
                <?php echo $result_count; ?>
            </div>
        </div>

        <div class="card">
            <h3>Attendance</h3>
            <div class="number">
                <?php echo $attendance_percentage; ?>%
            </div>
        </div>

        <div class="card">
            <h3>Total Paid</h3>
            <div class="number">
                KSh <?php echo number_format($total_paid, 2); ?>
            </div>
        </div>

    </div>

    <!-- Student Information -->

    <div class="section">

        <div class="section-header">
            <h2>My Information</h2>
            <a href="profile.php" class="view-link">View Full Profile →</a>
        </div>

        <table>

            <tr>
                <th>Student Number</th>
                <td><?php echo htmlspecialchars($student["student_number"]); ?></td>
            </tr>

            <tr>
                <th>Full Name</th>
                <td><?php echo htmlspecialchars($student["full_name"]); ?></td>
            </tr>

            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($student["phone"]); ?></td>
            </tr>

            <tr>
                <th>Gender</th>
                <td><?php echo htmlspecialchars($student["gender"]); ?></td>
            </tr>

            <tr>
                <th>Year of Study</th>
                <td><?php echo htmlspecialchars($student["year_of_study"]); ?></td>
            </tr>

            <tr>
                <th>Admission Date</th>
                <td><?php echo htmlspecialchars($student["admission_date"]); ?></td>
            </tr>

            <tr>
                <th>Status</th>
                <td>
                    <span class="badge <?php echo htmlspecialchars(strtolower($student["status"])); ?>">
                        <?php echo htmlspecialchars($student["status"]); ?>
                    </span>
                </td>
            </tr>

        </table>

    </div>

    <!-- Recent Courses -->

    <div class="section">

        <div class="section-header">
            <h2>My Recent Courses</h2>
            <a href="courses.php" class="view-link">View All Courses →</a>
        </div>

        <?php if ($courses->num_rows > 0): ?>

        <table>

            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($course = $courses->fetch_assoc()): ?>

                <tr>

                    <td><?php echo htmlspecialchars($course["course_code"]); ?></td>

                    <td><?php echo htmlspecialchars($course["course_name"]); ?></td>

                    <td><?php echo htmlspecialchars($course["academic_year"]); ?></td>

                    <td><?php echo htmlspecialchars($course["semester"]); ?></td>

                    <td>
                        <span class="badge <?php echo htmlspecialchars(strtolower($course["status"])); ?>">
                            <?php echo htmlspecialchars($course["status"]); ?>
                        </span>
                    </td>

                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

        <?php else: ?>

        <div class="empty">
            You are not currently enrolled in any courses.
        </div>

        <?php endif; ?>

    </div>

    <!-- Recent Results -->

    <div class="section">

        <div class="section-header">
            <h2>Recent Results</h2>
            <a href="results.php" class="view-link">View All Results →</a>
        </div>

        <?php if ($results->num_rows > 0): ?>

        <table>

            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Total Marks</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>

                <?php while ($result = $results->fetch_assoc()): ?>

                <tr>

                    <td><?php echo htmlspecialchars($result["course_code"]); ?></td>

                    <td><?php echo htmlspecialchars($result["course_name"]); ?></td>

                    <td><?php echo htmlspecialchars($result["total_marks"]); ?></td>

                    <td><?php echo htmlspecialchars($result["grade"]); ?></td>

                    <td><?php echo htmlspecialchars($result["remarks"] ?? ""); ?></td>

                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

        <?php else: ?>

        <div class="empty">
            No results have been published yet.
        </div>

        <?php endif; ?>

    </div>

    <!-- Attendance Summary -->

    <div class="section">

        <div class="section-header">
            <h2>Attendance Summary</h2>
            <a href="attendance.php" class="view-link">View Attendance →</a>
        </div>

        <div class="summary-text">

            <p>
                Total Attendance Records:
                <strong><?php echo $total_attendance; ?></strong>
            </p>

            <p>
                Present:
                <strong><?php echo $present_count; ?></strong>
            </p>

            <p>
                Attendance Percentage:
                <strong><?php echo $attendance_percentage; ?>%</strong>
            </p>

        </div>

    </div>

    <!-- Payment Summary -->

    <div class="section">

        <div class="section-header">
            <h2>Payment Summary</h2>
            <a href="payments.php" class="view-link">View Payments →</a>
        </div>

        <div class="summary-text">

            <p>
                Total Amount Paid:
                <strong>
                    KSh <?php echo number_format($total_paid, 2); ?>
                </strong>
            </p>

        </div>

    </div>

</div>

</body>
</html>