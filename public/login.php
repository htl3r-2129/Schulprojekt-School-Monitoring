<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth();

if (isset($_COOKIE['user'])) {
    header(header: 'Location: dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($email, $password)) {
        $uuid = $auth->getUUID($email);
        setcookie("user", "$uuid", time() + (86400 * 30), "/");
        header(header: 'Location: dashboard.php');
        exit;
    } else {
        $error = "Benutzername oder Passwort ist falsch!";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles/style.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<header class="topbar">
    <a href="https://www.htlrennweg.at/" class="logo-link">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <div class="brand">Schulmonitor</div>
</header>

<main class="center-wrap">
    <h1 class="page-title">Login Page</h1>

    <form method="post" class="login-form" novalidate>
        <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>

        <label class="field-label">School E-Mail Address:</label>
        <input type="email" name="email" placeholder="school@domain.edu" required>

        <label class="field-label">Password:</label>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" class="btn accent">Login</button>

        <div class="links">
            <a href="forgotpassword.php">Forgot password?</a>
            <a href="register.php">No account? Register now</a>
        </div>
    </form>
</main>

</body>
</html>