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
// QUICK STATISTICS
// --------------------------------------------------

$students_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students"
);

$total_students = mysqli_fetch_assoc($students_query)["total"];


$lecturers_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM lecturers"
);

$total_lecturers = mysqli_fetch_assoc($lecturers_query)["total"];


$courses_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM courses"
);

$total_courses = mysqli_fetch_assoc($courses_query)["total"];


$enrollments_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM enrollments"
);

$total_enrollments = mysqli_fetch_assoc($enrollments_query)["total"];


$payments_query = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total FROM payments"
);

$total_payments = mysqli_fetch_assoc($payments_query)["total"];

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
        Reports Dashboard | Student Management System
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
            max-width: 1250px;
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
            margin: 0 0 6px;
            font-size: 28px;
        }


        .header p {
            margin: 0;
            color: #666;
        }


        .header-actions {
            display: flex;
            gap: 10px;
        }


        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 14px;
        }


        .dashboard-btn {
            background: #6b7280;
        }


        .print-btn {
            background: #2563eb;
        }


        /* ------------------------------------------
           QUICK STATISTICS
        ------------------------------------------ */

        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }


        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }


        .stat-card h3 {
            margin: 0 0 10px;
            color: #666;
            font-size: 15px;
        }


        .stat-number {
            font-size: 28px;
            font-weight: bold;
        }


        .money {
            font-size: 22px;
        }


        /* ------------------------------------------
           REPORTS SECTION
        ------------------------------------------ */

        .reports-section {
            margin-bottom: 30px;
        }


        .reports-section h2 {
            margin-bottom: 18px;
        }


        .reports-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }


        .report-card {
            background: white;
            padding: 22px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            min-height: 190px;

            transition: transform .2s ease,
                        box-shadow .2s ease;
        }


        .report-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 5px 15px rgba(0, 0, 0, .12);
        }


        .report-card h3 {
            margin: 0 0 10px;
            font-size: 18px;
        }


        .report-card p {
            margin: 0 0 20px;
            color: #666;
            line-height: 1.5;
            font-size: 14px;
        }


        .view-btn {
            display: inline-block;
            width: 100%;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            background: #2563eb;
            color: white;
            font-size: 14px;
        }


        .view-btn:hover {
            background: #1d4ed8;
        }


        .summary-card {
            border-left: 4px solid #16a34a;
        }


        .summary-card .view-btn {
            background: #16a34a;
        }


        .summary-card .view-btn:hover {
            background: #15803d;
        }


        /* ------------------------------------------
           INFORMATION
        ------------------------------------------ */

        .information {
            background: white;
            padding: 22px;
            border-radius: 8px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, .08);

            line-height: 1.6;
        }


        .information h2 {
            margin-top: 0;
        }


        /* ------------------------------------------
           RESPONSIVE DESIGN
        ------------------------------------------ */

        @media (max-width: 1100px) {

            .stats {
                grid-template-columns: repeat(3, 1fr);
            }


            .reports-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 700px) {

            .container {
                width: 92%;
            }


            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }


            .stats {
                grid-template-columns: 1fr;
            }


            .reports-grid {
                grid-template-columns: 1fr;
            }


            .header-actions {
                width: 100%;
            }


            .btn {
                text-align: center;
            }

        }


        /* ------------------------------------------
           PRINT
        ------------------------------------------ */

        @media print {

            .header-actions,
            .view-btn {
                display: none;
            }


            body {
                background: white;
            }


            .container {
                width: 100%;
                margin: 0;
            }


            .report-card,
            .stat-card,
            .information {
                box-shadow: none;
                border: 1px solid #ddd;
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
                Reports Dashboard
            </h1>

            <p>
                Generate and view reports from the
                Student Management System.
            </p>

        </div>


        <div class="header-actions">

            <a
                href="../dashboard.php"
                class="btn dashboard-btn"
            >
                Dashboard
            </a>


            <button
                onclick="window.print()"
                class="btn print-btn"
            >
                Print
            </button>

        </div>

    </div>



    <!-- ==========================================
         QUICK STATISTICS
    =========================================== -->

    <div class="stats">


        <div class="stat-card">

            <h3>
                Total Students
            </h3>

            <div class="stat-number">

                <?php echo $total_students; ?>

            </div>

        </div>



        <div class="stat-card">

            <h3>
                Total Lecturers
            </h3>

            <div class="stat-number">

                <?php echo $total_lecturers; ?>

            </div>

        </div>



        <div class="stat-card">

            <h3>
                Total Courses
            </h3>

            <div class="stat-number">

                <?php echo $total_courses; ?>

            </div>

        </div>



        <div class="stat-card">

            <h3>
                Total Enrollments
            </h3>

            <div class="stat-number">

                <?php echo $total_enrollments; ?>

            </div>

        </div>



        <div class="stat-card">

            <h3>
                Total Payments
            </h3>

            <div class="stat-number money">

                KSh
                <?php
                echo number_format(
                    $total_payments,
                    2
                );
                ?>

            </div>

        </div>


    </div>



    <!-- ==========================================
         REPORTS
    =========================================== -->

    <div class="reports-section">

        <h2>
            Available Reports
        </h2>


        <div class="reports-grid">


            <!-- Students -->

            <div class="report-card">

                <div>

                    <h3>
                        Student Report
                    </h3>

                    <p>
                        View student information,
                        programs, year of study,
                        admission dates and status.
                    </p>

                </div>


                <a
                    href="students.php"
                    class="view-btn"
                >
                    View Student Report
                </a>

            </div>



            <!-- Lecturers -->

            <div class="report-card">

                <div>

                    <h3>
                        Lecturer Report
                    </h3>

                    <p>
                        View lecturer names, emails,
                        phone numbers, departments
                        and status.
                    </p>

                </div>


                <a
                    href="lecturers.php"
                    class="view-btn"
                >
                    View Lecturer Report
                </a>

            </div>



            <!-- Courses -->

            <div class="report-card">

                <div>

                    <h3>
                        Course Report
                    </h3>

                    <p>
                        View course codes, course names,
                        descriptions, credit hours
                        and assigned lecturers.
                    </p>

                </div>


                <a
                    href="courses.php"
                    class="view-btn"
                >
                    View Course Report
                </a>

            </div>



            <!-- Enrollments -->

            <div class="report-card">

                <div>

                    <h3>
                        Enrollment Report
                    </h3>

                    <p>
                        View student enrollments,
                        courses, academic years,
                        semesters and enrollment status.
                    </p>

                </div>


                <a
                    href="enrollments.php"
                    class="view-btn"
                >
                    View Enrollment Report
                </a>

            </div>



            <!-- Attendance -->

            <div class="report-card">

                <div>

                    <h3>
                        Attendance Report
                    </h3>

                    <p>
                        View attendance records,
                        present and absent records,
                        dates and remarks.
                    </p>

                </div>


                <a
                    href="attendance.php"
                    class="view-btn"
                >
                    View Attendance Report
                </a>

            </div>



            <!-- Results -->

            <div class="report-card">

                <div>

                    <h3>
                        Results Report
                    </h3>

                    <p>
                        View CAT marks, examination
                        marks, total marks, grades
                        and academic remarks.
                    </p>

                </div>


                <a
                    href="results.php"
                    class="view-btn"
                >
                    View Results Report
                </a>

            </div>



            <!-- Payments -->

            <div class="report-card">

                <div>

                    <h3>
                        Payment Report
                    </h3>

                    <p>
                        View student payments,
                        payment dates, methods,
                        references and amounts.
                    </p>

                </div>


                <a
                    href="payments.php"
                    class="view-btn"
                >
                    View Payment Report
                </a>

            </div>



            <!-- System Summary -->

            <div class="report-card summary-card">

                <div>

                    <h3>
                        System Summary
                    </h3>

                    <p>
                        View an overall summary of
                        students, lecturers, courses,
                        enrollments, results,
                        attendance and payments.
                    </p>

                </div>


                <a
                    href="summary.php"
                    class="view-btn"
                >
                    View System Summary
                </a>

            </div>


        </div>

    </div>



    <!-- ==========================================
         INFORMATION
    =========================================== -->

    <div class="information">

        <h2>
            About Reports
        </h2>

        <p>
            The Reports module provides administrators
            with read-only information generated from
            the records stored in the Student Management
            System.
        </p>

        <p>
            Each report focuses on a specific area of
            the system, while the System Summary provides
            an overall view of the institution's records.
        </p>

        <strong>
            Note:
        </strong>

        Reports do not modify student, lecturer,
        course, enrollment, attendance, result or
        payment records.

    </div>


</div>


</body>

</html>