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

$error = "";


// Fetch students
$student_sql = "
    SELECT
        student_id,
        student_number,
        full_name,
        program
    FROM students
    WHERE status = 'active'
    ORDER BY full_name ASC
";

$student_result = $conn->query($student_sql);


// Fetch courses
$course_sql = "
    SELECT
        c.course_id,
        c.course_code,
        c.course_name,
        l.full_name AS lecturer_name
    FROM courses c
    LEFT JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id
    ORDER BY c.course_code ASC
";

$course_result = $conn->query($course_sql);


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int)($_POST["student_id"] ?? 0);
    $course_id = (int)($_POST["course_id"] ?? 0);
    $academic_year = trim($_POST["academic_year"] ?? "");
    $semester = trim($_POST["semester"] ?? "");
    $enrollment_date = $_POST["enrollment_date"] ?? "";


    // Validate required fields
    if (
        $student_id <= 0 ||
        $course_id <= 0 ||
        empty($academic_year) ||
        empty($semester) ||
        empty($enrollment_date)
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !in_array(
            $semester,
            ["Semester 1", "Semester 2", "Semester 3"],
            true
        )
    ) {

        $error = "Please select a valid semester.";

    } else {

        // Check whether student exists and is active
        $student_check_sql = "
            SELECT student_id
            FROM students
            WHERE student_id = ?
            AND status = 'active'
            LIMIT 1
        ";

        $student_check = $conn->prepare($student_check_sql);

        $student_check->bind_param(
            "i",
            $student_id
        );

        $student_check->execute();

        $student_check_result = $student_check->get_result();


        if ($student_check_result->num_rows === 0) {

            $error = "The selected student does not exist or is inactive.";

        } else {

            // Check whether course exists
            $course_check_sql = "
                SELECT course_id
                FROM courses
                WHERE course_id = ?
                LIMIT 1
            ";

            $course_check = $conn->prepare($course_check_sql);

            $course_check->bind_param(
                "i",
                $course_id
            );

            $course_check->execute();

            $course_check_result =
                $course_check->get_result();


            if ($course_check_result->num_rows === 0) {

                $error = "The selected course does not exist.";

            } else {

                // Check duplicate enrollment
                $duplicate_sql = "
                    SELECT enrollment_id
                    FROM enrollments
                    WHERE student_id = ?
                    AND course_id = ?
                    AND academic_year = ?
                    AND semester = ?
                    LIMIT 1
                ";

                $duplicate_stmt =
                    $conn->prepare($duplicate_sql);

                $duplicate_stmt->bind_param(
                    "iiss",
                    $student_id,
                    $course_id,
                    $academic_year,
                    $semester
                );

                $duplicate_stmt->execute();

                $duplicate_result =
                    $duplicate_stmt->get_result();


                if ($duplicate_result->num_rows > 0) {

                    $error =
                        "This student is already enrolled in this course for the selected academic year and semester.";

                } else {

                    // Insert enrollment
                    $insert_sql = "
                        INSERT INTO enrollments
                        (
                            student_id,
                            course_id,
                            academic_year,
                            semester,
                            enrollment_date,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, 'active')
                    ";

                    $insert_stmt =
                        $conn->prepare($insert_sql);

                    $insert_stmt->bind_param(
                        "iisss",
                        $student_id,
                        $course_id,
                        $academic_year,
                        $semester,
                        $enrollment_date
                    );


                    if ($insert_stmt->execute()) {

                        header(
                            "Location: index.php?success=added"
                        );

                        exit();

                    } else {

                        $error =
                            "Failed to enroll the student. Please try again.";
                    }

                    $insert_stmt->close();
                }

                $duplicate_stmt->close();
            }

            $course_check->close();
        }

        $student_check->close();
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

    <title>Enroll Student - Student Management System</title>

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
            width: 700px;
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

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        select,
        input {
            width: 100%;
            padding: 11px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info {
            background: #eff6ff;
            color: #1e40af;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .buttons {
            display: flex;
            gap: 10px;
        }

        .save-btn {
            background: #2563eb;
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

    </style>

</head>

<body>


<div class="container">

    <h1>Enroll Student</h1>


    <div class="info">

        Select an active student and assign them to an existing course.

    </div>


    <?php if (!empty($error)): ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>
            Student *
        </label>


        <select name="student_id" required>

            <option value="">
                -- Select Student --
            </option>


            <?php if ($student_result && $student_result->num_rows > 0): ?>

                <?php while ($student = $student_result->fetch_assoc()): ?>

                    <option
                        value="<?php echo $student["student_id"]; ?>"
                        <?php
                        if (
                            isset($_POST["student_id"]) &&
                            $_POST["student_id"] ==
                            $student["student_id"]
                        ) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php

                        echo htmlspecialchars(
                            $student["student_number"]
                        );

                        echo " - ";

                        echo htmlspecialchars(
                            $student["full_name"]
                        );

                        echo " (";

                        echo htmlspecialchars(
                            $student["program"]
                        );

                        echo ")";

                        ?>

                    </option>

                <?php endwhile; ?>

            <?php endif; ?>

        </select>


        <label>
            Course *
        </label>


        <select name="course_id" required>

            <option value="">
                -- Select Course --
            </option>


            <?php if ($course_result && $course_result->num_rows > 0): ?>

                <?php while ($course = $course_result->fetch_assoc()): ?>

                    <option
                        value="<?php echo $course["course_id"]; ?>"
                        <?php
                        if (
                            isset($_POST["course_id"]) &&
                            $_POST["course_id"] ==
                            $course["course_id"]
                        ) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php

                        echo htmlspecialchars(
                            $course["course_code"]
                        );

                        echo " - ";

                        echo htmlspecialchars(
                            $course["course_name"]
                        );

                        if (!empty($course["lecturer_name"])) {

                            echo " - Lecturer: ";

                            echo htmlspecialchars(
                                $course["lecturer_name"]
                            );
                        }

                        ?>

                    </option>

                <?php endwhile; ?>

            <?php endif; ?>

        </select>


        <label>
            Academic Year *
        </label>


        <input
            type="text"
            name="academic_year"
            placeholder="e.g. 2026/2027"
            value="<?php
                echo htmlspecialchars(
                    $_POST["academic_year"] ?? ""
                );
            ?>"
            required
        >


        <label>
            Semester *
        </label>


        <select name="semester" required>

            <option value="">
                -- Select Semester --
            </option>

            <option
                value="Semester 1"
                <?php
                if (
                    ($_POST["semester"] ?? "") ===
                    "Semester 1"
                ) {
                    echo "selected";
                }
                ?>
            >
                Semester 1
            </option>

            <option
                value="Semester 2"
                <?php
                if (
                    ($_POST["semester"] ?? "") ===
                    "Semester 2"
                ) {
                    echo "selected";
                }
                ?>
            >
                Semester 2
            </option>

            <option
                value="Semester 3"
                <?php
                if (
                    ($_POST["semester"] ?? "") ===
                    "Semester 3"
                ) {
                    echo "selected";
                }
                ?>
            >
                Semester 3
            </option>

        </select>


        <label>
            Enrollment Date *
        </label>


        <input
            type="date"
            name="enrollment_date"
            value="<?php
                echo htmlspecialchars(
                    $_POST["enrollment_date"]
                    ?? date("Y-m-d")
                );
            ?>"
            required
        >


        <div class="buttons">

            <button
                type="submit"
                class="save-btn"
            >
                Enroll Student
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