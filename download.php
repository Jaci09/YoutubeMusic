<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
$url = trim($data['url'] ?? $_POST['url'] ?? '');

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'URL no proporcionada']);
    exit;
}

if (strpos($url, 'music.youtube.com') === false) {
    echo json_encode([
        'success' => false, 
        'error' => 'Solo se permiten enlaces pertenecientes a YouTube Music (music.youtube.com).'
    ]);
    exit;
}

$downloadDir = __DIR__ . '/temp_downloads';
if (!file_exists($downloadDir)) {
    mkdir($downloadDir, 0777, true);
}

$downloadId = uniqid('dl_', true);

$logFile  = $downloadDir . '/' . $downloadId . '.log';
$doneFile = $downloadDir . '/' . $downloadId . '.done';

$outTemplate = $downloadDir . '/' . $downloadId . '_FINAL_%(title)s.%(ext)s';

// Detección automática de cookies.txt si existe en el proyecto
$cookiesFile = __DIR__ . '/cookies.txt';
$cookieFlag  = file_exists($cookiesFile) ? '--cookies ' . escapeshellarg($cookiesFile) . ' ' : '';

// Clientes tv_embedded y web_embedded para eludir bloqueos de IP
$cmdArgs = '--no-playlist --newline --no-warnings ' .
           $cookieFlag .
           '--extractor-args "youtube:player_client=tv_embedded,web_embedded,android" ' .
           '-x --audio-format mp3 --audio-quality 0 ' .
           '--postprocessor-args "ExtractAudio:-b:a 320k" ' .
           '--embed-thumbnail --convert-thumbnails jpg ' .
           '--embed-metadata ' .
           '-o ' . escapeshellarg($outTemplate) . ' ' .
           escapeshellarg($url);

$cmd = "(yt-dlp " . $cmdArgs . " > " . escapeshellarg($logFile) . " 2>&1 && echo OK > " . escapeshellarg($doneFile) . " || echo ERROR > " . escapeshellarg($doneFile) . ") > /dev/null 2>&1 &";

exec($cmd);

echo json_encode([
    'success' => true,
    'download_id' => $downloadId
]);
exit;
?>
