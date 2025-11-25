<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();
$error = '';

// --- PRÜFUNG: IST DER BENUTZER BEREITS ANGEMELDET? ---
if (isset($_SESSION['username'])) {
    // Wenn bereits angemeldet, zeige die "Bereits angemeldet" Ansicht
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
        <a href="www.htlrennweg.at">
            <img src="images/logo.png" alt="Logo" class="logo">
        </a>
        <h1>Schulmonitor Login</h1>
    </header>

    <!-- HIER WIRD MAIN HINZUGEFÜGT -->
    <main>
        <div class="card">
            <h2>Willkommen zurück, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Du bist bereits angemeldet.</p>

            <a href="dashboard.php" class="button-link main">Zum Dashboard</a>

            <form method="post" action="logout.php">
                <button type="submit" class="secondary">Logout</button>
            </form>
        </div>
    </main>
    <!-- ENDE ANPASSUNG -->

    </body>
    </html>
    <?php
    exit; // Wichtig: Skript beenden, damit der untere Teil nicht ausgeführt wird
}

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
    <a href="www.htlrennweg.at">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <h1>Schulmonitor Login</h1>
</header>

<!-- Hier beginnt das eigentliche Login-Formular -->
<form method="post">
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <label>Benutzername:</label>
    <input type="text" name="username" required>
    <label>Passwort:</label>
    <input type="password" name="password" id="password" required>

    <div class="password-toggle">
        <input type="checkbox" id="showPassword">
        <label for="showPassword">Passwort anzeigen</label>
    </div>

    <button type="submit" class="main">Login</button>
</form>

<script>
    const passwordInput = document.getElementById('password');
    const toggle = document.getElementById('showPassword');

    toggle.addEventListener('change', function() {
        if (this.checked) {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });
</script>
</body>
</html>
