<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['username'])) { 
    // Wenn nicht angemeldet: HTML für die Fehlermeldung ausgeben und Skript beenden
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Zugriff verweigert</title>
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
        <div class="card">
            <h2>Zugriff verweigert</h2>
            <p>Sie haben keinen Zugriff auf diese Seite, da Sie nicht angemeldet sind.</p>
            <a href="login.php" class="button-link main">Zur Login-Seite</a>
        </div>
    </main>

    </body>
    </html>
    <?php
    exit; // Wichtig: Skript beenden
}

// Wenn der Code hier ankommt, ist der Benutzer angemeldet (das eigentliche Dashboard)
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
    <h2>Willkommen, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Dies ist das geschützte Dashboard. Ihr Login ist jetzt doppelt gesichert.</p>
    
    <form method="post" action="logout.php">
        <button type="submit" class="secondary">Logout</button>
    </form>
    
</main>
</body>
</html>