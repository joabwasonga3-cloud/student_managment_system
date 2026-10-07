```php
<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// --------------------------------------------------
// SYSTEM STATISTICS
// --------------------------------------------------

$students = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM students"
    )
)["total"];


$lecturers = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM lecturers"
    )
)["total"];


$courses = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM courses"
    )
)["total"];


$enrollments = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM enrollments"
    )
)["total"];


$attendance = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM attendance"
    )
)["total"];


$results = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM results"
    )
)["total"];


$payment_data = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT
            COUNT(*) AS transactions,
            COALESCE(SUM(amount), 0) AS total
         FROM payments"
    )
);

$payment_transactions = $payment_data["transactions"];
$total_payments = $payment_data["total"];


// --------------------------------------------------
// ACTIVE USERS
// --------------------------------------------------

$active_users = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE status = 'active'"
    )
)["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    System Summary | Student Management System
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    color: #222;
}

.container {
    width: 95%;
    max-width: 1200px;
    margin: 30px auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0;
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

.section {
    margin-bottom: 25px;
}

.section h2 {
    margin-bottom: 15px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.card {
    background: white;
    padding: 22px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.card h3 {
    margin: 0 0 10px;
    color: #555;
    font-size: 15px;
}

.number {
    font-size: 28px;
    font-weight: bold;
}

.payment {
    color: #15803d;
}

.note {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    line-height: 1.6;
}

@media print {

    .buttons {
        display: none;
    }

    body {
        background: white;
    }

    .container {
        width: 100%;
        margin: 0;
    }

}

@media (max-width: 900px) {

    .grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 600px) {

    .grid {
        grid-template-columns: 1fr;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

}

</style>

</head>

<body>

<div class="container">


    <div class="header">

        <h1>
            System Summary
        </h1>


        <div class="buttons">

            <a
                href="index.php"
                class="btn back"
            >
                Reports
            </a>


            <button
                onclick="window.print()"
                class="btn print"
            >
                Print Summary
            </button>

        </div>

    </div>


    <!-- Academic Information -->

    <div class="section">

        <h2>
            Academic Information
        </h2>


        <div class="grid">


            <div class="card">

                <h3>
                    Total Students
                </h3>

                <div class="number">
                    <?php echo $students; ?>
                </div>

            </div>


            <div class="card">

                <h3>
                    Total Lecturers
                </h3>

                <div class="number">
                    <?php echo $lecturers; ?>
                </div>

            </div>


            <div class="card">

                <h3>
                    Total Courses
                </h3>

                <div class="number">
                    <?php echo $courses; ?>
                </div>

            </div>


            <div class="card">

                <h3>
                    Total Enrollments
                </h3>

                <div class="number">
                    <?php echo $enrollments; ?>
                </div>

            </div>


        </div>

    </div>


    <!-- Academic Records -->

    <div class="section">

        <h2>
            Academic Records
        </h2>


        <div class="grid">


            <div class="card">

                <h3>
                    Attendance Records
                </h3>

                <div class="number">
                    <?php echo $attendance; ?>
                </div>

            </div>


            <div class="card">

                <h3>
                    Results Records
                </h3>

                <div class="number">
                    <?php echo $results; ?>
                </div>

            </div>


            <div class="card">

                <h3>
                    Payment Transactions
                </h3>

                <div class="number">
                    <?php echo $payment_transactions; ?>
                </div>

            </div>


            <div class="card">

                <h3>
                    Active Users
                </h3>

                <div class="number">
                    <?php echo $active_users; ?>
                </div>

            </div>


        </div>

    </div>


    <!-- Financial Information -->

    <div class="section">

        <h2>
            Financial Information
        </h2>


        <div class="grid">


            <div class="card">

                <h3>
                    Total Payments Collected
                </h3>

                <div class="number payment">

                    KSh
                    <?php
                    echo number_format(
                        $total_payments,
                        2
                    );
                    ?>

                </div>

            </div>


        </div>

    </div>


    <div class="note">

        <strong>
            Report Description:
        </strong>

        This report provides an overall summary of the
        information currently stored in the Student Management
        System. Individual reports can be accessed from the
        Reports page.

    </div>


</div>

</body>

</html>
```