<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

// Die Auth Klasse wird ohne Argumente instanziiert
$auth = new Auth(); 
$error = '';
$username = ''; // Für die HTML-Ausgabe

// --- PRÜFUNG: IST DER BENUTZER BEREITS ANGEMELDET? ---
if (isset($_SESSION['username'])) { 
    header('Location: dashboard.php');
    exit;
}

// -----------------------------------------------------
// --- POST-VERARBEITUNG ---
// -----------------------------------------------------

// Prüfen, ob der Benutzer im 2FA-Schritt ist
$is_2fa_step = isset($_SESSION['2fa_pending_email']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nimmt entweder Username/Passwort oder den 2FA-Code entgegen
    $input_username = $_POST['username'] ?? ''; 
    $password = $_POST['password'] ?? '';
    $code = $_POST['2fa_code'] ?? '';
    
    // Fall 1: Benutzer ist im 2FA-Schritt
    if ($is_2fa_step) {
        $username = $_SESSION['2fa_pending_username']; // Username aus der Session holen

        if (empty($code)) {
             $error = "Bitte geben Sie den 6-stelligen Code ein.";
        } elseif ($auth->verify2FA($code)) {
            // Verifizierung erfolgreich, Login abgeschlossen
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Falscher oder abgelaufener Code. Bitte versuchen Sie es erneut.";
        }
        
    } 
    // Fall 2: Normaler Login-Versuch (Passwort-Prüfung)
    elseif (!empty($input_username) && !empty($password)) {
        $username = $input_username; // Username für die Anzeige setzen

        if ($auth->login($username, $password)) {
            // Wenn $auth->login() true zurückgibt, wurde das Passwort akzeptiert
            // und der 2FA-Code wurde in der Session gespeichert und versendet.
            if (isset($_SESSION['2fa_pending_email'])) {
                $is_2fa_step = true; // Wechsle zum 2FA-Formular
                
                $email = $_SESSION['2fa_pending_email'];
                $error = "Passwort korrekt. Ein 6-stelliger Code wurde an **" . htmlspecialchars($email) . "** gesendet. Bitte geben Sie ihn ein. (Checken Sie ggf. den Spam-Ordner)";
                
            } else {
                 header('Location: dashboard.php');
                 exit;
            }
        } else {
            $error = "Benutzername oder Passwort ist falsch!"; 
        }
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
    <a href="https://www.htlrennweg.at">
        <img src="images/logo.png" alt="Logo" class="logo">
    </a>
    <h1>Schulmonitor Login</h1>
</header>

<form method="post">
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    
    <?php if ($is_2fa_step): ?>
        <h2>Zwei-Faktor-Authentifizierung</h2>
        <p>Geben Sie den 6-stelligen Code ein, den Sie per E-Mail erhalten haben.</p>
        
        <label>Benutzername:</label> 
        <input type="text" name="username" required value="<?= htmlspecialchars($username) ?>" readonly> 
        
        <label>Code (6-stellig):</label>
        <input type="text" name="2fa_code" inputmode="numeric" pattern="[0-9]{6}" required autofocus>

    <?php else: ?>
        <label>Benutzername (E-Mail):</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($username) ?>">
        
        <label>Passwort:</label>
        <input type="password" name="password" id="password" required>

        <div class="password-toggle">
            <input type="checkbox" id="showPassword">
            <label for="showPassword">Passwort anzeigen</label>
        </div>
    <?php endif; ?>

    <button type="submit" class="main">
        <?= $is_2fa_step ? 'Code prüfen & Anmelden' : 'Login' ?>
    </button>
</form>

<a href="register.php" class="button-link secondary" style="
    max-width: 400px; 
    margin: 10px auto 50px auto; 
    display: block; 
">Registrieren</a>


<script>
    // Holt das Passwortfeld und die Checkbox
    const passwordInput = document.getElementById('password');
    const toggle = document.getElementById('showPassword');

    // Stellt sicher, dass das passwordInput-Feld existiert, bevor der Listener hinzugefügt wird
    if (passwordInput && toggle) {
        toggle.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    }
</script>
</body>
</html>