```php
<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// Summary
$total_query = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments"
);

$total_payments = mysqli_fetch_assoc($total_query)["total"];


$count_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM payments"
);

$total_transactions = mysqli_fetch_assoc($count_query)["total"];


$average_payment = $total_transactions > 0
    ? $total_payments / $total_transactions
    : 0;


// Report
$sql = "
    SELECT
        s.student_number,
        s.full_name AS student_name,
        p.amount,
        p.payment_date,
        p.payment_method,
        p.reference_number,
        p.academic_year,
        p.semester

    FROM payments p

    INNER JOIN students s
        ON p.student_id = s.student_id

    ORDER BY p.payment_date DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error generating payment report: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payment Report</title>

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
    margin-bottom: 25px;
}

.buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 10px 15px;
    border-radius: 5px;
    border: none;
    text-decoration: none;
    cursor: pointer;
    color: white;
}

.back {
    background: #6b7280;
}

.print {
    background: #2563eb;
}

.summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.card,
.report {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.card h3 {
    margin: 0 0 10px;
    color: #555;
}

.number {
    font-size: 27px;
    font-weight: bold;
}

.report h2 {
    margin-top: 0;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 11px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    white-space: nowrap;
}

th {
    background: #f1f5f9;
}

.amount {
    font-weight: bold;
}

@media print {

    .buttons,
    .summary {
        display: none;
    }

    .container {
        width: 100%;
        margin: 0;
    }

}

@media (max-width: 700px) {

    .summary {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>Payment Report</h1>

        <div class="buttons">

            <a href="index.php" class="btn back">
                Reports
            </a>

            <button onclick="window.print()" class="btn print">
                Print Report
            </button>

        </div>

    </div>


    <div class="summary">

        <div class="card">
            <h3>Total Payments</h3>

            <div class="number">
                KSh <?php echo number_format($total_payments, 2); ?>
            </div>
        </div>


        <div class="card">
            <h3>Total Transactions</h3>

            <div class="number">
                <?php echo $total_transactions; ?>
            </div>
        </div>


        <div class="card">
            <h3>Average Payment</h3>

            <div class="number">
                KSh <?php echo number_format($average_payment, 2); ?>
            </div>
        </div>

    </div>


    <div class="report">

        <h2>Payment Records</h2>

        <div class="table-container">

            <?php if (mysqli_num_rows($result) > 0): ?>

            <table>

                <thead>

                <tr>
                    <th>Student Number</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                </tr>

                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($row["student_number"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["student_name"]); ?>
                    </td>

                    <td class="amount">
                        KSh
                        <?php echo number_format($row["amount"], 2); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["payment_date"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["payment_method"]); ?>
                    </td>

                    <td>
                        <?php
                        echo $row["reference_number"]
                            ? htmlspecialchars($row["reference_number"])
                            : "N/A";
                        ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["academic_year"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["semester"]); ?>
                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

            <?php else: ?>

                <p>No payment records found.</p>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
```