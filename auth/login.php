<?php
session_start();

// These variables can be set by your authentication logic
$error = $error ?? "";
$success = $success ?? "";
$email_val = $email_val ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Student Management System</title>


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

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 25px;
        }


        /* =========================
           MAIN SPLIT-SCREEN CONTAINER
        ========================== */

        .auth-container {
            width: 100%;

            max-width: 1050px;

            min-height: 650px;

            background: white;

            display: grid;

            grid-template-columns: 45% 55%;

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.18);

            animation: appear 0.7s ease;
        }


        @keyframes appear {

            from {
                opacity: 0;

                transform: translateY(25px);
            }

            to {
                opacity: 1;

                transform: translateY(0);
            }
        }


        /* =========================
           PHOTO SIDE
        ========================== */

        .photo-section {
            position: relative;

            min-height: 650px;

            background-image:
                linear-gradient(
                    rgba(15, 23, 42, 0.35),
                    rgba(15, 23, 42, 0.65)
                ),
                url("../assets/images/joab.jpg");

            background-size: cover;

            background-position: center center;

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


        .system-badge {
            display: inline-block;

            background: rgba(255, 255, 255, 0.18);

            border: 1px solid rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(8px);

            padding: 8px 14px;

            border-radius: 20px;

            font-size: 12px;

            margin-bottom: 18px;
        }


        .photo-content h2 {
            font-size: 34px;

            line-height: 1.2;

            margin-bottom: 12px;
        }


        .photo-content p {
            font-size: 15px;

            line-height: 1.6;

            max-width: 350px;

            color: rgba(255, 255, 255, 0.88);
        }


        /* =========================
           LOGIN SIDE
        ========================== */

        .form-section {
            display: flex;

            align-items: center;

            justify-content: center;

            padding: 55px;
        }


        .form-container {
            width: 100%;

            max-width: 400px;
        }


        .logo {
            width: 62px;

            height: 62px;

            border-radius: 50%;

            background: #2563eb;

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 20px;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.25);
        }


        .form-container h1 {
            color: #0f172a;

            font-size: 30px;

            margin-bottom: 8px;
        }


        .subtitle {
            color: #64748b;

            font-size: 14px;

            margin-bottom: 30px;
        }


        /* =========================
           ERROR MESSAGE
        ========================== */

        .error {
            background: #fee2e2;

            border: 1px solid #fecaca;

            color: #b91c1c;

            padding: 12px;

            border-radius: 8px;

            font-size: 13px;

            margin-bottom: 20px;
        }


        /* =========================
           SUCCESS MESSAGE
        ========================== */

        .success {
            background: #dcfce7;

            border: 1px solid #bbf7d0;

            color: #166534;

            padding: 12px;

            border-radius: 8px;

            font-size: 13px;

            margin-bottom: 20px;
        }


        /* =========================
           FORM
        ========================== */

        .form-group {
            margin-bottom: 20px;
        }


        label {
            display: block;

            color: #334155;

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 8px;
        }


        input {
            width: 100%;

            padding: 14px;

            border: 1px solid #cbd5e1;

            border-radius: 9px;

            font-size: 14px;

            outline: none;

            transition: 0.3s;
        }


        input:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.10);
        }


        /* =========================
           PASSWORD
        ========================== */

        .password-wrapper {
            position: relative;
        }


        .password-wrapper input {
            padding-right: 65px;
        }


        .show-password {
            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #2563eb;

            cursor: pointer;

            font-size: 12px;

            font-weight: bold;
        }


        /* =========================
           LOGIN BUTTON
        ========================== */

        .login-btn {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 9px;

            background: #2563eb;

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;
        }


        .login-btn:hover {
            background: #1d4ed8;

            transform: translateY(-1px);

            box-shadow:
                0 7px 18px rgba(37, 99, 235, 0.25);
        }


        /* =========================
           FORGOT PASSWORD
        ========================== */

        .forgot-password {
            text-align: right;

            margin-top: 12px;
        }


        .forgot-password a {
            color: #2563eb;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;
        }


        .forgot-password a:hover {
            text-decoration: underline;
        }


        /* =========================
           DIVIDER
        ========================== */

        .divider {
            display: flex;

            align-items: center;

            gap: 10px;

            margin: 28px 0;

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
           SIGNUP
        ========================== */

        .signup-text {
            text-align: center;

            color: #64748b;

            font-size: 14px;
        }


        .signup-text a {
            color: #2563eb;

            font-weight: bold;

            text-decoration: none;
        }


        .signup-text a:hover {
            text-decoration: underline;
        }


        /* =========================
           FOOTER
        ========================== */

        .footer {
            text-align: center;

            margin-top: 30px;

            color: #94a3b8;

            font-size: 11px;

            line-height: 1.7;
        }


        .footer .developer-credit {
            margin-top: 5px;
        }


        .footer .developer-credit a {
            color: #2563eb;

            text-decoration: none;

            font-weight: 600;

            transition: 0.3s;
        }


        .footer .developer-credit a:hover {
            color: #1d4ed8;

            text-decoration: underline;
        }


        /* =========================
           TABLET / MOBILE
        ========================== */

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

                align-items: flex-end;

                background-position: center center;
            }


            .photo-content h2 {
                font-size: 27px;
            }


            .form-section {
                padding: 40px 30px;
            }
        }


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
                padding: 35px 22px;
            }
        }

    </style>

