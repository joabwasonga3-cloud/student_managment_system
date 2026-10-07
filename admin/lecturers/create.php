 
<?php

session_start();

require_once "../../config/database.php";

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Check admin
if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $status = $_POST["status"];
    $password = $_POST["password"];

    // Validate fields
    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($department) ||
        empty($password)
    ) {

        $message = "All fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";

    } else {

        // Check whether email already exists
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {

            $message = "This email already exists.";

        } else {

            // Start transaction
            mysqli_begin_transaction($conn);

            try {

                // Hash password
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Create user account
                $user_stmt = $conn->prepare(
                    "INSERT INTO users
                    (email, password_hash, role, status)
                    VALUES (?, ?, 'lecturer', ?)"
                );

                $user_stmt->bind_param(
                    "sss",
                    $email,
                    $password_hash,
                    $status
                );

                $user_stmt->execute();

                // Get newly created user ID
                $user_id = $conn->insert_id;

                $user_stmt->close();

                // Create lecturer record
                $lecturer_stmt = $conn->prepare(
                    "INSERT INTO lecturers
                    (user_id, full_name, phone, department, status)
                    VALUES (?, ?, ?, ?, ?)"
                );

                $lecturer_stmt->bind_param(
                    "issss",
                    $user_id,
                    $full_name,
                    $phone,
                    $department,
                    $status
                );

                $lecturer_stmt->execute();

                $lecturer_stmt->close();

                // Commit
                mysqli_commit($conn);

                header("Location: index.php");
                exit();

            } catch (Exception $e) {

                mysqli_rollback($conn);

                $message = "Failed to create lecturer.";
            }
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Lecturer</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 600px;
            max-width: 90%;
            margin: 40px auto;
        }

        .form-box {
            background-color: white;
            padding: 30px;
            border-radius: 8px;

            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        h1 {
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;

            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;

            background-color: #007bff;
            color: white;

            border: none;
            border-radius: 5px;

            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;

            color: #007bff;
            text-decoration: none;
        }

        .message {
            background-color: #f8d7da;
            color: #721c24;

            padding: 12px;
            border-radius: 5px;

            margin-bottom: 20px;
        }

    </style>

</head>

<body>

    <div class="container">

        <a href="index.php" class="back">
            ← Back to Lecturers
        </a>

        <div class="form-box">

            <h1>Add Lecturer</h1>

            <?php if (!empty($message)): ?>

                <div class="message">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <label>Full Name</label>

                <input
                    type="text"
                    name="full_name"
                    required
                >

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    required
                >

                <label>Phone Number</label>

                <input
                    type="text"
                    name="phone"
                    required
                >

                <label>Department</label>

                <input
                    type="text"
                    name="department"
                    required
                >

                <label>Status</label>

                <select name="status" required>

                    <option value="active">
                        Active
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    required
                >

                <button type="submit">
                    Add Lecturer
                </button>

            </form>

        </div>

    </div>

</body>

</html>

