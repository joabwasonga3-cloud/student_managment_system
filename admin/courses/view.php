<?php

session_start();

require_once "../../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Check admin
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

// Fetch all courses with lecturer names
$sql = "
    SELECT 
        c.course_id,
        c.course_code,
        c.course_name,
        c.description,
        c.credit_hours,
        c.lecturer_id,
        l.full_name AS lecturer_name
    FROM courses c
    LEFT JOIN lecturers l ON c.lecturer_id = l.lecturer_id
    ORDER BY c.course_id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Courses - Student Management System</title>

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
            margin-bottom: 30px;
            text-align: center;
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

        .edit-link {
            color: #059669;
        }

        .delete-link {
            color: #dc2626;
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

    <a href="../dashboard.php">Dashboard</a>

    <a href="../students/index.php">Students</a>

    <a href="../lecturers/index.php">Lecturers</a>

    <a href="index.php">Courses</a>

    <a href="../../auth/logout.php">Logout</a>

</div>


<div class="content">

    <div class="topbar">

        <h1>Course Management</h1>

        <a href="create.php" class="add-btn">
            + Add Course
        </a>

    </div>


    <?php if (isset($_GET["success"]) && $_GET["success"] === "added"): ?>

        <div class="message">
            Course added successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["success"]) && $_GET["success"] === "updated"): ?>

        <div class="message">
            Course updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["deleted"]) && $_GET["deleted"] === "1"): ?>

        <div class="message">
            Course deleted successfully.
        </div>

    <?php endif; ?>


    <table>

        <thead>

            <tr>
                <th>#</th>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Credit Hours</th>
                <th>Lecturer</th>
                <th>Actions</th>
            </tr>

        </thead>

        <tbody>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php $count = 1; ?>

            <?php while ($course = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php echo $count++; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($course["course_code"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($course["course_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($course["credit_hours"]); ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $course["lecturer_name"] ?? "Not assigned"
                        );
                        ?>
                    </td>

                    <td>

                        <a
                            href="view.php?id=<?php echo $course["course_id"]; ?>"
                            class="action-link view-link"
                        >
                            View
                        </a>

                        <a
                            href="edit.php?id=<?php echo $course["course_id"]; ?>"
                            class="action-link edit-link"
                        >
                            Edit
                        </a>

                        <a
                            href="delete.php?id=<?php echo $course["course_id"]; ?>"
                            class="action-link delete-link"
                        >
                            Delete
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td colspan="6" class="empty">
                    No courses found.
                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>
</html>