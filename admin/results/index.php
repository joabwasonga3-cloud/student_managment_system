<?php

session_start();

require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| GET ALL RESULTS
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        r.result_id,
        r.enrollment_id,
        r.cat_marks,
        r.exam_marks,
        r.total_marks,
        r.grade,
        r.remarks,

        s.student_number,
        s.full_name AS student_name,

        c.course_code,
        c.course_name,

        e.academic_year,
        e.semester

    FROM results r

    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    ORDER BY r.result_id DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Results Management</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            margin: 30px auto;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .header h1 {
            color: #333;
            margin-bottom: 8px;
        }

        .header p {
            color: #666;
        }

        .top-actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background-color: #2563eb;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .btn.secondary {
            background-color: #6b7280;
        }

        .btn.secondary:hover {
            background-color: #4b5563;
        }

        .message {
            padding: 12px;
            background-color: #d1fae5;
            color: #065f46;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background-color: #1f2937;
            color: white;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .actions a {
            padding: 7px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 12px;
        }

        .view {
            background-color: #2563eb;
        }

        .edit {
            background-color: #f59e0b;
        }

        .delete {
            background-color: #dc3545;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Results Management</h1>

        <p>
            View and manage student academic results.
        </p>

        <div class="top-actions">

            <a
                href="create.php"
                class="btn"
            >
                + Record Results
            </a>

            <a
                href="../dashboard.php"
                class="btn secondary"
            >
                Back to Dashboard
            </a>

        </div>

    </div>


    <?php if (isset($_GET["success"])): ?>

        <?php if ($_GET["success"] === "added"): ?>

            <div class="message">
                Result recorded successfully.
            </div>

        <?php elseif ($_GET["success"] === "updated"): ?>

            <div class="message">
                Result updated successfully.
            </div>

        <?php elseif ($_GET["success"] === "deleted"): ?>

            <div class="message">
                Result deleted successfully.
            </div>

        <?php endif; ?>

    <?php endif; ?>


    <div class="table-container">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Student</th>
                        <th>Student Number</th>
                        <th>Course</th>
                        <th>Academic Year</th>
                        <th>Semester</th>
                        <th>CAT</th>
                        <th>Exam</th>
                        <th>Total</th>
                        <th>Grade</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php echo $row["result_id"]; ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["student_name"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["student_number"]
                            );
                            ?>
                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $row["course_code"]
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $row["course_name"]
                            );
                            ?>

                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["academic_year"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["semester"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo $row["cat_marks"]; ?>
                        </td>

                        <td>
                            <?php echo $row["exam_marks"]; ?>
                        </td>

                        <td>
                            <?php echo $row["total_marks"]; ?>
                        </td>

                        <td>
                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $row["grade"]
                                );
                                ?>
                            </strong>
                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="view.php?id=<?php echo $row["result_id"]; ?>"
                                    class="view"
                                >
                                    View
                                </a>

                                <a
                                    href="edit.php?id=<?php echo $row["result_id"]; ?>"
                                    class="edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="delete.php?id=<?php echo $row["result_id"]; ?>"
                                    class="delete"
                                >
                                    Delete
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                No results have been recorded yet.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>