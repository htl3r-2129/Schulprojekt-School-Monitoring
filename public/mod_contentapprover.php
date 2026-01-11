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
            transition: box-shadow 0.2s;
            cursor: move;
        }
        .queue-card:hover {
            box-shadow: 0 4px 18px rgba(0,0,0,0.13);
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
            <span class="user-name"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></span>
        </div>
        <a href="logout.php" class="btn logout-btn">Log-out</a>
    </div>
</header>

<main class="center-wrap">
    <h2 class="mod-greeting">Content Approver</h2>
    <p class="mod-link"><a href="mod.php">Return to Moderator</a></p>

    <div class="mod-section">
        <div class="content-grid-container">
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
            <button class="btn accent" style="background:#668099;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;font-family:'Segoe UI',Roboto,Arial,sans-serif;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.07);transition:background 0.2s,box-shadow 0.2s;margin-left:0;margin-top:10px;display:inline-block;" onclick="approveContent()">Approve</button>
            <button class="btn accent delete" style="background:#e23c21;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;font-family:'Segoe UI',Roboto,Arial,sans-serif;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.07);transition:background 0.2s,box-shadow 0.2s;margin-left:0;margin-top:10px;display:inline-block;" onclick="deleteContent()">Delete</button>
            <button class="btn accent" style="background:#3d4752;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:1.1rem;font-family:'Segoe UI',Roboto,Arial,sans-serif;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.07);transition:background 0.2s,box-shadow 0.2s;margin-left:0;margin-top:10px;display:inline-block;" onclick="blockUser()">Block</button>
            <!-- TODO: add username DB fetch implementation -->
            <span class="modal-uploader" style="margin-left:18px;font-size:1.08rem;font-family:'Segoe UI',Roboto,Arial,sans-serif;color:#374151;font-weight:400;vertical-align:middle;">Von [Vorname] [Nachname]</span>
        </div>
    </div>
</div>

<script>
let currentContentId = null;
let draggedCard = null;

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
            mediaHtml = '<video src="' + thumbnail + '" controls autoplay muted playsinline style="width:100%;height:100%;object-fit:contain;display:block;border-radius:14px;background:#e0e0e0;" ></video>';
        } else if (["jpg","jpeg","png","gif","bmp","webp"].includes(ext)) {
            mediaHtml = '<img src="' + thumbnail + '" alt="Content Preview" style="width:100%;height:100%;object-fit:contain;display:block;border-radius:14px;background:#e0e0e0;" />';
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
    
    // Initialize drag and drop
    initDragAndDrop();
});

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
                const container = document.querySelector('.content-grid-container');
                const allCards = Array.from(container.querySelectorAll('.queue-card'));
                const draggedIndex = allCards.indexOf(draggedCard);
                const targetIndex = allCards.indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    this.parentNode.insertBefore(draggedCard, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedCard, this);
                }
                
                // Log new order
                const newOrder = Array.from(container.querySelectorAll('.queue-card')).map(c => c.dataset.contentId);
                console.log('New content order after drag and drop:', newOrder);
                
                // TODO: Send the new order to the server via AJAX
            }
            
            this.classList.remove('drag-over');
            return false;
        });
    });
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
function blockUser() {
    if (!currentContentId) return;
    console.log('Blocking user for content with ID:', currentContentId);
    closeContentModal();
    alert('Block user functionality to be implemented');
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
</script>

</body>
</html>
