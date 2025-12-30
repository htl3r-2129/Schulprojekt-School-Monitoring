<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {
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
        <input type="text" name="username" placeholder="school@domain.edu" required>

        <label class="field-label">Password:</label>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" class="btn login">Login</button>

        <div class="links">
            <a href="forgotpassword.php">Forgot password?</a>
            <a href="register.php">No account? Register now</a>
        </div>
    </form>
</main>

</body>
</html>