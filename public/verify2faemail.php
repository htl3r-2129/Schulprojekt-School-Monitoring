<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth();
$error = '';
$success = '';

$sent_email = $_SESSION['verify_email'];
$_SESSION['verify_email'] = '';

// If user submitted the verification code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['code'])){

    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Verify E-Mail</title>
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
    <h1 class="page-title">Verify E-Mail</h1>

    <p class="verify-text">A verification link has been sent to <strong><?php echo htmlspecialchars($sent_email ?: 'xxxx@htl.rennweg.at', ENT_QUOTES); ?></strong>.<br>
        Please check your inbox and open the link to verify your E-Mail.</p>

    <form method="post" class="login-form" novalidate>
        <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success-message'>" . htmlspecialchars($success, ENT_QUOTES) . "</p>"; ?>

        <label class="field-label">2-Factor-Code:</label>
        <input type="number" name="code" placeholder="000000" required>

        <button type="submit" class="btn accent">Verify</button>
    </form>
</main>

</body>
</html>
