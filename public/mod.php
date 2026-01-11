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

// Sample content queue
//TODO: (replace with DB fetch)
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
        'id' => '2',
        'title' => 'Erde',
        'thumbnail_url' => 'media/Images/AWWWWWWWWWWWW.jpg',
    ],
    [
        'id' => '2',
        'title' => 'Erde',
        'thumbnail_url' => 'media/Images/AWWWWWWWWWWWW.jpg',
    ],
    [
        'id' => '2',
        'title' => 'Erde',
        'thumbnail_url' => 'media/Images/AWWWWWWWWWWWW.jpg',
    ],
    [
        'id' => '2',
        'title' => 'Erde',
        'thumbnail_url' => 'media/Images/AWWWWWWWWWWWW.jpg',
    ]
];

// If requested, output the queue as JSON (same order as $queue_items)
if (isset($_GET['export']) && $_GET['export'] === 'json') {
    $out = [];
    foreach ($queue_items as $item) {
        $out[] = [
            'title' => $item['title'] ?? '',
            'text'  => $item['text'] ?? '',
            'media' => $item['thumbnail_url'] ?? $item['id'] ?? ''
        ];
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Receive client-side JSON (current order) via POST so it can be inspected in DevTools
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['save_client_json'])) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    header('Content-Type: application/json; charset=utf-8');
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON', 'error' => json_last_error_msg()]);
        exit;
    }

    // Return the received payload and the current server-side queue for reference
    echo json_encode([
        'success' => true,
        'message' => 'Received client queue JSON',
        'received' => $data,
        'server_queue' => $queue_items
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <style>
                .content-queue-container {
                    display: flex;
                    flex-wrap: nowrap;
                    gap: 18px;
                    justify-content: flex-start;
                    align-items: flex-start;
                    overflow-x: auto;
                    padding-bottom: 10px; /* For scrollbar space */
                }
                .btn.accent.delete {
                    background: var(--primary-red, #e23c21);
                    color: #fff;
                    border: none;
                    border-radius: 8px;
                    padding: 10px 28px;
                    font-size: 1.1rem;
                    font-family: "Segoe UI", Roboto, Arial, sans-serif;
                    font-weight: 700;
                    cursor: pointer;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
                    transition: background 0.2s, box-shadow 0.2s;
                    margin-left: 0;
                    margin-top: 10px;
                    display: inline-block;
                }
                .btn.accent.delete:hover {
                    background: #c22c13;
                    box-shadow: 0 4px 18px rgba(0,0,0,0.13);
                }
        .modal-separator {
            border: none;
            border-top: 2px solid var(--primary-blue, #668099);
            margin: 18px 0 0 0;
            align-self: flex-start;
            transition: width 0.2s;
            background: none;
            height: 0;
            display: block;
        }
    .modal-extra-text {
        width: 100%;
        max-width: 900px;
        font-size: clamp(16px, 2.5vw, 24px);
        color: var(--dark-blue, #303b46);
        text-align: center;
        padding: 16px 0;
        word-break: break-word;
        font-weight: 400;
        font-family: "Segoe UI", Roboto, Arial, sans-serif;
        line-height: 1.6;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    </style>
        <style>
        .modal-content {
            max-width: 700px;
            width: 98vw;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.18);
            padding: 32px 28px 24px 28px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
        }
        .modal-title {
            margin: 0;
            font-weight: 900;
            font-size: clamp(20px, 3.5vw, 40px);
            color: var(--primary-red, #e23c21);
            text-align: right;
            padding-top: 100px;
            word-break: break-word;
            width: 100%;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.1;
        }
        .modal-preview {
            width: 600px;
            height: 340px;
            background: #f3f3f3;
            border-radius: 14px;
            overflow: hidden;
            display: block;
            margin-bottom: 22px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            max-width: 90vw;
        }
        .modal-preview img,
        .modal-preview video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 14px;
            background: #e0e0e0;
            display: block;
        }
        .modal-preview .preview-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            font-size: 1.2rem;
            color: #888;
        }
        @media (max-width: 800px) {
            .modal-content {
                max-width: 98vw;
                padding: 18px 2vw 18px 2vw;
            }
            .modal-preview {
                width: 98vw;
                height: 48vw;
                min-height: 120px;
                max-width: 98vw;
            }
        }
        </style>
    <meta charset="UTF-8">
    <title>Moderator</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
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
            transition: box-shadow 0.2s;
            position: relative;
        }
        .queue-card:hover {
            box-shadow: 0 4px 18px rgba(0,0,0,0.13);
        }
    .card-preview {
        width: 320px;
        height: 200px;
        background: #f3f3f3;
        overflow: hidden;
        border-radius: 12px;
        position: relative;
        margin: 0 auto 10px auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        padding: 0;
        display: block;
    }
    .card-preview img,
    .card-preview video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        border-radius: 12px;
        background: #e0e0e0;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        margin: 0;
        padding: 0;
    }
    .card-preview .preview-placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
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
    .queue-card {
        cursor: move;
    }
    .queue-card.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    .queue-card.drag-over {
        border: 2px dashed var(--primary-blue, #668099);
        transform: scale(1.02);
    }
    .card-order-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: var(--primary-blue, #668099);
        color: #fff;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        z-index: 5;
        opacity: 0;
        transform: translateY(-4px) scale(0.98);
        transition: opacity 0.18s ease, transform 0.18s ease;
        pointer-events: none;
    }
    .queue-card:hover .card-order-badge {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }
    </style>
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
            <div class="user-name-row">
                <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
                <a href="logout.php" class="btn accent logout">Log-out</a>
            </div>
        </div>
    </div>
</header>

<main class="center-wrap">
    <h2 class="mod-greeting">Hello Moderator!</h2>
    <p class="mod-link"><a href="admin.php">Return to Admin</a></p>

    <div class="mod-section">
        <div class="section-header">
            <h3 class="queue-title">Active Content Queue:</h3>
        </div>
        <button class="btn secondary apply-changes-btn" onclick="applyChanges()">Apply Changes</button>
        
        <div class="content-queue-container">
            <?php
            // Only show cards with valid media (image or video)
            foreach($queue_items as $index => $item) {
                $media_url = $item['thumbnail_url'] ?? '';
                $title = $item['title'] ?? '';
                $extra_text = '';
                if ($index === 0) {
                    $extra_text = 'Feuchtigkeit ist wichtig';
                } elseif ($index === 1) {
                    $extra_text = 'Das ist ein Beispielbild.';
                }
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
            <div class="queue-card" data-content-id="<?php echo $item['id']; ?>" data-original-id="<?php echo $item['id']; ?>" data-order-id="<?php echo ($index + 1); ?>" data-title="<?php echo htmlspecialchars($title); ?>" data-thumbnail="<?php echo htmlspecialchars($media_url); ?>" data-extra-text="<?php echo htmlspecialchars($extra_text); ?>" onclick="openContentModal(this)">
                <div class="card-preview">
                    <?php echo $media_html; ?>
                </div>
                <div class="card-order-badge"><?php echo ($index + 1); ?></div>
                <div class="card-subtitle"><?php echo htmlspecialchars($short_title); ?></div>
            </div>
            <?php }} ?>
        </div>
    </div>

    <div class="mod-actions">
        <a href="mod_contentapprover.php" class="btn secondary approver">Content Approver</a>
    </div>

</main>

<!-- Content Preview Modal -->
<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="btn primary modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title" id="modalTitle">Von [Username]</div>
        <hr class="modal-separator" id="modalSeparator" style="display:none;" />
        <div class="modal-extra-text" id="modalExtraText"></div>
        <div class="modal-preview" id="modalPreviewArea">
            <span class="preview-placeholder">PREVIEW</span>
        </div>
        <div class="modal-footer">
            <button class="btn accent delete" onclick="deleteContent()">Delete</button>
            <!-- TODO: add username DB fetch implementation -->
            <span class="modal-uploader" style="margin-left:18px;font-size:1.08rem;font-family:'Segoe UI',Roboto,Arial,sans-serif;color:#374151;font-weight:400;vertical-align:middle;">Von [Vorname] [Nachname]</span>
        </div>
    </div>
</div>

<script>
let currentContentId = null;
// currentQueueJson mirrors the current DOM order; updated after any reorder
let currentQueueJson = [];

// Play video on hover for small preview boxes
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
    
    // Initialize drag and drop
    initDragAndDrop();
    // Ensure initial order IDs are assigned and visible
    if (typeof reassignOrderIds === 'function') reassignOrderIds();

    // Client-side JSON is now auto-managed; no manual export button needed
});

