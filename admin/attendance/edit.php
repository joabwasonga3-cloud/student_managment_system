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


$attendance_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($attendance_id <= 0) {

    header("Location: index.php");
    exit();

}


$error = "";


/*
|--------------------------------------------------------------------------
| GET CURRENT ATTENDANCE
|--------------------------------------------------------------------------
*/

$query = "

    SELECT

        a.*,

        s.student_number,
        s.full_name AS student_name,

        c.course_code,
        c.course_name,

        e.academic_year,
        e.semester

    FROM attendance a

    INNER JOIN enrollments e
        ON a.enrollment_id = e.enrollment_id

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    WHERE a.attendance_id = ?

    LIMIT 1

";


$stmt = mysqli_prepare(
    $conn,
    $query
);


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $attendance_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result(
    $stmt
);


if (
    mysqli_num_rows($result) === 0
) {

    header("Location: index.php");
    exit();

}


$current = mysqli_fetch_assoc(
    $result
);


/*
|--------------------------------------------------------------------------
| PROCESS UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $date = $_POST["date"] ?? "";

    $status = $_POST["status"] ?? "";

    $remarks = trim(
        $_POST["remarks"] ?? ""
    );


    if (empty($date)) {

        $error =
            "Please select an attendance date.";

    } elseif (
        !in_array(
            $status,
            ["Present", "Absent", "Late"],
            true
        )
    ) {

        $error =
            "Please select a valid attendance status.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE DATE
        |--------------------------------------------------------------------------
        */

        $check_query = "

            SELECT attendance_id

            FROM attendance

            WHERE enrollment_id = ?
            AND date = ?
            AND attendance_id != ?

            LIMIT 1

        ";


        $check_stmt = mysqli_prepare(
            $conn,
            $check_query
        );


        mysqli_stmt_bind_param(
            $check_stmt,
            "isi",
            $current["enrollment_id"],
            $date,
            $attendance_id
        );


        mysqli_stmt_execute(
            $check_stmt
        );


        $check_result =
            mysqli_stmt_get_result(
                $check_stmt
            );


        if (
            mysqli_num_rows(
                $check_result
            ) > 0
        ) {

            $error =
                "Attendance already exists for this student on this date.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            $update_query = "

                UPDATE attendance

                SET

                    date = ?,
                    status = ?,
                    remarks = ?

                WHERE attendance_id = ?

            ";


            $update_stmt = mysqli_prepare(
                $conn,
                $update_query
            );


            mysqli_stmt_bind_param(
                $update_stmt,
                "sssi",
                $date,
                $status,
                $remarks,
                $attendance_id
            );


            if (
                mysqli_stmt_execute(
                    $update_stmt
                )
            ) {

                header(
                    "Location: index.php?success=updated"
                );

                exit();

            } else {

                $error =
                    "Failed to update attendance.";

            }

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

    <title>Edit Attendance</title>


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

            margin-bottom: 20px;

        }

        label {

            display: block;
            margin-bottom: 7px;
            font-weight: bold;

        }

        input,
        select,
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
        .cancel {

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

    </style>

</head>


<body>


<div class="container">


    <div class="form-container">


        <h1>
            Edit Attendance
        </h1>


        <p class="subtitle">
            Update the student's attendance record.
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

                echo htmlspecialchars(
                    $error
                );

                ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label for="date">

                    Attendance Date

                </label>

                <input
                    type="date"
                    name="date"
                    id="date"
                    value="<?php
                        echo htmlspecialchars(
                            $current["date"]
                        );
                    ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="status">

                    Attendance Status

                </label>


                <select
                    name="status"
                    id="status"
                    required
                >

                    <option
                        value="Present"
                        <?php
                        if (
                            $current["status"]
                            === "Present"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
                        Present
                    </option>


                    <option
                        value="Absent"
                        <?php
                        if (
                            $current["status"]
                            === "Absent"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
                        Absent
                    </option>


                    <option
                        value="Late"
                        <?php
                        if (
                            $current["status"]
                            === "Late"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
                        Late
                    </option>

                </select>

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

                    Update Attendance

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