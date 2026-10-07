
<?php

session_start();

require_once "../../config/database.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Check if user is an admin
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

// Check lecturer ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$lecturer_id = (int) $_GET["id"];

// Get lecturer details together with email from users table
$stmt = $conn->prepare(
    "SELECT
        lecturers.lecturer_id,
        lecturers.user_id,
        lecturers.full_name,
        users.email,
        lecturers.phone,
        lecturers.department,
        lecturers.status
     FROM lecturers
     INNER JOIN users
        ON lecturers.user_id = users.user_id
     WHERE lecturers.lecturer_id = ?"
);

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$lecturer = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Lecturer - Student Management System</title>

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

        /* =========================
           SIDEBAR
        ========================= */

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

        /* =========================
           MAIN CONTENT
        ========================= */

        .main-content {
            margin-left: 240px;
            padding: 25px;
        }

        /* =========================
           TOP BAR
        ========================= */

        .topbar {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .topbar h1 {
            color: #333;
        }

        /* =========================
           BUTTONS
        ========================= */

        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #545b62;
        }

        .edit-btn {
            background-color: #ffc107;
            color: black;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
        }

        .edit-btn:hover {
            background-color: #d39e00;
        }

        /* =========================
           DETAILS CARD
        ========================= */

        .details-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);

            max-width: 800px;
        }

        .details-card h2 {
            color: #333;
            margin-bottom: 25px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .detail-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            width: 200px;
            font-weight: bold;
            color: #555;
        }

        .detail-value {
            flex: 1;
            color: #333;
        }

        /* STATUS */

        .active {
            color: #198754;
            font-weight: bold;
        }

        .inactive {
            color: #dc3545;
            font-weight: bold;
        }

        /* =========================
           ACTIONS
        ========================= */

        .actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 600px) {

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

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .detail-label {
                width: 100%;
            }

        }

    </style>

</head>

<body>

    <!-- =========================
         SIDEBAR
    ========================= -->

    <div class="sidebar">

        <h2>Admin Panel</h2>

        <ul>

            <li>
                <a href="../dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="../students/index.php">
                    Students
                </a>
            </li>

            <li>
                <a href="index.php">
                    Lecturers
                </a>
            </li>

            <li>
                <a href="#">
                    Courses
                </a>
            </li>

            <li>
                <a href="#">
                    Reports
                </a>
            </li>

        </ul>

        <div style="margin-top: 30px;">

            <a
                href="../../auth/logout.php"
                style="
                    display:block;
                    background-color:#dc3545;
                    color:white;
                    text-align:center;
                    padding:12px;
                    border-radius:5px;
                    text-decoration:none;
                "
            >
                Logout
            </a>

        </div>

    </div>


    <!-- =========================
         MAIN CONTENT
    ========================= -->

    <div class="main-content">

        <!-- TOP BAR -->

        <div class="topbar">

            <h1>Lecturer Details</h1>

            <a href="index.php" class="back-btn">
                ← Back to Lecturers
            </a>

        </div>


        <!-- =========================
             LECTURER DETAILS
        ========================= -->

        <div class="details-card">

            <h2>
                <?php echo htmlspecialchars($lecturer["full_name"]); ?>
            </h2>


            <div class="detail-row">

                <div class="detail-label">
                    Lecturer ID
                </div>

                <div class="detail-value">
                    <?php echo htmlspecialchars($lecturer["lecturer_id"]); ?>
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    User ID
                </div>

                <div class="detail-value">
                    <?php echo htmlspecialchars($lecturer["user_id"]); ?>
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Full Name
                </div>

                <div class="detail-value">
                    <?php echo htmlspecialchars($lecturer["full_name"]); ?>
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Email
                </div>

                <div class="detail-value">
                    <?php echo htmlspecialchars($lecturer["email"]); ?>
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Phone Number
                </div>

                <div class="detail-value">
                    <?php echo htmlspecialchars($lecturer["phone"]); ?>
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Department
                </div>

                <div class="detail-value">
                    <?php echo htmlspecialchars($lecturer["department"]); ?>
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Status
                </div>

                <div class="detail-value">

                    <?php if ($lecturer["status"] === "active"): ?>

                        <span class="active">
                            Active
                        </span>

                    <?php else: ?>

                        <span class="inactive">
                            Inactive
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ACTIONS -->

            <div class="actions">

                <a
                    href="edit.php?id=<?php echo $lecturer["lecturer_id"]; ?>"
                    class="edit-btn"
                >
                    Edit Lecturer
                </a>

                <a
                    href="index.php"
                    class="back-btn"
                >
                    Back
                </a>

            </div>

        </div>

    </div>

</body>

</html>