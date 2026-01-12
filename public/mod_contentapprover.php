<?php
/* ======================================================
   HANDLE APPROVE ACTION (AUTO-INCREMENT original_id)
   ====================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve') {

    $file = __DIR__ . '/content_source.json';

    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
    }

    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json)) {
        $json = [];
    }

    // Find highest original_id
    $lastId = 0;
    foreach ($json as $entry) {
        if (isset($entry['original_id'])) {
            $lastId = max($lastId, (int)$entry['original_id']);
        }
    }
    $nextId = (string)($lastId + 1);

    // Append new entry
    $json[] = [
        'original_id' => $nextId,
        'title'       => $_POST['title'],
        'type'        => $_POST['type'],
        'media'       => $_POST['media'],
        'text'        => $_POST['text']
    ];

    file_put_contents(
        $file,
        json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo 'OK';
    exit;
}

session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';

use Insi\Ssm\Auth;

$auth = new Auth();

//TODO : Check if user is moderator, else redirect

$username = $_SESSION['username'] ?? 'Moderator';
$first_name = 'Vorname';
$last_name = 'NACHNAME';

$queue_items = [
    [
        'id' => '1',
        'title' => 'Wasser ist feucht und wichtig zu trinken !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!',
        'thumbnail_url' => 'media/Videos/WALKWAY0025-0220.mp4',
    ],
    [
        'id' => '2',
        'title' => 'Feuer',
        'thumbnail_url' => 'media/Images/Houser.jpg',
    ],
    [
        'id' => '3',
        'title' => 'Erde',
        'thumbnail_url' => 'media/Images/AWWWWWWWWWWWW.jpg',
    ]
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Moderator</title>
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
        .queue-card.dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }
        .queue-card.drag-over {
            border: 2px dashed var(--primary-blue, #668099);
            transform: scale(1.02);
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
                <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
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
                     data-content-id="<?= $item['id'] ?>"
                     data-title="<?= htmlspecialchars($item['title']) ?>"
                     data-thumbnail="<?= htmlspecialchars($item['thumbnail_url']) ?>"
                     data-extra-text="">
                    <div class="card-preview" style="width:250px;height:200px;background:#f3f3f3;overflow:hidden;border-radius:12px;position:relative;margin:0 auto 10px auto;">
                        <?php
                        $ext = strtolower(pathinfo($item['thumbnail_url'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['mp4','webm','ogg'])) {
                            echo '<video src="'.$item['thumbnail_url'].'" muted playsinline style="width:100%;height:100%;object-fit:contain;"></video>';
                        } else {
                            echo '<img src="'.$item['thumbnail_url'].'" style="width:100%;height:100%;object-fit:contain;">';
                        }
                        ?>
                    </div>
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
        <div class="modal-preview" id="modalPreviewArea"></div>
        <div class="modal-footer">
            <button class="btn accent" onclick="approveContent()">Approve</button>
            <button class="btn accent" onclick="deleteContent()">Delete</button>
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
    document.getElementById('modalTitle').textContent = card.dataset.title;

    const media = card.dataset.thumbnail;
    const ext = media.split('.').pop().toLowerCase();

    document.getElementById('modalPreviewArea').innerHTML =
        ["mp4","webm","ogg"].includes(ext)
            ? `<video src="${media}" controls style="width:100%;height:100%"></video>`
            : `<img src="${media}" style="width:100%;height:100%">`;

    document.getElementById('contentModal').style.display = 'flex';
}

function closeContentModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('contentModal').style.display = 'none';
    currentContentId = null;
}

function approveContent() {
    if (!currentContentId) return;

    const card = document.querySelector(`.queue-card[data-content-id="${currentContentId}"]`);
    const media = card.dataset.thumbnail;
    const ext = media.split('.').pop().toLowerCase();
    const type = ["mp4","webm","ogg"].includes(ext) ? "video" : "image";

    const formData = new FormData();
    formData.append('action', 'approve');
    formData.append('title', card.dataset.title);
    formData.append('media', media);
    formData.append('type', type);
    formData.append('text', card.dataset.extraText || '');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    });

    card.remove();
    closeContentModal();
}

function deleteContent() {
    if (!currentContentId) return;

    const card = document.querySelector(
        `.queue-card[data-content-id="${currentContentId}"]`
    );

    if (card) {
        card.remove(); // UI ONLY
    }

    closeContentModal();
}


</script>

</body>
</html>
