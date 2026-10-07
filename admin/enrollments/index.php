<?php

session_start();

require_once "../../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Only admin can access this page
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// Fetch all enrollments
$sql = "
    SELECT
        e.enrollment_id,
        e.student_id,
        e.course_id,
        e.academic_year,
        e.semester,
        e.enrollment_date,
        e.status,

        s.student_number,
        s.full_name AS student_name,
        s.program,

        c.course_code,
        c.course_name,

        l.full_name AS lecturer_name

    FROM enrollments e

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    LEFT JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id

    ORDER BY e.enrollment_id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Enrollments - Student Management System</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
        }

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

        .content {
            margin-left: 240px;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 {
            color: #222;
        }

        .add-btn {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 5px;
        }

        .add-btn:hover {
            background: #1d4ed8;
        }

        .message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #1f2937;
            color: white;
        }

        tr:hover {
            background: #f9fafb;
        }

        .action-link {
            text-decoration: none;
            margin-right: 8px;
            font-weight: bold;
        }

        .view-link {
            color: #2563eb;
        }

        .delete-link {
            color: #dc2626;
        }

        .status-active {
            color: #059669;
            font-weight: bold;
        }

        .status-completed {
            color: #2563eb;
            font-weight: bold;
        }

        .status-dropped {
            color: #dc2626;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 25px;
        }

    </style>

</head>

<body>


<div class="sidebar">

    <h2>Admin Panel</h2>

    <a href="../dashboard.php">
        Dashboard
    </a>

    <a href="../students/index.php">
        Students
    </a>

    <a href="../lecturers/index.php">
        Lecturers
    </a>

    <a href="../courses/index.php">
        Courses
    </a>

    <a href="index.php">
        Enrollments
    </a>

    <a href="../../auth/logout.php">
        Logout
    </a>

</div>


<div class="content">

    <div class="topbar">

        <h1>Enrollment Management</h1>

        <a href="create.php" class="add-btn">
            + Enroll Student
        </a>

    </div>


    <?php if (isset($_GET["success"])): ?>

        <?php if ($_GET["success"] === "added"): ?>

            <div class="message">
                Student enrolled successfully.
            </div>

        <?php elseif ($_GET["success"] === "deleted"): ?>

            <div class="message">
                Enrollment removed successfully.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <table>

        <thead>

            <tr>

                <th>#</th>

                <th>Student</th>

                <th>Program</th>

                <th>Course</th>

                <th>Lecturer</th>

                <th>Academic Year</th>

                <th>Semester</th>

                <th>Date</th>

                <th>Status</th>

                <th>Actions</th>

            </tr>

        </thead>


        <tbody>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php $count = 1; ?>

            <?php while ($enrollment = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo $count++; ?>
                    </td>


                    <td>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $enrollment["student_name"]
                            );
                            ?>
                        </strong>

                        <br>

                        <small>
                            <?php
                            echo htmlspecialchars(
                                $enrollment["student_number"]
                            );
                            ?>
                        </small>

                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $enrollment["program"]
                        );
                        ?>
                    </td>


                    <td>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $enrollment["course_code"]
                            );
                            ?>
                        </strong>

                        <br>

                        <?php
                        echo htmlspecialchars(
                            $enrollment["course_name"]
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $enrollment["lecturer_name"]
                            ?? "Not assigned"
                        );
                        ?>

                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $enrollment["academic_year"]
                        );
                        ?>
                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $enrollment["semester"]
                        );
                        ?>
                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $enrollment["enrollment_date"]
                        );
                        ?>
                    </td>


                    <td>

                        <?php

                        $status_class = "";

                        if ($enrollment["status"] === "active") {
                            $status_class = "status-active";
                        } elseif ($enrollment["status"] === "completed") {
                            $status_class = "status-completed";
                        } elseif ($enrollment["status"] === "dropped") {
                            $status_class = "status-dropped";
                        }

                        ?>

                        <span class="<?php echo $status_class; ?>">

                            <?php
                            echo htmlspecialchars(
                                ucfirst($enrollment["status"])
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <a
                            href="view.php?id=<?php echo $enrollment["enrollment_id"]; ?>"
                            class="action-link view-link"
                        >
                            View
                        </a>


                        <a
                            href="delete.php?id=<?php echo $enrollment["enrollment_id"]; ?>"
                            class="action-link delete-link"
                        >
                            Remove
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td colspan="10" class="empty">
                    No enrollments found.
                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>

</html>