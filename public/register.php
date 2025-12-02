<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

// DEFINITION GANZ OBEN für sauberen Code
$required_domain = '@htl.rennweg.at'; 

// Instanziierung ohne Argument, da die DB-Verbindung jetzt intern in Auth.php liegt
$auth = new Auth(); 
$error = '';
$success = '';

// Wenn der Benutzer bereits eingeloggt ist, umleiten
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

// Variablen für die Formularwerte (werden auch für die Anzeige nach einem Fehler verwendet)
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Eingabeprüfung ---
    if (empty($email) || empty($password) || empty($password_confirm)) {
        $error = "Bitte füllen Sie alle Felder aus.";
    } elseif (!str_ends_with($email, $required_domain)) {
        $error = "Registrierung nur mit einer E-Mail-Adresse von " . htmlspecialchars($required_domain) . " möglich.";
    } elseif ($password !== $password_confirm) {
        $error = "Passwörter stimmen nicht überein.";
    } else {
        // --- 2. Registrierungsversuch ---
        // Übergabe der E-Mail als Platzhalter für $username und $email (Datenbank erwartet beides)
        $registration_result = $auth->register($email, $email, $password); 
        
        if ($registration_result === true) {
            // Erfolg
            $success = "Registrierung erfolgreich! Sie können sich jetzt <a href='login.php'>anmelden</a>.";
            // Felder leeren
            $email = '';
        } else {
            // Fehler (wird als String von Auth::register zurückgegeben, z.B. "DB-Verbindung fehlgeschlagen")
            $error = $registration_result; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Registrieren</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<header>
    <a href="https://www.htlrennweg.at">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <h1>Schulmonitor Registrierung</h1>
</header>

<form method="post">
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

    <label>E-Mail (nur <?= htmlspecialchars($required_domain) ?>):</label>
    <input type="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">

    <label>Passwort:</label>
    <input type="password" name="password" id="password" required>
    
    <label>Passwort bestätigen:</label>
    <input type="password" name="password_confirm" id="password_confirm" required>

    <div class="password-toggle">
        <input type="checkbox" id="showPassword">
        <label for="showPassword">Passwort anzeigen</label>
    </div>
    
    <button type="submit" class="main">Registrieren</button>
    <a href="login.php" class="button-link secondary">Zurück zum Login</a>
</form>

<script>
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const toggle = document.getElementById('showPassword');

    if (passwordInput && passwordConfirmInput && toggle) {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.type = 'text';
                passwordConfirmInput.type = 'text';
            } else {
                passwordInput.type = 'password';
                passwordConfirmInput.type = 'password';
            }
        });
    }
</script>
</body>
</html>