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
    : (int)($_POST["enrollment_id"] ?? 0);

if ($enrollment_id <= 0) {
    header("Location: index.php");
    exit();
}


$error = "";


// Delete after confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sql = "
        DELETE FROM enrollments
        WHERE enrollment_id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $enrollment_id
    );


    if ($stmt->execute()) {

        header(
            "Location: index.php?success=deleted"
        );

        exit();

    } else {

        $error =
            "Unable to remove the enrollment. Please try again.";
    }

    $stmt->close();
}


// Fetch enrollment information
$sql = "
    SELECT

        e.enrollment_id,
        e.academic_year,
        e.semester,

        s.student_number,
        s.full_name AS student_name,

        c.course_code,
        c.course_name

    FROM enrollments e

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

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
        Remove Enrollment - Student Management System
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
            width: 600px;
            max-width: 90%;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
        }

        h1 {
            color: #dc2626;
            margin-bottom: 20px;
        }

        p {
            line-height: 1.6;
        }

        .warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 11px 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel-btn {
            background: #6b7280;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 5px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>


<div class="container">

    <h1>
        Remove Enrollment
    </h1>


    <p>
        Are you sure you want to remove this enrollment?
    </p>


    <div class="warning">

        <strong>
            Student:
        </strong>

        <?php
        echo htmlspecialchars(
            $enrollment["student_name"]
        );
        ?>

        <br>

        <strong>
            Student Number:
        </strong>

        <?php
        echo htmlspecialchars(
            $enrollment["student_number"]
        );
        ?>

        <br><br>

        <strong>
            Course:
        </strong>

        <?php
        echo htmlspecialchars(
            $enrollment["course_code"]
        );
        ?>

        -
        <?php
        echo htmlspecialchars(
            $enrollment["course_name"]
        );
        ?>

        <br>

        <strong>
            Academic Year:
        </strong>

        <?php
        echo htmlspecialchars(
            $enrollment["academic_year"]
        );
        ?>

        <br>

        <strong>
            Semester:
        </strong>

        <?php
        echo htmlspecialchars(
            $enrollment["semester"]
        );
        ?>

    </div>


    <?php if (!empty($error)): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <input
            type="hidden"
            name="enrollment_id"
            value="<?php echo $enrollment_id; ?>"
        >


        <div class="buttons">

            <button
                type="submit"
                class="delete-btn"
            >
                Yes, Remove
            </button>


            <a
                href="index.php"
                class="cancel-btn"
            >
                Cancel
            </a>

        </div>

    </form>

</div>

</body>

</html>