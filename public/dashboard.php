<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
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
    <a href="https://www.htlrennweg.at/">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <h1>Schulmonitor Dashboard</h1>
</header>

<main>
    <h2>Willkommen, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Hier kannst du deine Schulmonitor-Daten einsehen und verwalten.</p>

    <form method="post" action="logout.php">
        <button type="submit" class="secondary">Logout</button>
    </form>
</main>
</body>
</html>
