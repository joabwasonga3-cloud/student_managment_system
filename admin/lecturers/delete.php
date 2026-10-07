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

// Get user_id belonging to lecturer
$stmt = $conn->prepare(
    "SELECT user_id
     FROM lecturers
     WHERE lecturer_id = ?"
);

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit();
}

$lecturer = $result->fetch_assoc();

$user_id = $lecturer["user_id"];

$stmt->close();


// Delete lecturer and user
mysqli_begin_transaction($conn);

try {

    // Delete lecturer
    $delete_lecturer = $conn->prepare(
        "DELETE FROM lecturers
         WHERE lecturer_id = ?"
    );

    $delete_lecturer->bind_param(
        "i",
        $lecturer_id
    );

    $delete_lecturer->execute();

    $delete_lecturer->close();


    // Delete user account
    $delete_user = $conn->prepare(
        "DELETE FROM users
         WHERE user_id = ?"
    );

    $delete_user->bind_param(
        "i",
        $user_id
    );

    $delete_user->execute();

    $delete_user->close();


    // Commit transaction
    mysqli_commit($conn);

} catch (Exception $e) {

    // Undo changes if something fails
    mysqli_rollback($conn);
}

// Return to lecturer list
header("Location: index.php");
exit();

?>