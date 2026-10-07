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

/* Get courses */
$stmt = $conn->prepare("
    SELECT
        course_id,
        course_code,
        course_name,
        description,
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

<title>My Courses | Lecturer Portal</title>

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
    left: 0;
    top: 0;
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
    text-decoration: none;
    color: #cbd5e1;
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

.header {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 8px;
}

.header p {
    margin: 0;
    color: #64748b;
}

.course-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.course-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

.course-code {
    font-size: 14px;
    font-weight: bold;
    color: #2563eb;
    margin-bottom: 10px;
}

.course-card h2 {
    margin: 0 0 15px;
}

.course-card p {
    color: #64748b;
    line-height: 1.5;
}

.credit {
    display: inline-block;
    margin-top: 15px;
    padding: 6px 10px;
    border-radius: 15px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 13px;
}

.empty {
    background: white;
    padding: 30px;
    border-radius: 12px;
    color: #64748b;
}

.logout {
    color: #fecaca !important;
}

@media(max-width: 900px) {
    .course-grid {
        grid-template-columns: repeat(2, 1fr);
    }
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

    .course-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <h2>Lecturer Portal</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="courses.php" class="active">My Courses</a>
    <a href="students.php">My Students</a>
    <a href="attendance.php">Attendance</a>
    <a href="results.php">Manage Results</a>
    <a href="../auth/logout.php" class="logout">Logout</a>

</div>

<div class="main">

    <div class="header">
        <h1>My Courses</h1>
        <p>
            Courses assigned to
            <strong><?php echo e($lecturer["full_name"]); ?></strong>
        </p>
    </div>

    <?php if ($courses->num_rows > 0): ?>

        <div class="course-grid">

            <?php while ($course = $courses->fetch_assoc()): ?>

                <div class="course-card">

                    <div class="course-code">
                        <?php echo e($course["course_code"]); ?>
                    </div>

                    <h2>
                        <?php echo e($course["course_name"]); ?>
                    </h2>

                    <p>
                        <?php
                        echo e(
                            $course["description"]
                            ?: "No course description available."
                        );
                        ?>
                    </p>

                    <span class="credit">
                        <?php echo e($course["credit_hours"]); ?> Credit Hours
                    </span>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">
            No courses have been assigned to you yet.
        </div>

    <?php endif; ?>

</div>

</body>
</html>