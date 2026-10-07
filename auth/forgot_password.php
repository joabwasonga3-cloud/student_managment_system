<?php
session_start();

require_once "../config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $role = $_POST["role"] ?? "";
    $identifier = trim($_POST["identifier"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if (empty($role) || empty($identifier) || empty($email)) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (!in_array($role, ["student", "lecturer"])) {

        $message = "Invalid account type.";
        $message_type = "error";

    } else {

        $user_id = null;
        $registered_email = null;
        $account_exists = false;

        /*
         * STUDENT
         */
        if ($role === "student") {

            $sql = "
                SELECT 
                    s.student_id,
                    s.student_number,
                    s.user_id,
                    u.user_id AS account_user_id,
                    u.email
                FROM students s
                LEFT JOIN users u 
                    ON s.user_id = u.user_id
                WHERE s.student_number = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $identifier);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $row = $result->fetch_assoc();

                if (!empty($row["account_user_id"])) {
                    $account_exists = true;
                    $user_id = $row["account_user_id"];
                    $registered_email = $row["email"];
                }
            }

            $stmt->close();
        }

        /*
         * LECTURER
         */
        if ($role === "lecturer") {

            $sql = "
                SELECT
                    l.lecturer_id,
                    l.user_id,
                    u.user_id AS account_user_id,
                    u.email
                FROM lecturers l
                LEFT JOIN users u
                    ON l.user_id = u.user_id
                WHERE l.lecturer_id = ? OR u.email = ? OR l.phone = ?
                LIMIT 1
            ";

            $lecturer_id_candidate = ctype_digit($identifier) ? (int)$identifier : 0;
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $lecturer_id_candidate, $identifier, $identifier);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $row = $result->fetch_assoc();

                if (!empty($row["account_user_id"])) {
                    $account_exists = true;
                    $user_id = $row["account_user_id"];
                    $registered_email = $row["email"];
                }
            }

            $stmt->close();
        }

        /*
         * Check account
         */
        if (!$account_exists) {

            $message = "We could not find an active account matching those details. If you have never created an account, please use Sign Up.";
            $message_type = "error";

        } elseif (strtolower($email) !== strtolower($registered_email)) {

            $message = "The details provided do not match our records.";
            $message_type = "error";

        } else {

            /*
             * Delete old reset tokens for this user or expired tokens
             */
            $delete_sql = "
                DELETE FROM password_resets
                WHERE user_id = ?
                OR expires_at < NOW()
            ";

            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();

            /*
             * Generate secure token
             */
            $token = bin2hex(random_bytes(32));

            /*
             * Store only the hash of the token
             */
            $token_hash = hash("sha256", $token);

            /*
             * Token expires after 30 minutes
             * Using MySQL DATE_ADD(NOW(), INTERVAL 30 MINUTE) prevents timezone divergence between PHP and MySQL
             */
            $insert_sql = "
                INSERT INTO password_resets
                (user_id, token_hash, expires_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))
            ";

            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param(
                "is",
                $user_id,
                $token_hash
            );

            if ($insert_stmt->execute()) {

                /*
                 * LOCAL DEVELOPMENT MODE
                 *
                 * Because this is running on WAMP localhost,
                 * we display the reset link for testing.
                 */
                $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https://" : "http://";
                $dir = str_replace("\\", "/", dirname($_SERVER["PHP_SELF"]));
                $reset_link =
                    $protocol .
                    $_SERVER["HTTP_HOST"] .
                    rtrim($dir, "/") .
                    "/reset_password.php?token=" .
                    urlencode($token);

                $message = "Your password reset request was successful.";
                $message_type = "success";

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }

            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Student Management System</title>

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
            width: 430px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        input:focus,
        select:focus {
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

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            line-height: 1.5;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .reset-link {
            margin-top: 15px;
            padding: 12px;
            background: #f1f1f1;
            border-radius: 5px;
            word-break: break-all;
        }

        .reset-link a {
            color: #007bff;
            font-weight: bold;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>Forgot Password?</h2>

    <p class="description">
        Enter your account details below to reset your password.
    </p>

    <?php if (!empty($message)): ?>

        <div class="message <?= htmlspecialchars($message_type) ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>

    <?php if (
        isset($reset_link) &&
        $message_type === "success"
    ): ?>

        <div class="reset-link">

            <strong>Development Reset Link:</strong>

            <br><br>

            <a href="<?= htmlspecialchars($reset_link) ?>">
                Reset Your Password
            </a>

            <br><br>

            <small>
                This link expires in 30 minutes.
            </small>

        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="form-group">

            <label for="role">
                Account Type
            </label>

            <select
                name="role"
                id="role"
                required
            >

                <option value="">
                    -- Select Account Type --
                </option>

                <option value="student" <?= (isset($role) && $role === "student") ? "selected" : "" ?>>
                    Student
                </option>

                <option value="lecturer" <?= (isset($role) && $role === "lecturer") ? "selected" : "" ?>>
                    Lecturer
                </option>

            </select>

        </div>

        <div class="form-group">

            <label for="identifier">
                Student Number / Lecturer ID
            </label>

            <input
                type="text"
                name="identifier"
                id="identifier"
                placeholder="Enter your identifier"
                value="<?= htmlspecialchars($identifier ?? '') ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="email">
                Registered Email
            </label>

            <input
                type="email"
                name="email"
                id="email"
                placeholder="Enter your registered email"
                value="<?= htmlspecialchars($email ?? '') ?>"
                required
            >

        </div>

        <button type="submit">
            Verify Account
        </button>

    </form>

    <div class="back-link">

        <a href="login.php">
            ← Back to Login
        </a>

    </div>

</div>

<script>

    const role = document.getElementById("role");
    const identifier = document.getElementById("identifier");

    role.addEventListener("change", function () {

        if (this.value === "student") {

            identifier.placeholder = "e.g. KAFU/CS/001";

        } else if (this.value === "lecturer") {

            identifier.placeholder = "e.g. 1";

        } else {

            identifier.placeholder = "Enter your identifier";

        }

    });

</script>

</body>

</html>