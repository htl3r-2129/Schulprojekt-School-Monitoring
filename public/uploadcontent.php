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
$queue_items = array_fill(0, 12, ['id' => '1', 'title' => 'Überschrift 1', 'thumbnail_url' => null, 'extra_text' => 'Dies ist ein Beispieltext für zusätzliche Informationen.']);
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
            <div class="user-role">User</div>
            <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
        </div>
        <a href="logout.php" class="btn logout-btn">Log-out</a>
    </div>
</header>

<main class="center-wrap">
    <h2 class="section-title">Content Creation</h2>

    <div class="creation-control">
        <label class="sr-only" for="creationSelect">Select content type</label>
        <select id="creationSelect" class="select-control">
            <option value="" selected>Select an option</option>
            <option value="media">Media</option>
            <option value="text">Text</option>
        </select>
    </div>

    <div id="mediaSection" class="media-section" hidden>
        <div class="media-layout">
            <div class="media-form">
                <div class="form-row">
                    <label for="mediaTitle">Überschrift:</label>
                    <input id="mediaTitle" type="text" placeholder="Text" maxlength="80" required>
                </div>
                <div class="form-row">
                    <label for="mediaUrl">Bild/Video einfügen:</label>
                    <div class="input-with-button">
                        <input id="mediaUrl" type="text" placeholder="Bild-URL oder Dateiname" autocomplete="off" required>
                        <label for="mediaFile" class="btn file-button">Upload</label>
                        <input id="mediaFile" type="file" accept="image/*,video/*" class="sr-only">
                    </div>
                </div>
                <div class="form-row">
                    <label for="mediaExtra">Zusätzlicher Text:</label>
                    <textarea id="mediaExtra" rows="4" placeholder="Text"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-send" id="sendMedia">Send Content</button>
                    <button type="button" class="btn btn-clear" id="clearMedia">Clear</button>
                </div>
            </div>
            <div class="preview-container">
                <h3 id="previewTitle" class="preview-title"></h3>
                <hr id="previewSeparator" class="preview-separator" hidden>
                <p id="previewExtra" class="preview-extra"></p>
                <div class="media-preview">
                    <div id="previewMedia" class="preview-media-area">
                        <span class="preview-placeholder">PREVIEW</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="textSection" class="media-section" hidden>
        <div class="media-layout">
            <div class="media-form">
                <div class="form-row">
                    <label for="textTitle">Überschrift:</label>
                    <input id="textTitle" type="text" placeholder="Text" maxlength="80" required>
                </div>
                <div class="form-row">
                    <label for="textExtra">Zusätzlicher Text:</label>
                    <textarea id="textExtra" rows="4" placeholder="Text" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-send" id="sendText">Send Content</button>
                    <button type="button" class="btn btn-clear" id="clearText">Clear</button>
                </div>
            </div>
            <div class="preview-container">
                <div class="text-preview">
                    <h3 id="textPreviewTitle" class="text-preview-title"></h3>
                    <hr id="textPreviewSeparator" class="preview-separator" hidden>
                    <p id="textPreviewExtra" class="text-preview-extra"></p>
                </div>
            </div>
        </div>
    </div>

    <hr class="section-divider">

    <h3 class="section-subtitle">Your Content History</h3>

    <div class="content-history-grid">
        <?php foreach($queue_items as $index => $item): ?>
        <div class="queue-card" data-content-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>" data-thumbnail="<?php echo htmlspecialchars($item['thumbnail_url'] ?? ''); ?>" data-extra="<?php echo htmlspecialchars($item['extra_text'] ?? ''); ?>" onclick="openContentModal(this)">
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

</main>

<!-- Content Preview Modal -->
<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title">Von [Username]</div>
        <div class="modal-preview" id="modalPreviewArea">
            <span class="preview-placeholder">PREVIEW</span>
        </div>
        <div class="modal-text-content">
            <h3 id="modalContentTitle" class="modal-content-title"></h3>
            <p id="modalContentExtra" class="modal-content-extra"></p>
        </div>
    </div>
</div>

<script>
let currentContentId = null;

