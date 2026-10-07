<?php

session_start();

require_once "../config/database.php";

// Make sure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

// Make sure the user is a student
if ($_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Student Profile
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        s.student_id,
        s.student_number,
        s.full_name,
        u.email,
        s.phone,
        s.gender,
        s.program,
        s.year_of_study,
        s.admission_date,
        s.status
    FROM students s
    INNER JOIN users u
        ON s.user_id = u.user_id
    WHERE s.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student profile not found.");
}

$student = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - Student Management System</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        /* Sidebar */

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #1f2937;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background: #374151;
        }

        .sidebar .active {
            background: #374151;
        }

        .logout {
            margin-top: 30px;
            background: #dc2626;
        }

        /* Main */

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        /* Profile */

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 900px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-icon {
            width: 90px;
            height: 90px;
            margin: auto;
            border-radius: 50%;
            background: #374151;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
        }

        .profile-header h2 {
            margin-top: 15px;
        }

        .profile-header p {
            color: #666;
            margin-top: 5px;
        }

        /* Information */

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .info-box label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
        }

        .info-box p {
            font-size: 16px;
            font-weight: bold;
            color: #222;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .back-button {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 18px;
            background: #374151;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background: #1f2937;
        }

        /* Responsive */

        @media (max-width: 700px) {

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main {
                margin-left: 0;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- Sidebar -->

<div class="sidebar">

    <h2>Student Portal</h2>

    <a href="dashboard.php">
        Dashboard
    </a>

    <a href="profile.php" class="active">
        My Profile
    </a>

    <a href="dashboard.php#courses">
        My Courses
    </a>

    <a href="dashboard.php#results">
        My Results
    </a>

    <a href="dashboard.php#attendance">
        Attendance
    </a>

    <a href="dashboard.php#payments">
        Payments
    </a>

    <a href="../auth/logout.php" class="logout">
        Logout
    </a>

</div>


<!-- Main Content -->

<div class="main">

    <div class="header">

        <h1>My Profile</h1>

        <p>View your personal and academic information.</p>

    </div>


    <div class="profile-card">

        <!-- Profile Header -->

        <div class="profile-header">

            <div class="profile-icon">

                <?php
                echo strtoupper(substr($student["full_name"], 0, 1));
                ?>

            </div>

            <h2>
                <?php echo htmlspecialchars($student["full_name"]); ?>
            </h2>

            <p>
                <?php echo htmlspecialchars($student["student_number"]); ?>
            </p>

        </div>


        <!-- Profile Information -->

        <div class="profile-info">

            <div class="info-box">

                <label>Student Number</label>

                <p>
                    <?php echo htmlspecialchars($student["student_number"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Full Name</label>

                <p>
                    <?php echo htmlspecialchars($student["full_name"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Email Address</label>

                <p>
                    <?php echo htmlspecialchars($student["email"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Phone Number</label>

                <p>
                    <?php echo htmlspecialchars($student["phone"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Gender</label>

                <p>
                    <?php echo htmlspecialchars($student["gender"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Program</label>

                <p>
                    <?php echo htmlspecialchars($student["program"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Year of Study</label>

                <p>
                    <?php echo htmlspecialchars($student["year_of_study"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Admission Date</label>

                <p>
                    <?php echo htmlspecialchars($student["admission_date"]); ?>
                </p>

            </div>


            <div class="info-box">

                <label>Account Status</label>

                <p>

                    <?php if ($student["status"] === "active"): ?>

                        <span class="status status-active">
                            Active
                        </span>

                    <?php else: ?>

                        <span class="status status-inactive">
                            <?php echo htmlspecialchars($student["status"]); ?>
                        </span>

                    <?php endif; ?>

                </p>

            </div>

        </div>


        <a href="dashboard.php" class="back-button">
            ← Back to Dashboard
        </a>

    </div>

</div>

</body>

</html>