<?php
session_start();

// --- Simple POST save queue ---
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['queue_data'])){
    $data = json_decode($_POST['queue_data'], true);
    $file = __DIR__ . '/queue.json';
    if(file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))){
        $message = "Queue saved successfully!";
    } else {
        $message = "Failed to write queue.json";
    }
}

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';
use App\classes\Auth;

$auth = new Auth();

// TODO: Check if user is moderator, else redirect
$username = $_SESSION['username'] ?? 'Moderator';
$first_name = 'Vorname';
$last_name = 'NACHNAME';

// Sample queue items (duplicates preserved)
$queue_items = [
    ['id'=>'1','title'=>'Wasser ist feucht und wichtig zu trinken !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!','thumbnail_url'=>'media/Videos/WALKWAY0025-0220.mp4'],
    ['id'=>'2','title'=>'Feuer','thumbnail_url'=>'media/Images/Houser.jpg'],
    ['id'=>'3','title'=>'Erde','thumbnail_url'=>'media/Images/AWWWWWWWWWWWW.jpg'],
    ['id'=>'4','title'=>'Erde','thumbnail_url'=>'media/Images/AWWWWWWWWWWWW.jpg'],
    ['id'=>'5','title'=>'Erde','thumbnail_url'=>'media/Images/AWWWWWWWWWWWW.jpg'],
    ['id'=>'6','title'=>'Erde','thumbnail_url'=>'media/Images/AWWWWWWWWWWWW.jpg']
];

// Load saved queue.json if exists
$queueFile = __DIR__ . '/queue.json';
if(file_exists($queueFile)){
    $loaded = json_decode(file_get_contents($queueFile), true);
    if(is_array($loaded) && count($loaded)>0){
        $queue_items = $loaded;
    }
}

// Export JSON
if(isset($_GET['export']) && $_GET['export']==='json'){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($queue_items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Moderator</title>
<link rel="stylesheet" href="styles/style.css">
<link rel="stylesheet" href="styles/style_mod.css">
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
                <span class="user-name"><?php echo htmlspecialchars($first_name.' '.$last_name); ?></span>
                <a href="logout.php" class="btn accent logout">Log-out</a>
            </div>
        </div>
    </div>
</header>

<main class="center-wrap">
<h2 class="mod-greeting">Hello Moderator!</h2>
<p class="mod-link"><a href="admin.php">Return to Admin</a></p>

<?php if(isset($message)): ?>
    <p style="color:green;font-weight:bold;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<div class="mod-section">
    <div class="section-header">
        <h3 class="queue-title">Active Content Queue:</h3>
    </div>

    <!-- APPLY CHANGES FORM -->
    <form method="POST">
        <input type="hidden" name="queue_data" id="queue_data">
        <button type="submit" class="btn secondary apply-changes-btn" onclick="prepareQueueData()">Apply Changes</button>
    </form>

    <div class="content-queue-container">
        <?php foreach($queue_items as $index=>$item):
            $media_url = $item['media'] ?? $item['thumbnail_url'] ?? '';
            $title = $item['title'] ?? '';
            $extra_text = $item['text'] ?? '';
            $media_html = '';
            $show_card = false;
            if(!empty($media_url) && file_exists($media_url)){
                $ext = strtolower(pathinfo($media_url, PATHINFO_EXTENSION));
                if(in_array($ext,['mp4','webm','ogg'])){
                    $media_html = '<video src="'.htmlspecialchars($media_url).'" class="preview-video" muted playsinline></video>';
                    $show_card=true;
                } elseif(in_array($ext,['jpg','jpeg','png','gif','bmp','webp'])){
                    $media_html = '<img src="'.htmlspecialchars($media_url).'" alt="Preview" class="preview-img" />';
                    $show_card=true;
                }
            }
            if($show_card):
                $short_title = mb_strlen($title)>30 ? mb_substr($title,0,30).' ...' : $title;
        ?>
        <div class="queue-card"
             data-content-id="<?php echo $item['original_id'] ?? $item['id'] ?? $index+1; ?>"
             data-original-id="<?php echo $item['original_id'] ?? $item['id'] ?? $index+1; ?>"
             data-order-id="<?php echo $index+1; ?>"
             data-title="<?php echo htmlspecialchars($title); ?>"
             data-thumbnail="<?php echo htmlspecialchars($media_url); ?>"
             data-extra-text="<?php echo htmlspecialchars($extra_text); ?>"
             onclick="openContentModal(this)">
            <div class="card-preview"><?php echo $media_html; ?></div>
            <div class="card-order-badge"><?php echo $index+1; ?></div>
            <div class="card-subtitle"><?php echo htmlspecialchars($short_title); ?></div>
        </div>
        <?php endif; endforeach; ?>
    </div>
</div>

<div class="mod-actions">
    <a href="mod_contentapprover.php" class="btn secondary approver">Content Approver</a>
</div>
</main>

<!-- Content Modal -->
<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="btn primary modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title" id="modalTitle">Von [Username]</div>
        <hr class="modal-separator" id="modalSeparator" style="display:none;" />
        <div class="modal-extra-text" id="modalExtraText"></div>
        <div class="modal-preview" id="modalPreviewArea"><span class="preview-placeholder">PREVIEW</span></div>
        <div class="modal-footer">
            <button class="btn accent delete" onclick="deleteContent()">Delete</button>
            <span class="modal-uploader" style="margin-left:18px;font-size:1.08rem;color:#374151;">Von [Vorname] [Nachname]</span>
        </div>
    </div>
</div>

<script>
// GLOBAL
let currentContentId = null;

// Prepare queue JSON before submitting
function prepareQueueData(){
    const cards = document.querySelectorAll('.queue-card');
    const data = Array.from(cards).map((c, idx)=>({
    original_id: c.dataset.originalId,
    order_id: idx+1,
    title: c.dataset.title,
    media: c.dataset.thumbnail,
    text: c.dataset.extraText || ''
}));

    document.getElementById('queue_data').value = JSON.stringify(data);
}

// Drag-drop, modal, hover previews, delete, scroll — same as before
let draggedCard=null;

document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('.queue-card .card-preview').forEach(preview=>{
        preview.addEventListener('mouseenter',()=>{ const v=preview.querySelector('video'); if(v){v.muted=true; v.play();} });
        preview.addEventListener('mouseleave',()=>{ const v=preview.querySelector('video'); if(v){v.pause(); v.currentTime=0;} });
    });
    initDragAndDrop();
    reassignOrderIds();
});

