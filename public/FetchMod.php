<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fetch Mod - Queue Laden</title>
    <style>
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        button {
            background: #668099;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin: 10px 5px;
        }
        button:hover {
            background: #556080;
        }
        #output {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            min-height: 100px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        .error {
            color: #e23c21;
        }
        .success {
            color: #2d7a2d;
        }
        h2 {
            color: #303b46;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Queue JSON Loader</h2>
    <p>Lade die aktuelle Queue von mod.php als JSON:</p>
    
    <button id="load">JSON laden</button>
    <button id="clear">Löschen</button>
    
    <div id="output"></div>
</div>

<script>
const outputDiv = document.getElementById('output');

document.getElementById('load').addEventListener('click', () => {
    outputDiv.innerHTML = '<span class="success">Lädt...</span>';
    
    fetch('mod.php?export=json', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP-Fehler: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        outputDiv.innerHTML = '';
        outputDiv.textContent = JSON.stringify(data, null, 2);
        console.log('Queue geladen:', data);
    })
    .catch(error => {
        outputDiv.innerHTML = '<span class="error">Fehler: ' + error.message + '</span>';
        console.error('Fehler beim Laden:', error);
    });
});

document.getElementById('clear').addEventListener('click', () => {
    outputDiv.innerHTML = '';
});
</script>

</body>
</html>