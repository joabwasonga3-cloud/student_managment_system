<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "lecturer") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* Get lecturer */
$stmt = $conn->prepare("
    SELECT lecturer_id, full_name
    FROM lecturers
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$lecturer = $stmt->get_result()->fetch_assoc();

if (!$lecturer) {
    die("Lecturer profile not found.");
}

$lecturer_id = $lecturer["lecturer_id"];

/* Selected course */
$course_id = isset($_GET["course_id"])
    ? (int)$_GET["course_id"]
    : 0;

$message = "";

/* Save results */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $posted_course_id = (int)$_POST["course_id"];

    /* Verify lecturer owns course */
    $verify = $conn->prepare("
        SELECT course_id
        FROM courses
        WHERE course_id = ?
        AND lecturer_id = ?
    ");

    $verify->bind_param(
        "ii",
        $posted_course_id,
        $lecturer_id
    );

    $verify->execute();

    if ($verify->get_result()->num_rows === 0) {

        $message = "Invalid course.";

    } else {

        foreach ($_POST["cat"] as $enrollment_id => $cat) {

            $enrollment_id = (int)$enrollment_id;

            $cat = (float)$cat;

            $exam = (float)(
                $_POST["exam"][$enrollment_id] ?? 0
            );

            /* Validate marks */
            if ($cat < 0 || $cat > 30) {
                continue;
            }

            if ($exam < 0 || $exam > 70) {
                continue;
            }

            /* Verify enrollment belongs to lecturer's course */
            $check = $conn->prepare("
                SELECT e.enrollment_id
                FROM enrollments e
                INNER JOIN courses c
                    ON e.course_id = c.course_id
                WHERE e.enrollment_id = ?
                AND e.course_id = ?
                AND c.lecturer_id = ?
            ");

            $check->bind_param(
                "iii",
                $enrollment_id,
                $posted_course_id,
                $lecturer_id
            );

            $check->execute();

            if ($check->get_result()->num_rows === 0) {
                continue;
            }

            $total = $cat + $exam;

            /* Determine grade */
            if ($total >= 70) {
                $grade = "A";
            } elseif ($total >= 60) {
                $grade = "B";
            } elseif ($total >= 50) {
                $grade = "C";
            } elseif ($total >= 40) {
                $grade = "D";
            } else {
                $grade = "F";
            }

            $remarks = ($grade === "F")
                ? "Fail"
                : "Pass";

            /* Check existing result */
            $existing = $conn->prepare("
                SELECT result_id
                FROM results
                WHERE enrollment_id = ?
            ");

            $existing->bind_param(
                "i",
                $enrollment_id
            );

            $existing->execute();

            $result = $existing->get_result();

            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();

                $update = $conn->prepare("
                    UPDATE results
                    SET cat_marks = ?,
                        exam_marks = ?,
                        total_marks = ?,
                        grade = ?,
                        remarks = ?
                    WHERE result_id = ?
                ");

                $update->bind_param(
                    "dddssi",
                    $cat,
                    $exam,
                    $total,
                    $grade,
                    $remarks,
                    $row["result_id"]
                );

                $update->execute();

            } else {

                $insert = $conn->prepare("
                    INSERT INTO results
                    (
                        enrollment_id,
                        cat_marks,
                        exam_marks,
                        total_marks,
                        grade,
                        remarks
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $insert->bind_param(
                    "idddss",
                    $enrollment_id,
                    $cat,
                    $exam,
                    $total,
                    $grade,
                    $remarks
                );

                $insert->execute();
            }
        }

        $message = "Results saved successfully.";
    }
}

/* Get lecturer courses */
$stmt = $conn->prepare("
    SELECT course_id, course_code, course_name
    FROM courses
    WHERE lecturer_id = ?
    ORDER BY course_code
");

$stmt->bind_param("i", $lecturer_id);
$stmt->execute();

$courses = $stmt->get_result();

/* Get enrolled students and existing results */
$students = null;

if ($course_id > 0) {

    $stmt = $conn->prepare("
        SELECT
            e.enrollment_id,
            s.student_number,
            s.full_name,
            r.cat_marks,
            r.exam_marks,
            r.total_marks,
            r.grade,
            r.remarks
        FROM enrollments e

        INNER JOIN students s
            ON e.student_id = s.student_id

        INNER JOIN courses c
            ON e.course_id = c.course_id

        LEFT JOIN results r
            ON e.enrollment_id = r.enrollment_id

        WHERE e.course_id = ?
        AND c.lecturer_id = ?
        AND e.status = 'active'

        ORDER BY s.full_name
    ");

    $stmt->bind_param(
        "ii",
        $course_id,
        $lecturer_id
    );

    $stmt->execute();

    $students = $stmt->get_result();
}

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Results | Lecturer Portal</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
}

.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    background: #0f172a;
    padding: 25px 15px;
}

.sidebar h2 {
    color: white;
    text-align: center;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 13px 15px;
    border-radius: 7px;
    margin-bottom: 8px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #1e40af;
    color: white;
}

.main {
    margin-left: 240px;
    padding: 30px;
}

.header,
.panel {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0 0 8px;
}

.header p {
    color: #64748b;
}

.form-row {
    display: flex;
    gap: 15px;
    align-items: end;
}

.form-group {
    flex: 1;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

select {
    width: 100%;
    padding: 11px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}

button {
    padding: 11px 18px;
    border: none;
    border-radius: 6px;
    background: #2563eb;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

th {
    background: #f8fafc;
}

.mark-input {
    width: 80px;
    padding: 8px;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
}

.grade {
    font-weight: bold;
}

.message {
    padding: 12px;
    background: #dcfce7;
    color: #166534;
    border-radius: 6px;
    margin-bottom: 20px;
}

.logout {
    color: #fecaca !important;
}

@media(max-width: 800px) {

    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .form-row {
        flex-direction: column;
        align-items: stretch;
    }

    .panel {
        overflow-x: auto;
    }

}

</style>

</head>

<body>

<div class="sidebar">

    <h2>Lecturer Portal</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="profile.php">My Profile</a>
    <a href="courses.php">My Courses</a>
    <a href="students.php">My Students</a>
    <a href="attendance.php">Attendance</a>
    <a href="results.php" class="active">Manage Results</a>
    <a href="../auth/logout.php" class="logout">Logout</a>

</div>

<div class="main">

    <div class="header">

        <h1>Manage Results</h1>

        <p>
            Enter and update examination results for your students.
        </p>

    </div>

    <?php if ($message): ?>

        <div class="message">
            <?php echo e($message); ?>
        </div>

    <?php endif; ?>

    <div class="panel">

        <form method="GET">

            <div class="form-row">

                <div class="form-group">

                    <label>Select Course</label>

                    <select name="course_id" required>

                        <option value="">
                            Select Course
                        </option>

                        <?php while ($course = $courses->fetch_assoc()): ?>

                            <option
                                value="<?php echo $course["course_id"]; ?>"
                                <?php
                                echo
                                ($course_id == $course["course_id"])
                                    ? "selected"
                                    : "";
                                ?>
                            >

                                <?php echo e($course["course_code"]); ?>
                                -
                                <?php echo e($course["course_name"]); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <button type="submit">
                    Load Students
                </button>

            </div>

        </form>

    </div>

    <?php if ($students !== null): ?>

        <div class="panel">

            <?php if ($students->num_rows > 0): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="course_id"
                        value="<?php echo $course_id; ?>"
                    >

                    <table>

                        <thead>

                            <tr>

                                <th>Student Number</th>

                                <th>Student Name</th>

                                <th>CAT / 30</th>

                                <th>Exam / 70</th>

                                <th>Total</th>

                                <th>Grade</th>

                                <th>Remarks</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php while ($student = $students->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo e(
                                        $student["student_number"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo e(
                                        $student["full_name"]
                                    );
                                    ?>
                                </td>

                                <td>

                                    <input
                                        type="number"
                                        class="mark-input"
                                        name="cat[
                                        <?php
                                        echo $student["enrollment_id"];
                                        ?>
                                        ]"
                                        min="0"
                                        max="30"
                                        step="0.01"
                                        value="<?php
                                        echo e(
                                            $student["cat_marks"] ?? ""
                                        );
                                        ?>"
                                        required
                                    >

                                </td>

                                <td>

                                    <input
                                        type="number"
                                        class="mark-input"
                                        name="exam[
                                        <?php
                                        echo $student["enrollment_id"];
                                        ?>
                                        ]"
                                        min="0"
                                        max="70"
                                        step="0.01"
                                        value="<?php
                                        echo e(
                                            $student["exam_marks"] ?? ""
                                        );
                                        ?>"
                                        required
                                    >

                                </td>

                                <td>
                                    <?php
                                    echo e(
                                        $student["total_marks"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td class="grade">
                                    <?php
                                    echo e(
                                        $student["grade"] ?? "-"
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo e(
                                        $student["remarks"] ?? "-"
                                    );
                                    ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                    <br>

                    <button type="submit">
                        Save Results
                    </button>

                </form>

            <?php else: ?>

                <p>
                    No students are currently enrolled in this course.
                </p>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>