function openContentModal(cardElement) {
    const contentId = cardElement.dataset.contentId;
    const title = cardElement.dataset.title;
    const thumbnail = cardElement.dataset.thumbnail;
    const extraText = cardElement.dataset.extra;
    
    currentContentId = contentId;
    
    // Update modal title
    document.querySelector('.modal-title').textContent = 'Von [Username]';
    
    // Update preview area with image/video support
    const previewArea = document.getElementById('modalPreviewArea');
    if (thumbnail && thumbnail.trim() !== '') {
        const videoExt = /(\.mp4|\.webm|\.ogg|\.mov|\.m4v)$/i;
        const isVideo = videoExt.test(thumbnail);
        renderInto(previewArea, thumbnail, isVideo);
    } else {
        previewArea.innerHTML = '<span class="preview-placeholder">PREVIEW</span>';
    }
    
    // Update content title and extra text
    document.getElementById('modalContentTitle').textContent = title || '';
    document.getElementById('modalContentExtra').textContent = extraText || '';
    
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

// Toggle media section based on selector
const creationSelect = document.getElementById('creationSelect');
const mediaSection = document.getElementById('mediaSection');
const mediaFileInput = document.getElementById('mediaFile');
const mediaUrlInput = document.getElementById('mediaUrl');
const mediaPreview = document.querySelector('.media-preview');
const mediaTitleInput = document.getElementById('mediaTitle');
const mediaExtraInput = document.getElementById('mediaExtra');
const clearMediaBtn = document.getElementById('clearMedia');

function renderInto(target, src, isVideo) {
    const makeVideo = () => {
        const video = document.createElement('video');
        video.controls = true;
        video.src = src;
        target.innerHTML = '';
        target.appendChild(video);
    };

    if (isVideo) {
        makeVideo();
        return;
    }

    const img = document.createElement('img');
    img.alt = 'Preview';
    img.src = src;
    img.onerror = makeVideo; // fallback to video if image fails (e.g., CDN video)
    target.innerHTML = '';
    target.appendChild(img);
}

function handleUrlPreview() {
    const url = mediaUrlInput.value.trim();
    const previewMediaArea = document.getElementById('previewMedia');
    if (!url) {
        previewMediaArea.innerHTML = '<span class="preview-placeholder">PREVIEW</span>';
        mediaFileInput.value = '';
        return;
    }
    mediaUrlInput.readOnly = false; // allow manual edits
    mediaFileInput.value = '';
    const videoExt = /(\.mp4|\.webm|\.ogg|\.mov|\.m4v)$/i;
    const isVideo = videoExt.test(url);
    renderInto(previewMediaArea, url, isVideo);
}

creationSelect.addEventListener('change', function() {
    const textSection = document.getElementById('textSection');
    if (this.value === 'media') {
        mediaSection.hidden = false;
        textSection.hidden = true;
    } else if (this.value === 'text') {
        mediaSection.hidden = true;
        textSection.hidden = false;
    } else {
        mediaSection.hidden = true;
        textSection.hidden = true;
    }
});

// Show file name in URL box when uploading a file
if (mediaFileInput) {
    mediaFileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            mediaUrlInput.value = this.files[0].name;
            mediaUrlInput.readOnly = true;
            const file = this.files[0];
            const url = URL.createObjectURL(file);
            const isVideo = file.type.startsWith('video');
            const previewMediaArea = document.getElementById('previewMedia');
            renderInto(previewMediaArea, url, isVideo);
        }
    });
}

if (mediaUrlInput) {
    mediaUrlInput.addEventListener('change', handleUrlPreview);
}

// Update preview title and extra text in real-time
if (mediaTitleInput) {
    mediaTitleInput.addEventListener('input', function() {
        document.getElementById('previewTitle').textContent = this.value;
        const separator = document.getElementById('previewSeparator');
        const hasContent = this.value.trim() || mediaExtraInput.value.trim();
        separator.hidden = !hasContent;
    });
}

if (mediaExtraInput) {
    mediaExtraInput.addEventListener('input', function() {
        document.getElementById('previewExtra').textContent = this.value;
        const separator = document.getElementById('previewSeparator');
        const hasContent = this.value.trim() || mediaTitleInput.value.trim();
        separator.hidden = !hasContent;
    });
}

if (clearMediaBtn) {
    clearMediaBtn.addEventListener('click', function() {
        mediaTitleInput.value = '';
        mediaUrlInput.value = '';
        mediaUrlInput.readOnly = false;
        mediaExtraInput.value = '';
        mediaFileInput.value = '';
        document.getElementById('previewMedia').innerHTML = '<span class="preview-placeholder">PREVIEW</span>';
        document.getElementById('previewTitle').textContent = '';
        document.getElementById('previewExtra').textContent = '';
        document.getElementById('previewSeparator').hidden = true;
    });
}

const sendMediaBtn = document.getElementById('sendMedia');
if (sendMediaBtn) {
    sendMediaBtn.addEventListener('click', function() {
        if (!mediaTitleInput.value.trim()) {
            alert('Bitte Überschrift eingeben');
            return;
        }
        if (!mediaUrlInput.value.trim()) {
            alert('Bitte Bild/Video einfügen');
            return;
        }
        if (!mediaExtraInput.value.trim()) {
            alert('Bitte zusätzlichen Text eingeben');
            return;
        }
        // TODO: Implement actual submission logic
        alert('Send content functionality to be implemented');
    });
}

// Text section handlers
const textTitleInput = document.getElementById('textTitle');
const textExtraInput = document.getElementById('textExtra');
const clearTextBtn = document.getElementById('clearText');
const sendTextBtn = document.getElementById('sendText');

if (textTitleInput) {
    textTitleInput.addEventListener('input', function() {
        document.getElementById('textPreviewTitle').textContent = this.value;
        const separator = document.getElementById('textPreviewSeparator');
        const hasContent = this.value.trim() || textExtraInput.value.trim();
        separator.hidden = !hasContent;
    });
}

if (textExtraInput) {
    textExtraInput.addEventListener('input', function() {
        document.getElementById('textPreviewExtra').textContent = this.value;
        const separator = document.getElementById('textPreviewSeparator');
        const hasContent = this.value.trim() || textTitleInput.value.trim();
        separator.hidden = !hasContent;
    });
}

if (clearTextBtn) {
    clearTextBtn.addEventListener('click', function() {
        textTitleInput.value = '';
        textExtraInput.value = '';
        document.getElementById('textPreviewTitle').textContent = '';
        document.getElementById('textPreviewExtra').textContent = '';
        document.getElementById('textPreviewSeparator').hidden = true;
    });
}

if (sendTextBtn) {
    sendTextBtn.addEventListener('click', function() {
        if (!textTitleInput.value.trim()) {
            alert('Bitte Überschrift eingeben');
            return;
        }
        if (!textExtraInput.value.trim()) {
            alert('Bitte zusätzlichen Text eingeben');
            return;
        }
        // TODO: Implement actual submission logic
        alert('Send content functionality to be implemented');
    });
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
