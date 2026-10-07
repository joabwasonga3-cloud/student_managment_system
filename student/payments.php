<?php
session_start();

require_once "../config/database.php";

// Check login
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get student profile
$stmt = $conn->prepare(
    "SELECT student_id, full_name, student_number
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student profile not found. Please contact the administrator.");
}

$student_id = $student["student_id"];

// Payment summary
$stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_transactions,
        COALESCE(SUM(amount), 0) AS total_paid
     FROM payments
     WHERE student_id = ?"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$summary = $stmt->get_result()->fetch_assoc();

$total_transactions = (int)$summary["total_transactions"];
$total_paid = (float)$summary["total_paid"];

// Fetch payment history
$stmt = $conn->prepare(
    "SELECT
        payment_id,
        amount,
        payment_date,
        payment_method,
        reference_number,
        academic_year,
        semester
     FROM payments
     WHERE student_id = ?
     ORDER BY payment_date DESC, payment_id DESC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$payments = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Payments | Student Portal</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    color: #333;
}

.sidebar {
    width: 240px;
    height: 100vh;
    background: #1e293b;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    padding: 25px 15px;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 13px 15px;
    border-radius: 6px;
    margin-bottom: 8px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #334155;
    color: white;
}

.main {
    margin-left: 240px;
    padding: 30px;
}

.header {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.card h3 {
    color: #64748b;
    font-size: 14px;
    margin-top: 0;
}

.card p {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 0;
}

.table-container {
    background: white;
    padding: 22px;
    border-radius: 10px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th, td {
    padding: 13px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}

th {
    background: #f8fafc;
}

.back-link {
    display: inline-block;
    margin-bottom: 18px;
    color: #2563eb;
    text-decoration: none;
}

.note {
    margin-top: 20px;
    color: #64748b;
    font-size: 13px;
}

@media (max-width: 650px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .cards {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>Student Portal</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="courses.php">My Courses</a>
    <a href="results.php">My Results</a>
    <a href="attendance.php">My Attendance</a>
    <a href="payments.php" class="active">My Payments</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <div class="header">
        <h2>My Payments</h2>
        <p>
            Student: <?php echo e($student["full_name"]); ?>
            |
            Student Number: <?php echo e($student["student_number"]); ?>
        </p>
    </div>

    <div class="cards">

        <div class="card">
            <h3>Total Amount Paid</h3>
            <p>Ksh <?php echo number_format($total_paid, 2); ?></p>
        </div>

        <div class="card">
            <h3>Total Transactions</h3>
            <p><?php echo $total_transactions; ?></p>
        </div>

    </div>

    <div class="table-container">

        <h3>Payment History</h3>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Amount (Ksh)</th>
                    <th>Payment Date</th>
                    <th>Payment Method</th>
                    <th>Reference Number</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($payments->num_rows > 0): ?>

                <?php $count = 1; ?>

                <?php while ($row = $payments->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo number_format((float)$row["amount"], 2); ?></td>
                        <td><?php echo e($row["payment_date"]); ?></td>
                        <td><?php echo e($row["payment_method"]); ?></td>
                        <td><?php echo e($row["reference_number"] ?: "—"); ?></td>
                        <td><?php echo e($row["academic_year"] ?: "—"); ?></td>
                        <td><?php echo e($row["semester"] ?: "—"); ?></td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" style="text-align:center;">
                        No payment records available yet.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

        <p class="note">
            For payment corrections or inquiries, please contact the university
            administration or finance office.
        </p>

    </div>

</div>

</body>
</html>