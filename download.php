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

if (strpos($url, 'music.youtube.com') === false && strpos($url, 'youtube.com') === false) {
    echo json_encode([
        'success' => false, 
        'error' => 'Solo se permiten enlaces pertenecientes a YouTube Music.'
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

// Ruta absoluta donde Docker instala yt-dlp en Linux
$ytdlpBin = '/usr/local/bin/yt-dlp';
if (!file_exists($ytdlpBin)) {
    $ytdlpBin = 'yt-dlp'; // Fallback
}

$cookiesFile = __DIR__ . '/cookies.txt';
$cookieFlag  = (file_exists($cookiesFile) && filesize($cookiesFile) > 0) 
    ? '--cookies ' . escapeshellarg($cookiesFile) . ' ' 
    : '';

// Inicializar el log con marca de tiempo
file_put_contents($logFile, "[download] Conectando con los servidores de YouTube...\n");

$cmdArgs = '--no-playlist --newline --no-warnings ' .
           $cookieFlag .
           '-f "bestaudio/best" ' .
           '-x --audio-format mp3 --audio-quality 0 ' .
           '--postprocessor-args "ExtractAudio:-b:a 320k" ' .
           '--embed-thumbnail --convert-thumbnails jpg ' .
           '--embed-metadata ' .
           '-o ' . escapeshellarg($outTemplate) . ' ' .
           escapeshellarg($url);

// Subshell robusto con ruta absoluta para Linux
$innerCmd = sprintf(
    '%s %s >> %s 2>&1 && echo OK > %s || echo ERROR > %s',
    $ytdlpBin,
    $cmdArgs,
    escapeshellarg($logFile),
    escapeshellarg($doneFile),
    escapeshellarg($doneFile)
);

$bgCmd = 'nohup sh -c ' . escapeshellarg($innerCmd) . ' > /dev/null 2>&1 &';

exec($bgCmd);

echo json_encode([
    'success' => true,
    'download_id' => $downloadId
]);
exit;
?>
