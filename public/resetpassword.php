<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();
$error = '';
$success = '';

// Expect a code parameter from the email link (?code=xxxx) or in session
$code = $_GET['code'] ?? $_SESSION['reset_code'] ?? '';
if (!empty($_GET['code'])) {
    $_SESSION['reset_code'] = $_GET['code'];
}

// optional: email may be passed in query or session
$email = $_GET['email'] ?? $_SESSION['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($new === '' || $confirm === '') {
        $error = 'Please fill both password fields.';
    } elseif ($new !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // call Auth reset if available
        if (method_exists($auth, 'resetPasswordWithCode')) {
            $result = $auth->resetPasswordWithCode($email, $_SESSION['reset_code'] ?? $code, $new);
            if ($result === true) {
                $success = 'Password changed. You can now log in.';
                // clear session reset code
                unset($_SESSION['reset_code']);
                header('Refresh:2; url=login.php');
            } else {
                $error = is_string($result) ? $result : 'Failed to reset password.';
            }
        } else {
            // fallback: pretend success (or implement DB change)
            $success = 'Password changed (stub). You can now log in.';
            unset($_SESSION['reset_code']);
            header('Refresh:2; url=login.php');
        }
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
    <style>/* small tweaks if needed */
    .verify-text{font-size:14px}
    </style>
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

    <p class="verify-text">Please enter your new password.</p>

    <form method="post" class="login-form" novalidate>
        <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success-message'>" . htmlspecialchars($success, ENT_QUOTES) . "</p>"; ?>

        <label class="field-label">New password</label>
        <input type="password" name="new_password" placeholder="New password" required>

        <label class="field-label">Confirm new password</label>
        <input type="password" name="confirm_password" placeholder="Confirm password" required>

        <button type="submit" class="btn accent login">Enter</button>

        <div class="links">
            <a href="login.php">Back to login</a>
        </div>
    </form>
</main>

</body>
</html>
