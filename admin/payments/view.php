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


// Get payment details
$sql = "
    SELECT
        p.payment_id,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.reference_number,
        p.academic_year,
        p.semester,
        s.student_number,
        s.full_name,
        s.phone,
        s.program
    FROM payments p
    INNER JOIN students s ON p.student_id = s.student_id
    WHERE p.payment_id = ?
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Payment - Student Management System</title>

    <style>

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

        .detail {
            display: flex;
            justify-content: space-between;
            padding: 13px 0;
            border-bottom: 1px solid #eee;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        .value {
            text-align: right;
        }

        .amount {
            font-size: 20px;
            font-weight: bold;
        }

        .buttons {
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            margin-right: 5px;
        }

        .btn-edit {
            background: #f59e0b;
        }

        .btn-back {
            background: #6b7280;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Payment Details</h1>


        <div class="detail">

            <span class="label">
                Student Number
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["student_number"]); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Student Name
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["full_name"]); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Program
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["program"]); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Phone
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["phone"] ?? "N/A"); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Amount
            </span>

            <span class="value amount">
                KSh <?php echo number_format($payment["amount"], 2); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Payment Date
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["payment_date"]); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Payment Method
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["payment_method"]); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Reference Number
            </span>

            <span class="value">
                <?php
                echo $payment["reference_number"]
                    ? htmlspecialchars($payment["reference_number"])
                    : "N/A";
                ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Academic Year
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["academic_year"]); ?>
            </span>

        </div>


        <div class="detail">

            <span class="label">
                Semester
            </span>

            <span class="value">
                <?php echo htmlspecialchars($payment["semester"]); ?>
            </span>

        </div>


        <div class="buttons">

            <a
                href="edit.php?id=<?php echo $payment["payment_id"]; ?>"
                class="btn btn-edit"
            >
                Edit Payment
            </a>

            <a
                href="index.php"
                class="btn btn-back"
            >
                Back to Payments
            </a>

        </div>

    </div>

</div>

</body>

</html>