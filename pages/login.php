<?php
require '../includes/db.php';

// Default theme
$primary_color = '#667eea';
$secondary_color = '#5b9df9';
$system_logo = 'assets/default-logo.svg';

// Load from database
$theme_query = "SELECT primary_color, secondary_color, logo_path FROM system_theme LIMIT 1";
$theme_result = $conn->query($theme_query);
if ($theme_result && $theme_row = $theme_result->fetch_assoc()) {
    if (!empty($theme_row['primary_color']))
        $primary_color = $theme_row['primary_color'];
    if (!empty($theme_row['secondary_color']))
        $secondary_color = $theme_row['secondary_color'];
    if (!empty($theme_row['logo_path']) && file_exists("../" . $theme_row['logo_path']))
        $system_logo = $theme_row['logo_path'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PDVS Visitor Management Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color:
                <?php echo $primary_color;
                ?>
            ;
            --secondary-color:
                <?php echo $secondary_color;
                ?>
            ;
        }

        /* Full-screen animated gradient */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), #9face6, #f7797d);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            z-index: -2;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Floating soft blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            filter: blur(50px);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        .blob.one {
            width: 400px;
            height: 400px;
            top: 10%;
            left: -100px;
            animation-delay: 0s;
        }

        .blob.two {
            width: 300px;
            height: 300px;
            bottom: 5%;
            right: -80px;
            animation-delay: 5s;
        }

        .blob.three {
            width: 250px;
            height: 250px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) translateX(0);
            }

            50% {
                transform: translateY(-30px) translateX(20px);
            }
        }

        /* Glassmorphism card */
        .login-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            padding: 50px 40px;
            border-radius: 20px;
            width: 380px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
        }

        .company-logo img {
            max-height: 60px;
            max-width: 200px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
            animation: floatGlow 3s ease-in-out infinite;
        }

        @keyframes floatGlow {

            0%,
            100% {
                transform: translateY(0);
                text-shadow: 0 0 20px rgba(255, 255, 255, 0.8);
            }

            50% {
                transform: translateY(-6px);
                text-shadow: 0 0 35px rgba(255, 255, 255, 1);
            }
        }

        .login-card h4 {
            margin-bottom: 25px;
            font-weight: 600;
            color: #fff;
        }

        input.form-control {
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 18px;
            font-size: 15px;
            border: none;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        input.form-control::placeholder {
            color: #f0f0f0;
        }

        input.form-control:focus {
            background: rgba(255, 255, 255, 0.4);
            outline: none;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
        }

        button[type="submit"] {
            padding: 14px;
            font-weight: 600;
            border-radius: 12px;
            background: var(--primary-color);
            color: #fff;
            border: none;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            background: var(--secondary-color);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
            transform: translateY(-2px);
        }

        #loginError {
            color: #ff6b6b;
            margin-top: 15px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        @media (max-width: 400px) {
            .login-card {
                width: 90%;
                padding: 35px 25px;
            }
        }
    </style>
</head>

<body>

    <div class="blob one"></div>
    <div class="blob two"></div>
    <div class="blob three"></div>

    <div class="login-card">
        <?php if (!empty($system_logo) && $system_logo !== 'assets/default-logo.svg'): ?>
            <div class="company-logo">
                <img src="../<?php echo htmlspecialchars($system_logo); ?>" alt="System Logo">
            </div>
        <?php endif; ?>

        <h4>Visitor Pass Login</h4>
        <form method="POST" action="auth.php">
            <input type="hidden" name="login_type" id="loginType" value="host" />
            <input type="email" name="email" class="form-control" placeholder="Email" required />
            <input type="password" name="password" class="form-control" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
        <div id="loginError"></div>
    </div>

    <script>
        const params = new URLSearchParams(window.location.search);
        if (params.get("error")) {
            const el = document.getElementById("loginError");
            if (params.get("error") === "invalid") el.textContent = "Invalid email or password.";
            else if (params.get("error") === "wrongtype") el.textContent = "Wrong login type selected.";
            else el.textContent = "Login error.";
            el.style.display = "block";
        }
    </script>
</body>

</html>