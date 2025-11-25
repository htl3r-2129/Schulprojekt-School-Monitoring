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
        header('Location: dashboard.php');
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
</head>
<body>
<header>
    <a href="https://www.htlrennweg.at/">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <h1>Schulmonitor Login</h1>
</header>

<form method="post">
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <label>Benutzername:</label>
    <input type="text" name="username" required>

    <label>Passwort:</label>
    <input type="password" name="password" required>

    <button type="submit" class="main">Login</button>
</form>
</body>
</html>
