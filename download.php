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
        'error' => 'Solo se permiten enlaces de YouTube Music.'
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

// Crear el archivo log inmediatamente para desbloquear progress.php
file_put_contents($logFile, "[download] 5% Conectando con los servidores de YouTube...\n");

$cookiesFile = __DIR__ . '/cookies.txt';
$cookieFlag  = file_exists($cookiesFile) ? '--cookies ' . escapeshellarg($cookiesFile) . ' ' : '';

// Comando simplificado y directo
$cmdArgs = '--no-playlist --newline --no-warnings ' .
           $cookieFlag .
           '-f "bestaudio/best" ' .
           '-x --audio-format mp3 --audio-quality 0 ' .
           '--postprocessor-args "ExtractAudio:-b:a 320k" ' .
           '--embed-thumbnail --convert-thumbnails jpg ' .
           '--embed-metadata ' .
           '-o ' . escapeshellarg($outTemplate) . ' ' .
           escapeshellarg($url);

$execCmd = "yt-dlp " . $cmdArgs . " >> " . escapeshellarg($logFile) . " 2>&1";

// Ejecutar en segundo plano mediante nohup en Linux
system("nohup " . $execCmd . " > /dev/null 2>&1 && echo OK > " . escapeshellarg($doneFile) . " || echo ERROR > " . escapeshellarg($doneFile) . " &");

echo json_encode([
    'success' => true,
    'download_id' => $downloadId
]);
exit;
?>
