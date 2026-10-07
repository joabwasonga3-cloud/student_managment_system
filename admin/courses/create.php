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

// Fetch lecturers
$lecturer_sql = "
    SELECT lecturer_id, full_name
    FROM lecturers
    WHERE status = 'active'
    ORDER BY full_name ASC
";

$lecturer_result = $conn->query($lecturer_sql);


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $course_code = trim($_POST["course_code"] ?? "");
    $course_name = trim($_POST["course_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $credit_hours = $_POST["credit_hours"] ?? "";
    $lecturer_id = $_POST["lecturer_id"] ?? "";

    // Validation
    if (
        empty($course_code) ||
        empty($course_name) ||
        empty($credit_hours) ||
        empty($lecturer_id)
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!is_numeric($credit_hours) || $credit_hours <= 0) {

        $error = "Credit hours must be a valid positive number.";

    } else {

        // Check duplicate course code
        $check_sql = "
            SELECT course_id
            FROM courses
            WHERE course_code = ?
            LIMIT 1
        ";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $course_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $error = "A course with this course code already exists.";

        } else {

            // Insert course
            $sql = "
                INSERT INTO courses
                (
                    course_code,
                    course_name,
                    description,
                    credit_hours,
                    lecturer_id
                )
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($sql);

            $credit_hours = (int)$credit_hours;
            $lecturer_id = (int)$lecturer_id;

            $stmt->bind_param(
                "sssii",
                $course_code,
                $course_name,
                $description,
                $credit_hours,
                $lecturer_id
            );

            if ($stmt->execute()) {

                header("Location: index.php?success=added");
                exit();

            } else {

                $error = "Failed to add course. Please try again.";

            }

            $stmt->close();
        }

        $check_stmt->close();
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

    <title>Add Course - Student Management System</title>

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

        input,
        textarea,
        select {
            width: 100%;
            padding: 11px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
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

    <h1>Add Course</h1>

    <?php if (!empty($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Course Code *</label>

        <input
            type="text"
            name="course_code"
            placeholder="e.g. CSC 101"
            value="<?php echo htmlspecialchars($_POST["course_code"] ?? ""); ?>"
            required
        >


        <label>Course Name *</label>

        <input
            type="text"
            name="course_name"
            placeholder="e.g. Introduction to Programming"
            value="<?php echo htmlspecialchars($_POST["course_name"] ?? ""); ?>"
            required
        >


        <label>Description</label>

        <textarea
            name="description"
            placeholder="Enter course description"
        ><?php echo htmlspecialchars($_POST["description"] ?? ""); ?></textarea>


        <label>Credit Hours *</label>

        <input
            type="number"
            name="credit_hours"
            min="1"
            value="<?php echo htmlspecialchars($_POST["credit_hours"] ?? ""); ?>"
            required
        >


        <label>Lecturer *</label>

        <select name="lecturer_id" required>

            <option value="">-- Select Lecturer --</option>

            <?php if ($lecturer_result && $lecturer_result->num_rows > 0): ?>

                <?php while ($lecturer = $lecturer_result->fetch_assoc()): ?>

                    <option
                        value="<?php echo $lecturer["lecturer_id"]; ?>"
                        <?php
                        if (
                            isset($_POST["lecturer_id"]) &&
                            $_POST["lecturer_id"] == $lecturer["lecturer_id"]
                        ) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php echo htmlspecialchars($lecturer["full_name"]); ?>

                    </option>

                <?php endwhile; ?>

            <?php endif; ?>

        </select>


        <div class="buttons">

            <button type="submit" class="save-btn">
                Save Course
            </button>

            <a href="index.php" class="cancel-btn">
                Cancel
            </a>

        </div>

    </form>

</div>

</body>
</html>