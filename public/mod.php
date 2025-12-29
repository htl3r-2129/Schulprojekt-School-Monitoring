<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use App\classes\Auth;

$auth = new Auth();

//TODO : Check if user is moderator, else redirect

$username = $_SESSION['username'] ?? 'Moderator';
$first_name = 'Vorname';
$last_name = 'NACHNAME';

// Sample content queue (replace with DB fetch)
$queue_items = array_fill(0, 12, ['id' => '1', 'title' => 'Überschrift 1', 'thumbnail_url' => null]);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Moderator</title>
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
            <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
        </div>
        <a href="logout.php" class="btn logout-btn">Log-out</a>
    </div>
</header>

<main class="center-wrap">
    <h2 class="mod-greeting">Hello Moderator!</h2>
    <p class="mod-link"><a href="admin.php">Return to Admin</a></p>

    <div class="mod-section">
        <h3 class="queue-title">Active Content Queue:</h3>
        
        <div class="content-queue-container">
            <?php foreach($queue_items as $item): ?>
            <div class="queue-card">
                <div class="card-preview">
                    <?php if(!empty($item['thumbnail_url']) && file_exists($item['thumbnail_url'])): ?>
                        <img src="<?php echo htmlspecialchars($item['thumbnail_url']); ?>" alt="Thumbnail" class="preview-img">
                    <?php else: ?>
                        <span class="preview-placeholder">PREVIEW</span>
                    <?php endif; ?>
                </div>
                <div class="card-subtitle"><?php echo htmlspecialchars($item['title']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mod-actions">
        <button class="btn btn-approver">Content Approver</button>
    </div>

</main>

</body>
</html>
