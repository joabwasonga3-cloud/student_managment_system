<?php
session_start();

// Redirect logged in users to their dashboard
if (isset($_SESSION["user_id"]) && isset($_SESSION["role"])) {
    switch ($_SESSION["role"]) {
        case "admin":
            header("Location: ../admin/dashboard.php");
            exit();
        case "student":
            header("Location: ../student/dashboard.php");
            exit();
        case "lecturer":
            header("Location: ../lecturer/dashboard.php");
            exit();
    }
}

$error = $_SESSION['signup_error'] ?? '';
unset($_SESSION['signup_error']);

$success = $_SESSION['signup_success'] ?? '';
unset($_SESSION['signup_success']);

$saved_role = $_SESSION['signup_role'] ?? '';
unset($_SESSION['signup_role']);

$saved_identifier = $_SESSION['signup_identifier'] ?? '';
unset($_SESSION['signup_identifier']);

$saved_email = $_SESSION['signup_email'] ?? '';
unset($_SESSION['signup_email']);
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
        Create Account | Student Management System
    </title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        body {

            min-height: 100vh;

            background: #e2e8f0;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 25px;
        }


        /* =========================
           MAIN CONTAINER
        ========================= */

        .auth-container {

            width: 100%;

            max-width: 1100px;

            min-height: 700px;

            display: grid;

            grid-template-columns:
                45% 55%;

            background: white;

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 20px 60px
                rgba(0, 0, 0, 0.20);

            animation: fadeIn 0.7s ease;
        }


        @keyframes fadeIn {

            from {

                opacity: 0;

                transform:
                    translateY(25px);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0);
            }
        }


        /* =========================
           LEFT PHOTO
        ========================= */

        .photo-section {

            position: relative;

            min-height: 700px;

            background-image:

                linear-gradient(
                    rgba(15, 23, 42, 0.20),
                    rgba(15, 23, 42, 0.65)
                ),

                url("../assets/images/joab.jpg");


            background-size: cover;

            /*
                CHANGE THIS IF YOUR
                FACE NEEDS REPOSITIONING.

                Examples:

                center 25%;
                60% 25%;
                65% 30%;
            */

            background-position:
                center center;

            background-repeat: no-repeat;

            display: flex;

            align-items: flex-end;

            padding: 45px;
        }


        .photo-content {

            color: white;

            position: relative;

            z-index: 2;
        }


        .badge {

            display: inline-block;

            padding:
                8px 14px;

            border-radius: 30px;

            background:
                rgba(255, 255, 255, 0.18);

            border:
                1px solid
                rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(8px);

            font-size: 12px;

            font-weight: bold;

            margin-bottom: 18px;
        }


        .photo-content h2 {

            font-size: 36px;

            line-height: 1.2;

            margin-bottom: 12px;
        }


        .photo-content p {

            max-width: 370px;

            font-size: 15px;

            line-height: 1.6;

            color:
                rgba(255, 255, 255, 0.90);
        }


        /* =========================
           RIGHT FORM
        ========================= */

        .form-section {

            display: flex;

            justify-content: center;

            align-items: center;

            padding:
                45px 55px;
        }


        .form-container {

            width: 100%;

            max-width: 410px;
        }


        .logo {

            width: 58px;

            height: 58px;

            display: flex;

            justify-content: center;

            align-items: center;

            border-radius: 50%;

            background: #2563eb;

            color: white;

            font-size: 17px;

            font-weight: bold;

            margin-bottom: 17px;

            box-shadow:
                0 8px 20px
                rgba(37, 99, 235, 0.25);
        }


        h1 {

            color: #0f172a;

            font-size: 29px;

            margin-bottom: 7px;
        }


        .subtitle {

            color: #64748b;

            font-size: 14px;

            margin-bottom: 25px;
        }


        /* =========================
           MESSAGES
        ========================= */

        .error {

            background: #fee2e2;

            border:
                1px solid
                #fecaca;

            color: #b91c1c;

            padding: 11px;

            border-radius: 8px;

            font-size: 13px;

            margin-bottom: 18px;

            text-align: center;
        }


        .success {

            background: #dcfce7;

            border:
                1px solid
                #bbf7d0;

            color: #166534;

            padding: 11px;

            border-radius: 8px;

            font-size: 13px;

            margin-bottom: 18px;

            text-align: center;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {

            margin-bottom: 15px;
        }


        label {

            display: block;

            color: #334155;

            font-size: 13px;

            font-weight: 600;

            margin-bottom: 7px;
        }


        input,
        select {

            width: 100%;

            padding:
                12px 13px;

            border:
                1px solid
                #cbd5e1;

            border-radius: 9px;

            font-size: 14px;

            background: white;

            outline: none;

            transition: 0.3s;
        }


        input:focus,
        select:focus {

            border-color:
                #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);
        }


        /* =========================
           PASSWORD
        ========================= */

        .password-wrapper {

            position: relative;
        }


        .password-wrapper input {

            padding-right: 60px;
        }


        .show-password {

            position: absolute;

            right: 12px;

            top: 50%;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            color: #2563eb;

            cursor: pointer;

            font-size: 12px;

            font-weight: bold;
        }


        .hint {

            display: block;

            color: #94a3b8;

            font-size: 10px;

            margin-top: 5px;
        }


        /* =========================
           SIGNUP BUTTON
        ========================= */

        .signup-btn {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 9px;

            background: #2563eb;

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 5px;

            transition: 0.3s;
        }


        .signup-btn:hover {

            background: #1d4ed8;

            transform:
                translateY(-1px);

            box-shadow:
                0 7px 18px
                rgba(37, 99, 235, 0.25);
        }


        /* =========================
           DIVIDER
        ========================= */

        .divider {

            display: flex;

            align-items: center;

            gap: 10px;

            margin: 22px 0;

            color: #94a3b8;

            font-size: 12px;
        }


        .divider::before,
        .divider::after {

            content: "";

            flex: 1;

            height: 1px;

            background: #e2e8f0;
        }


        /* =========================
           LOGIN LINK
        ========================= */

        .login-text {

            text-align: center;

            color: #64748b;

            font-size: 14px;
        }


        .login-text a {

            color: #2563eb;

            font-weight: bold;

            text-decoration: none;
        }


        .login-text a:hover {

            text-decoration: underline;
        }


        .footer {

            text-align: center;

            margin-top: 22px;

            color: #94a3b8;

            font-size: 11px;
        }


        /* =========================
           TABLET
        ========================= */

        @media (max-width: 800px) {

            body {

                padding: 15px;
            }


            .auth-container {

                grid-template-columns: 1fr;

                max-width: 500px;

                min-height: auto;
            }


            .photo-section {

                min-height: 300px;

                padding: 30px;

                background-position:
                    center center;
            }


            .photo-content h2 {

                font-size: 28px;
            }


            .form-section {

                padding:
                    40px 30px;
            }
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 500px) {

            body {

                padding: 0;
            }


            .auth-container {

                min-height: 100vh;

                border-radius: 0;
            }


            .photo-section {

                min-height: 250px;
            }


            .form-section {

                padding:
                    30px 22px;
            }


            h1 {

                font-size: 25px;
            }
        }

    </style>

</head>


<body>


<div class="auth-container">


    <!-- =========================
         PHOTO SECTION
    ========================== -->

    <div class="photo-section">

        <div class="photo-content">

            <div class="badge">

                STUDENT MANAGEMENT SYSTEM

            </div>


            <h2>

                Create Your Account

            </h2>


            <p>

                Get access to your academic
                information and university
                services through one secure platform.

            </p>

        </div>

    </div>


    <!-- =========================
         SIGNUP FORM
    ========================== -->

    <div class="form-section">

        <div class="form-container">


            <div class="logo">

                SMS

            </div>


            <h1>

                Create Account

            </h1>


            <p class="subtitle">

                Register your existing student
                or lecturer account.

            </p>


            <?php if (!empty($error)): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <?php if (!empty($success)): ?>

                <div class="success">

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                </div>

            <?php endif; ?>


            <form
                action="signup_process.php"
                method="POST"
                onsubmit="return validateSignupForm()"
            >


                <!-- ACCOUNT TYPE -->

                <div class="form-group">

                    <label for="role">

                        Account Type

                    </label>


                    <select
                        name="role"
                        id="role"
                        onchange="updateIdentifier()"
                        required
                    >

                        <option value="">

                            Select account type

                        </option>


                        <option value="student" <?= ($saved_role === "student") ? "selected" : "" ?>>

                            Student

                        </option>


                        <option value="lecturer" <?= ($saved_role === "lecturer") ? "selected" : "" ?>>

                            Lecturer

                        </option>

                    </select>

                </div>


                <!-- IDENTIFIER -->

                <div class="form-group">

                    <label
                        for="identifier"
                        id="identifierLabel"
                    >

                        Student Number / Lecturer ID

                    </label>


                    <input
                        type="text"
                        name="identifier"
                        id="identifier"
                        placeholder="Enter your student number or lecturer ID"
                        value="<?= htmlspecialchars($saved_identifier) ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">

                        Email Address

                    </label>


                    <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Enter your email address"
                        value="<?= htmlspecialchars($saved_email) ?>"
                        autocomplete="email"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">

                        Create Password

                    </label>


                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Create your password"
                            minlength="6"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            onclick="
                                togglePassword(
                                    'password',
                                    this
                                )
                            "
                        >

                            Show

                        </button>

                    </div>


                    <span class="hint">

                        Password must contain at least 6 characters.

                    </span>

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label for="confirm_password">

                        Confirm Password

                    </label>


                    <div class="password-wrapper">

                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Confirm your password"
                            minlength="6"
                            required
                        >


                        <button
                            type="button"
                            class="show-password"
                            onclick="
                                togglePassword(
                                    'confirm_password',
                                    this
                                )
                            "
                        >

                            Show

                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    class="signup-btn"
                >

                    Create Account

                </button>


            </form>


            <div class="divider">

                OR

            </div>


            <div class="login-text">

                Already have an account?

                <a href="login.php">

                    Login

                </a>

            </div>


            <div class="footer">

                © <?php echo date("Y"); ?>

                Student Management System

            </div>


        </div>

    </div>

</div>


<script>

function togglePassword(
    inputId,
    button
) {

    const input =
        document.getElementById(inputId);


    if (input.type === "password") {

        input.type = "text";

        button.textContent = "Hide";

    } else {

        input.type = "password";

        button.textContent = "Show";

    }

}


function updateIdentifier() {

    const role =
        document.getElementById("role").value;


    const label =
        document.getElementById(
            "identifierLabel"
        );


    const input =
        document.getElementById(
            "identifier"
        );


    if (role === "student") {

        label.textContent =
            "Student Number";

        input.placeholder =
            "Enter your student number";

    }

    else if (role === "lecturer") {

        label.textContent =
            "Lecturer ID";

        input.placeholder =
            "Enter your lecturer ID";

    }

    else {

        label.textContent =
            "Student Number / Lecturer ID";

        input.placeholder =
            "Enter your student number or lecturer ID";

    }

}

function validateSignupForm() {
    const p1 = document.getElementById("password").value;
    const p2 = document.getElementById("confirm_password").value;
    if (p1 !== p2) {
        alert("Passwords do not match. Please ensure both fields match.");
        return false;
    }
    if (p1.length < 6) {
        alert("Password must contain at least 6 characters.");
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("role").value) {
        updateIdentifier();
    }
});

</script>


</body>
</html>