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
        <a href="logout.php" class="btn accent logout">Log-out</a>
    </div>
</header>

<main class="center-wrap">
    <h2 class="mod-greeting">Content Approver</h2>
    <p class="mod-link"><a href="mod.php">Return to Moderator</a></p>

    <div class="mod-section">
        <div class="content-grid-container">
            <?php foreach($queue_items as $index => $item): ?>
            <div class="queue-card" data-content-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-thumbnail="<?php echo htmlspecialchars($item['thumbnail_url'] ?? ''); ?>" onclick="openContentModal(this)">
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

</main>

<!-- Content Preview Modal -->
<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class=" primary modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title">Von [Username]</div>
        <div class="modal-preview" id="modalPreviewArea">
            <span class="preview-placeholder">PREVIEW</span>
        </div>
        <div class="modal-footer">
            <button class="btn primary approve" onclick="approveContent()">Approve</button>
            <button class="btn secondary delete" onclick="deleteContent()">Delete</button>
            <button class="btn accent block" onclick="blockUser()">Block User</button>
        </div>
    </div>
</div>

<script>
let currentContentId = null;

function openContentModal(cardElement) {
    const contentId = cardElement.dataset.contentId;
    const title = cardElement.dataset.title;
    const thumbnail = cardElement.dataset.thumbnail;
    
    currentContentId = contentId;
    
    // Update modal title
    document.querySelector('.modal-title').textContent = 'Von [Username]';
    
    // Update preview area
    const previewArea = document.getElementById('modalPreviewArea');
    if (thumbnail && thumbnail.trim() !== '') {
        previewArea.innerHTML = '<img src="' + thumbnail + '" alt="Content Preview" class="modal-preview-img">';
    } else {
        previewArea.innerHTML = '<span class="preview-placeholder">PREVIEW</span>';
    }
    
    // Show modal
    document.getElementById('contentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeContentModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    document.getElementById('contentModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentContentId = null;
}

function approveContent() {
    if (!currentContentId) return;
    console.log('Approving content with ID:', currentContentId);
    closeContentModal();
    alert('Approve functionality to be implemented');
}

function deleteContent() {
    if (!currentContentId) return;
    console.log('Deleting content with ID:', currentContentId);
    closeContentModal();
    alert('Delete functionality to be implemented');
}

function blockUser() {
    if (!currentContentId) return;
    console.log('Blocking user for content with ID:', currentContentId);
    closeContentModal();
    alert('Block user functionality to be implemented');
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeContentModal();
    }
});
</script>

</body>
</html>