let draggedCard = null;

function initDragAndDrop() {
    const cards = document.querySelectorAll('.queue-card');
    
    cards.forEach(card => {
        card.draggable = true;
        
        card.addEventListener('dragstart', function(e) {
            draggedCard = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });
        
        card.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            cards.forEach(c => c.classList.remove('drag-over'));
            draggedCard = null;
        });
        
        card.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            if (this !== draggedCard) {
                this.classList.add('drag-over');
            }
            return false;
        });
        
        card.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        card.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            
            if (this !== draggedCard && draggedCard) {
                const container = document.querySelector('.content-queue-container');
                const allCards = Array.from(container.querySelectorAll('.queue-card'));
                const draggedIndex = allCards.indexOf(draggedCard);
                const targetIndex = allCards.indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    this.parentNode.insertBefore(draggedCard, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedCard, this);
                }
                
                // Reassign positional IDs and log new mapping (original_id -> order_id)
                if (typeof reassignOrderIds === 'function') reassignOrderIds();
                const newOrder = Array.from(container.querySelectorAll('.queue-card')).map(c => ({ original_id: c.dataset.originalId || c.dataset.contentId, order_id: Number(c.dataset.orderId) }));
                console.log('New content order after drag and drop (original_id -> order_id):', newOrder);
            }
            
            this.classList.remove('drag-over');
            return false;
        });
    });
}

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
        extraTextDiv.style.display = '';
        // Show and size separator
        separator.style.display = 'block';
        // Wait for DOM update to measure widths
        setTimeout(() => {
            const titleWidth = modalTitle.scrollWidth;
            const textWidth = extraTextDiv.scrollWidth;
            const sepWidth = Math.max(titleWidth, textWidth);
            separator.style.width = sepWidth + 'px';
            separator.style.margin = '18px auto 0 auto';
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
            mediaHtml = '<video src="' + thumbnail + '" controls autoplay muted playsinline></video>';
        } else if (["jpg","jpeg","png","gif","bmp","webp"].includes(ext)) {
            mediaHtml = '<img src="' + thumbnail + '" alt="Content Preview" />';
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

function deleteContent() {
    if (!currentContentId) return;
    
    if (!confirm('Are you sure you want to delete this content?')) {
        return;
    }
    
    // Find and remove the card from DOM
    const card = document.querySelector(`.queue-card[data-content-id="${currentContentId}"]`);
    
    if (card) {
        // TODO: Implement AJAX call to delete from database
        // Example implementation:
        /*
        fetch('api/delete-content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                content_id: currentContentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                card.remove();
                closeContentModal();
                console.log('Content deleted successfully');
            } else {
                alert('Error deleting content: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting content');
        });
        */
        
        // For now, just remove from DOM (comment out when implementing DB)
        card.remove();
        closeContentModal();
        console.log('Deleting content with ID:', currentContentId);
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeContentModal();
    }
});

// Reassign sequential order IDs (1..N) to cards and update visible badge
function reassignOrderIds() {
    const container = document.querySelector('.content-queue-container');
    if (!container) return;
    const cards = Array.from(container.querySelectorAll('.queue-card'));
    const seen = new Set();
    const mapping = [];
    cards.forEach((c, idx) => {
        const newId = idx + 1;
        const original = c.dataset.originalId || c.dataset.contentId || null;
        c.dataset.orderId = String(newId);
        // update badge if exists
        const badge = c.querySelector('.card-order-badge');
        if (badge) badge.textContent = String(newId);
        mapping.push({ original_id: original, order_id: newId });
        if (seen.has(newId)) {
            console.error('Duplicate order id detected:', newId, 'mapping:', mapping);
        }
        seen.add(newId);
    });
    // Build client-side JSON representation using current DOM order
    currentQueueJson = cards.map(c => {
        const media = c.dataset.thumbnail || c.dataset.contentId || '';
        // Determine type based on file extension
        let type = 'image'; // default
        if (media) {
            const ext = media.split('.').pop().toLowerCase();
            if (['mp4', 'webm', 'ogg', 'avi', 'mkv'].includes(ext)) {
                type = 'video';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext)) {
                type = 'image';
            }
        }
        return {
            title: c.dataset.title || '',
            text: c.dataset.extraText || '',
            type: type,
            media: media
        };
    });
    console.log('Order reassigned:', mapping);
    console.log('Current client-side queue JSON updated:', currentQueueJson);
    console.log('Complete JSON (formatted):');
    console.log(JSON.stringify(currentQueueJson, null, 2));
}

function applyChanges() {
    // TODO: Implement apply changes functionality
    alert('Apply Changes functionality to be implemented');
}

// Enable horizontal scrolling with mouse wheel for content queue
document.querySelector('.content-queue-container').addEventListener('wheel', function(e) {
    e.preventDefault();
    this.scrollLeft += e.deltaY * 2; // Faster scrolling
});
</script>

</body>
</html>
