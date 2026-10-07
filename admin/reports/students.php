<?php

session_start();

require_once "../../config/database.php";


// --------------------------------------------------
// ADMIN ACCESS CONTROL
// --------------------------------------------------

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}


// --------------------------------------------------
// FILTERS
// --------------------------------------------------

$status = $_GET["status"] ?? "all";
$program = $_GET["program"] ?? "all";


// --------------------------------------------------
// GET PROGRAMS
// --------------------------------------------------

$program_query = mysqli_query(
    $conn,
    "SELECT DISTINCT program
     FROM students
     WHERE program IS NOT NULL
     AND program != ''
     ORDER BY program ASC"
);

if (!$program_query) {
    die("Error loading programs: " . mysqli_error($conn));
}


// --------------------------------------------------
// SUMMARY STATISTICS
// --------------------------------------------------

$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students"
);

if (!$total_query) {
    die("Error counting students: " . mysqli_error($conn));
}

$total_students = mysqli_fetch_assoc($total_query)["total"];


$active_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students
     WHERE status = 'active'"
);

if (!$active_query) {
    die("Error counting active students: " . mysqli_error($conn));
}

$active_students = mysqli_fetch_assoc($active_query)["total"];


$inactive_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students
     WHERE status = 'inactive'"
);

if (!$inactive_query) {
    die("Error counting inactive students: " . mysqli_error($conn));
}

$inactive_students = mysqli_fetch_assoc($inactive_query)["total"];


// --------------------------------------------------
// BUILD REPORT QUERY
// --------------------------------------------------

$sql = "
    SELECT
        student_id,
        student_number,
        full_name,
        phone,
        gender,
        program,
        year_of_study,
        admission_date,
        status
    FROM students
    WHERE 1 = 1
";


// Status filter
if ($status === "active" || $status === "inactive") {

    $safe_status = mysqli_real_escape_string(
        $conn,
        $status
    );

    $sql .= " AND status = '$safe_status'";
}


// Program filter
if ($program !== "all") {

    $safe_program = mysqli_real_escape_string(
        $conn,
        $program
    );

    $sql .= " AND program = '$safe_program'";
}


$sql .= " ORDER BY full_name ASC";


