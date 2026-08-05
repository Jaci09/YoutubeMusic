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

$downloadDir = __DIR__ . '/temp_downloads';
if (!file_exists($downloadDir)) {
    mkdir($downloadDir, 0777, true);
}

$downloadId = uniqid('dl_', true);

$logFile  = $downloadDir . '/' . $downloadId . '.log';
$doneFile = $downloadDir . '/' . $downloadId . '.done';
$shFile   = $downloadDir . '/' . $downloadId . '_runner.sh';
$outTemplate = $downloadDir . '/' . $downloadId . '_FINAL_%(title)s.%(ext)s';

$cookiesFile = __DIR__ . '/cookies.txt';
$cookieFlag  = (file_exists($cookiesFile) && filesize($cookiesFile) > 0) 
    ? '--cookies ' . escapeshellarg($cookiesFile) . ' ' 
    : '';

// Generamos un script de ejecución limpia para Linux
$shContent  = "#!/bin/sh\n";
$shContent .= sprintf(
    "/usr/local/bin/yt-dlp --no-playlist --newline --no-warnings %s-f \"bestaudio/best\" -x --audio-format mp3 --audio-quality 0 --postprocessor-args \"ExtractAudio:-b:a 320k\" --embed-thumbnail --convert-thumbnails jpg --embed-metadata -o %s %s > %s 2>&1\n",
    $cookieFlag,
    escapeshellarg($outTemplate),
    escapeshellarg($url),
    escapeshellarg($logFile)
);
$shContent .= "if [ $? -eq 0 ]; then\n";
$shContent .= sprintf("  echo OK > %s\n", escapeshellarg($doneFile));
$shContent .= "else\n";
$shContent .= sprintf("  echo ERROR > %s\n", escapeshellarg($doneFile));
$shContent .= "fi\n";
$shContent .= sprintf("rm -f %s\n", escapeshellarg($shFile));

file_put_contents($shFile, $shContent);
chmod($shFile, 0755);

// Ejecución desacoplada en segundo plano
pclose(popen(escapeshellarg($shFile) . " > /dev/null 2>&1 &", "r"));

echo json_encode([
    'success' => true,
    'download_id' => $downloadId
]);
exit;
?>
