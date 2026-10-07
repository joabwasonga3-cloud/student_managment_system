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

$error = "";
$success = "";

// Get existing student
$sql = "SELECT 
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


// Update student
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_number = trim($_POST["student_number"]);
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $gender = $_POST["gender"];
    $program = trim($_POST["program"]);
    $year_of_study = intval($_POST["year_of_study"]);
    $admission_date = $_POST["admission_date"];
    $status = $_POST["status"];

    // Basic validation
    if (
        empty($student_number) ||
        empty($full_name) ||
        empty($program) ||
        empty($admission_date)
    ) {

        $error = "Please fill in all required fields.";

    } elseif ($year_of_study < 1) {

        $error = "Year of study must be at least 1.";

    } else {

        $sql = "UPDATE students
                SET student_number = ?,
                    full_name = ?,
                    phone = ?,
                    gender = ?,
                    program = ?,
                    year_of_study = ?,
                    admission_date = ?,
                    status = ?
                WHERE student_id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssssissi",
            $student_number,
            $full_name,
            $phone,
            $gender,
            $program,
            $year_of_study,
            $admission_date,
            $status,
            $student_id
        );

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: view.php?id=" . $student_id);
            exit();

        } else {

            if (mysqli_errno($conn) == 1062) {
                $error = "The student number or phone number already exists.";
            } else {
                $error = "Failed to update student: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Student - Student Management System</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back {
            display: inline-block;
            margin-left: 10px;
            padding: 11px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Edit Student</h1>

    <?php if (!empty($error)): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="form-group">

            <label>Student Number *</label>

            <input
                type="text"
                name="student_number"
                value="<?php echo htmlspecialchars($student["student_number"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Full Name *</label>

            <input
                type="text"
                name="full_name"
                value="<?php echo htmlspecialchars($student["full_name"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Phone Number</label>

            <input
                type="text"
                name="phone"
                value="<?php echo htmlspecialchars($student["phone"]); ?>"
            >

        </div>


        <div class="form-group">

            <label>Gender *</label>

            <select name="gender" required>

                <option value="Male"
                    <?php echo ($student["gender"] === "Male") ? "selected" : ""; ?>>
                    Male
                </option>

                <option value="Female"
                    <?php echo ($student["gender"] === "Female") ? "selected" : ""; ?>>
                    Female
                </option>

                <option value="Other"
                    <?php echo ($student["gender"] === "Other") ? "selected" : ""; ?>>
                    Other
                </option>

            </select>

        </div>


        <div class="form-group">

            <label>Program *</label>

            <input
                type="text"
                name="program"
                value="<?php echo htmlspecialchars($student["program"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Year of Study *</label>

            <input
                type="number"
                name="year_of_study"
                min="1"
                value="<?php echo htmlspecialchars($student["year_of_study"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Admission Date *</label>

            <input
                type="date"
                name="admission_date"
                value="<?php echo htmlspecialchars($student["admission_date"]); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Status *</label>

            <select name="status" required>

                <option value="active"
                    <?php echo ($student["status"] === "active") ? "selected" : ""; ?>>
                    Active
                </option>

                <option value="inactive"
                    <?php echo ($student["status"] === "inactive") ? "selected" : ""; ?>>
                    Inactive
                </option>

                <option value="graduated"
                    <?php echo ($student["status"] === "graduated") ? "selected" : ""; ?>>
                    Graduated
                </option>

                <option value="suspended"
                    <?php echo ($student["status"] === "suspended") ? "selected" : ""; ?>>
                    Suspended
                </option>

            </select>

        </div>


        <button type="submit">
            Update Student
        </button>

        <a href="view.php?id=<?php echo $student_id; ?>" class="back">
            Cancel
        </a>

    </form>

</div>

</body>

</html>