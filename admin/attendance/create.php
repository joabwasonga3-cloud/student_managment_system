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


$error = "";


/*
|--------------------------------------------------------------------------
| GET ACTIVE ENROLLMENTS
|--------------------------------------------------------------------------
*/

$enrollment_query = "

    SELECT

        e.enrollment_id,

        s.student_number,
        s.full_name AS student_name,

        c.course_code,
        c.course_name,

        e.academic_year,
        e.semester

    FROM enrollments e

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    WHERE e.status = 'active'

    ORDER BY
        s.full_name ASC

";

$enrollment_result = mysqli_query(
    $conn,
    $enrollment_query
);


/*
|--------------------------------------------------------------------------
| PROCESS FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $enrollment_id = isset(
        $_POST["enrollment_id"]
    )
        ? (int) $_POST["enrollment_id"]
        : 0;

    $date = $_POST["date"] ?? "";

    $status = $_POST["status"] ?? "";

    $remarks = trim(
        $_POST["remarks"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATE
    |--------------------------------------------------------------------------
    */

    if ($enrollment_id <= 0) {

        $error = "Please select a student enrollment.";

    } elseif (empty($date)) {

        $error = "Please select an attendance date.";

    } elseif (
        !in_array(
            $status,
            ["Present", "Absent", "Late"],
            true
        )
    ) {

        $error = "Please select a valid attendance status.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE
        |--------------------------------------------------------------------------
        */

        $check_query = "

            SELECT attendance_id

            FROM attendance

            WHERE enrollment_id = ?
            AND date = ?

            LIMIT 1

        ";

        $check_stmt = mysqli_prepare(
            $conn,
            $check_query
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "is",
            $enrollment_id,
            $date
        );

        mysqli_stmt_execute(
            $check_stmt
        );

        $check_result = mysqli_stmt_get_result(
            $check_stmt
        );


        if (
            mysqli_num_rows(
                $check_result
            ) > 0
        ) {

            $error =
                "Attendance has already been recorded for this student on this date.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | INSERT ATTENDANCE
            |--------------------------------------------------------------------------
            */

            $insert_query = "

                INSERT INTO attendance (

                    enrollment_id,
                    date,
                    status,
                    remarks

                )

                VALUES (?, ?, ?, ?)

            ";

            $insert_stmt = mysqli_prepare(
                $conn,
                $insert_query
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "isss",
                $enrollment_id,
                $date,
                $status,
                $remarks
            );


            if (
                mysqli_stmt_execute(
                    $insert_stmt
                )
            ) {

                header(
                    "Location: index.php?success=added"
                );

                exit();

            } else {

                $error =
                    "Failed to record attendance.";

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

    <title>Record Attendance</title>


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

        select,
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
            Record Attendance
        </h1>


        <p class="subtitle">
            Record attendance for an enrolled student.
        </p>


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

                <label for="enrollment_id">

                    Student Enrollment

                </label>


                <select
                    name="enrollment_id"
                    id="enrollment_id"
                    required
                >

                    <option value="">

                        -- Select Student Enrollment --

                    </option>


                    <?php while (
                        $enrollment =
                            mysqli_fetch_assoc(
                                $enrollment_result
                            )
                    ): ?>

                        <option
                            value="<?php
                                echo $enrollment[
                                    "enrollment_id"
                                ];
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $enrollment[
                                    "student_number"
                                ]
                            );

                            ?>

                            -

                            <?php

                            echo htmlspecialchars(
                                $enrollment[
                                    "student_name"
                                ]
                            );

                            ?>

                            |

                            <?php

                            echo htmlspecialchars(
                                $enrollment[
                                    "course_code"
                                ]
                            );

                            ?>

                            -

                            <?php

                            echo htmlspecialchars(
                                $enrollment[
                                    "course_name"
                                ]
                            );

                            ?>

                            |

                            <?php

                            echo htmlspecialchars(
                                $enrollment[
                                    "academic_year"
                                ]
                            );

                            ?>

                            |

                            <?php

                            echo htmlspecialchars(
                                $enrollment[
                                    "semester"
                                ]
                            );

                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="date">
                    Attendance Date
                </label>

                <input
                    type="date"
                    name="date"
                    id="date"
                    value="<?php
                        echo date("Y-m-d");
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

                    <option value="">
                        -- Select Status --
                    </option>

                    <option value="Present">
                        Present
                    </option>

                    <option value="Absent">
                        Absent
                    </option>

                    <option value="Late">
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
                    placeholder="Optional remarks..."
                ></textarea>

            </div>


            <div class="buttons">

                <button type="submit">

                    Record Attendance

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