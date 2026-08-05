<?php
error_reporting(0);
ini_set('display_errors', 0);

$downloadId = $_GET['download_id'] ?? '';

if (empty($downloadId)) {
    die("ID no especificado.");
}

$downloadDir = __DIR__ . '/temp_downloads';
$files = glob($downloadDir . '/' . $downloadId . '_FINAL_*.mp3');

if (empty($files)) {
    die("El archivo de audio aún no está listo.");
}

$filePath = $files[0];

if (!file_exists($filePath)) {
    die("El archivo no existe.");
}

$rawFilename = basename($filePath);
$cleanFilename = preg_replace('/^dl_[a-f0-9\.]+_FINAL_/', '', $rawFilename);

if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Description: File Transfer');
header('Content-Type: audio/mpeg');
header('Content-Disposition: attachment; filename="' . addslashes($cleanFilename) . '"; filename*=UTF-8\'\'' . rawurlencode($cleanFilename));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);

// Limpieza completa tras entregar la descarga al teléfono
@unlink($filePath);
@unlink($downloadDir . '/' . $downloadId . '.log');
@unlink($downloadDir . '/' . $downloadId . '.done');

exit;
?>