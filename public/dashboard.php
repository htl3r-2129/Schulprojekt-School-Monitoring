<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';
// TODO: wird nicht mehr benötigt. Überprüfen ob gelöscht werden kann.
// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user'])) {
    header(header: 'Location: login.php');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<header>
    <a href="https://www.htlrennweg.at">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <h1>Schulmonitor Dashboard</h1>
</header>

<main>
    <h2>Willkommen, <?= htmlspecialchars($_SESSION['name']); ?>!</h2>

    <a href="login.php">Login</a>
    <br>
    <a href="logout.php">Logout</a>
    <br>
    <a href="register.php">Register</a>
    <br>
    <a href="mod.php">Register</a>
    <br>
    <a href="admin.php">Register</a>
    <form method="post" action="logout.php">
        <button type="submit" class="secondary">Logout</button>
    </form>
    
</main>
</body>
</html>