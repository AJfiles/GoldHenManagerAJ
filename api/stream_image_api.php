<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 - API: STREAMING Y CACHÉ DE CAPTURAS
 * DEVELOPED By SeBaS - RUTA: api/stream_image_api.php
 * ====================================================================
 */
error_reporting(0);
@set_time_limit(120);

$ip = $_GET['ip'] ?? '';
$path = $_GET['path'] ?? '';
$port = 2121;

if (!$ip || !$path) { http_response_code(400); exit; }

$cache_dir = __DIR__ . '/../user/cache/capturas';
if (!file_exists($cache_dir)) { @mkdir($cache_dir, 0777, true); }

// 🔥 FIX: Blindaje para que Android ignore las capturas de la Play
@file_put_contents($cache_dir . '/.nomedia', ''); 

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$hash = md5($path);
$cache_file = $cache_dir . '/' . $hash . '.' . $ext;

header('Content-Type: image/' . ($ext === 'png' ? 'png' : 'jpeg'));
header('Cache-Control: max-age=31536000');

if (file_exists($cache_file) && filesize($cache_file) > 0) {
    readfile($cache_file);
    exit;
}

$ch = curl_init();
$ftp_url = "ftp://$ip:$port" . implode('/', array_map('rawurlencode', explode('/', $path)));
curl_setopt($ch, CURLOPT_URL, $ftp_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$data = curl_exec($ch);
curl_close($ch);

if ($data && strlen($data) > 0) {
    file_put_contents($cache_file, $data);
    echo $data;
} else {
    http_response_code(404);
}
?>
