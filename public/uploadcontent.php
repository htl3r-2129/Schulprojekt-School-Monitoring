<?php
session_start();

// Composer Autoload
require __DIR__ . '/../vendor/autoload.php';
use Insi\Ssm\Auth;

$auth = new Auth();

// --- HANDLE AJAX REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    // Media Upload
    if ($action === 'upload') {
        if (!isset($_FILES['mediaFile'])) {
            echo json_encode(['success'=>false,'message'=>'Keine Datei hochgeladen']);
            exit;
        }
        $file = $_FILES['mediaFile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $allowedImages = ['jpg','jpeg','png','gif','webp'];
        $allowedVideos = ['mp4','webm','ogg','mov','m4v'];

        if (in_array($ext, $allowedImages)) {
            $type = 'image';
            $targetDir = __DIR__ . '/media/Images/';
        } elseif (in_array($ext, $allowedVideos)) {
            $type = 'video';
            $targetDir = __DIR__ . '/media/Videos/';
        } else {
            echo json_encode(['success'=>false,'message'=>'Dateityp nicht erlaubt']);
            exit;
        }

        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $url = 'media/' . ($type==='image'?'Images/':'Videos/') . $filename;
            echo json_encode(['success'=>true,'url'=>$url]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Fehler beim Verschieben der Datei']);
        }
        exit;
    }

    // Save Content
    if ($action === 'save') {
        $jsonFile = __DIR__ . '/content_source.json';
        if (!file_exists($jsonFile)) file_put_contents($jsonFile, json_encode([]));

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['title'])) {
            echo json_encode(['success'=>false,'message'=>'Fehlende Daten']);
            exit;
        }

        // --- MINIMAL-LENGTH VALIDATION ---
        if (strlen(trim($data['title'])) < 1) {
            echo json_encode(['success'=>false,'message'=>'Titel muss mindestens 1 Zeichen haben']);
            exit;
        }
        if (($data['type'] ?? '') === 'text' && strlen(trim($data['text'] ?? '')) < 1) {
            echo json_encode(['success'=>false,'message'=>'Text darf nicht leer sein']);
            exit;
        }

        $json = json_decode(file_get_contents($jsonFile), true);

        // --- CORRECT INCREMENT LOGIC ---
        $maxId = 0;
        foreach ($json as $entry) {
            $maxId = max($maxId, intval($entry['original_id']));
        }
        $newId = $maxId + 1;

        // --- TYPE LOGIC ---
        $type = '';
        if (($data['type'] ?? '') === 'media') {
            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $data['media'] ?? '')) $type = 'image';
            elseif (preg_match('/\.(mp4|webm|ogg|mov|m4v)$/i', $data['media'] ?? '')) $type = 'video';
        }

        $newEntry = [
            'original_id' => strval($newId),
            'title' => trim($data['title']),
            'type' => $type,
            'media' => $data['media'] ?? '',
            'approved' => false,
            'ProvidedBy' => htmlspecialchars($_SESSION['name'] ?? 'Moderator'),
            'text' => trim($data['text'] ?? '')
        ];

        $json[] = $newEntry;

        if (file_put_contents($jsonFile,json_encode($json,JSON_PRETTY_PRINT))) {
            echo json_encode(['success'=>true,'entry'=>$newEntry]);
        } else {
            echo json_encode(['success'=>false,'message'=>'Fehler beim Speichern']);
        }
        exit;
    }
}
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
<div class="user-role">Administrator</div>
<div class="user-name-row">
<span class="user-name"><?= htmlspecialchars($_SESSION['name']); ?></span>
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

<!-- MEDIA -->
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
<input id="mediaUrl" type="text" placeholder="Bild-URL oder Dateiname" autocomplete="off" required readonly>
<label for="mediaFile" class="btn file-selector">Upload</label>
<input id="mediaFile" type="file" accept="image/*,video/*" class="sr-only">
</div>
</div>
<div class="form-row">
<label for="mediaExtra">Zusätzlicher Text:</label>
<textarea id="mediaExtra" rows="4" placeholder="Text"></textarea>
</div>
<div class="form-actions">
<button type="button" class="btn accent send" id="sendMedia" disabled>Send Content</button>
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

<!-- TEXT -->
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
<button type="button" class="btn accent send" id="sendText" disabled>Send Content</button>
<button type="button" class="btn primary clear" id="clearText">Clear</button>
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
const creationSelect=document.getElementById('creationSelect');
const mediaSection=document.getElementById('mediaSection');
const textSection=document.getElementById('textSection');

const mediaFileInput=document.getElementById('mediaFile');
const mediaUrlInput=document.getElementById('mediaUrl');
const mediaTitleInput=document.getElementById('mediaTitle');
const mediaExtraInput=document.getElementById('mediaExtra');
const clearMediaBtn=document.getElementById('clearMedia');
const sendMediaBtn=document.getElementById('sendMedia');

const textTitleInput=document.getElementById('textTitle');
const textExtraInput=document.getElementById('textExtra');
const clearTextBtn=document.getElementById('clearText');
const sendTextBtn=document.getElementById('sendText');

// Show sections based on select
creationSelect.addEventListener('change',()=>{ 
    if(creationSelect.value==='media'){mediaSection.hidden=false;textSection.hidden=true;}
    else if(creationSelect.value==='text'){mediaSection.hidden=true;textSection.hidden=false;}
    else{mediaSection.hidden=true;textSection.hidden=true;}
});

