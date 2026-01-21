<?php
session_start();

/* =========================================================
   JSON Helper
   ========================================================= */
function sendNoCacheJson($data) {
    header('Content-Type: application/json; charset=utf-8');
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* =========================================================
   Paths
   ========================================================= */
$queueFile         = __DIR__ . '/queue.json';
$contentSourceFile = __DIR__ . '/content_source.json';

/* =========================================================
   🔴 DELETE HANDLER – MUSS GANZ OBEN SEIN
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $delete_id = (string)$_POST['delete_id'];

    /* ---------- content_source.json ---------- */
    $content_source = file_exists($contentSourceFile)
        ? json_decode(file_get_contents($contentSourceFile), true)
        : [];

    foreach ($content_source as $idx => $item) {
        if ((string)$item['original_id'] === $delete_id) {

            // Datei löschen
            if (!empty($item['media'])) {
                $mediaPath = realpath(__DIR__ . '/' . $item['media']);
                if ($mediaPath && is_file($mediaPath)) {
                    unlink($mediaPath);
                }
            }

            unset($content_source[$idx]);
            break;
        }
    }

    $content_source = array_values($content_source);
    file_put_contents(
        $contentSourceFile,
        json_encode($content_source, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    /* ---------- queue.json ---------- */
    $queue_items = file_exists($queueFile)
        ? json_decode(file_get_contents($queueFile), true)
        : [];

    $queue_items = array_filter(
        $queue_items,
        fn($item) => (string)$item['original_id'] !== $delete_id
    );

    $queue_items = array_values($queue_items);
    foreach ($queue_items as $i => &$item) {
        $item['order_id'] = $i + 1;
    }
    unset($item);

    file_put_contents(
        $queueFile,
        json_encode($queue_items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );

    sendNoCacheJson(['success' => true]);
}

/* =========================================================
   JSON API
   ========================================================= */
if (isset($_GET['get_content_json'])) {
    $content_source = file_exists($contentSourceFile)
        ? json_decode(file_get_contents($contentSourceFile), true)
        : [];
    sendNoCacheJson($content_source);
}

/* =========================================================
   Cache Headers
   ========================================================= */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

/* =========================================================
   Auth
   ========================================================= */
require_once __DIR__ . '/../vendor/autoload.php';
use Insi\Ssm\Auth;

$auth = new Auth();
if (!isset($_SESSION['user']) || !$auth->isModerator($_SESSION['user'])) {
    header('Location: error/401.php');
    exit;
}

/* =========================================================
   Load content_source.json (approved only)
   ========================================================= */
$content_source = file_exists($contentSourceFile)
    ? json_decode(file_get_contents($contentSourceFile), true)
    : [];

$content_source = array_values(array_filter(
    $content_source,
    fn($i) => !empty($i['approved'])
));

/* =========================================================
   Load queue.json
   ========================================================= */
$queue_items = file_exists($queueFile)
    ? json_decode(file_get_contents($queueFile), true)
    : [];

/* =========================================================
   ProvidedBy Map
   ========================================================= */
$providedByMap = [];
foreach ($content_source as $c) {
    $providedByMap[$c['original_id']] =
        !empty(trim($c['ProvidedBy'] ?? ''))
            ? 'Von: '.$c['ProvidedBy']
            : 'Von: Unbekannt';
}

/* =========================================================
   SYNC (nur bei Page Load GET)
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $source_map = [];
    foreach ($content_source as $item) $source_map[$item['original_id']] = $item;

    $new_queue = [];
    foreach ($queue_items as $q_item) {
        if (isset($source_map[$q_item['original_id']])) {
            $src = $source_map[$q_item['original_id']];
            $new_queue[] = [
                'original_id' => $q_item['original_id'],
                'order_id'    => $q_item['order_id'],
                'title'       => $src['title'] ?? $q_item['title'],
                'media'       => $src['media'] ?? $q_item['media'],
                'text'        => $src['text']  ?? $q_item['text'],
                'type'        => $src['type']  ?? $q_item['type']
            ];
            unset($source_map[$q_item['original_id']]);
        }
    }

    $order_counter = count($new_queue) + 1;
    foreach ($source_map as $item) {
        $new_queue[] = [
            'original_id' => $item['original_id'],
            'order_id'    => $order_counter++,
            'title'       => $item['title'],
            'media'       => $item['media'],
            'text'        => $item['text'],
            'type'        => $item['type']
        ];
    }

    $queue_items = $new_queue;
    file_put_contents(
        $queueFile,
        json_encode($queue_items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}

/* =========================================================
   POST Save Queue (Drag&Drop changes)
   ========================================================= */
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['queue_data'])){
    $data = json_decode($_POST['queue_data'], true);

    foreach($data as &$item) unset($item['uploader_text']);
    unset($item);

    if(file_put_contents(
        $queueFile,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    )){
        $message = "Queue saved successfully!";
        $queue_items = $data;
    } else {
        $message = "Failed to write queue.json";
    }
}

/* =========================================================
   Export queue.json
   ========================================================= */
if (isset($_GET['export']) && $_GET['export'] === 'json') {
    $out = [];
    foreach ($queue_items as $item) {
        $out[] = [
            'title' => $item['title'] ?? '',
            'text'  => $item['text'] ?? '',
            'media' => $item['thumbnail_url'] ?? $item['id'] ?? ''
        ];
    }
    sendNoCacheJson($out);
}

/* =========================================================
   Receive client-side JSON via POST
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['save_client_json'])) {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        sendNoCacheJson(['success' => false, 'message' => 'Invalid JSON', 'error' => json_last_error_msg()]);
    }
    sendNoCacheJson([
        'success' => true,
        'message' => 'Received client queue JSON',
        'received' => $data,
        'server_queue' => $queue_items
    ]);
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
            <div class="user-role">Moderator</div>
            <div class="user-name-row">
                <span class="user-name"><?= htmlspecialchars($_SESSION['name']); ?></span>
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

    <form method="POST">
        <input type="hidden" name="queue_data" id="queue_data">
        <button type="submit" class="btn primary apply-changes-btn" onclick="prepareQueueData()">Apply Changes</button>
    </form>

    <div class="content-queue-container">
        <?php foreach($queue_items as $index=>$item):
            $media_url  = $item['media'] ?? '';
            $title      = $item['title'] ?? '';
            $extra_text = $item['text'] ?? '';
            $uploader   = $providedByMap[$item['original_id']] ?? 'Von: Unbekannt';
            $media_html = '';
            $show_card  = false;
            $type       = $item['type'] ?? '';

            // --- Show card if it has media OR title OR text ---
            if((!empty($media_url) && file_exists($media_url)) || !empty(trim($title)) || !empty(trim($extra_text))){
                $show_card = true;

                if(!empty($media_url) && file_exists($media_url)){
                    $ext = strtolower(pathinfo($media_url, PATHINFO_EXTENSION));
                    if(in_array($ext,['mp4','webm','ogg'])){
                        $media_html = '<video src="'.htmlspecialchars($media_url).'" class="preview-video" muted playsinline></video>';
                        $type = 'video';
                    } elseif(in_array($ext,['jpg','jpeg','png','gif','bmp','webp'])){
                        $media_html = '<img src="'.htmlspecialchars($media_url).'" alt="Preview" class="preview-img" />';
                        $type = 'image';
                    }
                }
            }

            if($show_card):
                $short_title = mb_strlen($title)>30 ? mb_substr($title,0,30).' ...' : $title;
        ?>
        <div class="queue-card"
             data-content-id="<?php echo $item['original_id']; ?>"
             data-original-id="<?php echo $item['original_id']; ?>"
             data-order-id="<?php echo $item['order_id']; ?>"
             data-title="<?php echo htmlspecialchars($title); ?>"
             data-thumbnail="<?php echo htmlspecialchars($media_url); ?>"
             data-extra-text="<?php echo htmlspecialchars($extra_text); ?>"
             data-type="<?php echo $type; ?>"
             data-uploader="<?php echo htmlspecialchars($uploader); ?>"
             onclick="openContentModal(this)">
            <div class="card-preview"><?php echo $media_html; ?></div>
            <div class="card-order-badge"><?php echo $item['order_id']; ?></div>
            <div class="card-subtitle"><?php echo htmlspecialchars($short_title); ?></div>
        </div>
        <?php endif; endforeach; ?>
    </div>
</div>

<div class="mod-actions">
    <a href="mod_contentapprover.php" class="btn secondary approver">Content Approver</a>
</div>
</main>

<div id="contentModal" class="modal-overlay" onclick="closeContentModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="btn primary modal-close" onclick="closeContentModal()">&times;</button>
        <div class="modal-title" id="modalTitle">Von: [Username]</div>
        <hr class="modal-separator" id="modalSeparator" style="display:none;" />
        <div class="modal-extra-text" id="modalExtraText"></div>
        <div class="modal-preview" id="modalPreviewArea"><span class="preview-placeholder">PREVIEW</span></div>
        <div class="modal-footer">
            <button class="btn modalbtn primary" onclick="deleteContent()">Delete</button>
            <button id="modalUploader" class="btn modalbtn accent modal-uploader" style="margin-left:18px;font-size:1.08rem;">Von: [Vorname] [Nachname]</button>
        </div>
    </div>
</div>

<script>
let currentContentId = null;

function prepareQueueData(){
    const cards = document.querySelectorAll('.queue-card');
    const data = Array.from(cards).map((c, idx)=>({
        original_id: c.dataset.originalId,
        order_id: idx+1,
        title: c.dataset.title,
        type: c.dataset.type,
        media: c.dataset.thumbnail,
        text: c.dataset.extraText || ''
    }));
    document.getElementById('queue_data').value = JSON.stringify(data);
}

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
        const badge=c.querySelector('.card-order-badge');
        if(badge) badge.textContent=idx+1;
    });
}

function openContentModal(card){
    currentContentId=card.dataset.contentId;
    const t = card.dataset.title || 'Von: [Username]';
    const thumb = card.dataset.thumbnail;
    const extra = card.dataset.extraText;
    const uploaderText = card.dataset.uploader || 'Von: Unbekannt';

    const modalTitle = document.getElementById('modalTitle');
    const modalExtra = document.getElementById('modalExtraText');
    const modalUploader = document.getElementById('modalUploader');
    const sep = document.getElementById('modalSeparator');

    modalTitle.textContent = t;
    modalUploader.textContent = uploaderText;

    if(extra && extra.trim()!==''){
        modalExtra.textContent = extra;
        modalExtra.style.display='';
        sep.style.display='block';
        setTimeout(()=>{ sep.style.width = Math.max(modalTitle.offsetWidth, modalExtra.offsetWidth)+'px'; },0);
    } else {
        modalExtra.style.display='none';
        sep.style.display='block';
        setTimeout(()=>{ sep.style.width = modalTitle.offsetWidth+'px'; },0);
    }

    const preview=document.getElementById('modalPreviewArea');
    if(thumb){
        const ext = thumb.split('.').pop().toLowerCase();
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

    fetch('', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'delete_id='+encodeURIComponent(currentContentId)
    }).then(res=>res.json())
      .then(data=>{
        if(data.success){
            const card=document.querySelector(`.queue-card[data-content-id="${currentContentId}"]`);
            if(card) card.remove();
            closeContentModal();
            reassignOrderIds();
        } else alert('Failed to delete content.');
    });
}

document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeContentModal(); });
document.querySelector('.content-queue-container').addEventListener('wheel',e=>{ e.preventDefault(); e.currentTarget.scrollLeft+=e.deltaY*2; });
</script>

</body>
</html>
