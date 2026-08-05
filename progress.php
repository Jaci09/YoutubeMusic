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
        
        // Extraer las últimas 3 líneas del log para mostrar el error exacto
        $lines = array_filter(explode("\n", trim($logContent)));
        $lastLines = array_slice($lines, -3);
        $errorMsg = !empty($lastLines) ? implode(' | ', $lastLines) : 'Error desconocido al ejecutar yt-dlp.';

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
    $percent = !empty($matches[1]) ? floatval(end($matches[1])) : 10;

    $statusMsg = 'Descargando audio... ' . round($percent) . '%';
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

echo json_encode(['progress' => 5, 'status' => 'downloading', 'message' => 'Iniciando conexión...']);
exit;
?>
