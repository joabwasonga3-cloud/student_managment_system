<?php

session_start();

require_once "../config/database.php";

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit();
}

$role = trim($_POST["role"] ?? "");
$identifier = trim($_POST["identifier"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

// Save form values to session so user doesn't have to retype on error
$_SESSION["signup_role"] = $role;
$_SESSION["signup_identifier"] = $identifier;
$_SESSION["signup_email"] = $email;

// Validate required fields
if (empty($role) || empty($identifier) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION["signup_error"] = "Please fill in all required fields.";
    header("Location: signup.php");
    exit();
}

// Validate role
if (!in_array($role, ["student", "lecturer"])) {
    $_SESSION["signup_error"] = "Invalid account type selected.";
    header("Location: signup.php");
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["signup_error"] = "Please enter a valid email address.";
    header("Location: signup.php");
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    $_SESSION["signup_error"] = "Password must be at least 6 characters.";
    header("Location: signup.php");
    exit();
}

// Validate password match
if ($password !== $confirm_password) {
    $_SESSION["signup_error"] = "Passwords do not match.";
    header("Location: signup.php");
    exit();
}

// Check if email already exists in users table
$email_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
$email_stmt->bind_param("s", $email);
$email_stmt->execute();
$email_stmt->store_result();

if ($email_stmt->num_rows > 0) {
    $email_stmt->close();
    $_SESSION["signup_error"] = "An account with this email address already exists. Please log in or reset your password.";
    header("Location: signup.php");
    exit();
}
$email_stmt->close();

if ($role === "student") {
    // Check if student record exists
    $student_stmt = $conn->prepare("SELECT student_id, user_id, status FROM students WHERE student_number = ? LIMIT 1");
    $student_stmt->bind_param("s", $identifier);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();

    if ($student_result->num_rows !== 1) {
        $student_stmt->close();
        $_SESSION["signup_error"] = "No student record found with student number '" . htmlspecialchars($identifier) . "'. Please contact the administration.";
        header("Location: signup.php");
        exit();
    }

    $student = $student_result->fetch_assoc();
    $student_stmt->close();

    // Check if an account is already linked
    if (!empty($student["user_id"])) {
        $_SESSION["signup_error"] = "An account has already been registered for this student number. Please log in or use Forgot Password.";
        header("Location: signup.php");
        exit();
    }

    // Check student status
    if ($student["status"] !== "active") {
        $_SESSION["signup_error"] = "Your student status is currently " . htmlspecialchars($student["status"]) . ". Please contact administration.";
        header("Location: signup.php");
        exit();
    }

    // Begin database transaction to create user and link student
    $conn->begin_transaction();

    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $user_stmt = $conn->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'student', 'active')");
        $user_stmt->bind_param("ss", $email, $password_hash);
        $user_stmt->execute();

        $new_user_id = $conn->insert_id;
        $user_stmt->close();

        $link_stmt = $conn->prepare("UPDATE students SET user_id = ? WHERE student_id = ?");
        $link_stmt->bind_param("ii", $new_user_id, $student["student_id"]);
        $link_stmt->execute();
        $link_stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION["signup_error"] = "Account creation failed due to a database error. Please try again.";
        header("Location: signup.php");
        exit();
    }

} elseif ($role === "lecturer") {
    // Check if lecturer record exists
    $lecturer_id_candidate = ctype_digit($identifier) ? (int)$identifier : 0;
    $lec_stmt = $conn->prepare("
        SELECT l.lecturer_id, l.user_id, l.status, u.email AS user_email
        FROM lecturers l
        LEFT JOIN users u ON l.user_id = u.user_id
        WHERE l.lecturer_id = ? OR u.email = ? OR l.phone = ?
        LIMIT 1
    ");
    $lec_stmt->bind_param("iss", $lecturer_id_candidate, $identifier, $identifier);
    $lec_stmt->execute();
    $lec_res = $lec_stmt->get_result();

    if ($lec_res->num_rows !== 1) {
        $lec_stmt->close();
        $_SESSION["signup_error"] = "No lecturer record found matching '" . htmlspecialchars($identifier) . "'. Please contact the administration.";
        header("Location: signup.php");
        exit();
    }

    $lecturer = $lec_res->fetch_assoc();
    $lec_stmt->close();

    // In this system, lecturer accounts are provisioned by the admin
    if (!empty($lecturer["user_id"])) {
        $_SESSION["signup_error"] = "Your lecturer account has already been created by administration. Please log in or use Forgot Password if you need to set a new password.";
        header("Location: signup.php");
        exit();
    }

    // If an unlinked lecturer record exists, link it
    $conn->begin_transaction();

    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $user_stmt = $conn->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'lecturer', 'active')");
        $user_stmt->bind_param("ss", $email, $password_hash);
        $user_stmt->execute();

        $new_user_id = $conn->insert_id;
        $user_stmt->close();

        $link_stmt = $conn->prepare("UPDATE lecturers SET user_id = ? WHERE lecturer_id = ?");
        $link_stmt->bind_param("ii", $new_user_id, $lecturer["lecturer_id"]);
        $link_stmt->execute();
        $link_stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION["signup_error"] = "Account creation failed due to a database error. Please try again.";
        header("Location: signup.php");
        exit();
    }
}

// Clear temporary form session data
unset($_SESSION["signup_role"]);
unset($_SESSION["signup_identifier"]);
unset($_SESSION["signup_email"]);

// Set success message and pre-fill email on login page
$_SESSION["login_success"] = "Account created successfully! You can now log in with your email and password.";
$_SESSION["login_email"] = $email;

header("Location: login.php");
exit();

?>
