<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;


//TODO : Check if user is admin, else redirect

# $username = $_SESSION['username'] ?? 'Admin';
# $first_name = 'Vorname';
# $last_name = 'NACHNAME';

// Sample lists (replace with DB fetch)
$moderators = array_fill(0,9, 'Vorname Nachname (0000)');
$users = array_fill(0,9, 'Vorname Nachname (0000)');
$blocked = array_fill(0,9, 'Vorname Nachname (0000)');
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
                <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
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
                                <div class="user-label"><?php echo htmlspecialchars($m); ?></div>
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
                                <div class="user-label"><?php echo htmlspecialchars($u); ?></div>
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
                                <div class="user-label"><?php echo htmlspecialchars($b); ?></div>
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