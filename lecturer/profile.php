<?php
session_start();

require_once "../config/database.php";

// Authentication
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "lecturer") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch lecturer information
$stmt = $conn->prepare(
    "SELECT
        l.lecturer_id,
        l.user_id,
        l.full_name,
        l.phone,
        l.department,
        l.status,
        u.email,
        u.created_at
     FROM lecturers l
     INNER JOIN users u ON l.user_id = u.user_id
     WHERE l.user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$lecturer = $stmt->get_result()->fetch_assoc();

if (!$lecturer) {
    die("Lecturer profile not found. Please contact the administrator.");
}`

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile | Lecturer Portal</title>

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
    padding: 22px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.profile-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 850px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.profile-top {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-bottom: 25px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 25px;
}

.avatar {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    background: #2563eb;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 32px;
    font-weight: bold;
}

.profile-top h2 {
    margin-bottom: 8px;
}

.profile-top p {
    color: #64748b;
    margin: 0;
}

.details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
}

.detail {
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.detail label {
    display: block;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
}

.detail p {
    margin: 0;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.status {
    display: inline-block;
    padding: 6px 12px;
    background: #dcfce7;
    color: #166534;
    border-radius: 15px;
    font-size: 12px;
}

.back-link {
    display: inline-block;
    margin-bottom: 18px;
    color: #2563eb;
    text-decoration: none;
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

    .details {
        grid-template-columns: 1fr;
    }

    .profile-top {
        align-items: flex-start;
    }
}
</style>
</head>

<body>

<div class="sidebar">

    <h2>Lecturer Portal</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php" class="active">My Profile</a>
    <a href="courses.php">My Courses</a>
    <a href="students.php">My Students</a>
    <a href="attendance.php">Attendance</a>
    <a href="results.php">Manage Results</a>
    <a href="../auth/logout.php">Logout</a>

</div>

<div class="main">

    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <div class="header">
        <h2>My Profile</h2>
        <p>View your personal and professional information.</p>
    </div>

    <div class="profile-container">

        <div class="profile-top">

            <div class="avatar">
                <?php
                echo strtoupper(substr($lecturer["full_name"], 0, 1));
                ?>
            </div>

            <div>
                <h2><?php echo e($lecturer["full_name"]); ?></h2>
                <p><?php echo e($lecturer["department"]); ?></p>
            </div>

        </div>

        <div class="details">

            <div class="detail">
                <label>Lecturer ID</label>
                <p><?php echo e($lecturer["lecturer_id"]); ?></p>
            </div>

            <div class="detail">
                <label>Full Name</label>
                <p><?php echo e($lecturer["full_name"]); ?></p>
            </div>

            <div class="detail">
                <label>Email Address</label>
                <p><?php echo e($lecturer["email"]); ?></p>
            </div>

            <div class="detail">
                <label>Phone Number</label>
                <p><?php echo e($lecturer["phone"]); ?></p>
            </div>

            <div class="detail">
                <label>Department</label>
                <p><?php echo e($lecturer["department"]); ?></p>
            </div>

            <div class="detail">
                <label>Account Status</label>
                <p>
                    <span class="status">
                        <?php echo e($lecturer["status"]); ?>
                    </span>
                </p>
            </div>

            <div class="detail">
                <label>Account Created</label>
                <p><?php echo e($lecturer["created_at"]); ?></p>
            </div>

        </div>

    </div>

</div>

</body>
</html>