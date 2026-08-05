<?php
header('Content-Type: application/json');

$downloadId = $_GET['download_id'] ?? '';

if (empty($downloadId)) {
    echo json_encode(['progress' => 0, 'status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$downloadDir = __DIR__ . '/temp_downloads';
$logFile  = $downloadDir . '/' . $downloadId . '.log';
$doneFile = $downloadDir . '/' . $downloadId . '.done';

if (file_exists($doneFile)) {
    $result = trim(file_get_contents($doneFile));
    
    if ($result === 'ERROR') {
        $logContent = file_exists($logFile) ? file_get_contents($logFile) : '';
        
        // Extraer la causa exacta devuelta por yt-dlp
        preg_match_all('/ERROR:\s*(.*)/i', $logContent, $matches);
        $errorMsg = !empty($matches[1]) ? end($matches[1]) : 'Error al procesar el audio en el servidor.';

        @unlink($logFile);
        @unlink($doneFile);

        echo json_encode([
            'progress' => 0,
            'status' => 'error',
            'message' => $errorMsg
        ]);
        exit;
    }

    echo json_encode([
        'progress' => 100,
        'status' => 'finished',
        'message' => '¡Audio procesado! Descargando archivo MP3...'
    ]);
    exit;
}

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);

    preg_match_all('/\[download\]\s+([\d\.]+)%/i', $content, $matches);
    $percent = !empty($matches[1]) ? floatval(end($matches[1])) : 5;

    $statusMsg = 'Descargando audio de alta calidad... ' . round($percent) . '%';
    if ($percent >= 98) {
        $statusMsg = 'Incrustando portada HD y metadatos...';
    }

    echo json_encode([
        'progress' => $percent,
        'status' => 'downloading',
        'message' => $statusMsg
    ]);
    exit;
}

echo json_encode(['progress' => 2, 'status' => 'downloading', 'message' => 'Iniciando servidor...']);
exit;
?>
