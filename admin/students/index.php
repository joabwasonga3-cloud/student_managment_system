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

// Get all students
$sql = "SELECT 
            student_id,
            student_number,
            full_name,
            phone,
            gender,
            program,
            year_of_study,
            admission_date,
            status
        FROM students
        ORDER BY student_id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Students - Student Management System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background-color: #1f2937;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            display: block;
            padding: 12px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .sidebar ul li a:hover {
            background-color: #374151;
        }

        /* MAIN CONTENT */

        .main-content {
            margin-left: 240px;
            padding: 25px;
        }

        /* TOP BAR */

        .topbar {
            background-color: white;
            padding: 18px 25px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .topbar h1 {
            color: #333;
            font-size: 24px;
        }

        /* ADD BUTTON */

        .add-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 5px;
        }

        .add-btn:hover {
            background-color: #0056b3;
        }

        /* TABLE */

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
            background-color: #f8f9fa;
            color: #333;
        }

        table tr:hover {
            background-color: #f8f9fa;
        }

        /* STUDENT NUMBER LINK */

        .student-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .student-link:hover {
            text-decoration: underline;
        }

        /* ACTION LINKS */

        .action-link {
            text-decoration: none;
            margin-right: 8px;
            font-size: 14px;
        }

        .view-link {
            color: #007bff;
        }

        .edit-link {
            color: #28a745;
        }

        .delete-link {
            color: #dc3545;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        /* STATUS */

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
        }

        .active {
            background-color: #d4edda;
            color: #155724;
        }

        .inactive {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .graduated {
            background-color: #cce5ff;
            color: #004085;
        }

        .suspended {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* EMPTY MESSAGE */

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

        /* SUCCESS MESSAGE */

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        /* LOGOUT */

        .logout {
            margin-top: 30px;
        }

        .logout a {
            display: block;
            background-color: #dc3545;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 5px;
            text-decoration: none;
        }

        .logout a:hover {
            background-color: #b02a37;
        }

        /* RESPONSIVE */

        @media (max-width: 900px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

        }

    </style>

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <h2>Admin Panel</h2>

    <ul>

        <li>
            <a href="../dashboard.php">Dashboard</a>
        </li>

        <li>
            <a href="index.php">Students</a>
        </li>

        <li>
            <a href="../lecturers/index.php">Lecturers</a>
        </li>

        <li>
            <a href="../courses/index.php">Courses</a>
        </li>

        <li>
            <a href="#">Reports</a>
        </li>

    </ul>

    <div class="logout">

        <a href="../../auth/logout.php">Logout</a>

    </div>

</div>


<!-- MAIN CONTENT -->

<div class="main-content">


    <!-- TOP BAR -->

    <div class="topbar">

        <h1>Students</h1>

        <a href="create.php" class="add-btn">
            + Add Student
        </a>

    </div>


    <?php if (isset($_GET["success"]) && $_GET["success"] === "added"): ?>

        <div class="success-message">
            Student has been added successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["success"]) && $_GET["success"] === "updated"): ?>

        <div class="success-message">
            Student has been updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["deleted"]) && $_GET["deleted"] === "1"): ?>

        <div class="success-message">
            Student has been deleted successfully.
        </div>

    <?php endif; ?>


    <!-- STUDENT TABLE -->

    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>#</th>

                    <th>Student Number</th>

                    <th>Full Name</th>

                    <th>Phone</th>

                    <th>Gender</th>

                    <th>Program</th>

                    <th>Year</th>

                    <th>Admission Date</th>

                    <th>Status</th>

                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>

                <?php if ($result && mysqli_num_rows($result) > 0): ?>

                    <?php while ($student = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>
                                <?php echo $student["student_id"]; ?>
                            </td>


                            <td>

                                <a
                                    href="view.php?id=<?php echo $student["student_id"]; ?>"
                                    class="student-link"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $student["student_number"]
                                    );
                                    ?>

                                </a>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["full_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo !empty($student["phone"])
                                    ? htmlspecialchars($student["phone"])
                                    : "—";
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["gender"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["program"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["year_of_study"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["admission_date"]
                                );
                                ?>

                            </td>


                            <td>

                                <span
                                    class="status <?php echo strtolower($student["status"]); ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $student["status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <a
                                    href="view.php?id=<?php echo $student["student_id"]; ?>"
                                    class="action-link view-link"
                                >
                                    View
                                </a>


                                <a
                                    href="edit.php?id=<?php echo $student["student_id"]; ?>"
                                    class="action-link edit-link"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete.php?id=<?php echo $student["student_id"]; ?>"
                                    class="action-link delete-link"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="10" class="empty">

                            No students have been registered yet.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


</body>

</html>