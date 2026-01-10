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
    <title>Registrierung</title>
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
    <h1 class="page-title">Sign-Up Page</h1>

    <form method="post" class="login-form" novalidate>
        <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success-message'>" . $success . "</p>"; ?>

        <label class="field-label">School E-Mail Address:</label>
        <input type="email" name="email" placeholder="school@domain.edu" required value="<?= htmlspecialchars($email ?? '') ?>">

        <label class="field-label">Password:</label>
        <input type="password" name="password" id="password" placeholder="Password" required>

        <label class="field-label">Confirm Password:</label>
        <input type="password" name="password_confirm" id="password_confirm" placeholder="Confirm Password" required>

        <button type="submit" class="btn accent login">Sign-Up</button>

        <div class="links">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </form>
</main>

</body>
</html>