<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = 'Please enter your E-Mail address.';
    } else {
        // store email in session for the verify page
        $_SESSION['email'] = $email;

        // attempt to send reset code (if Auth provides a method)
        if (method_exists($auth, 'sendResetCode')) {
            $auth->sendResetCode($email);
        } elseif (method_exists($auth, 'sendVerificationCode')) {
            $auth->sendVerificationCode($email);
        }

        // redirect user to the verify page to enter the code
        header('Location: forgotpassword.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Password reset</title>
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
    <h1 class="page-title">Password reset</h1>

    <p class="verify-text">Please enter your E-Mail.</p>

    <form method="post" class="login-form" novalidate>
        <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success-message'>" . htmlspecialchars($success, ENT_QUOTES) . "</p>"; ?>

        <label class="field-label">School e-mail address:</label>
        <input type="email" name="email" placeholder="school@domain.edu" required>

        <button type="submit" class="btn login">Enter</button>

        <div class="links">
            <a href="login.php">Back to login</a>
        </div>
    </form>
</main>

</body>
</html>
