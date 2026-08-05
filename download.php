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

// Parámetros con clientes ios,mweb para evasión de bloqueos en IPs de la nube
$cmdArgs = '--no-playlist --newline --no-warnings ' .
           '--extractor-args "youtube:player_client=ios,mweb,android" ' .
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
