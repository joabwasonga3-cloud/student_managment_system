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

// Get all payments
$sql = "
    SELECT 
        p.payment_id,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.reference_number,
        p.academic_year,
        p.semester,
        s.student_id,
        s.student_number,
        s.full_name
    FROM payments p
    INNER JOIN students s ON p.student_id = s.student_id
    ORDER BY p.payment_date DESC, p.payment_id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error fetching payments: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payments - Student Management System</title>

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
            width: 95%;
            margin: 30px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            color: #222;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2563eb;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-view {
            background: #16a34a;
        }

        .btn-edit {
            background: #f59e0b;
        }

        .btn-delete {
            background: #dc2626;
        }

        .btn-dashboard {
            background: #6b7280;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #dcfce7;
            color: #166534;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            color: #333;
        }

        tr:hover {
            background: #f8fafc;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a {
            margin-right: 5px;
        }

        .amount {
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">

        <h1>Payments</h1>

        <div>
            <a href="../dashboard.php" class="btn btn-dashboard">
                Dashboard
            </a>

            <a href="create.php" class="btn btn-primary">
                + Record Payment
            </a>
        </div>

    </div>

    <?php if (isset($_GET["success"])): ?>

        <?php if ($_GET["success"] === "added"): ?>
            <div class="message">
                Payment recorded successfully.
            </div>
        <?php elseif ($_GET["success"] === "updated"): ?>
            <div class="message">
                Payment updated successfully.
            </div>
        <?php elseif ($_GET["success"] === "deleted"): ?>
            <div class="message">
                Payment deleted successfully.
            </div>
        <?php endif; ?>

    <?php endif; ?>


    <div class="table-container">

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Student Number</th>
                    <th>Student Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php $count = 1; ?>

                <?php while ($payment = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php echo $count++; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["student_number"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["full_name"]); ?>
                        </td>

                        <td class="amount">
                            KSh <?php echo number_format($payment["amount"], 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["payment_date"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["payment_method"]); ?>
                        </td>

                        <td>
                            <?php
                            echo $payment["reference_number"]
                                ? htmlspecialchars($payment["reference_number"])
                                : "N/A";
                            ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["academic_year"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["semester"]); ?>
                        </td>

                        <td class="actions">

                            <a
                                href="view.php?id=<?php echo $payment["payment_id"]; ?>"
                                class="btn btn-view"
                            >
                                View
                            </a>

                            <a
                                href="edit.php?id=<?php echo $payment["payment_id"]; ?>"
                                class="btn btn-edit"
                            >
                                Edit
                            </a>

                            <a
                                href="delete.php?id=<?php echo $payment["payment_id"]; ?>"
                                class="btn btn-delete"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="10" style="text-align:center;">
                        No payment records found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>