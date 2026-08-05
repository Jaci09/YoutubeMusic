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

// Si el proceso de Windows terminó completamente
if (file_exists($doneFile)) {
    $result = trim(file_get_contents($doneFile));
    
    if ($result === 'ERROR') {
        @unlink($logFile);
        @unlink($doneFile);
        echo json_encode([
            'progress' => 0,
            'status' => 'error',
            'message' => 'Error procesando el audio en el servidor.'
        ]);
        exit;
    }

    echo json_encode([
        'progress' => 100,
        'status' => 'finished',
        'message' => '¡Audio procesado! Descargando a tu dispositivo...'
    ]);
    exit;
}

// Seguimiento del progreso visual mientras trabaja FFmpeg
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);

    preg_match_all('/\[download\]\s+([\d\.]+)%/i', $content, $matches);
    $percent = !empty($matches[1]) ? floatval(end($matches[1])) : 5;

    $statusMsg = 'Descargando audio... ' . round($percent) . '%';
    if ($percent >= 99) {
        $statusMsg = 'Incrustando portada HD y metadatos (Espere un momento)...';
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