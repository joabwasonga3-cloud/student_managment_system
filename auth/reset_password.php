<?php

require_once "../config/database.php";

$token = $_GET["token"] ?? "";
$form_error = $_GET["error"] ?? "";

$valid_token = false;
$user_id = null;
$error = "";

if (empty($token)) {

    $error = "Invalid password reset link.";

} else {

    $token_hash = hash("sha256", $token);

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

    if ($result->num_rows === 1) {

        $row = $result->fetch_assoc();

        if ((int)$row["used"] === 1) {

            $error = "This password reset link has already been used.";

        } elseif ((int)$row["is_expired"] === 1) {

            $error = "This password reset link has expired.";

        } else {

            $valid_token = true;
            $user_id = $row["user_id"];
        }

    } else {

        $error = "Invalid password reset link.";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password - Student Management System</title>

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
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #007bff;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            text-align: center;
            line-height: 1.5;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

        .password-note {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Reset Password</h2>

    <?php if (!$valid_token): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

        <div class="back-link">

            <a href="forgot_password.php">
                Request a new reset link
            </a>

        </div>

    <?php else: ?>

        <p class="description">
            Create a new password for your account.
        </p>

        <?php if (!empty($form_error)): ?>
            <div class="error" style="margin-bottom: 20px;">
                <?= htmlspecialchars($form_error) ?>
            </div>
        <?php endif; ?>

        <form
            method="POST"
            action="reset_password_process.php"
            onsubmit="return validateResetForm()"
        >

            <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars($token) ?>"
            >

            <div class="form-group">

                <label for="password">
                    New Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="6"
                    required
                >

                <p class="password-note">
                    Password must contain at least 6 characters.
                </p>

            </div>

            <div class="form-group">

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    minlength="6"
                    required
                >

            </div>

            <button type="submit">
                Reset Password
            </button>

        </form>

        <script>
        function validateResetForm() {
            var p1 = document.getElementById('password').value;
            var p2 = document.getElementById('confirm_password').value;
            if (p1 !== p2) {
                alert('Passwords do not match. Please ensure both fields match.');
                return false;
            }
            return true;
        }
        </script>

    <?php endif; ?>

</div>

</body>

</html>