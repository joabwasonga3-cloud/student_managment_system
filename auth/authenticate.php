<?php

session_start();

require_once "../config/database.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

// Get submitted data
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION["login_error"] = "Please enter both email and password.";
    $_SESSION["login_email"] = $email;
    header("Location: login.php");
    exit();
}

// Find user
$sql = "SELECT user_id, email, password_hash, role, status
        FROM users
        WHERE email = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION["login_error"] = "Database error occurred.";
    $_SESSION["login_email"] = $email;
    header("Location: login.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

// Check user
if (mysqli_num_rows($result) !== 1) {
    $_SESSION["login_error"] = "Invalid email or password.";
    $_SESSION["login_email"] = $email;
    mysqli_stmt_close($stmt);

    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Check account status
if ($user["status"] !== "active") {
    $_SESSION["login_error"] = "Your account is inactive. Please contact the administrator.";
    $_SESSION["login_email"] = $email;
    mysqli_stmt_close($stmt);

    header("Location: login.php");
    exit();
}

// Verify password
if (!password_verify($password, $user["password_hash"])) {
    $_SESSION["login_error"] = "Invalid email or password.";
    $_SESSION["login_email"] = $email;
    mysqli_stmt_close($stmt);

    header("Location: login.php");
    exit();
}

mysqli_stmt_close($stmt);

// Profile verification before completing session
if ($user["role"] === "student") {
    $p_stmt = mysqli_prepare($conn, "SELECT student_id, status FROM students WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($p_stmt, "i", $user["user_id"]);
    mysqli_stmt_execute($p_stmt);
    $p_res = mysqli_stmt_get_result($p_stmt);
    $p_row = mysqli_fetch_assoc($p_res);
    mysqli_stmt_close($p_stmt);

    if (!$p_row) {
        $_SESSION["login_error"] = "Student profile not linked to this account. Please contact the administrator.";
        $_SESSION["login_email"] = $email;
        header("Location: login.php");
        exit();
    }

    if ($p_row["status"] !== "active") {
        $_SESSION["login_error"] = "Your student account status is currently " . htmlspecialchars($p_row["status"]) . ". Please contact administration.";
        $_SESSION["login_email"] = $email;
        header("Location: login.php");
        exit();
    }
} elseif ($user["role"] === "lecturer") {
    $p_stmt = mysqli_prepare($conn, "SELECT lecturer_id, status FROM lecturers WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($p_stmt, "i", $user["user_id"]);
    mysqli_stmt_execute($p_stmt);
    $p_res = mysqli_stmt_get_result($p_stmt);
    $p_row = mysqli_fetch_assoc($p_res);
    mysqli_stmt_close($p_stmt);

    if (!$p_row) {
        $_SESSION["login_error"] = "Lecturer profile not linked to this account. Please contact the administrator.";
        $_SESSION["login_email"] = $email;
        header("Location: login.php");
        exit();
    }

    if ($p_row["status"] !== "active") {
        $_SESSION["login_error"] = "Your lecturer account is currently inactive. Please contact the administrator.";
        $_SESSION["login_email"] = $email;
        header("Location: login.php");
        exit();
    }
}

// Login successful
session_regenerate_id(true);

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

// Redirect according to role
switch ($user["role"]) {

    case "admin":
        header("Location: ../admin/dashboard.php");
        break;

    case "student":
        header("Location: ../student/dashboard.php");
        break;

    case "lecturer":
        header("Location: ../lecturer/dashboard.php");
        break;

    default:
        session_destroy();
        header("Location: login.php");
        break;
}

exit();

?>