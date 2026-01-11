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
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Moderator</title>
    <link rel="stylesheet" href="styles/style.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        /* =========================================================
   PREVIEW – Schwarzer Rahmen + Text + Media (FINAL)
   ========================================================= */

        /* Schwarzer Rahmen */
        .black-preview {
            border: 3px solid #000;
            padding: 25px 20px;
            background: #fff;

            display: flex;
            flex-direction: column;
        }

        /* ---------- TEXTBLOCK (OBEN) ---------- */
        .preview-text {
            display: flex;
            flex-direction: column;
            align-items: center;   /* 🔥 jede Zeile einzeln zentriert */
            gap: 10px;

            margin-bottom: 20px;
        }

        /* Überschrift */
        .preview-title {
            color: #e53935;
            font-size: 30px;
            font-weight: 700;

            margin: 0;
            padding: 0;

            text-align: center;
            max-width: 100%;
            word-wrap: break-word;
        }

        /* Trennlinie */
        .preview-separator {
            width: 70%;
            border: 1px solid #bbb;
            margin: 0;
        }

        .preview-separator[hidden] {
            display: none !important;
        }

        /* Zusatztext */
        .preview-extra {
            font-size: 16px;
            color: #444;

            margin: 0;
            padding: 0;

            text-align: center;
            max-width: 100%;
            word-wrap: break-word;
        }

        /* ---------- MEDIA (UNTEN) ---------- */
        .preview-media-area {
            display: flex;
            justify-content: center;
            align-items: center;

            width: 100%;
            min-height: 200px;
        }

        /* Bild & Video */
        .preview-media-area img,
        .preview-media-area video {
            width: 100%;
            max-height: 450px;

            object-fit: contain;
            display: block;
        }

        /* Platzhalter */
        .preview-placeholder {
            color: #999;
            font-size: 18px;
            letter-spacing: 2px;
        }

        /* =========================================================
   TEXT-ONLY PREVIEW – ZENTRIERT
   ========================================================= */

        .text-preview {
            display: flex;
            flex-direction: column;
            align-items: center;   /* 🔥 jede Zeile einzeln zentriert */
            gap: 10px;

            padding: 25px 20px;
        }

        /* Überschrift */
        .text-preview-title {
            font-size: 30px;
            font-weight: 700;
            color: #e53935;

            margin: 0;
            text-align: center;
            max-width: 100%;
            word-wrap: break-word;
        }

        /* Trennlinie */
        .text-preview .preview-separator {
            width: 70%;
            border: 1px solid #bbb;
            margin: 0;
        }

        /* Zusatztext */
        .text-preview-extra {
            font-size: 16px;
            color: #444;

            margin: 0;
            text-align: center;
            max-width: 100%;
            word-wrap: break-word;
        }

        /* ---------- MEDIA IM PREVIEW ---------- */
        .preview-media-area img,
        .preview-media-area video {
            width: 75%;          /* 🔽 noch kleiner */
            max-height: 280px;   /* 🔽 deutlich niedriger */
            object-fit: contain;

            display: block;
            margin: 0 auto;

            border: none;        /* ❌ kein schwarzer Rahmen */
            outline: none;
            box-shadow: none;
        }

        .preview-media-area * {
            border: none !important;
        }


        /* Gilt für Überschrift UND Text */
        .preview-title,
        .preview-extra,
        .text-preview-title,
        .text-preview-extra {
            max-width: 100%;
            text-align: center;

            /* 🔥 Zeilenumbruch erzwingen */
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }


        /* VORGEFERTIGTES PREVIEW-KÄSTCHEN */
        .preview-media-area {
            width: 100%;
            height: 180px;              /* feste Höhe */
            overflow: hidden;           /* 🔥 nichts darf raus */

            display: flex;
            justify-content: center;
            align-items: center;

            background: #f5f7f9;
            border-radius: 10px;
        }

        .preview-media-area img,
        .preview-media-area video {
            max-width: 100%;
            max-height: 100%;

            object-fit: cover;          /* 🔥 füllt die Box sauber */
            display: block;

            border: none;
            box-shadow: none;
        }

        .card-preview {
            width: 100%;
            height: 160px;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 0;
            border: none;
        }

        .card-preview img,
        .card-preview video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }



        /* ==============================
   CARD PREVIEW – FINAL
   ============================== */

        .card-preview {
            width: 100%;
            height: 160px;

            display: flex;
            justify-content: center;
            align-items: center;

            background: #f3f6f8;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-preview img,
        .card-preview video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }



        .preview-media-area {
            width: 100%;
            height: 320px;              /* 🔥 feste Höhe für große Preview */

            display: flex;
            justify-content: center;
            align-items: center;

            background: transparent;
            padding: 0;
        }

        .preview-media-area img,
        .preview-media-area video {
            max-width: 100%;
            max-height: 100%;

            width: auto;
            height: auto;

            object-fit: contain;
            display: block;
        }

        .card-subtitle {
            color: #ffffff;      /* White text for card subtitles */
            font-weight: 600;
            text-align: center;
        }

