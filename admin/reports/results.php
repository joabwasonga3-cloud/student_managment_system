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
    "SELECT COUNT(*) AS total FROM results"
);

$total_results = mysqli_fetch_assoc($total_query)["total"];


$passed_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM results
     WHERE grade NOT IN ('E', 'F')"
);

$passed_results = mysqli_fetch_assoc($passed_query)["total"];


$failed_results = $total_results - $passed_results;


// Report
$sql = "
    SELECT
        s.student_number,
        s.full_name AS student_name,
        c.course_code,
        c.course_name,
        r.cat_marks,
        r.exam_marks,
        r.total_marks,
        r.grade,
        r.remarks

    FROM results r

    INNER JOIN enrollments e
        ON r.enrollment_id = e.enrollment_id

    INNER JOIN students s
        ON e.student_id = s.student_id

    INNER JOIN courses c
        ON e.course_id = c.course_id

    ORDER BY s.full_name ASC, c.course_code ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error generating results report: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Results Report</title>

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
    font-size: 28px;
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

.pass {
    color: #15803d;
    font-weight: bold;
}

.fail {
    color: #dc2626;
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

        <h1>Results Report</h1>

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
            <h3>Total Results</h3>
            <div class="number">
                <?php echo $total_results; ?>
            </div>
        </div>

        <div class="card">
            <h3>Passed</h3>
            <div class="number">
                <?php echo $passed_results; ?>
            </div>
        </div>

        <div class="card">
            <h3>Failed</h3>
            <div class="number">
                <?php echo $failed_results; ?>
            </div>
        </div>

    </div>


    <div class="report">

        <h2>Academic Results</h2>

        <div class="table-container">

            <?php if (mysqli_num_rows($result) > 0): ?>

            <table>

                <thead>

                <tr>
                    <th>Student Number</th>
                    <th>Student</th>
                    <th>Course Code</th>
                    <th>Course</th>
                    <th>CAT</th>
                    <th>Exam</th>
                    <th>Total</th>
                    <th>Grade</th>
                    <th>Remarks</th>
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

                    <td>
                        <?php echo htmlspecialchars($row["course_code"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["course_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["cat_marks"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["exam_marks"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["total_marks"]); ?>
                    </td>

                    <td>

                        <?php
                        $grade = strtoupper($row["grade"]);

                        if (in_array($grade, ["E", "F"])) {
                            echo '<span class="fail">';
                        } else {
                            echo '<span class="pass">';
                        }

                        echo htmlspecialchars($grade);
                        echo '</span>';
                        ?>

                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["remarks"] ?? ""); ?>
                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

            <?php else: ?>

                <p>No results found.</p>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
```