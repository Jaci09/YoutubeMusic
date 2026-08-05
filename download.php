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

// En Linux no se usa el doble %, se pasa %(title)s directo
$outTemplate = $downloadDir . '/' . $downloadId . '_FINAL_%(title)s.%(ext)s';

$cmdArgs = '--no-playlist --newline --no-warnings ' .
           '--extractor-args "youtube:player_client=android,web" ' .
           '-x --audio-format mp3 --audio-quality 0 ' .
           '--postprocessor-args "ExtractAudio:-b:a 320k" ' .
           '--embed-thumbnail --convert-thumbnails jpg ' .
           '--embed-metadata ' .
           '-o ' . escapeshellarg($outTemplate) . ' ' .
           escapeshellarg($url);

// Ejecución en segundo plano nativa en Linux (& al final e independizada)
$cmd = "(yt-dlp " . $cmdArgs . " > " . escapeshellarg($logFile) . " 2>&1 && echo OK > " . escapeshellarg($doneFile) . " || echo ERROR > " . escapeshellarg($doneFile) . ") > /dev/null 2>&1 &";

exec($cmd);

echo json_encode([
    'success' => true,
    'download_id' => $downloadId
]);
exit;
?>