
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

// Validate lecturer ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$lecturer_id = (int) $_GET["id"];

$message = "";

// Get lecturer and user information
$stmt = $conn->prepare(
    "SELECT
        lecturers.lecturer_id,
        lecturers.user_id,
        lecturers.full_name,
        lecturers.phone,
        lecturers.department,
        lecturers.status,
        users.email
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


// Update lecturer
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $status = $_POST["status"];

    if (
        empty($full_name) ||
        empty($phone) ||
        empty($department)
    ) {

        $message = "All fields are required.";

    } else {

        mysqli_begin_transaction($conn);

        try {

            // Update lecturer table
            $update_lecturer = $conn->prepare(
                "UPDATE lecturers
                 SET full_name = ?,
                     phone = ?,
                     department = ?,
                     status = ?
                 WHERE lecturer_id = ?"
            );

            $update_lecturer->bind_param(
                "ssssi",
                $full_name,
                $phone,
                $department,
                $status,
                $lecturer_id
            );

            $update_lecturer->execute();

            $update_lecturer->close();


            // Update users status
            $update_user = $conn->prepare(
                "UPDATE users
                 SET status = ?
                 WHERE user_id = ?"
            );

            $update_user->bind_param(
                "si",
                $status,
                $lecturer["user_id"]
            );

            $update_user->execute();

            $update_user->close();


            mysqli_commit($conn);

            header("Location: index.php");
            exit();

        } catch (Exception $e) {

            mysqli_rollback($conn);

            $message = "Failed to update lecturer.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Lecturer</title>

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
            color: #333;
            margin-bottom: 25px;
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

        input[readonly] {
            background-color: #eee;
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

            <h1>Edit Lecturer</h1>

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
                    value="<?php echo htmlspecialchars($lecturer["full_name"]); ?>"
                    required
                >

                <label>Email</label>

                <input
                    type="email"
                    value="<?php echo htmlspecialchars($lecturer["email"]); ?>"
                    readonly
                >

                <label>Phone Number</label>

                <input
                    type="text"
                    name="phone"
                    value="<?php echo htmlspecialchars($lecturer["phone"]); ?>"
                    required
                >

                <label>Department</label>

                <input
                    type="text"
                    name="department"
                    value="<?php echo htmlspecialchars($lecturer["department"]); ?>"
                    required
                >

                <label>Status</label>

                <select name="status" required>

                    <option
                        value="active"
                        <?php
                        echo ($lecturer["status"] === "active")
                            ? "selected"
                            : "";
                        ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?php
                        echo ($lecturer["status"] === "inactive")
                            ? "selected"
                            : "";
                        ?>
                    >
                        Inactive
                    </option>

                </select>

                <button type="submit">
                    Update Lecturer
                </button>

            </form>

        </div>

    </div>

</body>

</html>