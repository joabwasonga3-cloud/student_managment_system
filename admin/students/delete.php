<?php

session_start();

require_once "../../config/database.php";

// Check if admin is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Check admin role
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// Check student ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$student_id = intval($_GET["id"]);


// Get student information before deletion
$sql = "SELECT student_number, full_name
        FROM students
        WHERE student_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $student_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// Student does not exist
if (!$student) {
    header("Location: index.php");
    exit();
}


// Delete student
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sql = "DELETE FROM students WHERE student_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $student_id);

    if (mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        header("Location: index.php?deleted=1");
        exit();

    } else {

        $error = "Failed to delete student: " . mysqli_error($conn);

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Delete Student - Student Management System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 550px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            color: #dc3545;
        }

        .student-name {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }

        .warning {
            color: #555;
            margin-bottom: 25px;
        }

        button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel {
            display: inline-block;
            margin-left: 10px;
            padding: 11px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Delete Student</h1>

    <?php if (isset($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <p class="warning">
        Are you sure you want to delete the following student?
    </p>


    <div class="student-name">

        <?php echo htmlspecialchars($student["full_name"]); ?>

        <br>

        <small>
            <?php echo htmlspecialchars($student["student_number"]); ?>
        </small>

    </div>


    <p class="warning">
        This action cannot be undone.
    </p>


    <form method="POST">

        <button type="submit">
            Yes, Delete Student
        </button>

        <a href="view.php?id=<?php echo $student_id; ?>" class="cancel">
            Cancel
        </a>

    </form>

</div>

</body>

</html>