<?php  
session_start();

// Composer Autoload
require_once __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth();

if (isset($_SESSION['user'])) {
    if (!$auth->isModerator($_SESSION['user'])) {
        header(header: 'Location: error/401.php');
    }
} else {
    header(header: 'Location: error/401.php');
}

$file = __DIR__ . '/content_source.json';

// JSON-Datei laden oder anlegen
if (!file_exists($file)) {
    file_put_contents($file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$json = json_decode(file_get_contents($file), true);
if (!is_array($json)) $json = [];

// POST-Handler für Approve & Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['original_id'] ?? null;

    if ($id) {

        // --------------------
        // APPROVE
        // --------------------
        if ($action === 'approve') {
            foreach ($json as &$entry) {
                if ($entry['original_id'] === $id) {
                    $entry['approved'] = true;
                    break;
                }
            }
            unset($entry);
        }

        // --------------------
        // DELETE (JSON + FILE)
        // --------------------
        if ($action === 'delete') {

            foreach ($json as $index => $entry) {
                if ($entry['original_id'] === $id) {

                    // Datei löschen, falls vorhanden
                    if (!empty($entry['media'])) {

                        // Absoluter Pfad (media liegt im selben Ordner)
                        $mediaPath = realpath(__DIR__ . '/' . $entry['media']);

                        // Sicherheitsprüfung
                        if ($mediaPath && is_file($mediaPath)) {
                            unlink($mediaPath);
                        }
                    }

                    // JSON-Eintrag entfernen
                    unset($json[$index]);
                    break;
                }
            }

            // Array neu indexieren
            $json = array_values($json);
        }

        // JSON speichern
        file_put_contents(
            $file,
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        echo 'OK';
        exit;
    }
}


// Nur Items mit approved = false anzeigen
$queue_items = array_filter($json, fn($item) => empty($item['approved']));

// --- ProvidedBy Feature hinzufügen ---
foreach($queue_items as &$item) {
    $item['uploader_text'] = 'Von' . (isset($item['ProvidedBy']) && trim($item['ProvidedBy']) !== '' ? ' ' . $item['ProvidedBy'] : ' Unbekannt');
}
unset($item);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Content Approver</title>
    <link rel="stylesheet" href="styles/style.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
/* --- restored approver-specific CSS (old) --- */
/* Grundlayout */
html, body {
    background-color: #ffffff !important;
    height: 100% !important;
}
body {
    font-family: 'Trebuchet MS', Helvetica, Arial, sans-serif;
    margin: 0 !important;
    padding: 0 !important;
    min-height: 100vh !important;
    background-color: #ffffff !important;
}

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background:#fff;
            padding:20px;
            border-radius:12px;
            width:80%;
            max-width:600px;
            max-height:80%;
            position:relative;
        }
        .modal-close {
            position:absolute;
            top:10px;
            right:10px;
            width:42px;
            height:42px;
            border:none;
            border-radius:6px;
            font-size:24px;
            font-weight:700;
            cursor:pointer;
            padding:0;
            line-height:1;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#e23c21;
            color:white;
        }
        
        .modal-preview {
            width:100%;
            margin: 10px 0;
        }
        /* Ensure media fits its container without stretching */
        /* Make the preview container take the card width and preserve aspect ratio */
        .queue-card .card-preview {
            width: 100% !important;
            height: auto !important;
            aspect-ratio: 16/9;
            max-height: 320px;
            padding: 0;
            margin: 0 auto 10px auto;
            display: block;
            overflow: hidden;
            background: #f3f3f3;
        }

        /* Make images and videos fill the preview container while preserving aspect ratio */
        .card-preview img,
        .card-preview video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .modal-preview img,
        .modal-preview video {
            width: 100%;
            height: auto;
            max-height: 70vh;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        
        .modal-footer {
            display:flex;
            justify-content:flex-start;
            align-items:center;
            gap:10px;
        }
        .modal-footer span {
            margin-left:auto;
            font-size:1rem;
            color:#374151;
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
            <div class="user-role">Moderator</div>
            <div class="user-name-row">
                <span class="user-name"><?= htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn accent logout">Log-out</a>
            </div>
        </div>
    </div>
</header>

<main class="center-wrap">
    <h2 class="mod-greeting">Content Approver</h2>
    <p class="mod-link"><a href="mod.php">Return to Moderator</a></p>

    <div class="mod-section">
        <div class="content-grid-container">
            <?php foreach($queue_items as $item): ?>
                <?php $hasMedia = !empty($item['media']); ?>
                <div class="queue-card<?= $hasMedia ? '' : ' no-preview' ?>"
                     data-content-id="<?= $item['original_id'] ?>"
                     data-title="<?= htmlspecialchars($item['title']) ?>"
                     data-thumbnail="<?= htmlspecialchars($item['media'] ?? '') ?>"
                     data-extra-text="<?= htmlspecialchars($item['text'] ?? '') ?>"
                     data-uploader="<?= htmlspecialchars($item['uploader_text']) ?>">
                    <?php if ($hasMedia): ?>
                    <div class="card-preview">
                        <?php
                        $ext = strtolower(pathinfo($item['media'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['mp4','webm','ogg'])) {
                            echo '<video src="'.$item['media'].'" muted playsinline style="width:100%;height:100%;object-fit:contain;"></video>';
                        } else {
                            echo '<img src="'.$item['media'].'" style="width:100%;height:100%;object-fit:contain;">';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                    <div class="card-subtitle"><?= htmlspecialchars($item['title']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<!-- MODAL -->
<!-- Content Modal -->
<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="btn primary modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title" id="modalTitle"></div>
        <hr class="modal-separator" id="modalSeparator" style="display:none;" />
        <div class="modal-text" id="modalText"></div>
        <div class="modal-preview" id="modalPreviewArea"></div>
        <div class="modal-footer">
            <button class="modalbtn primary" onclick="approveContent()">Approve</button>
            <button class="modalbtn secondary" onclick="deleteContent()">Delete</button>
            <button id="modalUploader" type="button" class="modalbtn accent" aria-hidden="true"></button>
        </div>
    </div>
</div>

<script>
let currentContentId = null;

document.querySelectorAll('.queue-card').forEach(card => {
    card.onclick = () => openContentModal(card);
});

function openContentModal(card) {
    currentContentId = card.dataset.contentId;
    const title = card.dataset.title;
    const uploader = card.dataset.uploader || '';
    const extraText = card.dataset.extraText || '';

    const modalTitleEl = document.getElementById('modalTitle');
    const modalTextEl = document.getElementById('modalText');
    const modalUploaderEl = document.getElementById('modalUploader');
    const sep = document.getElementById('modalSeparator');

    modalTitleEl.textContent = title;
    modalTextEl.textContent = extraText;
    modalUploaderEl.textContent = uploader;

    // Show separator always; size to larger of title or extra text when extra exists
    if(sep){
        sep.style.display = 'block';
        setTimeout(()=>{
            const w1 = modalTitleEl ? modalTitleEl.offsetWidth : 0;
            const w2 = modalTextEl ? modalTextEl.offsetWidth : 0;
            sep.style.width = Math.max(w1, w2)+'px';
        },0);
    }

    const media = card.dataset.thumbnail;
    if (media) {
        const ext = media.split('.').pop().toLowerCase();
        document.getElementById('modalPreviewArea').innerHTML =
            ["mp4","webm","ogg"].includes(ext)
                ? `<video src="${media}" controls style="width:100%;height:100%"></video>`
                : `<img src="${media}" style="width:100%;height:100%">`;
    } else {
        document.getElementById('modalPreviewArea').innerHTML = ''; // kein Media anzeigen
    }

    document.getElementById('contentModal').style.display = 'flex';
}

function closeContentModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('contentModal').style.display = 'none';
    currentContentId = null;
}

function approveContent() {
    if (!currentContentId) return;

    const formData = new FormData();
    formData.append('action', 'approve');
    formData.append('original_id', currentContentId);

    fetch(window.location.href, { method: 'POST', body: formData })
        .then(() => {
            const card = document.querySelector(`.queue-card[data-content-id="${currentContentId}"]`);
            if (card) card.remove();
            closeContentModal();
        });
}

function deleteContent() {
    if (!currentContentId) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('original_id', currentContentId);

    fetch(window.location.href, { method: 'POST', body: formData })
        .then(() => {
            const card = document.querySelector(`.queue-card[data-content-id="${currentContentId}"]`);
            if (card) card.remove();
            closeContentModal();
        });
}
</script>
</body>
</html>