// Preview render function
function renderInto(target,src,isVideo){
    target.innerHTML='';
    if(isVideo){
        const video=document.createElement('video');
        video.src=src; video.muted=false; video.controls=true; video.playsInline=true;
        video.style.maxWidth='100%'; video.style.maxHeight='100%'; video.style.objectFit='contain';
        target.appendChild(video);
    } else{
        const img=document.createElement('img');
        img.src=src; img.alt='Preview'; img.style.maxWidth='100%'; img.style.maxHeight='100%'; img.style.objectFit='contain';
        target.appendChild(img);
    }
}

// MEDIA EVENTS
mediaTitleInput.addEventListener('input', validateMediaForm);
mediaExtraInput.addEventListener('input', validateMediaForm);
mediaFileInput.addEventListener('change',async function(){ 
    const file=this.files[0]; if(!file) return;
    const formData=new FormData(); formData.append('mediaFile',file);
    const response=await fetch('?action=upload',{method:'POST',body:formData});
    const result=await response.json();
    if(result.success){
        mediaUrlInput.value=result.url;
        mediaUrlInput.readOnly=true;
        renderInto(document.getElementById('previewMedia'),result.url,file.type.startsWith('video'));
        validateMediaForm();
    } else{alert('Upload fehlgeschlagen: '+result.message);}
});
mediaUrlInput.addEventListener('input', validateMediaForm);

// TEXT EVENTS
textTitleInput.addEventListener('input', validateTextForm);
textExtraInput.addEventListener('input', validateTextForm);

// CLEAR BUTTONS
clearMediaBtn.addEventListener('click', ()=>{
    mediaTitleInput.value='';
    mediaExtraInput.value='';
    mediaFileInput.value='';
    mediaUrlInput.value='';
    mediaUrlInput.readOnly=false;
    document.getElementById('previewTitle').textContent='';
    document.getElementById('previewExtra').textContent='';
    document.getElementById('previewMedia').innerHTML='<span class="preview-placeholder">PREVIEW</span>';
    sendMediaBtn.disabled=true;
});
clearTextBtn.addEventListener('click', ()=>{
    textTitleInput.value='';
    textExtraInput.value='';
    document.getElementById('textPreviewTitle').textContent='';
    document.getElementById('textPreviewExtra').textContent='';
    sendTextBtn.disabled=true;
});

// VALIDATION
function validateMediaForm(){
    const title = mediaTitleInput.value.trim();
    const extra = mediaExtraInput.value.trim();
    const mediaFilled = mediaUrlInput.value.trim().length>0;
    sendMediaBtn.disabled = !(title.length>=1 && mediaFilled);

    document.getElementById('previewTitle').textContent = title;
    const previewExtra = document.getElementById('previewExtra');
    const previewSep = document.getElementById('previewSeparator');
    document.getElementById('previewExtra').textContent = extra;

    // Show separator only when there is a title; size it to the larger of title/extra
    if(previewSep){
        if(title.length>0){
            previewSep.hidden = false;
            setTimeout(()=>{
                const titleEl = document.getElementById('previewTitle');
                const titleW = titleEl ? titleEl.offsetWidth : 0;
                const extraW = previewExtra ? previewExtra.offsetWidth : 0;
                previewSep.style.width = Math.max(titleW, extraW)+'px';
            },0);
        } else {
            previewSep.hidden = true;
        }
    }
    // Hide extra text element if empty
    previewExtra.style.display = extra && extra.length>0 ? '' : 'none';
}
function validateTextForm(){
    const title = textTitleInput.value.trim();
    const text = textExtraInput.value.trim();
    sendTextBtn.disabled = !(title.length>=1 && text.length>=1);

    document.getElementById('textPreviewTitle').textContent = title;
    const textPreviewExtra = document.getElementById('textPreviewExtra');
    const textPreviewSep = document.getElementById('textPreviewSeparator');
    textPreviewExtra.textContent = text;

    // Show separator for text preview only when there is a title; size to larger of title/extra
    if(textPreviewSep){
        if(title.length>0){
            textPreviewSep.hidden = false;
            setTimeout(()=>{
                const tEl = document.getElementById('textPreviewTitle');
                const tW = tEl ? tEl.offsetWidth : 0;
                const eW = textPreviewExtra ? textPreviewExtra.offsetWidth : 0;
                textPreviewSep.style.width = Math.max(tW, eW)+'px';
            },0);
        } else {
            textPreviewSep.hidden = true;
        }
    }
    textPreviewExtra.style.display = text && text.length>0 ? '' : 'none';
}

// SEND CONTENT
sendMediaBtn.addEventListener('click',()=>sendContent('media'));
sendTextBtn.addEventListener('click',()=>sendContent('text'));

async function sendContent(type){
    let title, text, media='';
    if(type==='media'){title=mediaTitleInput.value.trim(); text=mediaExtraInput.value.trim(); media=mediaUrlInput.value.trim();}
    else {title=textTitleInput.value.trim(); text=textExtraInput.value.trim();}

    const response = await fetch('?action=save',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({title,type,media,text})
    });
    const result = await response.json();
    if(result.success){
        alert('Content erfolgreich gespeichert!');
        if(type==='media') clearMediaBtn.click();
        else clearTextBtn.click();
    } else {
        alert('Fehler: '+result.message);
    }
}
</script>
</body>
</html>
