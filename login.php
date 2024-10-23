<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginInput = $_POST['loginInput'];
    $password = $_POST['password'];

    // Determine if the input is an email or username
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT * FROM serviceemployee WHERE email = ? AND password = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM serviceemployee WHERE username = ? AND password = ?");
    }

    $stmt->bind_param("ss", $loginInput, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        $_SESSION['employee_id'] = $employee['employee_id'];
        $_SESSION['employee_name'] = $employee['employee_name'];

        if ($employee['username'] === 'admin' || $employee['email'] === 'admin@gmail.com') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email/username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Queue Management</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        /* Import Google Font */
        @import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap");

        :root {
            --white-color: hsl(0, 0%, 100%);
            --black-color: hsl(0, 0%, 0%);
            --body-font: "Roboto", sans-serif;
            --h1-font-size: 2rem;
            --normal-font-size: 1rem;
            --small-font-size: 0.875rem;
            --font-medium: 500;
            --primary-color: #fcbf05;
            --secondary-color: #e0a800;
            --form-background: rgba(0, 0, 0, 0.6);
            
        }

        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }

        body,
        input,
        button {
            font-size: var(--normal-font-size);
            font-family: var(--body-font);
        }

        body {
            color: var(--white-color);
            position: relative;
            overflow: hidden;
        }

        input,
        button {
            border: none;
            outline: none;
        }

        a {
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /* Background Styling */
        .login {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(
                    rgba(0, 0, 0, 0.5),
                    rgba(0, 0, 0, 0.5)
                ),
                url('https://kaboompics.com/cache/a/3/4/5/0/a3450244f77da7185378bf44a3ed0b89b25ee940.jpeg') no-repeat center center/cover;
        }

        /* Form Styling */
        .login__form {
            background-color: var(--form-background);
            border: 2px solid var(--white-color);
            padding: 2.5rem 2rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            width: 90%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        .login__form:hover {
            transform: translateY(-5px);
        }

        .login__title {
            text-align: center;
            font-size: var(--h1-font-size);
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--white-color);
        }

        .login__content {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .login__box {
            display: flex;
            align-items: center;
            border-bottom: 2px solid var(--white-color);
            position: relative;
        }

        .login__icon {
            font-size: 1.5rem;
            color: var(--white-color);
            margin-right: 0.75rem;
            min-width: 1.5rem; /* Ensure icons are aligned even if some icons are smaller */
            text-align: center;
        }

        .login__box-input {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .login__input {
            width: 100%;
            padding: 0.8rem 0;
            background: none;
            color: var(--white-color);
            font-size: var(--normal-font-size);
            border: none;
            outline: none;
            position: relative;
            z-index: 1;
        }

        .login__input::placeholder {
            color: transparent;
        }

        .login__label {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            color: var(--white-color);
            pointer-events: none;
            transition: all 0.3s ease;
            font-weight: var(--font-medium);
            padding: 0 0.25rem;
            font-size: var(--normal-font-size);
        }

        .login__eye {
            font-size: 1.5rem;
            color: var(--white-color);
            margin-left: 0.75rem;
            cursor: pointer;
            z-index: 2;
        }

        /* Label Animation */
        .login__input:focus + .login__label,
        .login__input:not(:placeholder-shown) + .login__label {
            top: -0.6rem;
            font-size: var(--small-font-size);
        }

        /* Checkbox and Forgot Password */
        .login__check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .login__check-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .login__check-input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary-color);
        }

        .login__check-label,
        .login__forgot,
        .login__register {
            font-size: var(--small-font-size);
            color: var(--white-color);
        }

        .login__forgot:hover {
            text-decoration: underline;
        }

        /* Login Button */
        .login__button {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: var(--primary-color);
            color: var(--black-color);
            font-weight: var(--font-medium);
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .login__button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .login__button:active {
            background-color: var(--primary-color);
            transform: translateY(0);
        }

        /* Register Section */
        .login__register {
            text-align: center;
        }

        .login__register a {
            color: var(--primary-color);
            font-weight: var(--font-medium);
        }

        .login__register a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media screen and (min-width: 576px) {
            .login__form {
                padding: 3rem 2.5rem;
            }

            .login__title {
                font-size: 2.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="login">
        <form method="POST" action="login.php" class="login__form">
            <h1 class="login__title">Login</h1>
            <?php if (isset($error)) : ?>
                <div id="error-message" class="alert alert-danger" style="background-color: rgba(255, 0, 0, 0.7); padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="login__content">
                <!-- Username or Email -->
                <div class="login__box">
                    <i class="bx bx-user login__icon"></i>
                    <div class="login__box-input">
                        <input type="text" required class="login__input" placeholder=" " name="loginInput">
                        <label class="login__label">Username or Email</label>
                    </div>
                </div>

                <!-- Password -->
                <div class="login__box">
                    <i class="ri-lock-2-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" name="password" required class="login__input" id="login-pass" placeholder=" ">
                        <label for="login-pass" class="login__label">Password</label>
                        <i class="ri-eye-off-line login__eye" id="login-eye"></i>
                    </div>
                </div>

                <!-- Remember me and Forgot Password -->
                <div class="login__check">
                    <div class="login__check-group">
                        <input type="checkbox" class="login__check-input" id="remember-me" name="remember_me">
                        <label class="login__check-label" for="remember-me">Remember me</label>
                    </div>
                    <a href="#" class="login__forgot">Forgot Password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login__button">Login</button>
            </div>

            <!-- Register Section -->
            <div class="login__register">
                <p>Don't have an account? <a href="#">Register here</a></p>
            </div>
        </form>
    </div>

    <script>
        /*--================== Show Hidden - Password =================*/
        const showHiddenPass = (loginPass, loginEye) => {
            const input = document.getElementById(loginPass),
                iconEye = document.getElementById(loginEye);

            iconEye.addEventListener('click', () => {
                if (input.type === 'password') {
                    input.type = 'text';
                    iconEye.classList.add('ri-eye-line');
                    iconEye.classList.remove('ri-eye-off-line');
                } else {
                    input.type = 'password';
                    iconEye.classList.remove('ri-eye-line');
                    iconEye.classList.add('ri-eye-off-line');
                }
            });
        }

        showHiddenPass('login-pass', 'login-eye');

        window.onload = function () {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                // ซ่อนข้อความแสดงข้อผิดพลาดหลังจาก 3 วินาที (3000 มิลลิวินาที)
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            }
        };
    </script>
</body>

</html>
