<?php

require_once "config/database.php";

$email = "student@example.com";
$password = "student123";
$role = "student";
$status = "active";

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (email, password_hash, role, status)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssss",
    $email,
    $password_hash,
    $role,
    $status
);

if ($stmt->execute()) {

    $user_id = $stmt->insert_id;

    echo "Student user created successfully.<br>";
    echo "User ID: " . $user_id . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Password: " . $password;

} else {

    echo "Error: " . $stmt->error;

}
?>