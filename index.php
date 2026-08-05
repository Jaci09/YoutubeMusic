<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Music MP3 Downloader</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { 
            background-color: #fce4ec; 
            color: #4a3e43; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            padding: 20px; 
        }
        .container { 
            background: #ffffff; 
            width: 100%; 
            max-width: 520px; 
            padding: 30px; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(236, 128, 160, 0.2); 
            text-align: center; 
            border: 1px solid #f8bbd0;
        }
        h1 { 
            font-size: 1.6rem; 
            margin-bottom: 12px; 
            color: #ec407a; 
        }
        .notice { 
            font-size: 0.85rem; 
            color: #785a67; 
            margin-bottom: 20px; 
            background: #fff0f5; 
            padding: 10px 14px; 
            border-radius: 8px; 
            border-left: 4px solid #f48fb1; 
            text-align: left; 
            line-height: 1.4;
        }
        input[type="text"] { 
            width: 100%; 
            padding: 12px 14px; 
            border-radius: 8px; 
            border: 1px solid #f8bbd0; 
            background: #fffafc; 
            color: #4a3e43; 
            margin-bottom: 15px; 
            outline: none; 
            font-size: 0.95rem; 
            transition: border-color 0.2s;
        }
        input[type="text"]:focus { 
            border-color: #ec407a; 
            background: #ffffff;
        }
        button { 
            width: 100%; 
            padding: 12px; 
            border: none; 
            border-radius: 8px; 
            background: #f48fb1; 
            color: #ffffff; 
            font-size: 1rem; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.2s, transform 0.1s; 
        }
        button:hover { 
            background: #ec407a; 
        }
        button:active {
            transform: scale(0.99);
        }
        button:disabled { 
            background: #e0c8d1; 
            cursor: not-allowed; 
        }
        
        .progress-box { margin-top: 20px; display: none; }
        .progress-bar-bg { 
            width: 100%; 
            background: #f8bbd0; 
            height: 12px; 
            border-radius: 6px; 
            overflow: hidden; 
            margin-bottom: 10px; 
        }
        .progress-bar-fill { 
            width: 0%; 
            height: 100%; 
            background: #ec407a; 
            transition: width 0.3s ease; 
        }
        .status-text { 
            font-size: 0.9rem; 
            color: #886b78; 
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>YouTube Music MP3</h1>
    <div class="notice">
        <strong>⚠️ Importante:</strong> Solo se procesan enlaces oficiales de <strong>YouTube Music</strong> (<code>music.youtube.com</code>).
    </div>

    <input type="text" id="musicUrl" placeholder="Pega tu enlace de https://music.youtube.com/..." />
    <button id="downloadBtn" onclick="startDownload()">Descargar MP3 (320kbps)</button>

    <div class="progress-box" id="progressBox">
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progressBar"></div>
        </div>
        <div class="status-text" id="statusText">Iniciando...</div>
    </div>
</div>

<script>
let pollInterval = null;

async function startDownload() {
    const urlInput = document.getElementById('musicUrl');
    const btn = document.getElementById('downloadBtn');
    const progressBox = document.getElementById('progressBox');
    const progressBar = document.getElementById('progressBar');
    const statusText = document.getElementById('statusText');

    const url = urlInput.value.trim();

    if (!url.includes('music.youtube.com')) {
        alert('Error: Solo se aceptan enlaces de YouTube Music (music.youtube.com).');
        return;
    }

    btn.disabled = true;
    urlInput.disabled = true;
    progressBox.style.display = 'block';
    progressBar.style.width = '5%';
    statusText.innerText = 'Verificando enlace de YouTube Music...';

    try {
        const response = await fetch('download.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url: url })
        });

        const data = await response.json();

        if (data.success && data.download_id) {
            checkProgress(data.download_id);
        } else {
            showError(data.error || 'No se pudo iniciar la descarga.');
        }
    } catch (err) {
        showError('Error de conexión con el servidor.');
    }
}

function checkProgress(downloadId) {
    const progressBar = document.getElementById('progressBar');
    const statusText = document.getElementById('statusText');

    pollInterval = setInterval(async () => {
        try {
            const response = await fetch(`progress.php?download_id=${downloadId}`);
            const data = await response.json();

            if (data.status === 'downloading') {
                progressBar.style.width = `${Math.max(data.progress, 5)}%`;
                statusText.innerText = data.message;
            } else if (data.status === 'finished') {
                clearInterval(pollInterval);
                progressBar.style.width = '100%';
                statusText.innerText = data.message;
                
                window.location.href = `get_file.php?download_id=${downloadId}`;
                setTimeout(resetUI, 4000);
            } else if (data.status === 'error') {
                clearInterval(pollInterval);
                showError(data.message);
            }
        } catch (err) {
            clearInterval(pollInterval);
            showError('Error consultando el progreso.');
        }
    }, 1000);
}

function showError(msg) {
    const statusText = document.getElementById('statusText');
    statusText.innerText = msg;
    statusText.style.color = '#d81b60';
    setTimeout(resetUI, 4000);
}

function resetUI() {
    document.getElementById('downloadBtn').disabled = false;
    document.getElementById('musicUrl').disabled = false;
    document.getElementById('musicUrl').value = '';
    document.getElementById('progressBox').style.display = 'none';
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('statusText').style.color = '#886b78';
}
</script>

</body>
</html>