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


// Get payment information
$sql = "
    SELECT
        p.payment_id,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.reference_number,
        s.student_number,
        s.full_name
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


// Delete payment
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $delete_sql = "
        DELETE FROM payments
        WHERE payment_id = ?
    ";

    $delete_stmt = $conn->prepare($delete_sql);

    $delete_stmt->bind_param("i", $payment_id);

    if ($delete_stmt->execute()) {

        $delete_stmt->close();

        header("Location: index.php?success=deleted");
        exit();

    } else {

        $error = "Error deleting payment: " . $delete_stmt->error;

        $delete_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Delete Payment - Student Management System</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            width: 600px;
            max-width: 95%;
            margin: 60px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            color: #dc2626;
        }

        .warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .details {
            margin: 20px 0;
            padding: 15px;
            background: #f8fafc;
            border-radius: 5px;
        }

        .details p {
            margin: 8px 0;
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

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>Delete Payment</h1>


        <?php if (isset($error)): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <div class="warning">

            <strong>Warning:</strong>

            You are about to permanently delete this payment record.

            This action cannot be undone.

        </div>


        <div class="details">

            <p>
                <strong>Student:</strong>
                <?php echo htmlspecialchars($payment["full_name"]); ?>
            </p>

            <p>
                <strong>Student Number:</strong>
                <?php echo htmlspecialchars($payment["student_number"]); ?>
            </p>

            <p>
                <strong>Amount:</strong>
                KSh <?php echo number_format($payment["amount"], 2); ?>
            </p>

            <p>
                <strong>Date:</strong>
                <?php echo htmlspecialchars($payment["payment_date"]); ?>
            </p>

            <p>
                <strong>Method:</strong>
                <?php echo htmlspecialchars($payment["payment_method"]); ?>
            </p>

            <p>
                <strong>Reference:</strong>

                <?php
                echo $payment["reference_number"]
                    ? htmlspecialchars($payment["reference_number"])
                    : "N/A";
                ?>

            </p>

        </div>


        <form method="POST">

            <button
                type="submit"
                class="btn btn-delete"
            >
                Yes, Delete Payment
            </button>

            <a
                href="index.php"
                class="btn btn-cancel"
            >
                Cancel
            </a>

        </form>

    </div>

</div>

</body>

</html>