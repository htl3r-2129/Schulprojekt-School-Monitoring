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
        if ($action === 'approve') {
            foreach ($json as &$entry) {
                if ($entry['original_id'] === $id) {
                    $entry['approved'] = true;
                    break;
                }
            }
            unset($entry);
        }

        if ($action === 'delete') {
            $json = array_filter($json, fn($entry) => $entry['original_id'] !== $id);
            $json = array_values($json); // Reindex array
        }

        file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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

/* Centered content area */
.center-wrap {
    max-width: 1600px;
    margin: 40px auto;
    padding: 0 20px 60px;
    background-color: #ffffff !important;
}

.mod-greeting { color: #e23c21; font-size:36px; margin: 20px 0; text-align:center; }
.mod-link { text-align:center; margin-bottom:18px }

.content-grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    align-items: start;
    justify-items: stretch;
    width: 100%;
}

.queue-card {
    flex: 0 0 auto; /* prevent wrapping/shrinking */
    width: 320px;
    margin: 0;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 12px 12px 18px 12px;
    transition: all 0.25s ease;
    cursor: pointer;
}
.queue-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.18); }

.card-preview { width: 100%; height: 180px; background: #f3f3f3; overflow:hidden; border-radius:12px; display:block; margin:0 0 10px 0; position:relative; padding:0; box-sizing:border-box; }
.card-preview img, .card-preview video { position:absolute; inset:0; width:100%; height:100%; object-fit:contain; display:block; }

.card-subtitle { background: #3d4752; color: white; padding: 10px; text-align:center; font-size:14px; font-weight:700; border-radius:10px; }

/* When a card lacks a preview, push subtitle down to match cards that have previews */
.queue-card.no-preview .card-subtitle {
    margin-top: auto;
}

/* Modal Styles */
.modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.75); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(3px); }
.modal-content { background: linear-gradient(135deg,#ffffff 0%,#f5f7fa 100%); border-radius: 10px; width:92%; max-width:900px; display:flex; flex-direction:column; gap:20px; position:relative; padding:24px; align-items:center; justify-content:flex-start; }
.modal-close { position:absolute; top:14px; right:14px; width:42px; height:42px; border:none; border-radius:6px; font-size:28px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; background:#e23c21; color:#fff; }
.modal-title { font-size: clamp(20px,3.5vw,40px); font-weight:700; color:#e23c21; margin:0; text-align:center; width:100%; }
.modal-separator, .modal-title-separator { border:0; border-top:2px solid #668099; margin:12px auto; width:auto; }
.modal-preview { width:100%; max-width:100%; aspect-ratio:16/9; background:transparent; border-radius:10px; display:flex; align-items:center; justify-content:center; overflow:hidden; }
.modal-preview img, .modal-preview video { width:100%; height:100%; object-fit:contain; display:block; border-radius:10px; }
.modal-extra-text, .modal-text { width:100%; max-width:900px; font-size: clamp(16px,2.5vw,24px); color: #303b46; text-align:center; padding:16px 0; word-break:break-word; line-height:1.6; margin:0 auto; display:flex; align-items:center; justify-content:center; }
.modal-footer { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:20px 0 0; background:transparent; width:100%; }
.modalbtn { padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 700; border: none; cursor: pointer; transition: background 0.2s; }
.modal-footer .btn { padding:12px 24px; border-radius:8px; font-size:16px; font-weight:700; }
.modal-footer .modalbtn.primary { background-color: #e23c21; color: #fff; }
.modal-footer .modalbtn.primary:hover { background-color: #c13616; }
.modal-footer .modalbtn.secondary { background-color: #668099; color: #fff; }
.modal-footer .modalbtn.secondary:hover { background-color: #56667a; }
.modal-footer .modalbtn.accent { background-color: #303b46; color: #fff; }
.modal-footer .modalbtn.accent:hover { background-color: #3d4752; }
.modal-footer span { margin-left:auto; font-size:1rem; color:#374151; }

/* small helpers */
.btn { padding:6px 10px; border-radius:6px; font-weight:700; }

/* end restored CSS */
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
