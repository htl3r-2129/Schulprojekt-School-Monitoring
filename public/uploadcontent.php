<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth();

//TODO : Check if user is moderator, else redirect

$username = $_SESSION['username'] ?? 'Moderator';
$first_name = 'Vorname';
$last_name = 'NACHNAME';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Upload Content</title>
    <link rel="stylesheet" href="styles/style.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="./styles/style_upload.css" />
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
            <div class="user-name-row">
                <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                <a href="logout.php" class="btn accent logout">Log-out</a>
            </div>
        </div>
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
                        <label for="mediaFile" class="btn file-selector">Upload</label>
                        <input id="mediaFile" type="file" accept="image/*,video/*" class="sr-only">
                    </div>
                </div>
                <div class="form-row">
                    <label for="mediaExtra">Zusätzlicher Text:</label>
                    <textarea id="mediaExtra" rows="4" placeholder="Text"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn accent send" id="sendMedia">Send Content</button>
                    <button type="button" class="btn primary clear" id="clearMedia">Clear</button>
                </div>
            </div>
            <div class="preview-container">
                <div class="media-preview black-preview">
                    <div class="preview-text">
                        <h3 id="previewTitle" class="preview-title"></h3>
                        <hr id="previewSeparator" class="preview-separator" hidden>
                        <p id="previewExtra" class="preview-extra"></p>
                    </div>
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
                    <button type="button" class="btn accent send" id="sendMedia">Send Content</button>
                    <button type="button" class="btn primary clear" id="clearMedia">Clear</button>
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

</main>

<script>
const creationSelect = document.getElementById('creationSelect');
const mediaSection = document.getElementById('mediaSection');
const mediaFileInput = document.getElementById('mediaFile');
const mediaUrlInput = document.getElementById('mediaUrl');
const mediaTitleInput = document.getElementById('mediaTitle');
const mediaExtraInput = document.getElementById('mediaExtra');
const clearMediaBtn = document.getElementById('clearMedia');

const textTitleInput = document.getElementById('textTitle');
const textExtraInput = document.getElementById('textExtra');

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

// Media preview logic
function renderInto(target, src, isVideo) {
    target.innerHTML = '';
    if (isVideo) {
        const video = document.createElement('video');
        video.src = src;
        video.muted = false;
        video.controls = true;
        video.playsInline = true;
        video.style.maxWidth = '100%';
        video.style.maxHeight = '100%';
        video.style.objectFit = 'contain';
        target.appendChild(video);
        return;
    }
    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Preview';
    img.style.maxWidth = '100%';
    img.style.maxHeight = '100%';
    img.style.objectFit = 'contain';
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
    mediaUrlInput.readOnly = false;
    mediaFileInput.value = '';
    const isVideo = /\.(mp4|webm|ogg|mov|m4v)$/i.test(url);
    renderInto(previewMediaArea, url, isVideo);
}

if (mediaFileInput) {
    mediaFileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            mediaUrlInput.value = this.files[0].name;
            mediaUrlInput.readOnly = true;
            const url = URL.createObjectURL(this.files[0]);
            const isVideo = this.files[0].type.startsWith('video');
            renderInto(document.getElementById('previewMedia'), url, isVideo);
        }
    });
}

if (mediaUrlInput) mediaUrlInput.addEventListener('change', handleUrlPreview);

// Update previews for media
if (mediaTitleInput) mediaTitleInput.addEventListener('input', () => {
    document.getElementById('previewTitle').textContent = mediaTitleInput.value;
});
if (mediaExtraInput) mediaExtraInput.addEventListener('input', () => {
    document.getElementById('previewExtra').textContent = mediaExtraInput.value;
});
if (clearMediaBtn) clearMediaBtn.addEventListener('click', () => {
    mediaTitleInput.value = '';
    mediaUrlInput.value = '';
    mediaUrlInput.readOnly = false;
    mediaExtraInput.value = '';
    mediaFileInput.value = '';
    document.getElementById('previewMedia').innerHTML = '<span class="preview-placeholder">PREVIEW</span>';
    document.getElementById('previewTitle').textContent = '';
    document.getElementById('previewExtra').textContent = '';
});

// Text section preview
if (textTitleInput) textTitleInput.addEventListener('input', () => {
    document.getElementById('textPreviewTitle').textContent = textTitleInput.value;
});
if (textExtraInput) textExtraInput.addEventListener('input', () => {
    document.getElementById('textPreviewExtra').textContent = textExtraInput.value;
});
</script>

</body>
</html>
