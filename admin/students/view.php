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

// Get student details
$sql = "SELECT 
            student_id,
            student_number,
            full_name,
            phone,
            gender,
            program,
            year_of_study,
            admission_date,
            status
        FROM students
        WHERE student_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $student_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Student not found
if (!$student) {
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Profile - Student Management System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            margin-top: 0;
            color: #333;
        }

        .profile {
            margin-top: 25px;
        }

        .row {
            display: flex;
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .label {
            width: 200px;
            font-weight: bold;
            color: #555;
        }

        .value {
            flex: 1;
            color: #333;
        }

        .actions {
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 8px;
        }

        .back {
            background: #6c757d;
            color: white;
        }

        .edit {
            background: #007bff;
            color: white;
        }

        .delete {
            background: #dc3545;
            color: white;
        }

    </style>
</head>

<body>

<div class="container">

    <h1>Student Profile</h1>

    <div class="profile">

        <div class="row">
            <div class="label">Student Number</div>
            <div class="value">
                <?php echo htmlspecialchars($student["student_number"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Full Name</div>
            <div class="value">
                <?php echo htmlspecialchars($student["full_name"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Phone</div>
            <div class="value">
                <?php echo htmlspecialchars($student["phone"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Gender</div>
            <div class="value">
                <?php echo htmlspecialchars($student["gender"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Program</div>
            <div class="value">
                <?php echo htmlspecialchars($student["program"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Year of Study</div>
            <div class="value">
                <?php echo htmlspecialchars($student["year_of_study"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Admission Date</div>
            <div class="value">
                <?php echo htmlspecialchars($student["admission_date"]); ?>
            </div>
        </div>

        <div class="row">
            <div class="label">Status</div>
            <div class="value">
                <?php echo htmlspecialchars($student["status"]); ?>
            </div>
        </div>

    </div>

    <div class="actions">

        <a href="index.php" class="btn back">
            Back to Students
        </a>

        <a href="edit.php?id=<?php echo $student["student_id"]; ?>" class="btn edit">
            Edit Student
        </a>

        <a href="delete.php?id=<?php echo $student["student_id"]; ?>" 
           class="btn delete"
           onclick="return confirm('Are you sure you want to delete this student?');">
            Delete Student
        </a>

    </div>

</div>

</body>
</html>