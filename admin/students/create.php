<?php

session_start();

require_once "../../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Make sure the user is an admin
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

$message = "";
$message_type = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form values
    $student_number = trim($_POST["student_number"]);
    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $gender = $_POST["gender"];
    $program = trim($_POST["program"]);
    $year_of_study = $_POST["year_of_study"];
    $admission_date = $_POST["admission_date"];

    // Validate required fields
    if (
        empty($student_number) ||
        empty($full_name) ||
        empty($gender) ||
        empty($program) ||
        empty($year_of_study) ||
        empty($admission_date)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        // Check if student number already exists
        $check_sql = "SELECT student_id
                      FROM students
                      WHERE student_number = ?
                      LIMIT 1";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $check_stmt,
            "s",
            $student_number
        );

        mysqli_stmt_execute($check_stmt);

        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {

            $message = "A student with this student number already exists.";
            $message_type = "error";

        } else {

            // Insert student
            $sql = "INSERT INTO students
                    (
                        student_number,
                        full_name,
                        phone,
                        gender,
                        program,
                        year_of_study,
                        admission_date,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sssssis",
                $student_number,
                $full_name,
                $phone,
                $gender,
                $program,
                $year_of_study,
                $admission_date
            );

            if (mysqli_stmt_execute($stmt)) {

                // Redirect to students list
                header("Location: index.php?success=added");
                exit();

            } else {

                // Handle duplicate phone number
                if (mysqli_errno($conn) == 1062) {
                    $message = "The student number or phone number already exists.";
                } else {
                    $message = "Failed to add student: " . mysqli_error($conn);
                }

                $message_type = "error";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check_stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Student - Student Management System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background-color: #1f2937;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .sidebar ul li a:hover {
            background-color: #374151;
        }

        /* MAIN CONTENT */

        .main-content {
            margin-left: 240px;
            padding: 25px;
        }

        /* TOP BAR */

        .topbar {
            background-color: white;
            padding: 18px 25px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .topbar h1 {
            color: #333;
            font-size: 24px;
        }

        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }

        /* FORM CONTAINER */

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
        }

        .required {
            color: red;
        }

        /* FORM BUTTONS */

        .form-buttons {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .save-btn {
            background-color: #28a745;
            color: white;
            padding: 11px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
        }

        .save-btn:hover {
            background-color: #218838;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
            padding: 11px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        /* MESSAGE */

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* RESPONSIVE */

        @media (max-width: 900px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

        }

    </style>

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <h2>Admin Panel</h2>

    <ul>

        <li>
            <a href="../dashboard.php">Dashboard</a>
        </li>

        <li>
            <a href="index.php">Students</a>
        </li>

        <li>
            <a href="../lecturers/index.php">Lecturers</a>
        </li>

        <li>
            <a href="../courses/index.php">Courses</a>
        </li>

        <li>
            <a href="#">Reports</a>
        </li>

    </ul>

</div>


<!-- MAIN CONTENT -->

<div class="main-content">


    <!-- TOP BAR -->

    <div class="topbar">

        <h1>Add Student</h1>

        <a href="index.php" class="back-btn">
            ← Back to Students
        </a>

    </div>


    <!-- FORM -->

    <div class="form-container">

        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">


            <!-- STUDENT NUMBER -->

            <div class="form-group">

                <label for="student_number">
                    Student Number <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="student_number"
                    name="student_number"
                    placeholder="e.g. KAFU/CS/001"
                    value="<?php echo isset($_POST["student_number"]) ? htmlspecialchars($_POST["student_number"]) : ""; ?>"
                    required
                >

            </div>


            <!-- FULL NAME -->

            <div class="form-group">

                <label for="full_name">
                    Full Name <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter student's full name"
                    value="<?php echo isset($_POST["full_name"]) ? htmlspecialchars($_POST["full_name"]) : ""; ?>"
                    required
                >

            </div>


            <!-- PHONE -->

            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="e.g. 0712345678"
                    value="<?php echo isset($_POST["phone"]) ? htmlspecialchars($_POST["phone"]) : ""; ?>"
                >

            </div>


            <!-- GENDER -->

            <div class="form-group">

                <label for="gender">
                    Gender <span class="required">*</span>
                </label>

                <select
                    id="gender"
                    name="gender"
                    required
                >

                    <option value="">-- Select Gender --</option>

                    <option
                        value="Male"
                        <?php echo (isset($_POST["gender"]) && $_POST["gender"] === "Male") ? "selected" : ""; ?>
                    >
                        Male
                    </option>

                    <option
                        value="Female"
                        <?php echo (isset($_POST["gender"]) && $_POST["gender"] === "Female") ? "selected" : ""; ?>
                    >
                        Female
                    </option>

                    <option
                        value="Other"
                        <?php echo (isset($_POST["gender"]) && $_POST["gender"] === "Other") ? "selected" : ""; ?>
                    >
                        Other
                    </option>

                </select>

            </div>


            <!-- PROGRAM -->

            <div class="form-group">

                <label for="program">
                    Program <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="program"
                    name="program"
                    placeholder="e.g. Bachelor of Science in Computer Science"
                    value="<?php echo isset($_POST["program"]) ? htmlspecialchars($_POST["program"]) : ""; ?>"
                    required
                >

            </div>


            <!-- YEAR OF STUDY -->

            <div class="form-group">

                <label for="year_of_study">
                    Year of Study <span class="required">*</span>
                </label>

                <select
                    id="year_of_study"
                    name="year_of_study"
                    required
                >

                    <option value="">-- Select Year --</option>

                    <option value="1"
                        <?php echo (isset($_POST["year_of_study"]) && $_POST["year_of_study"] == "1") ? "selected" : ""; ?>>
                        Year 1
                    </option>

                    <option value="2"
                        <?php echo (isset($_POST["year_of_study"]) && $_POST["year_of_study"] == "2") ? "selected" : ""; ?>>
                        Year 2
                    </option>

                    <option value="3"
                        <?php echo (isset($_POST["year_of_study"]) && $_POST["year_of_study"] == "3") ? "selected" : ""; ?>>
                        Year 3
                    </option>

                    <option value="4"
                        <?php echo (isset($_POST["year_of_study"]) && $_POST["year_of_study"] == "4") ? "selected" : ""; ?>>
                        Year 4
                    </option>

                </select>

            </div>


            <!-- ADMISSION DATE -->

            <div class="form-group">

                <label for="admission_date">
                    Admission Date <span class="required">*</span>
                </label>

                <input
                    type="date"
                    id="admission_date"
                    name="admission_date"
                    value="<?php echo isset($_POST["admission_date"]) ? htmlspecialchars($_POST["admission_date"]) : ""; ?>"
                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="form-buttons">

                <button type="submit" class="save-btn">
                    Save Student
                </button>

                <a href="index.php" class="cancel-btn">
                    Cancel
                </a>

            </div>


        </form>

    </div>

</div>


</body>

</html>