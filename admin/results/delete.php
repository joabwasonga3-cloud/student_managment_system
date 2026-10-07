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

$error = "";

/*
|--------------------------------------------------------------------------
| GET RESULT INFORMATION
|--------------------------------------------------------------------------
*/

$query = "
    SELECT

        r.result_id,
        r.total_marks,
        r.grade,

        s.student_number,
        s.full_name AS student_name,

        c.course_code,
        c.course_name

    FROM results r

    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    WHERE r.result_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

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

/*
|--------------------------------------------------------------------------
| DELETE RESULT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $delete_query = "
        DELETE FROM results
        WHERE result_id = ?
    ";

    $delete_stmt = mysqli_prepare(
        $conn,
        $delete_query
    );

    mysqli_stmt_bind_param(
        $delete_stmt,
        "i",
        $result_id
    );

    if (mysqli_stmt_execute($delete_stmt)) {

        header(
            "Location: index.php?success=deleted"
        );

        exit();

    } else {

        $error = "Failed to delete result.";
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Delete Result</title>

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
            max-width: 600px;
            margin: 80px auto;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            color: #dc3545;
            margin-bottom: 15px;
        }

        p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .info {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        button,
        a {
            padding: 11px 18px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        button {
            background: #dc3545;
            color: white;
        }

        .cancel {
            background: #6b7280;
            color: white;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="box">

        <h1>Delete Result?</h1>

        <p>
            Are you sure you want to delete this result?
            This action cannot be undone.
        </p>


        <div class="info">

            <strong>Student:</strong>

            <?php
            echo htmlspecialchars(
                $row["student_name"]
            );
            ?>

            <br>

            <strong>Student Number:</strong>

            <?php
            echo htmlspecialchars(
                $row["student_number"]
            );
            ?>

            <br>

            <strong>Course:</strong>

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

            <br>

            <strong>Total:</strong>

            <?php
            echo $row["total_marks"];
            ?>

            / 100

            <br>

            <strong>Grade:</strong>

            <?php
            echo htmlspecialchars(
                $row["grade"]
            );
            ?>

        </div>


        <?php if ($error !== ""): ?>

            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <div class="buttons">

            <form method="POST">

                <button type="submit">
                    Yes, Delete
                </button>

            </form>

            <a
                href="index.php"
                class="cancel"
            >
                Cancel
            </a>

        </div>

    </div>

</div>

</body>

</html>