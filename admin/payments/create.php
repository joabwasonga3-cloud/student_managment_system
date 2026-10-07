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

$error = "";

// Get students
$students_sql = "
    SELECT student_id, student_number, full_name
    FROM students
    WHERE status = 'active'
    ORDER BY full_name ASC
";

$students_result = mysqli_query($conn, $students_sql);

if (!$students_result) {
    die("Error fetching students: " . mysqli_error($conn));
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = intval($_POST["student_id"] ?? 0);
    $amount = trim($_POST["amount"] ?? "");
    $payment_date = $_POST["payment_date"] ?? "";
    $payment_method = $_POST["payment_method"] ?? "";
    $reference_number = trim($_POST["reference_number"] ?? "");
    $academic_year = trim($_POST["academic_year"] ?? "");
    $semester = $_POST["semester"] ?? "";


    // Validation
    if ($student_id <= 0) {

        $error = "Please select a student.";

    } elseif ($amount === "" || !is_numeric($amount) || $amount <= 0) {

        $error = "Please enter a valid payment amount.";

    } elseif ($payment_date === "") {

        $error = "Please select the payment date.";

    } elseif (!in_array($payment_method, ["Cash", "M-Pesa", "Bank", "Card"])) {

        $error = "Please select a valid payment method.";

    } elseif ($academic_year === "") {

        $error = "Please enter the academic year.";

    } elseif (!in_array($semester, ["Semester 1", "Semester 2", "Semester 3"])) {

        $error = "Please select a valid semester.";

    } else {

        // Check that student exists
        $check_student = $conn->prepare("
            SELECT student_id
            FROM students
            WHERE student_id = ?
        ");

        $check_student->bind_param("i", $student_id);
        $check_student->execute();

        $student_result = $check_student->get_result();

        if ($student_result->num_rows === 0) {

            $error = "Selected student does not exist.";

        } else {

            // Check duplicate reference number if provided
            if ($reference_number !== "") {

                $check_reference = $conn->prepare("
                    SELECT payment_id
                    FROM payments
                    WHERE reference_number = ?
                ");

                $check_reference->bind_param("s", $reference_number);
                $check_reference->execute();

                $reference_result = $check_reference->get_result();

                if ($reference_result->num_rows > 0) {
                    $error = "This reference number already exists.";
                }

                $check_reference->close();
            }


            if ($error === "") {

                $sql = "
                    INSERT INTO payments
                    (
                        student_id,
                        amount,
                        payment_date,
                        payment_method,
                        reference_number,
                        academic_year,
                        semester
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = $conn->prepare($sql);

                // Convert empty reference to NULL
                $reference_value = $reference_number !== ""
                    ? $reference_number
                    : null;

                $amount_value = (float)$amount;

                $stmt->bind_param(
                    "idsssss",
                    $student_id,
                    $amount_value,
                    $payment_date,
                    $payment_method,
                    $reference_value,
                    $academic_year,
                    $semester
                );

                if ($stmt->execute()) {

                    header("Location: index.php?success=added");
                    exit();

                } else {

                    $error = "Error recording payment: " . $stmt->error;
                }

                $stmt->close();
            }
        }

        $check_student->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Record Payment - Student Management System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            width: 700px;
            max-width: 95%;
            margin: 40px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
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
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .buttons {
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 11px 17px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Record Payment</h1>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label for="student_id">
                    Student
                </label>

                <select name="student_id" id="student_id" required>

                    <option value="">
                        -- Select Student --
                    </option>

                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>

                        <option
                            value="<?php echo $student["student_id"]; ?>"
                            <?php
                            echo (
                                isset($_POST["student_id"]) &&
                                $_POST["student_id"] == $student["student_id"]
                            ) ? "selected" : "";
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $student["student_number"] .
                                " - " .
                                $student["full_name"]
                            );
                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="amount">
                    Amount (KSh)
                </label>

                <input
                    type="number"
                    name="amount"
                    id="amount"
                    step="0.01"
                    min="0.01"
                    value="<?php echo htmlspecialchars($_POST["amount"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="payment_date">
                    Payment Date
                </label>

                <input
                    type="date"
                    name="payment_date"
                    id="payment_date"
                    value="<?php echo htmlspecialchars($_POST["payment_date"] ?? date("Y-m-d")); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="payment_method">
                    Payment Method
                </label>

                <select name="payment_method" id="payment_method" required>

                    <option value="">
                        -- Select Method --
                    </option>

                    <option value="Cash">
                        Cash
                    </option>

                    <option value="M-Pesa">
                        M-Pesa
                    </option>

                    <option value="Bank">
                        Bank
                    </option>

                    <option value="Card">
                        Card
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label for="reference_number">
                    Reference Number
                </label>

                <input
                    type="text"
                    name="reference_number"
                    id="reference_number"
                    maxlength="50"
                    placeholder="e.g. M-Pesa transaction code"
                    value="<?php echo htmlspecialchars($_POST["reference_number"] ?? ""); ?>"
                >

            </div>


            <div class="form-group">

                <label for="academic_year">
                    Academic Year
                </label>

                <input
                    type="text"
                    name="academic_year"
                    id="academic_year"
                    maxlength="20"
                    placeholder="e.g. 2026/2027"
                    value="<?php echo htmlspecialchars($_POST["academic_year"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="semester">
                    Semester
                </label>

                <select name="semester" id="semester" required>

                    <option value="">
                        -- Select Semester --
                    </option>

                    <option value="Semester 1">
                        Semester 1
                    </option>

                    <option value="Semester 2">
                        Semester 2
                    </option>

                    <option value="Semester 3">
                        Semester 3
                    </option>

                </select>

            </div>


            <div class="buttons">

                <button type="submit" class="btn btn-primary">
                    Record Payment
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>