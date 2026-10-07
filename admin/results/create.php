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


$error = "";


/*
|--------------------------------------------------------------------------
| GET ENROLLMENTS THAT DO NOT YET HAVE RESULTS
|--------------------------------------------------------------------------
|
| Because results.enrollment_id is UNIQUE, one enrollment
| can only have one result.
|
*/

$enrollment_query = "

    SELECT

        e.enrollment_id,

        s.student_number,
        s.full_name AS student_name,
        s.program,

        c.course_code,
        c.course_name,

        e.academic_year,
        e.semester

    FROM enrollments e

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    LEFT JOIN results r
        ON e.enrollment_id = r.enrollment_id

    WHERE r.result_id IS NULL

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

    $enrollment_id = isset($_POST["enrollment_id"])
        ? (int) $_POST["enrollment_id"]
        : 0;

    $cat_marks = isset($_POST["cat_marks"])
        ? (float) $_POST["cat_marks"]
        : 0;

    $exam_marks = isset($_POST["exam_marks"])
        ? (float) $_POST["exam_marks"]
        : 0;

    $remarks = trim(
        $_POST["remarks"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATE ENROLLMENT
    |--------------------------------------------------------------------------
    */

    if ($enrollment_id <= 0) {

        $error = "Please select a student enrollment.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE CAT MARKS
    |--------------------------------------------------------------------------
    */

    elseif ($cat_marks < 0 || $cat_marks > 30) {

        $error = "CAT marks must be between 0 and 30.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE EXAM MARKS
    |--------------------------------------------------------------------------
    */

    elseif ($exam_marks < 0 || $exam_marks > 70) {

        $error = "Exam marks must be between 0 and 70.";

    }


    else {

        /*
        |--------------------------------------------------------------------------
        | CHECK WHETHER RESULT ALREADY EXISTS
        |--------------------------------------------------------------------------
        */

        $check_query = "

            SELECT result_id

            FROM results

            WHERE enrollment_id = ?

            LIMIT 1

        ";

        $check_stmt = mysqli_prepare(
            $conn,
            $check_query
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "i",
            $enrollment_id
        );

        mysqli_stmt_execute(
            $check_stmt
        );

        $check_result = mysqli_stmt_get_result(
            $check_stmt
        );


        if (mysqli_num_rows($check_result) > 0) {

            $error = "A result already exists for this enrollment.";

        }

        else {

            /*
            |--------------------------------------------------------------------------
            | CALCULATE TOTAL
            |--------------------------------------------------------------------------
            */

            $total_marks =
                $cat_marks + $exam_marks;


            /*
            |--------------------------------------------------------------------------
            | CALCULATE GRADE
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | INSERT RESULT
            |--------------------------------------------------------------------------
            */

            $insert_query = "

                INSERT INTO results (

                    enrollment_id,
                    cat_marks,
                    exam_marks,
                    total_marks,
                    grade,
                    remarks

                )

                VALUES (?, ?, ?, ?, ?, ?)

            ";

            $insert_stmt = mysqli_prepare(
                $conn,
                $insert_query
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "idddss",
                $enrollment_id,
                $cat_marks,
                $exam_marks,
                $total_marks,
                $grade,
                $remarks
            );


            if (mysqli_stmt_execute($insert_stmt)) {

                header(
                    "Location: index.php?success=added"
                );

                exit();

            }

            else {

                $error = "Failed to record the result.";

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| RELOAD ENROLLMENTS
|--------------------------------------------------------------------------
|
| This ensures that after a failed submission the dropdown
| still contains the available enrollments.
|
*/

$enrollment_result = mysqli_query(
    $conn,
    $enrollment_query
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

    <title>Record Result</title>


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

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);

        }


        h1 {

            margin-bottom: 8px;

            color: #333;

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

            color: #333;

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


        select:focus,

        input:focus,

        textarea:focus {

            outline: none;

            border-color: #2563eb;

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


        .info {

            background: #eff6ff;

            color: #1e40af;

            padding: 15px;

            border-radius: 5px;

            margin-bottom: 20px;

            line-height: 1.6;

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

            font-size: 14px;

        }


        button {

            background: #2563eb;

            color: white;

        }


        button:hover {

            background: #1d4ed8;

        }


        .cancel {

            background: #6b7280;

            color: white;

        }


        .cancel:hover {

            background: #4b5563;

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


        <h1>
            Record Student Result
        </h1>


        <p class="subtitle">

            Record academic results for a
            student's course enrollment.

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


        <div class="info">

            <strong>
                Important:
            </strong>

            Select an enrolled student and course
            before entering the marks.

        </div>


        <form method="POST">


            <!-- ENROLLMENT -->

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


                    <?php

                    if (
                        $enrollment_result &&
                        mysqli_num_rows(
                            $enrollment_result
                        ) > 0
                    ):

                    ?>

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


                    <?php else: ?>


                        <option value="">

                            No available enrollments found.

                        </option>


                    <?php endif; ?>

                </select>

            </div>


            <!-- MARKS -->

            <div class="marks">


                <!-- CAT -->

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
                        required
                    >

                </div>


                <!-- EXAM -->

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
                        required
                    >

                </div>


            </div>


            <!-- REMARKS -->

            <div class="form-group">

                <label for="remarks">

                    Remarks

                </label>


                <textarea
                    name="remarks"
                    id="remarks"
                    placeholder="Enter optional remarks..."
                ></textarea>

            </div>


            <!-- BUTTONS -->

            <div class="buttons">


                <button type="submit">

                    Record Result

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