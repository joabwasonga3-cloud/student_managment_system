<?php

session_start();

require_once "../../config/database.php";

// Authentication
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// Get payment ID
$payment_id = intval($_GET["id"] ?? 0);

if ($payment_id <= 0) {
    header("Location: index.php");
    exit();
}


// Get current payment
$sql = "
    SELECT
        payment_id,
        student_id,
        amount,
        payment_date,
        payment_method,
        reference_number,
        academic_year,
        semester
    FROM payments
    WHERE payment_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$payment = $result->fetch_assoc();

$stmt->close();


$error = "";


// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amount = trim($_POST["amount"] ?? "");
    $payment_date = $_POST["payment_date"] ?? "";
    $payment_method = $_POST["payment_method"] ?? "";
    $reference_number = trim($_POST["reference_number"] ?? "");
    $academic_year = trim($_POST["academic_year"] ?? "");
    $semester = $_POST["semester"] ?? "";


    if ($amount === "" || !is_numeric($amount) || $amount <= 0) {

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

        // Check reference number
        if ($reference_number !== "") {

            $check_reference = $conn->prepare("
                SELECT payment_id
                FROM payments
                WHERE reference_number = ?
                AND payment_id != ?
            ");

            $check_reference->bind_param(
                "si",
                $reference_number,
                $payment_id
            );

            $check_reference->execute();

            $reference_result = $check_reference->get_result();

            if ($reference_result->num_rows > 0) {

                $error = "This reference number already exists.";

            }

            $check_reference->close();
        }


        if ($error === "") {

            $sql = "
                UPDATE payments
                SET
                    amount = ?,
                    payment_date = ?,
                    payment_method = ?,
                    reference_number = ?,
                    academic_year = ?,
                    semester = ?
                WHERE payment_id = ?
            ";

            $stmt = $conn->prepare($sql);

            $reference_value = $reference_number !== ""
                ? $reference_number
                : null;

            $amount_value = (float)$amount;

            $stmt->bind_param(
                "dsssssi",
                $amount_value,
                $payment_date,
                $payment_method,
                $reference_value,
                $academic_year,
                $semester,
                $payment_id
            );

            if ($stmt->execute()) {

                header("Location: index.php?success=updated");
                exit();

            } else {

                $error = "Error updating payment: " . $stmt->error;
            }

            $stmt->close();
        }
    }

    // Preserve entered values
    $payment["amount"] = $amount;
    $payment["payment_date"] = $payment_date;
    $payment["payment_method"] = $payment_method;
    $payment["reference_number"] = $reference_number;
    $payment["academic_year"] = $academic_year;
    $payment["semester"] = $semester;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Payment - Student Management System</title>

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

        .readonly {
            background: #f1f5f9;
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

        <h1>Edit Payment</h1>


        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label>
                    Student
                </label>

                <input
                    type="text"
                    class="readonly"
                    value="<?php echo htmlspecialchars($payment["student_id"]); ?>"
                    readonly
                >

                <small>
                    Student cannot be changed after the payment is recorded.
                </small>

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
                    value="<?php echo htmlspecialchars($payment["amount"]); ?>"
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
                    value="<?php echo htmlspecialchars($payment["payment_date"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="payment_method">
                    Payment Method
                </label>

                <select
                    name="payment_method"
                    id="payment_method"
                    required
                >

                    <option value="Cash"
                        <?php echo $payment["payment_method"] === "Cash" ? "selected" : ""; ?>>
                        Cash
                    </option>

                    <option value="M-Pesa"
                        <?php echo $payment["payment_method"] === "M-Pesa" ? "selected" : ""; ?>>
                        M-Pesa
                    </option>

                    <option value="Bank"
                        <?php echo $payment["payment_method"] === "Bank" ? "selected" : ""; ?>>
                        Bank
                    </option>

                    <option value="Card"
                        <?php echo $payment["payment_method"] === "Card" ? "selected" : ""; ?>>
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
                    value="<?php echo htmlspecialchars($payment["reference_number"] ?? ""); ?>"
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
                    value="<?php echo htmlspecialchars($payment["academic_year"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="semester">
                    Semester
                </label>

                <select
                    name="semester"
                    id="semester"
                    required
                >

                    <option value="Semester 1"
                        <?php echo $payment["semester"] === "Semester 1" ? "selected" : ""; ?>>
                        Semester 1
                    </option>

                    <option value="Semester 2"
                        <?php echo $payment["semester"] === "Semester 2" ? "selected" : ""; ?>>
                        Semester 2
                    </option>

                    <option value="Semester 3"
                        <?php echo $payment["semester"] === "Semester 3" ? "selected" : ""; ?>>
                        Semester 3
                    </option>

                </select>

            </div>


            <div class="buttons">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Payment
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>