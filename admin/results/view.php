<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

$result_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($result_id <= 0) {
    header("Location: index.php");
    exit();
}

$query = "
    SELECT

        r.*,

        s.student_number,
        s.full_name AS student_name,
        s.program,

        c.course_code,
        c.course_name,

        e.academic_year,
        e.semester,
        e.enrollment_date,

        l.full_name AS lecturer_name

    FROM results r

    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    LEFT JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id

    WHERE r.result_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $result_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Result</title>

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
            width: 90%;
            max-width: 900px;
            margin: 35px auto;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            margin-bottom: 25px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section h2 {
            font-size: 18px;
            margin-bottom: 12px;
            color: #2563eb;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .item {
            padding: 12px;
            background: #f9fafb;
            border-radius: 5px;
        }

        .item strong {
            display: block;
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .total {
            font-size: 25px;
            font-weight: bold;
            color: #2563eb;
        }

        .grade {
            font-size: 25px;
            font-weight: bold;
            color: #16a34a;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            padding: 11px 18px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back {
            background: #6b7280;
        }

        .edit {
            background: #f59e0b;
        }

        .delete {
            background: #dc3545;
        }

        @media (max-width: 600px) {

            .details {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="box">

        <h1>Result Details</h1>


        <div class="section">

            <h2>Student Information</h2>

            <div class="details">

                <div class="item">

                    <strong>Student Number</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["student_number"]
                    );
                    ?>

                </div>

                <div class="item">

                    <strong>Full Name</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["student_name"]
                    );
                    ?>

                </div>

                <div class="item">

                    <strong>Program</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["program"]
                    );
                    ?>

                </div>

            </div>

        </div>


        <div class="section">

            <h2>Course Information</h2>

            <div class="details">

                <div class="item">

                    <strong>Course</strong>

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

                </div>

                <div class="item">

                    <strong>Lecturer</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["lecturer_name"] ?? "Not assigned"
                    );
                    ?>

                </div>

                <div class="item">

                    <strong>Academic Year</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["academic_year"]
                    );
                    ?>

                </div>

                <div class="item">

                    <strong>Semester</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["semester"]
                    );
                    ?>

                </div>

            </div>

        </div>


        <div class="section">

            <h2>Academic Results</h2>

            <div class="details">

                <div class="item">

                    <strong>CAT Marks</strong>

                    <?php
                    echo $row["cat_marks"];
                    ?>
                    / 30

                </div>

                <div class="item">

                    <strong>Exam Marks</strong>

                    <?php
                    echo $row["exam_marks"];
                    ?>
                    / 70

                </div>

                <div class="item">

                    <strong>Total Marks</strong>

                    <span class="total">
                        <?php
                        echo $row["total_marks"];
                        ?>
                        / 100
                    </span>

                </div>

                <div class="item">

                    <strong>Grade</strong>

                    <span class="grade">

                        <?php
                        echo htmlspecialchars(
                            $row["grade"]
                        );
                        ?>

                    </span>

                </div>

                <div class="item">

                    <strong>Remarks</strong>

                    <?php
                    echo htmlspecialchars(
                        $row["remarks"] ?? ""
                    );
                    ?>

                </div>

            </div>

        </div>


        <div class="buttons">

            <a
                href="index.php"
                class="btn back"
            >
                Back
            </a>

            <a
                href="edit.php?id=<?php echo $result_id; ?>"
                class="btn edit"
            >
                Edit
            </a>

            <a
                href="delete.php?id=<?php echo $result_id; ?>"
                class="btn delete"
            >
                Delete
            </a>

        </div>

    </div>

</div>

</body>

</html>