// --------------------------------------------------
// EXECUTE REPORT QUERY
// --------------------------------------------------

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error generating student report: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Student Report | Student Management System
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }


        .container {
            width: 95%;
            max-width: 1300px;
            margin: 30px auto;
        }


        /* ------------------------------------------
           HEADER
        ------------------------------------------ */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }


        .header h1 {
            margin: 0 0 5px;
        }


        .header p {
            margin: 0;
            color: #666;
        }


        .buttons {
            display: flex;
            gap: 10px;
        }


        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            text-decoration: none;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }


        .back {
            background: #6b7280;
        }


        .print {
            background: #2563eb;
        }


        /* ------------------------------------------
           FILTERS
        ------------------------------------------ */

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, .08);
        }


        .filters form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }


        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }


        .form-group label {
            font-size: 14px;
            font-weight: bold;
        }


        select {
            min-width: 180px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: white;
        }


        .filter-btn {
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }


        .reset-btn {
            padding: 10px 18px;
            border-radius: 5px;
            background: #6b7280;
            color: white;
            text-decoration: none;
        }


        /* ------------------------------------------
           SUMMARY
        ------------------------------------------ */

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }


        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, .08);
        }


        .card h3 {
            margin: 0 0 10px;
            color: #555;
            font-size: 15px;
        }


        .number {
            font-size: 28px;
            font-weight: bold;
        }


        /* ------------------------------------------
           REPORT TABLE
        ------------------------------------------ */

        .report {
            background: white;
            padding: 25px;
            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, .08);
        }


        .report h2 {
            margin-top: 0;
        }


        .table-container {
            overflow-x: auto;
        }


        table {
            width: 100%;
            border-collapse: collapse;
        }


        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            white-space: nowrap;
        }


        th {
            background: #f1f5f9;
        }


        .active {
            color: #15803d;
            font-weight: bold;
        }


        .inactive {
            color: #dc2626;
            font-weight: bold;
        }


        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }


        /* ------------------------------------------
           RESPONSIVE
        ------------------------------------------ */

        @media (max-width: 800px) {

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }


            .summary {
                grid-template-columns: 1fr;
            }


            .filters form {
                flex-direction: column;
                align-items: stretch;
            }


            select,
            .filter-btn,
            .reset-btn {
                width: 100%;
            }

        }


        /* ------------------------------------------
           PRINT
        ------------------------------------------ */

        @media print {

            .buttons,
            .filters,
            .summary {
                display: none;
            }


            body {
                background: white;
            }


            .container {
                width: 100%;
                margin: 0;
            }


            .report {
                box-shadow: none;
                padding: 0;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- ==========================================
         HEADER
    =========================================== -->

    <div class="header">

        <div>

            <h1>
                Student Report
            </h1>

            <p>
                Student information and enrollment status.
            </p>

        </div>


        <div class="buttons">

            <a
                href="index.php"
                class="btn back"
            >
                Reports
            </a>


            <button
                onclick="window.print()"
                class="btn print"
            >
                Print Report
            </button>

        </div>

    </div>



    <!-- ==========================================
         FILTERS
    =========================================== -->

    <div class="filters">

        <form method="GET">


            <div class="form-group">

                <label for="status">
                    Student Status
                </label>

                <select
                    name="status"
                    id="status"
                >

                    <option
                        value="all"
                        <?php
                        if ($status === "all") {
                            echo "selected";
                        }
                        ?>
                    >
                        All Students
                    </option>


                    <option
                        value="active"
                        <?php
                        if ($status === "active") {
                            echo "selected";
                        }
                        ?>
                    >
                        Active
                    </option>


                    <option
                        value="inactive"
                        <?php
                        if ($status === "inactive") {
                            echo "selected";
                        }
                        ?>
                    >
                        Inactive
                    </option>

                </select>

            </div>



            <div class="form-group">

                <label for="program">
                    Program
                </label>

                <select
                    name="program"
                    id="program"
                >

                    <option
                        value="all"
                    >
                        All Programs
                    </option>


                    <?php while ($program_row = mysqli_fetch_assoc($program_query)): ?>

                        <option
                            value="<?php echo htmlspecialchars($program_row["program"]); ?>"
                            <?php
                            if ($program === $program_row["program"]) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($program_row["program"]); ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>



            <button
                type="submit"
                class="filter-btn"
            >
                Apply Filters
            </button>


            <a
                href="students.php"
                class="reset-btn"
            >
                Reset
            </a>


        </form>

    </div>



    <!-- ==========================================
         SUMMARY CARDS
    =========================================== -->

    <div class="summary">


        <div class="card">

            <h3>
                Total Students
            </h3>

            <div class="number">
                <?php echo $total_students; ?>
            </div>

        </div>



        <div class="card">

            <h3>
                Active Students
            </h3>

            <div class="number">
                <?php echo $active_students; ?>
            </div>

        </div>



        <div class="card">

            <h3>
                Inactive Students
            </h3>

            <div class="number">
                <?php echo $inactive_students; ?>
            </div>

        </div>


    </div>



    <!-- ==========================================
         REPORT
    =========================================== -->

    <div class="report">

        <h2>
            Student Information
        </h2>


        <div class="table-container">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <table>


                    <thead>

                        <tr>

                            <th>
                                Student Number
                            </th>

                            <th>
                                Full Name
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Gender
                            </th>

                            <th>
                                Program
                            </th>

                            <th>
                                Year of Study
                            </th>

                            <th>
                                Admission Date
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["student_number"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["full_name"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["phone"] ?? "N/A"
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["gender"] ?? "N/A"
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["program"] ?? "N/A"
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["year_of_study"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["admission_date"]
                                    );
                                    ?>
                                </td>


                                <td>


                                    <?php if ($row["status"] === "active"): ?>

                                        <span class="active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="empty">

                    No student records found
                    matching the selected filters.

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


</body>

</html>