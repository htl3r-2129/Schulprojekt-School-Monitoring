<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth(); // TODO: have one global Auth()

if (isset($_SESSION['user'])) {
    if (!$auth->isAdmin($_COOKIE['user'])) {
        header(header: 'Location: error/401.php');
    }
} else {
    header(header: 'Location: error/401.php');
}

$moderators = $auth->getAllMods();
$users = $auth->getAllUsers();
$blocked = $auth->getAllLocked();
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Manage Users</title>
        <link rel="stylesheet" href="styles/style.css">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <style>
            /* small page-specific tweaks to keep everything self-contained */
            .page-title { text-align:center; color:#e23c21; font-family:Helvetica, Arial, sans-serif; font-size:40px; margin:40px 0; }
        </style>
    </head>
    <body>
    <header class="topbar">
        <a href="https://www.htlrennweg.at/" class="logo-link">
            <img src="images/logo.png" alt="Logo" class="logo">
        </a>
        <div class="brand">Schulmonitor</div>
        <div class="user-profile">
            <div class="user-info">
                <div class="user-role">Administrator</div>
                <span class="user-name"><<?= htmlspecialchars($_SESSION['name']); ?></span>
            </div>
            <a href="logout.php" class="btn primary">Log-out</a>
        </div>
    </header>
        <main class="center-wrap">
            <h1 class="page-title">Manage Users</h1>

            <div class="manage-container">
                <div class="user-column">
                    <h3>Moderators</h3>
                    <div class="user-list">
                        <?php foreach($moderators as $m): ?>
                            <div class="user-item">
                                <div class="user-label"><?php echo htmlspecialchars($m['username']); ?></div>
                                <div><?php echo htmlspecialchars($m['email']); ?></div>
                                <div><?php echo htmlspecialchars($m['PK_User_ID']); ?></div>
                                <div class="actions">
                                    <button class="btn small accent">remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="user-column">
                    <h3>Users</h3>
                    <div class="user-list">
                        <?php foreach($users as $u): ?>
                            <div class="user-item">
                                <div class="user-label"><?php echo htmlspecialchars($u['username']); ?></div>
                                <div><?php echo htmlspecialchars($u['email']); ?></div>
                                <div><?php echo htmlspecialchars($u['PK_User_ID']); ?></div>
                                <div class="actions">
                                    <button class="btn small accent">m</button>
                                    <button class="btn small primary">block</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="user-column">
                    <h3>Blocked</h3>
                    <div class="user-list">
                        <?php foreach($blocked as $b): ?>
                            <div class="user-item">
                                <div class="user-label"><?php echo htmlspecialchars($b['username']); ?></div>
                                <div><?php echo htmlspecialchars($b['email']); ?></div>
                                <div><?php echo htmlspecialchars($b['PK_User_ID']); ?></div>
                                <div class="actions">
                                    <button class="btn small accent">unblock</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <hr class="section-sep">
            <div class="manage-actions">
                <a href="admin.php" class="btn primary large">Admin Main</a>
                <a href="mod.php" class="btn secondary large">Mod view</a>
            </div>
        </main>
    </body>
</html>