</head>


<body>


    <!-- =========================
         MAIN AUTH CONTAINER
    ========================== -->

    <div class="auth-container">


        <!-- =========================
             PHOTO SECTION
        ========================== -->

        <div class="photo-section">

            <div class="photo-content">

                <div class="system-badge">
                    STUDENT MANAGEMENT SYSTEM
                </div>


                <h2>
                    Welcome Back
                </h2>


                <p>
                    Manage your academic information,
                    courses, results and university activities
                    from one place.
                </p>

            </div>

        </div>


        <!-- =========================
             LOGIN SECTION
        ========================== -->

        <div class="form-section">

            <div class="form-container">


                <!-- Logo -->

                <div class="logo">
                    SMS
                </div>


                <!-- Heading -->

                <h1>
                    Sign In
                </h1>


                <p class="subtitle">
                    Login to your account to continue.
                </p>


                <!-- Error Message -->

                <?php if (!empty($error)): ?>

                    <div class="error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <!-- Success Message -->

                <?php if (!empty($success)): ?>

                    <div class="success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>

                <?php endif; ?>


                <!-- Login Form -->

                <form
                    action="authenticate.php"
                    method="POST"
                >


                    <!-- Email -->

                    <div class="form-group">

                        <label for="email">
                            Email Address
                        </label>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($email_val); ?>"
                            autocomplete="email"
                            required
                        >

                    </div>


                    <!-- Password -->

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>


                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >


                            <button
                                type="button"
                                class="show-password"
                                onclick="togglePassword()"
                            >
                                Show
                            </button>

                        </div>

                    </div>


                    <!-- Login Button -->

                    <button
                        type="submit"
                        class="login-btn"
                    >
                        Login
                    </button>

                </form>


                <!-- Forgot Password -->

                <div class="forgot-password">

                    <a href="forgot_password.php">
                        Forgot Password?
                    </a>

                </div>


                <!-- Divider -->

                <div class="divider">
                    OR
                </div>


                <!-- Signup -->

                <div class="signup-text">

                    New Student or Lecturer?

                    <a href="signup.php">
                        Create Account
                    </a>

                </div>


                <!-- Footer -->

                <div class="footer">

                    <div>
                        © <?php echo date("Y"); ?>
                        Student Management System
                    </div>


                    <div class="developer-credit">

                        Developed by

                        <!-- developers.php is in the SAME auth folder -->
                        <a href="developers.php">
                            Joab Wasonga
                        </a>

                    </div>

                </div>


            </div>

        </div>


    </div>


    <!-- =========================
         JAVASCRIPT
    ========================== -->

    <script>

        function togglePassword() {

            const password =
                document.getElementById("password");

            const button =
                document.querySelector(".show-password");


            if (password.type === "password") {

                password.type = "text";

                button.textContent = "Hide";

            } else {

                password.type = "password";

                button.textContent = "Show";

            }

        }

    </script>


</body>

</html>