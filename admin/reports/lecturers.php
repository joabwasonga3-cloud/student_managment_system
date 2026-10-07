```php
<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// Summary
$total_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lecturers");
$total_lecturers = mysqli_fetch_assoc($total_query)["total"];

$active_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM lecturers WHERE status = 'active'"
);
$active_lecturers = mysqli_fetch_assoc($active_query)["total"];

$inactive_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM lecturers WHERE status = 'inactive'"
);
$inactive_lecturers = mysqli_fetch_assoc($inactive_query)["total"];


// Report
$sql = "
    SELECT
        l.lecturer_id,
        l.full_name,
        u.email,
        l.phone,
        l.department,
        l.status
    FROM lecturers l
    INNER JOIN users u ON l.user_id = u.user_id
    ORDER BY l.full_name ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error generating lecturer report: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lecturer Report</title>

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
    text-decoration: none;
    border: none;
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

.card {
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

.report {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
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
    white-space: nowrap;
}

th {
    background: #f1f5f9;
}

.active {
    color: #15803d;
    font-weight: bold;
}

.inactive {
    color: #dc2626;
    font-weight: bold;
}

@media print {

    .buttons,
    .summary {
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

@media (max-width: 700px) {

    .summary {
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

        <h1>Lecturer Report</h1>

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
            <h3>Total Lecturers</h3>
            <div class="number">
                <?php echo $total_lecturers; ?>
            </div>
        </div>

        <div class="card">
            <h3>Active Lecturers</h3>
            <div class="number">
                <?php echo $active_lecturers; ?>
            </div>
        </div>

        <div class="card">
            <h3>Inactive Lecturers</h3>
            <div class="number">
                <?php echo $inactive_lecturers; ?>
            </div>
        </div>

    </div>


    <div class="report">

        <h2>Lecturer Information</h2>

        <div class="table-container">

            <?php if (mysqli_num_rows($result) > 0): ?>

            <table>

                <thead>

                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Status</th>
                </tr>

                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($row["full_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["email"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["phone"] ?? "N/A"); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row["department"]); ?>
                    </td>

                    <td>

                        <?php if ($row["status"] === "active"): ?>

                            <span class="active">Active</span>

                        <?php else: ?>

                            <span class="inactive">Inactive</span>

                        <?php endif; ?>

                    </td>

                </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

            <?php else: ?>

                <p>No lecturer records found.</p>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
```