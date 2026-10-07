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
| GET CURRENT RESULT
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        r.*,
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

$current = mysqli_fetch_assoc($result);

/*
|--------------------------------------------------------------------------
| PROCESS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $cat_marks = (float) $_POST["cat_marks"];
    $exam_marks = (float) $_POST["exam_marks"];
    $remarks = trim($_POST["remarks"]);

    if ($cat_marks < 0 || $cat_marks > 30) {

        $error = "CAT marks must be between 0 and 30.";

    } elseif ($exam_marks < 0 || $exam_marks > 70) {

        $error = "Exam marks must be between 0 and 70.";

    } else {

        $total_marks =
            $cat_marks + $exam_marks;

        if ($total_marks >= 70) {
            $grade = "A";
        } elseif ($total_marks >= 60) {
            $grade = "B";
        } elseif ($total_marks >= 50) {
            $grade = "C";
        } elseif ($total_marks >= 40) {
            $grade = "D";
        } else {
            $grade = "F";
        }

        $update_query = "
            UPDATE results
            SET
                cat_marks = ?,
                exam_marks = ?,
                total_marks = ?,
                grade = ?,
                remarks = ?
            WHERE result_id = ?
        ";

        $update_stmt = mysqli_prepare(
            $conn,
            $update_query
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "dddssi",
            $cat_marks,
            $exam_marks,
            $total_marks,
            $grade,
            $remarks,
            $result_id
        );

        if (mysqli_stmt_execute($update_stmt)) {

            header(
                "Location: index.php?success=updated"
            );

            exit();

        } else {

            $error = "Failed to update result.";
        }
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

    <title>Edit Result</title>

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
            max-width: 800px;
            margin: 35px auto;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        .marks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .student-info {
            background: #eff6ff;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        button,
        a {
            padding: 11px 18px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #2563eb;
            color: white;
        }

        .cancel {
            background: #6b7280;
            color: white;
        }

        @media (max-width: 600px) {

            .marks {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="form-container">

        <h1>Edit Result</h1>

        <p class="subtitle">
            Update the student's academic result.
        </p>


        <div class="student-info">

            <strong>Student:</strong>

            <?php
            echo htmlspecialchars(
                $current["student_name"]
            );
            ?>

            <br>

            <strong>Student Number:</strong>

            <?php
            echo htmlspecialchars(
                $current["student_number"]
            );
            ?>

            <br>

            <strong>Course:</strong>

            <?php
            echo htmlspecialchars(
                $current["course_code"]
            );
            ?>

            -

            <?php
            echo htmlspecialchars(
                $current["course_name"]
            );
            ?>

            <br>

            <strong>Academic Year:</strong>

            <?php
            echo htmlspecialchars(
                $current["academic_year"]
            );
            ?>

            |

            <?php
            echo htmlspecialchars(
                $current["semester"]
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


        <form method="POST">

            <div class="marks">

                <div class="form-group">

                    <label for="cat_marks">
                        CAT Marks / 30
                    </label>

                    <input
                        type="number"
                        name="cat_marks"
                        id="cat_marks"
                        min="0"
                        max="30"
                        step="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $current["cat_marks"]
                            );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="exam_marks">
                        Exam Marks / 70
                    </label>

                    <input
                        type="number"
                        name="exam_marks"
                        id="exam_marks"
                        min="0"
                        max="70"
                        step="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $current["exam_marks"]
                            );
                        ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label for="remarks">
                    Remarks
                </label>

                <textarea
                    name="remarks"
                    id="remarks"
                ><?php
                    echo htmlspecialchars(
                        $current["remarks"] ?? ""
                    );
                ?></textarea>

            </div>


            <div class="buttons">

                <button type="submit">
                    Update Result
                </button>

                <a
                    href="index.php"
                    class="cancel"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>