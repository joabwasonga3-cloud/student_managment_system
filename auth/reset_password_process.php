<?php

session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");
    exit;
}

$token = $_POST["token"] ?? "";
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

if (empty($token)) {
    header("Location: forgot_password.php");
    exit();
}

if (empty($password) || empty($confirm_password)) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("Please fill in all fields."));
    exit();
}

if (strlen($password) < 6) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("Password must contain at least 6 characters."));
    exit();
}

if ($password !== $confirm_password) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("Passwords do not match."));
    exit();
}

/*
 * Hash token so that we search using the
 * same value stored in the database.
 */
$token_hash = hash("sha256", $token);

/*
 * Find the reset request (timezone safe using MySQL NOW())
 */
$sql = "
    SELECT
        reset_id,
        user_id,
        expires_at,
        used,
        (expires_at < NOW()) AS is_expired
    FROM password_resets
    WHERE token_hash = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("Invalid password reset link."));
    exit();
}

$reset = $result->fetch_assoc();
$stmt->close();

/*
 * Check whether token was already used
 */
if ((int)$reset["used"] === 1) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("This password reset link has already been used."));
    exit();
}

/*
 * Check expiry
 */
if ((int)$reset["is_expired"] === 1) {
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("This password reset link has expired."));
    exit();
}

$user_id = (int)$reset["user_id"];

/*
 * Create secure password hash
 */
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

/*
 * Update password and reset token inside transaction
 */
$conn->begin_transaction();

try {
    $update_sql = "
        UPDATE users
        SET password_hash = ?
        WHERE user_id = ?
    ";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $password_hash, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    $mark_sql = "
        UPDATE password_resets
        SET used = 1
        WHERE reset_id = ?
    ";
    $mark_stmt = $conn->prepare($mark_sql);
    $mark_stmt->bind_param("i", $reset["reset_id"]);
    $mark_stmt->execute();
    $mark_stmt->close();

    $cleanup_sql = "
        DELETE FROM password_resets
        WHERE user_id = ?
        AND reset_id != ?
    ";
    $cleanup_stmt = $conn->prepare($cleanup_sql);
    $cleanup_stmt->bind_param("ii", $user_id, $reset["reset_id"]);
    $cleanup_stmt->execute();
    $cleanup_stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: reset_password.php?token=" . urlencode($token) . "&error=" . urlencode("Unable to update password. Please try again."));
    exit();
}

$_SESSION['login_success'] = "Password reset successful! You can now log in with your new password.";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Password Reset Successful</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f6f9;
        }

        .container {
            width: 400px;
            background: white;
            padding: 35px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            color: #155724;
            margin-bottom: 15px;
        }

        p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        a {
            display: block;
            padding: 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        a:hover {
            background-color: #0056b3;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Password Reset Successful</h2>

    <p>
        Your password has been changed successfully.
        You can now log in using your new password.
    </p>

    <a href="login.php">
        Go to Login
    </a>

</div>

</body>

</html>