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


// Get enrollment ID
$enrollment_id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;

if ($enrollment_id <= 0) {
    header("Location: index.php");
    exit();
}


// Fetch enrollment
$sql = "
    SELECT

        e.enrollment_id,
        e.academic_year,
        e.semester,
        e.enrollment_date,
        e.status,

        s.student_id,
        s.student_number,
        s.full_name AS student_name,
        s.phone,
        s.gender,
        s.program,
        s.year_of_study,

        c.course_id,
        c.course_code,
        c.course_name,
        c.description,
        c.credit_hours,

        l.full_name AS lecturer_name

    FROM enrollments e

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    LEFT JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id

    WHERE e.enrollment_id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $enrollment_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}


$enrollment = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Enrollment Details - Student Management System
    </title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f6f9;
        }

        .container {
            width: 800px;
            max-width: 90%;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        h1 {
            margin-bottom: 25px;
        }

        .section-title {
            background: #1f2937;
            color: white;
            padding: 10px;
            margin-top: 20px;
            margin-bottom: 0;
        }

        .detail {
            padding: 13px;
            border-bottom: 1px solid #ddd;
        }

        .detail strong {
            display: inline-block;
            width: 190px;
        }

        .status {
            font-weight: bold;
            color: #059669;
        }

        .buttons {
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 8px;
        }

        .back {
            background: #6b7280;
            color: white;
        }

        .delete {
            background: #dc2626;
            color: white;
        }

    </style>

</head>

<body>


<div class="container">

    <h1>
        Enrollment Details
    </h1>


    <div class="section-title">
        Student Information
    </div>


    <div class="detail">

        <strong>Student Number:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["student_number"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Full Name:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["student_name"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Program:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["program"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Year of Study:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["year_of_study"]
        );
        ?>

    </div>


    <div class="section-title">
        Course Information
    </div>


    <div class="detail">

        <strong>Course Code:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["course_code"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Course Name:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["course_name"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Credit Hours:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["credit_hours"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Lecturer:</strong>

        <?php

        echo htmlspecialchars(
            $enrollment["lecturer_name"]
            ?? "Not assigned"
        );

        ?>

    </div>


    <div class="section-title">
        Enrollment Information
    </div>


    <div class="detail">

        <strong>Academic Year:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["academic_year"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Semester:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["semester"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Enrollment Date:</strong>

        <?php
        echo htmlspecialchars(
            $enrollment["enrollment_date"]
        );
        ?>

    </div>


    <div class="detail">

        <strong>Status:</strong>

        <span class="status">

            <?php

            echo htmlspecialchars(
                ucfirst(
                    $enrollment["status"]
                )
            );

            ?>

        </span>

    </div>


    <div class="buttons">

        <a
            href="index.php"
            class="btn back"
        >
            Back
        </a>


        <a
            href="delete.php?id=<?php echo $enrollment["enrollment_id"]; ?>"
            class="btn delete"
        >
            Remove Enrollment
        </a>

    </div>

</div>

</body>

</html>