```php
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

// Get all lecturers with their email from users table
$sql = "SELECT 
            lecturers.lecturer_id,
            lecturers.user_id,
            lecturers.full_name,
            users.email,
            lecturers.phone,
            lecturers.department,
            lecturers.status
        FROM lecturers
        INNER JOIN users ON lecturers.user_id = users.user_id
        ORDER BY lecturers.lecturer_id DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lecturers - Student Management System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background-color: #212529;
            color: white;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px 20px;
        }

        .sidebar a:hover {
            background-color: #343a40;
        }

        /* Main content */
        .main-content {
            margin-left: 240px;
            padding: 30px;
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

        .add-btn {
            background-color: #198754;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
        }

        .add-btn:hover {
            background-color: #157347;
        }

        /* Table */
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #343a40;
            color: white;
        }

        table tr:hover {
            background-color: #f8f9fa;
        }

        /* Status */
        .status-active {
            color: #198754;
            font-weight: bold;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }

        /* Action buttons */
        .action-btn {
            display: inline-block;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 4px;
            font-size: 14px;
        }

        .view-btn {
            background-color: #0dcaf0;
            color: black;
        }

        .view-btn:hover {
            background-color: #31d2f2;
        }

        .edit-btn {
            background-color: #ffc107;
            color: black;
        }

        .edit-btn:hover {
            background-color: #ffca2c;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #bb2d3b;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
        }

    </style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">

    <h2>Admin Panel</h2>

    <a href="../dashboard.php">Dashboard</a>

    <a href="../students/index.php">Students</a>

    <a href="index.php">Lecturers</a>

    <a href="../courses/index.php">Courses</a>

    <a href="../reports/index.php">Reports</a>

    <a href="../../auth/logout.php">Logout</a>

</div>


<!-- Main Content -->
<div class="main-content">

    <div class="header">

        <h1>Lecturers</h1>

        <a href="create.php" class="add-btn">
            + Add Lecturer
        </a>

    </div>


    <div class="table-container">

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($lecturer = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($lecturer["lecturer_id"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($lecturer["full_name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($lecturer["email"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($lecturer["phone"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($lecturer["department"]); ?>
                        </td>

                        <td>

                            <?php if ($lecturer["status"] === "active"): ?>

                                <span class="status-active">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="status-inactive">
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <!-- View -->
                            <a href="view.php?id=<?php echo $lecturer["lecturer_id"]; ?>"
                               class="action-btn view-btn">
                                View
                            </a>

                            <!-- Edit -->
                            <a href="edit.php?id=<?php echo $lecturer["lecturer_id"]; ?>"
                               class="action-btn edit-btn">
                                Edit
                            </a>

                            <!-- Delete -->
                            <a href="delete.php?id=<?php echo $lecturer["lecturer_id"]; ?>"
                               class="action-btn delete-btn"
                               onclick="return confirm('Are you sure you want to delete this lecturer?');">
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" class="no-data">
                        No lecturers found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
