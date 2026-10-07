<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Developer | Student Management System</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }


        body {
            min-height: 100vh;

            background: #f1f5f9;

            color: #0f172a;

            padding: 30px;
        }


        /* =========================
           MAIN PAGE
        ========================== */

        .page-container {
            width: 100%;

            max-width: 1050px;

            margin: auto;

            background: white;

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15);
        }


        /* =========================
           HEADER
        ========================== */

        .header {
            background: linear-gradient(
                135deg,
                #1d4ed8,
                #2563eb
            );

            color: white;

            padding: 25px 40px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .header-title h1 {
            font-size: 25px;

            margin-bottom: 5px;
        }


        .header-title p {
            font-size: 13px;

            color: rgba(255, 255, 255, 0.85);
        }


        .back-button {
            display: inline-block;

            text-decoration: none;

            color: white;

            border: 1px solid rgba(255, 255, 255, 0.6);

            padding: 10px 18px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: 600;

            transition: 0.3s;
        }


        .back-button:hover {
            background: white;

            color: #2563eb;
        }


        /* =========================
           DEVELOPER PROFILE
        ========================== */

        .profile {
            display: flex;

            gap: 45px;

            padding: 45px;

            align-items: flex-start;
        }


        /* =========================
           PHOTO
        ========================== */

        .photo-area {
            flex-shrink: 0;
        }


        .photo-frame {
            width: 250px;

            height: 300px;

            padding: 5px;

            background: #2563eb;

            border-radius: 15px;

            box-shadow:
                0 12px 30px rgba(37, 99, 235, 0.25);
        }


        .developer-photo {
            width: 100%;

            height: 100%;

            object-fit: contain;

            background: white;

            display: block;

            border-radius: 10px;
        }


        /* =========================
           PROFILE INFORMATION
        ========================== */

        .profile-info {
            padding-top: 5px;

            flex: 1;
        }


        .profile-info h2 {
            font-size: 34px;

            margin-bottom: 8px;

            color: #0f172a;
        }


        .role {
            color: #2563eb;

            font-size: 17px;

            font-weight: 600;

            margin-bottom: 20px;
        }


        .description {
            color: #64748b;

            font-size: 15px;

            line-height: 1.8;

            margin-bottom: 25px;
        }


        /* =========================
           CONTACT
        ========================== */

        .contact-box {
            background: #f8fafc;

            border: 1px solid #e2e8f0;

            border-radius: 12px;

            padding: 20px;
        }


        .contact-box h3 {
            color: #1d4ed8;

            font-size: 17px;

            margin-bottom: 15px;
        }


        .contact-item {
            margin-bottom: 10px;

            font-size: 14px;

            color: #475569;
        }


        .contact-item:last-child {
            margin-bottom: 0;
        }


        .contact-item strong {
            color: #0f172a;
        }


        .contact-item a {
            color: #2563eb;

            text-decoration: none;
        }


        .contact-item a:hover {
            text-decoration: underline;
        }


        /* =========================
           INFORMATION SECTION
        ========================== */

        .information {
            border-top: 1px solid #e2e8f0;

            padding: 40px 45px;

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 25px;
        }


        .info-card {
            background: #f8fafc;

            border: 1px solid #e2e8f0;

            border-radius: 12px;

            padding: 25px;
        }


        .info-card h3 {
            color: #1d4ed8;

            font-size: 18px;

            margin-bottom: 14px;
        }


        .info-card p {
            color: #64748b;

            font-size: 14px;

            line-height: 1.7;
        }


        /* =========================
           SKILLS
        ========================== */

        .skills {
            display: flex;

            flex-wrap: wrap;

            gap: 8px;
        }


        .skill {
            background: #dbeafe;

            color: #1d4ed8;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;
        }


        /* =========================
           FOOTER
        ========================== */

        .footer {
            border-top: 1px solid #e2e8f0;

            text-align: center;

            padding: 25px;

            color: #94a3b8;

            font-size: 12px;

            line-height: 1.6;
        }


        /* =========================
           TABLET
        ========================== */

        @media (max-width: 800px) {

            body {
                padding: 15px;
            }


            .header {
                padding: 22px;

                flex-direction: column;

                gap: 15px;

                text-align: center;
            }


            .profile {
                flex-direction: column;

                align-items: center;

                text-align: center;

                padding: 35px 25px;
            }


            .profile-info {
                width: 100%;
            }


            .description {
                max-width: 650px;

                margin-left: auto;

                margin-right: auto;
            }


            .information {
                grid-template-columns: 1fr;

                padding: 30px 25px;
            }


            .contact-box {
                text-align: left;
            }

        }


        /* =========================
           MOBILE
        ========================== */

        @media (max-width: 500px) {

            body {
                padding: 0;
            }


            .page-container {
                border-radius: 0;

                min-height: 100vh;
            }


            .photo-frame {
                width: 250px;

                height: 300px;
            }


            .profile-info h2 {
                font-size: 27px;
            }


            .role {
                font-size: 15px;
            }


            .profile {
                padding: 30px 20px;
            }


            .information {
                padding: 25px 20px;
            }

        }

    </style>

</head>


<body>


    <!-- =========================
         MAIN PAGE CONTAINER
    ========================== -->

    <div class="page-container">


        <!-- =========================
             HEADER
        ========================== -->

        <header class="header">

            <div class="header-title">

                <h1>
                    Developer Profile
                </h1>

                <p>
                    Student Management System
                </p>

            </div>


            <a
                href="login.php"
                class="back-button"
            >
                ← Back to Login
            </a>

        </header>


        <!-- =========================
             PROFILE SECTION
        ========================== -->

        <section class="profile">


            <!-- PHOTO -->

            <div class="photo-area">

                <div class="photo-frame">

                    <img
                        src="../assets/images/joab.jpg"
                        alt="Joab Wasonga"
                        class="developer-photo"
                    >

                </div>

            </div>


            <!-- PROFILE INFORMATION -->

            <div class="profile-info">

                <h2>
                    Joab Wasonga
                </h2>


                <div class="role">
                    BSc Computer Science Student & Software Developer
                </div>


                <p class="description">

                    I am a Computer Science student with a strong interest
                    in software engineering, web development, database
                    systems and application development. I enjoy designing
                    and developing practical software solutions that solve
                    real-world problems.

                </p>


                <!-- CONTACT -->

                <div class="contact-box">

                    <h3>
                        Contact Information
                    </h3>


                    <div class="contact-item">

                        <strong>
                            Email:
                        </strong>

                        <a href="mailto:joabwasong3@gmail.com">
                            joabwasong3@gmail.com
                        </a>

                    </div>


                    <div class="contact-item">

                        <strong>
                            GitHub:
                        </strong>

                        <a
                            href="https://github.com/joabwasonga3-cloud"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            github.com/joabwasonga3-cloud
                        </a>

                    </div>

                </div>

            </div>

        </section>


        <!-- =========================
             INFORMATION
        ========================== -->

        <section class="information">


            <!-- EDUCATION -->

            <div class="info-card">

                <h3>
                    Education
                </h3>

                <p>

                    <strong>
                        Bachelor of Science in Computer Science
                    </strong>

                    <br>

                    Kaimosi Friends University

                </p>

            </div>


            <!-- ABOUT -->

            <div class="info-card">

                <h3>
                    About
                </h3>

                <p>

                    Passionate about building reliable, user-friendly
                    and practical software systems. Interested in
                    software engineering, databases, web applications
                    and modern computing technologies.

                </p>

            </div>


            <!-- TECHNICAL SKILLS -->

            <div class="info-card">

                <h3>
                    Technical Skills
                </h3>


                <div class="skills">

                    <span class="skill">
                        PHP
                    </span>

                    <span class="skill">
                        MySQL
                    </span>

                    <span class="skill">
                        HTML
                    </span>

                    <span class="skill">
                        CSS
                    </span>

                    <span class="skill">
                        JavaScript
                    </span>

                    <span class="skill">
                        Discrete Mathematics
                    </span>

                    <span class="skill">
                        SQL
                    </span>
                    <span class="skill">
                        C++
                    </span>
                    <span class="skill">
                        C
                    </span>
                    <span class="skill">
                        Java
                    </span>
                    <span class="skill">
                        Data Structures & Algorithms
                    </span>

                    <span class="skill">
                        Power BI
                    </span>

                    <span class="skill">
                        Git & GitHub
                    </span>

                </div>

            </div>


            <!-- CURRENT PROJECT -->

            <div class="info-card">

                <h3>
                    Current Project
                </h3>

                <p>

                    <strong>
                        Student Management System
                    </strong>

                    <br><br>

                    A web-based system designed to manage students,
                    lecturers, courses, enrollments, results,
                    attendance and payments through a centralized
                    platform.

                </p>

            </div>


        </section>


        <!-- =========================
             FOOTER
        ========================== -->

        <footer class="footer">

            © <?php echo date("Y"); ?>
            Student Management System

            <br>

            Developed by
            <strong>
                Joab Wasonga
            </strong>

        </footer>


    </div>


</body>

</html>