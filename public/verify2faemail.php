<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();
$error = '';
$success = '';

// determine which email to show: POST (from previous form), GET (query), or session
$sent_email = '';
if (!empty($_POST['email'])) {
    $sent_email = $_POST['email'];
} elseif (!empty($_GET['email'])) {
    $sent_email = $_GET['email'];
} elseif (!empty($_SESSION['email'])) {
    $sent_email = $_SESSION['email'];
}

// If user submitted the verification code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    if ($code === '') {
        $error = 'Please enter the verification code.';
    } else {
        // Placeholder: in a real app verify the code with $auth or DB
        if ($code === '123456') {
            $success = 'E-Mail verified successfully.';
        } else {
            $error = 'Invalid verification code.';
        }
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

    <p class="verify-text">A verification code has been sent to <strong><?php echo htmlspecialchars($sent_email ?: 'xxxx@htl.rennweg.at', ENT_QUOTES); ?></strong>.<br>
        Please enter the code to verify your E-Mail.</p>

    <form method="post" class="login-form" novalidate>
        <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success-message'>" . htmlspecialchars($success, ENT_QUOTES) . "</p>"; ?>

        <label class="field-label">Code:</label>
        <input class="code-input" type="text" name="code" placeholder="" autocomplete="one-time-code">

        <button type="submit" class="btn login">Enter</button>
    </form>
</main>

</body>
</html>