function initDragAndDrop(){
    const cards=document.querySelectorAll('.queue-card');
    cards.forEach(card=>{
        card.draggable=true;
        card.addEventListener('dragstart',e=>{ draggedCard=card; card.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; });
        card.addEventListener('dragend',()=>{ card.classList.remove('dragging'); draggedCard=null; });
        card.addEventListener('dragover',e=>{ e.preventDefault(); card.classList.add('drag-over'); });
        card.addEventListener('dragleave',()=>{ card.classList.remove('drag-over'); });
        card.addEventListener('drop',e=>{
            e.preventDefault();
            if(draggedCard && draggedCard!==card){
                const container=document.querySelector('.content-queue-container');
                const all=Array.from(container.querySelectorAll('.queue-card'));
                const di=all.indexOf(draggedCard);
                const ti=all.indexOf(card);
                if(di<ti) container.insertBefore(draggedCard, card.nextSibling);
                else container.insertBefore(draggedCard, card);
                reassignOrderIds();
            }
            card.classList.remove('drag-over');
        });
    });
}

function reassignOrderIds(){
    const container=document.querySelector('.content-queue-container');
    const cards=Array.from(container.querySelectorAll('.queue-card'));
    cards.forEach((c,idx)=>{
        c.dataset.orderId=idx+1;
        const badge=c.querySelector('.card-order-badge'); if(badge) badge.textContent=idx+1;
    });
}

// MODAL
function openContentModal(card){
    currentContentId=card.dataset.contentId;
    const t=card.dataset.title||'Von [Username]';
    const thumb=card.dataset.thumbnail;
    const extra=card.dataset.extraText;
    const modalTitle=document.getElementById('modalTitle');
    const modalExtra=document.getElementById('modalExtraText');
    const sep=document.getElementById('modalSeparator');
    modalTitle.textContent=t;
    if(extra && extra.trim()!==''){ modalExtra.textContent=extra; modalExtra.style.display=''; sep.style.display='block'; }
    else { modalExtra.style.display='none'; sep.style.display='none'; }
    const preview=document.getElementById('modalPreviewArea');
    if(thumb){ const ext=thumb.split('.').pop().toLowerCase();
        if(['mp4','webm','ogg'].includes(ext)) preview.innerHTML='<video src="'+thumb+'" controls autoplay muted playsinline></video>';
        else preview.innerHTML='<img src="'+thumb+'" alt="Preview"/>';
    } else preview.innerHTML='<span class="preview-placeholder">PREVIEW</span>';
    document.getElementById('contentModal').style.display='flex';
    document.body.style.overflow='hidden';
}

function closeContentModal(event){
    if(event && event.target!==event.currentTarget) return;
    document.getElementById('contentModal').style.display='none';
    document.body.style.overflow='auto';
    currentContentId=null;
}

function deleteContent(){
    if(!currentContentId) return;
    if(!confirm('Are you sure you want to delete this content?')) return;
    const card=document.querySelector(`.queue-card[data-content-id="${currentContentId}"]`);
    if(card){ card.remove(); closeContentModal(); reassignOrderIds(); }
}

document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeContentModal(); });
document.querySelector('.content-queue-container').addEventListener('wheel',e=>{ e.preventDefault(); e.currentTarget.scrollLeft+=e.deltaY*2; });

</script>
</body>
</html>
