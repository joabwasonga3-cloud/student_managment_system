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


// Get course ID
$course_id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : (int)($_POST["course_id"] ?? 0);

if ($course_id <= 0) {
    header("Location: index.php");
    exit();
}


// Delete after confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sql = "
        DELETE FROM courses
        WHERE course_id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {

        header("Location: index.php?deleted=1");
        exit();

    } else {

        $error = "Unable to delete the course. It may be in use.";

    }

    $stmt->close();
}


// Fetch course for confirmation
$sql = "
    SELECT course_code, course_name
    FROM courses
    WHERE course_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$course = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Delete Course - Student Management System</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f6f9;
        }

        .container {
            width: 550px;
            max-width: 90%;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
        }

        h1 {
            color: #dc2626;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .warning {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
        }

        button {
            border: none;
            cursor: pointer;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
            padding: 11px 18px;
            border-radius: 5px;
        }

        .cancel-btn {
            background: #6b7280;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Delete Course</h1>


    <p>
        Are you sure you want to delete this course?
    </p>


    <div class="warning">

        <strong>
            <?php echo htmlspecialchars($course["course_code"]); ?>
        </strong>

        <br>

        <?php echo htmlspecialchars($course["course_name"]); ?>

    </div>


    <?php if (isset($error)): ?>

        <div class="warning">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <input
            type="hidden"
            name="course_id"
            value="<?php echo $course_id; ?>"
        >

        <div class="buttons">

            <button
                type="submit"
                class="delete-btn"
            >
                Yes, Delete
            </button>

            <a
                href="index.php"
                class="cancel-btn"
            >
                Cancel
            </a>

        </div>

    </form>

</div>

</body>

</html>