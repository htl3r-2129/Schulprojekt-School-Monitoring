<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth(); // TODO: have one global Auth()

if (isset($_SESSION['user'])) {
    if (!$auth->isAdmin($_SESSION['user'])) {
        header(header: 'Location: error/401.php');
    }
} else {
    header(header: 'Location: error/401.php');
}

$error = '';
$success = '';

// Handle settings form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $betriebszeit_start = $_POST['betriebszeit_start'] ?? '';
    $betriebszeit_end = $_POST['betriebszeit_end'] ?? '';
    $instant_message = $_POST['instant_message'] ?? '';
    $instant_message_time = $_POST['instant_message_time'] ?? '';
    $bilderzeit = $_POST['bilderzeit'] ?? '';
    $max_storage = $_POST['max_storage'] ?? '';

    if (isset($_POST['reset'])) {
        // Reset to defaults (stub)
        $success = 'Settings reset to defaults.';
    } elseif (isset($_POST['save'])) {
        // Save settings (stub - implement DB save if needed)
        $success = 'Settings saved successfully.';
    } elseif (isset($_POST['send_message'])) {
        // Send instant message
        if (!empty($instant_message)) {
            // enforce length limit server-side
            if (mb_strlen($instant_message) > 255) {
                $error = 'Message must be 255 characters or fewer.';
            } else {
                // TODO: implement sending logic (DB, email, notification service, etc.)
                $success = 'Message sent successfully.';
                $instant_message = ''; // clear after sending
            }
        } else {
            $error = 'Please enter a message to send.';
        }
    }
}

// Get current settings (stub - implement DB fetch if needed)
$betriebszeit_start = '08:00';
$betriebszeit_end = '17:45';
$instant_message = '';
$instant_message_time = '';
$bilderzeit = '10s';
$max_storage = '500GB';

// Calculate available disk space on server
$disk_total = disk_total_space('/');
$disk_free = disk_free_space('/');
$disk_used = $disk_total - $disk_free;
$storage_info = (function_exists('disk_free_space') && $disk_free !== false) 
    ? round($disk_free / (1024**3), 2) . ' GB available' 
    : 'System info unavailable';

$settings = [
    'betriebszeit_start' => $betriebszeit_start,
    'betriebszeit_end' => $betriebszeit_end,
    'instant_message' => $instant_message,
    'instant_message_time' => $instant_message_time,
    'bilderzeit' => $bilderzeit,
    'max_storage' => $max_storage,
    'storage_info' => $storage_info
];

$username = $_SESSION['username'] ?? 'Admin';
$first_name = 'Vorname';  // Would come from DB
$last_name = 'NACHNAME';   // Would come from DB
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Admin</title>
        <link rel="stylesheet" href="styles/style.css">
        <meta name="viewport" content="width=device-width,initial-scale=1">
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
                <span class="user-name"><?= htmlspecialchars($_SESSION['name']); ?></span>
            </div>
            <a href="logout.php" class="btn primary">Log-out</a>
        </div>
    </header>
        <main class="center-wrap">
            <h2 class="admin-greeting">Hello Admin!</h2>

            <div class="admin-box">
                <h3>Allgemeine Einstellungen</h3>

                <form method="post" novalidate>
                    <?php if (!empty($error)) echo "<p class='error-message'>" . htmlspecialchars($error, ENT_QUOTES) . "</p>"; ?>
                    <?php if (!empty($success)) echo "<p class='success-message'>" . htmlspecialchars($success, ENT_QUOTES) . "</p>"; ?>

                    <div class="form-row">
                        <label>Betriebszeit:</label>
                        <input type="time" name="betriebszeit_start" value="<?php echo htmlspecialchars($settings['betriebszeit_start']); ?>">
                        <span class="time-separator">-</span>
                        <input type="time" name="betriebszeit_end" value="<?php echo htmlspecialchars($settings['betriebszeit_end']); ?>">
                    </div>

                    <div class="form-row">
                        <label>Instant-Message:</label>
                    </div>
                    <div class="message-wrapper">
                        <textarea name="instant_message" placeholder="Type your message here..."><?php echo htmlspecialchars($settings['instant_message']); ?></textarea>
                    </div>
                    <div class="message-controls">
                        <input type="number" class="small-input" name="instant_message_time" value="<?php echo htmlspecialchars($settings['instant_message_time']); ?>" placeholder="Zeit in s" min="1">
                        <button type="submit" name="send_message" class="btn primary send">Send</button>
                    </div>

                    <div class="form-row">
                        <label>Bilderzeit:</label>
                        <input type="text" name="bilderzeit" value="<?php echo htmlspecialchars($settings['bilderzeit']); ?>">
                    </div>

                    <div class="form-row">
                        <label>Maximaler Speicherplatz:</label>
                        <input type="text" name="max_storage" value="<?php echo htmlspecialchars($settings['max_storage']); ?>">
                    </div>

                    <div class="form-row">
                        <label>Speicherplatzinfo:</label>
                        <input type="text" name="storage_info" value="<?php echo htmlspecialchars($settings['storage_info']); ?>" disabled>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="reset" class="btn accent reset">reset</button>
                        <button type="submit" name="save" class="btn primary save">Speichern</button>
                    </div>
                </form>
            </div>

            <hr class="section-sep">
            <div class="admin-actions">
                <a href="admin_manageusers.php" class="btn accent manage">Manage Users</a>
                <a href="mod.php" class="btn secondary mod">Mod view</a>
            </div>
        </main>
    </body>
</html>