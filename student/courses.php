<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Make sure the user is a student
if ($_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Student Information
|--------------------------------------------------------------------------
*/

$student_sql = "
    SELECT
        student_id,
        student_number,
        full_name
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


/*
|--------------------------------------------------------------------------
| Get Student Courses
|--------------------------------------------------------------------------
*/

$courses_sql = "
    SELECT
        c.course_code,
        c.course_name,
        c.description,
        c.credit_hours,
        l.full_name AS lecturer_name,
        e.academic_year,
        e.semester,
        e.enrollment_date,
        e.status
    FROM enrollments e

    INNER JOIN courses c
        ON e.course_id = c.course_id

    INNER JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id

    WHERE e.student_id = ?

    ORDER BY e.academic_year DESC, e.semester ASC, c.course_code ASC
";

$stmt = $conn->prepare($courses_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

$courses = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Courses - Student Management System</title>

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

        .sidebar a:hover {
            background: #374151;
        }

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

        /* Course table */

        .course-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        .course-container h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 13px;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-dropped {
            background: #fee2e2;
            color: #991b1b;
        }

        .no-courses {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        /* Responsive */

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

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="profile.php">
        My Profile
    </a>

    <a href="courses.php" class="active">
        My Courses
    </a>

    <a href="dashboard.php#results">
        My Results
    </a>

    <a href="dashboard.php#attendance">
        Attendance
    </a>

    <a href="dashboard.php#payments">
        Payments
    </a>

    <a href="../auth/logout.php" class="logout">
        Logout
    </a>

</div>


<!-- Main Content -->

<div class="main">

    <div class="header">

        <h1>My Courses</h1>

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


    <!-- Courses -->

    <div class="course-container">

        <h2>Enrolled Courses</h2>

        <?php if ($courses->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>Course Code</th>

                        <th>Course Name</th>

                        <th>Lecturer</th>

                        <th>Credit Hours</th>

                        <th>Academic Year</th>

                        <th>Semester</th>

                        <th>Enrollment Date</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    <?php while ($course = $courses->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($course["course_code"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($course["course_name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($course["lecturer_name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($course["credit_hours"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($course["academic_year"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($course["semester"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($course["enrollment_date"]); ?>
                            </td>

                            <td>

                                <?php if ($course["status"] === "active"): ?>

                                    <span class="status status-active">
                                        Active
                                    </span>

                                <?php elseif ($course["status"] === "completed"): ?>

                                    <span class="status status-completed">
                                        Completed
                                    </span>

                                <?php else: ?>

                                    <span class="status status-dropped">
                                        Dropped
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="no-courses">

                <p>
                    You are not currently enrolled in any courses.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>