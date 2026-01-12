<?php
session_start();

// Composer Autoload
require_once '../vendor/autoload.php';

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

/* ======================================================
   SETTINGS FILE
====================================================== */

$settingsFile = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';

function loadSettings(string $file): array {
    if (!file_exists($file)) {
        return [];
    }
    return json_decode(file_get_contents($file), true) ?? [];
}

function saveSettings(string $file, array $data): void {
    file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/* ======================================================
   LOAD SETTINGS + DEFAULTS
====================================================== */

$settings = array_merge([
    'betriebszeit_start'   => '08:00',
    'betriebszeit_end'     => '17:45',
    'bilderzeit'           => '10s',
    'max_storage'          => '500GB',
    'instant_message'      => '',
    'instant_message_time' => ''
], loadSettings($settingsFile));

/* ======================================================
   HANDLE POST
====================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['reset'])) {

        $settings = [
            'betriebszeit_start'   => '08:00',
            'betriebszeit_end'     => '17:45',
            'bilderzeit'           => '10s',
            'max_storage'          => '500GB',
            'instant_message'      => '',
            'instant_message_time' => ''
        ];

        saveSettings($settingsFile, $settings);
        header('Location: admin.php?reset=1');
        exit;
    }

    if (isset($_POST['save'])) {

        $settings['betriebszeit_start'] = $_POST['betriebszeit_start'] ?? '';
        $settings['betriebszeit_end']   = $_POST['betriebszeit_end'] ?? '';
        $settings['bilderzeit']         = $_POST['bilderzeit'] ?? '';
        $settings['max_storage']        = $_POST['max_storage'] ?? '';

        saveSettings($settingsFile, $settings);
        header('Location: admin.php?saved=1');
        exit;
    }

    if (isset($_POST['send_message'])) {

        $msg  = trim($_POST['instant_message'] ?? '');
        $time = trim($_POST['instant_message_time'] ?? '');

        if ($msg === '') {
            $error = 'Please enter a message to send.';
        } elseif (mb_strlen($msg) > 255) {
            $error = 'Message must be 255 characters or fewer.';
        } else {
            $settings['instant_message'] = $msg;
            $settings['instant_message_time'] = $time;

            saveSettings($settingsFile, $settings);
            header('Location: admin.php?sent=1');
            exit;
        }
    }
}

/* ======================================================
   FLASH MESSAGES
====================================================== */

if (isset($_GET['saved'])) $success = 'Settings saved successfully.';
if (isset($_GET['reset'])) $success = 'Settings reset to defaults.';
if (isset($_GET['sent']))  $success = 'Message sent successfully.';

/* ======================================================
   DISK INFO
====================================================== */

$disk_free = disk_free_space('/');
$storage_info = $disk_free !== false
    ? round($disk_free / (1024 ** 3), 2) . ' GB available'
    : 'System info unavailable';

/* ======================================================
   JSON EXPORT (UNCHANGED)
====================================================== */

$betriebszeiten =
    str_replace(':', '', $settings['betriebszeit_start']) . '-' .
    str_replace(':', '', $settings['betriebszeit_end']);

$jsonOutput = [
    "Betriebszeiten" => $betriebszeiten,
    "MsgTime"        => preg_replace('/\D/', '', $settings['bilderzeit']),
    "Speicher"       => preg_replace('/\D/', '', $settings['max_storage'])
];

$jsonInstOutput = [
    "Message" => $settings['instant_message'],
    "Time"    => preg_replace('/\D/', '', $settings['instant_message_time']),
    "Active"  => !empty($settings['instant_message'])
];

$wantsJson =
    isset($_SERVER['HTTP_ACCEPT']) &&
    str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

$api = $_GET['api'] ?? null;

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        $api === 'instant-message' ? $jsonInstOutput : $jsonOutput
    );
    exit;
}

/* ======================================================
   USER DATA (STUB)
====================================================== */

$username   = $_SESSION['username'] ?? 'Admin';
$first_name = 'Vorname';
$last_name  = 'NACHNAME';
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
        </div>
    </div>
</header>

<main class="center-wrap">
    <h2 class="admin-greeting">Hello Admin!</h2>

    <div class="admin-box">
        <h3>Allgemeine Einstellungen</h3>

        <form method="post" novalidate>

            <?php if ($error): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="success-message"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <div class="form-row">
                <label>Betriebszeit:</label>
                <input type="time" name="betriebszeit_start" value="<?= htmlspecialchars($settings['betriebszeit_start']) ?>">
                <span class="time-separator">-</span>
                <input type="time" name="betriebszeit_end" value="<?= htmlspecialchars($settings['betriebszeit_end']) ?>">
            </div>

            <div class="form-row">
                <label>Instant-Message:</label>
            </div>
            <div class="message-wrapper">
                <textarea name="instant_message"><?= htmlspecialchars($settings['instant_message']) ?></textarea>
            </div>
            <div class="message-controls">
                <input type="number" class="small-input" name="instant_message_time"
                       value="<?= htmlspecialchars($settings['instant_message_time']) ?>"
                       placeholder="Zeit in s" min="1">
                <button type="submit" name="send_message" class="btn primary send">Send</button>
            </div>

            <div class="form-row">
                <label>Bilderzeit:</label>
                <input type="text" name="bilderzeit" value="<?= htmlspecialchars($settings['bilderzeit']) ?>">
            </div>

            <div class="form-row">
                <label>Maximaler Speicherplatz:</label>
                <input type="text" name="max_storage" value="<?= htmlspecialchars($settings['max_storage']) ?>">
            </div>

            <div class="form-row">
                <label>Speicherplatzinfo:</label>
                <input type="text" value="<?= htmlspecialchars($storage_info) ?>" disabled>
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