/* Content Preview Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.75);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(3px);
}

.modal-content {
    background: linear-gradient(135deg, #ffffff 0%, #f5f7fa 100%);
    border-radius: 0;
    width: 92%;
    max-width: 900px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    position: relative;
    padding: 40px;
    align-items: center;
    justify-content: flex-start;
}

.modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 42px;
    height: 42px;
    background: #e23c21;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 28px;
    font-weight: 700;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    z-index: 1;
}

.modal-close:hover {
    background: #c13616;
}

.modal-title {
    font-family: Helvetica, Arial, sans-serif;
    font-size: clamp(20px, 3.5vw, 40px);
    font-weight: 700;
    color: #e23c21;
    margin: 0;
    text-align: center;
    align-self: flex-end;
    padding-top: 0;
    width: 100%;
}

.modal-title-separator {
    border: none;
    border-top: 2px solid #668099;
    padding: 0;
    margin: 12px auto;
    align-self: center;
    width: auto;
}

.modal-preview {
    width: 100%;
    max-width: 100%;
    aspect-ratio: 16 / 9;
    background: transparent;
    border: 0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    font-weight: 700;
    letter-spacing: 4px;
    color: #303b46;
    overflow: hidden;
}

.modal-preview-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    border-radius: 10px;
}

.modal-extra-text {
    font-size: clamp(16px, 2.5vw, 24px);
    color: #303b46;
    text-align: center;
    padding: 16px 0;
    line-height: 1.6;
    max-width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}


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
            <div class="user-role">User</div>
            <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
        </div>
        <a href="logout.php" class="btn accent logout">Log-out</a>
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

                    <!-- TEXT OBEN -->
                    <div class="preview-text">
                        <h3 id="previewTitle" class="preview-title"></h3>
                        <hr id="previewSeparator" class="preview-separator" hidden>
                        <p id="previewExtra" class="preview-extra"></p>
                    </div>

                    <!-- MEDIA UNTEN -->
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

    <hr class="section-divider">

    <h3 class="section-subtitle">Your Content History</h3>

    <div class="content-history-grid">
        <?php
        // Sample content for demonstration - replace with actual DB data
        $history_items = [
            [
                'id' => '1',
                'title' => 'Wasser ist feucht und wichtig zu trinken !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!',
                'thumbnail_url' => 'media/Videos/WALKWAY0025-0220.mp4',
                'extra_text' => 'Feuchtigkeit ist wichtig'
            ],
            [
                'id' => '2',
                'title' => 'Feuer',
                'thumbnail_url' => 'media/Images/Houser.jpg',
                'extra_text' => 'Das ist ein Beispielbild.'
            ],
            [
                'id' => '3',
                'title' => 'Erde',
                'thumbnail_url' => 'media/Images/AWWWWWWWWWWWW.jpg',
                'extra_text' => ''
            ]
        ];

        // Only show cards with valid media (image or video)
        foreach($history_items as $index => $item) {
            $media_url = $item['thumbnail_url'] ?? '';
            $title = $item['title'] ?? '';
            $extra_text = $item['extra_text'] ?? '';
            $media_html = '';
            $show_card = false;
            if (!empty($media_url) && file_exists($media_url)) {
                $ext = strtolower(pathinfo($media_url, PATHINFO_EXTENSION));
                if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                    $media_html = '<video src="' . htmlspecialchars($media_url) . '" class="preview-video" muted playsinline></video>';
                    $show_card = true;
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                    $media_html = '<img src="' . htmlspecialchars($media_url) . '" alt="Preview" class="preview-img" />';
                    $show_card = true;
                }
            }
            $max_len = 30;
            $short_title = mb_strlen($title) > $max_len ? mb_substr($title, 0, $max_len) . ' ...' : $title;
            if ($show_card) {
        ?>
        <div class="queue-card" data-content-id="<?php echo $item['id']; ?>" data-title="<?php echo htmlspecialchars($title); ?>" data-thumbnail="<?php echo htmlspecialchars($media_url); ?>" data-extra-text="<?php echo htmlspecialchars($extra_text); ?>" onclick="openContentModal(this)">
            <div class="card-preview" style="width:250px;height:200px;background:#f3f3f3;overflow:hidden;border-radius:12px;position:relative;margin:0 auto 10px auto;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:0;display:block;">
                <?php
                if (!empty($media_url) && file_exists($media_url)) {
                    $ext = strtolower(pathinfo($media_url, PATHINFO_EXTENSION));
                    if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                        echo '<video src="' . htmlspecialchars($media_url) . '" class="preview-video" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;display:block;border-radius:12px;background:#e0e0e0;box-shadow:0 1px 4px rgba(0,0,0,0.04);margin:0;padding:0;" muted playsinline></video>';
                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                        echo '<img src="' . htmlspecialchars($media_url) . '" alt="Preview" class="preview-img" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:contain;display:block;border-radius:12px;background:#e0e0e0;box-shadow:0 1px 4px rgba(0,0,0,0.04);margin:0;padding:0;" />';
                    }
                }
                ?>
            </div>
            <div class="card-subtitle"><?php echo htmlspecialchars($short_title); ?></div>
        </div>
        <?php }} ?>
    </div>

</main>

<!-- Content Preview Modal -->
<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="btn primary modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title" id="modalTitle">Von [Username]</div>
        <hr class="modal-title-separator" id="modalSeparator" style="display:none;" />
        <div class="modal-extra-text" id="modalExtraText"></div>
        <div class="modal-preview" id="modalPreviewArea">
            <span class="preview-placeholder">PREVIEW</span>
        </div>
    </div>
</div>

<script>
let currentContentId = null;

function openContentModal(cardElement) {
    const contentId = cardElement.dataset.contentId;
    const title = cardElement.dataset.title;
    const thumbnail = cardElement.dataset.thumbnail;
    const extraText = cardElement.dataset.extraText;
    currentContentId = contentId;
    const modalTitle = document.getElementById('modalTitle');
    modalTitle.style.textAlign = 'center';
    modalTitle.textContent = title ? title : 'Von [Username]';
    // Set extra text in its own div
    const extraTextDiv = document.getElementById('modalExtraText');
    const separator = document.getElementById('modalSeparator');
    if (extraText && extraText.trim() !== '') {
        extraTextDiv.textContent = extraText;
        extraTextDiv.style.display = 'flex';
        // Show and size separator
        separator.style.display = 'block';
        // Wait for DOM update to measure widths
        setTimeout(() => {
            const titleWidth = modalTitle.scrollWidth;
            const textWidth = extraTextDiv.scrollWidth;
            const sepWidth = Math.max(titleWidth, textWidth);
            separator.style.width = sepWidth + 'px';
            separator.style.margin = '12px auto';
        }, 0);
    } else {
        extraTextDiv.textContent = '';
        extraTextDiv.style.display = 'none';
        separator.style.display = 'none';
    }
    // Update preview area: only the media
    const previewArea = document.getElementById('modalPreviewArea');
    let mediaHtml = '';
    if (thumbnail && thumbnail.trim() !== '') {
        const ext = thumbnail.split('.').pop().toLowerCase();
        if (["mp4","webm","ogg"].includes(ext)) {
            mediaHtml = '<video src="' + thumbnail + '" controls autoplay muted playsinline class="modal-preview-img" ></video>';
        } else if (["jpg","jpeg","png","gif","bmp","webp"].includes(ext)) {
            mediaHtml = '<img src="' + thumbnail + '" alt="Content Preview" class="modal-preview-img" />';
        } else {
            mediaHtml = '<span class="preview-placeholder">PREVIEW</span>';
        }
    } else {
        mediaHtml = '<span class="preview-placeholder">PREVIEW</span>';
    }
    previewArea.innerHTML = mediaHtml;
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
    target.innerHTML = '';

    if (isVideo) {
        const video = document.createElement('video');
        video.src = src;

        video.muted = false;
        video.controls = true;
        video.playsInline = true;
        video.preload = 'metadata';

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

// Play video on hover for small preview boxes
// No image sizing JS, rely on CSS only

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.queue-card .card-preview').forEach(function(preview) {
        preview.addEventListener('mouseenter', function() {
            const video = preview.querySelector('video');
            if (video) {
                video.muted = true;
                video.play();
            }
        });
        preview.addEventListener('mouseleave', function() {
            const video = preview.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeContentModal();
    }
});
</script>



</body>
</html>
