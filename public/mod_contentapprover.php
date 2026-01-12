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
        .content-grid-container {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            justify-content: flex-start;
            align-items: flex-start;
        }
        .queue-card {
            width: 340px;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 12px 0 18px 0;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .queue-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
        }
        .card-subtitle {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 320px;
            margin: 0 auto;
            font-size: 1.15rem;
            font-weight: 500;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
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
            overflow:auto;
            position:relative;
        }
        .modal-close {
            position:absolute;
            top:10px;
            right:10px;
            font-size:24px;
            background:none;
            border:none;
            cursor:pointer;
        }
        .modal-preview {
            width:100%;
            margin: 10px 0;
        }
        .modal-title {
            font-size:1.3rem;
            font-weight:600;
            margin-bottom:10px;
        }
        .modal-text {
            font-size:1rem;
            color:#333;
            margin-bottom:10px;
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
            <div class="user-role">Administrator</div>
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
                <div class="queue-card"
                     data-content-id="<?= $item['original_id'] ?>"
                     data-title="<?= htmlspecialchars($item['title']) ?>"
                     data-thumbnail="<?= htmlspecialchars($item['media'] ?? '') ?>"
                     data-extra-text="<?= htmlspecialchars($item['text'] ?? '') ?>"
                     data-uploader="<?= htmlspecialchars($item['uploader_text']) ?>">
                    <?php if (!empty($item['media'])): ?>
                    <div class="card-preview" style="width:250px;height:200px;background:#f3f3f3;overflow:hidden;border-radius:12px;position:relative;margin:0 auto 10px auto;">
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
        <div class="modal-text" id="modalText"></div>
        <div class="modal-preview" id="modalPreviewArea"></div>
        <div class="modal-footer">
            <button class="btn accent" onclick="approveContent()">Approve</button>
            <button class="btn accent" onclick="deleteContent()">Delete</button>
            <span id="modalUploader"></span>
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

    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalText').textContent = extraText;
    document.getElementById('modalUploader').textContent = uploader;

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
