<?php

session_start();

require_once "../config/database.php";

// Check if user is logged in
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
    SELECT student_id, student_number, full_name
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

// Get student's results
$sql = "
    SELECT
        c.course_code,
        c.course_name,
        r.cat_marks,
        r.exam_marks,
        r.total_marks,
        r.grade,
        r.remarks,
        e.academic_year,
        e.semester
    FROM results r
    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id
    INNER JOIN courses c
        ON e.course_id = c.course_id
    WHERE e.student_id = ?
    ORDER BY e.academic_year DESC, e.semester ASC, c.course_code ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$results = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Results - Student Management System</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
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
.sidebar .active {
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
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.header h1 {
    margin-bottom: 8px;
}

/* Results */

.results-container {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    overflow-x: auto;
}

.results-container h2 {
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
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

.grade {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 15px;
    background: #dbeafe;
    color: #1e40af;
    font-weight: bold;
}

.no-results {
    text-align: center;
    padding: 30px;
    color: #666;
}

@media (max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
    }

}

</style>

</head>

<body>

<!-- Sidebar -->

<div class="sidebar">

    <h2>Student Portal</h2>

    <a href="dashboard.php">Dashboard</a>

    <a href="profile.php">My Profile</a>

    <a href="courses.php">My Courses</a>

    <a href="results.php" class="active">My Results</a>

    <a href="dashboard.php#attendance">Attendance</a>

    <a href="dashboard.php#payments">Payments</a>

    <a href="../auth/logout.php" class="logout">Logout</a>

</div>

<!-- Main Content -->

<div class="main">

    <div class="header">

        <h1>My Results</h1>

        <p>
            Student:
            <strong>
                <?php echo htmlspecialchars($student["full_name"]); ?>
            </strong>

            &nbsp; | &nbsp;

            Student Number:
            <strong>
                <?php echo htmlspecialchars($student["student_number"]); ?>
            </strong>
        </p>

    </div>

    <div class="results-container">

        <h2>Academic Performance</h2>

        <?php if ($results->num_rows > 0): ?>

        <table>

            <thead>

                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>CAT (30)</th>
                    <th>Exam (70)</th>
                    <th>Total (100)</th>
                    <th>Grade</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Remarks</th>
                </tr>

            </thead>

            <tbody>

                <?php while ($result = $results->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($result["course_code"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($result["course_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($result["cat_marks"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($result["exam_marks"]); ?>
                    </td>

                    <td>
                        <strong>
                            <?php echo htmlspecialchars($result["total_marks"]); ?>
                        </strong>
                    </td>

                    <td>
                        <span class="grade">
                            <?php echo htmlspecialchars($result["grade"]); ?>
                        </span>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($result["academic_year"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($result["semester"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($result["remarks"] ?? ""); ?>
                    </td>

                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

        <?php else: ?>

        <div class="no-results">

            <h3>No Results Available</h3>

            <p>Your academic results have not yet been published.</p>

        </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>