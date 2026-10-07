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
| GET ATTENDANCE RECORDS
|--------------------------------------------------------------------------
*/

$query = "

    SELECT

        a.attendance_id,
        a.date,
        a.status,
        a.remarks,

        s.student_number,
        s.full_name AS student_name,

        c.course_code,
        c.course_name,

        e.academic_year,
        e.semester,

        l.full_name AS lecturer_name

    FROM attendance a

    INNER JOIN enrollments e
        ON a.enrollment_id = e.enrollment_id

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    LEFT JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id

    ORDER BY
        a.date DESC,
        s.full_name ASC

";

$result = mysqli_query(
    $conn,
    $query
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Attendance Management</title>

    <style>

        * {

            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;

        }

        body {

            background: #f4f6f9;

        }

        .container {

            width: 95%;
            max-width: 1400px;
            margin: 30px auto;

        }

        .header {

            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;

        }

        .header h1 {

            margin-bottom: 8px;
            color: #333;

        }

        .header p {

            color: #666;

        }

        .actions {

            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;

        }

        .btn {

            display: inline-block;
            padding: 11px 18px;
            border-radius: 5px;
            text-decoration: none;
            color: white;

        }

        .primary {

            background: #2563eb;

        }

        .secondary {

            background: #6b7280;

        }

        .table-container {

            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;

        }

        table {

            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;

        }

        th,
        td {

            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;

        }

        th {

            background: #f3f4f6;
            color: #374151;

        }

        tr:hover {

            background: #f9fafb;

        }

        .status {

            display: inline-block;
            padding: 5px 9px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;

        }

        .present {

            background: #dcfce7;
            color: #166534;

        }

        .absent {

            background: #fee2e2;
            color: #991b1b;

        }

        .late {

            background: #fef3c7;
            color: #92400e;

        }

        .actions-cell {

            display: flex;
            gap: 6px;
            flex-wrap: wrap;

        }

        .view {

            background: #2563eb;

        }

        .edit {

            background: #f59e0b;

        }

        .delete {

            background: #dc3545;

        }

        .empty {

            text-align: center;
            padding: 30px;
            color: #666;

        }

        .success {

            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;

        }

    </style>

</head>

<body>


<div class="container">


    <div class="header">

        <h1>
            Attendance Management
        </h1>

        <p>
            Record and manage student attendance.
        </p>


        <div class="actions">

            <a
                href="create.php"
                class="btn primary"
            >
                + Record Attendance
            </a>

            <a
                href="../dashboard.php"
                class="btn secondary"
            >
                Back to Dashboard
            </a>

        </div>

    </div>


    <?php if (
        isset($_GET["success"]) &&
        $_GET["success"] === "added"
    ): ?>

        <div class="success">
            Attendance record added successfully.
        </div>

    <?php endif; ?>


    <?php if (
        isset($_GET["success"]) &&
        $_GET["success"] === "updated"
    ): ?>

        <div class="success">
            Attendance record updated successfully.
        </div>

    <?php endif; ?>


    <?php if (
        isset($_GET["success"]) &&
        $_GET["success"] === "deleted"
    ): ?>

        <div class="success">
            Attendance record deleted successfully.
        </div>

    <?php endif; ?>


    <div class="table-container">


        <?php if (
            $result &&
            mysqli_num_rows($result) > 0
        ): ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            Date
                        </th>

                        <th>
                            Student
                        </th>

                        <th>
                            Student Number
                        </th>

                        <th>
                            Course
                        </th>

                        <th>
                            Academic Year
                        </th>

                        <th>
                            Semester
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while (
                    $row = mysqli_fetch_assoc($result)
                ): ?>


                    <tr>

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["date"]
                            );

                            ?>

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

                            <?php

                            $status_class =
                                strtolower(
                                    $row["status"]
                                );

                            ?>

                            <span
                                class="status
                                <?php
                                echo $status_class;
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $row["status"]
                                );

                                ?>

                            </span>

                        </td>


                        <td>

                            <div class="actions-cell">

                                <a
                                    href="view.php?id=<?php
                                        echo $row[
                                            "attendance_id"
                                        ];
                                    ?>"
                                    class="btn view"
                                >
                                    View
                                </a>


                                <a
                                    href="edit.php?id=<?php
                                        echo $row[
                                            "attendance_id"
                                        ];
                                    ?>"
                                    class="btn edit"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete.php?id=<?php
                                        echo $row[
                                            "attendance_id"
                                        ];
                                    ?>"
                                    class="btn delete"
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

                No attendance records found.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>