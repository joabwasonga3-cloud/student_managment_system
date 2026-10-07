```php
<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// Summary
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM courses");
$total_courses = mysqli_fetch_assoc($total_query)["total"];

$assigned_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM courses
     WHERE lecturer_id IS NOT NULL"
);

$assigned_courses = mysqli_fetch_assoc($assigned_query)["total"];

$unassigned_courses = $total_courses - $assigned_courses;


// Report
$sql = "
    SELECT
        c.course_code,
        c.course_name,
        c.description,
        c.credit_hours,
        l.full_name AS lecturer_name
    FROM courses c
    LEFT JOIN lecturers l
        ON c.lecturer_id = l.lecturer_id
    ORDER BY c.course_code ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error generating course report: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Course Report</title>

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
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #f1f5f9;
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

        <h1>Course Report</h1>

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
            <h3>Total Courses</h3>
            <div class="number">
                <?php echo $total_courses; ?>
            </div>
        </div>

        <div class="card">
            <h3>Assigned Courses</h3>
            <div class="number">
                <?php echo $assigned_courses; ?>
            </div>
        </div>

        <div class="card">
            <h3>Unassigned Courses</h3>
            <div class="number">
                <?php echo $unassigned_courses; ?>
            </div>
        </div>

    </div>


    <div class="report">

        <h2>Course Information</h2>

        <div class="table-container">

            <?php if (mysqli_num_rows($result) > 0): ?>

            <table>

                <thead>

                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Credit Hours</th>
                    <th>Lecturer</th>
                </tr>

                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($row["course_code"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["course_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["description"] ?? "N/A"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["credit_hours"]); ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $row["lecturer_name"] ?? "Not Assigned"
                        );
                        ?>
                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

            <?php else: ?>

                <p>No course records found.</p>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
```