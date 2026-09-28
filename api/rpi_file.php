<?php
// Sirve exclusivamente PKG seleccionados en el escaneo RPI reciente.
error_reporting(0);
ini_set('display_errors', '0');
@ini_set('zlib.output_compression', '0');
set_time_limit(0);

$id = strtolower((string)($_GET['id'] ?? ''));
if (!preg_match('/^[a-f0-9]{32,64}$/', $id)) {
    http_response_code(400);
    exit;
}

$project = realpath(__DIR__ . '/..') ?: __DIR__;
$registry_file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ghm_rpi_' . substr(hash('sha256', $project), 0, 16) . '.json';
$registry = is_file($registry_file) ? json_decode((string)@file_get_contents($registry_file), true) : null;
$entry = $registry['files'][$id] ?? null;
$path = is_array($entry) ? realpath($entry['path'] ?? '') : false;
$name = is_array($entry) ? (string)($entry['name'] ?? '') : '';

if (!is_array($entry) || $path === false || !is_file($path) || !is_readable($path)
    || dirname($path) !== dirname((string)($entry['path'] ?? ''))
    || basename($path) !== $name || !preg_match('/\.pkg$/i', $name)
    || (int)@filesize($path) !== (int)($entry['size'] ?? -1)
    || (int)@filemtime($path) !== (int)($entry['mtime'] ?? -1)
    || (int)($entry['created'] ?? 0) < time() - 21600) {
    http_response_code(404);
    exit;
}

$size = (int)filesize($path);
$start = 0;
$end = max(0, $size - 1);
$status = 200;
$range = $_SERVER['HTTP_RANGE'] ?? '';
if ($range !== '') {
    if (strpos($range, ',') !== false || !preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $match) || $size === 0) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }
    if ($match[1] === '') {
        $suffix = (int)$match[2];
        if ($suffix <= 0) {
            header('Content-Range: bytes */' . $size);
            http_response_code(416);
            exit;
        }
        $start = max(0, $size - $suffix);
    } else {
        $start = (int)$match[1];
        if ($match[2] !== '') $end = min($end, (int)$match[2]);
    }
    if ($start >= $size || $end < $start) {
        header('Content-Range: bytes */' . $size);
        http_response_code(416);
        exit;
    }
    $status = 206;
}

$length = $size === 0 ? 0 : $end - $start + 1;
$download_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
if ($download_name === '' || !preg_match('/\.pkg$/i', $download_name)) $download_name = 'package.pkg';
$pathName = (string)($_GET['path_name'] ?? '');
if ($pathName !== '' && !hash_equals($download_name, $pathName)) {
    http_response_code(404);
    exit;
}
http_response_code($status);
header('Content-Type: application/octet-stream');
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);
header('Content-Disposition: attachment; filename="' . $download_name . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
if ($status === 206) header("Content-Range: bytes $start-$end/$size");
$requestMethod = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');

$jobId = strtolower((string)($_GET['job'] ?? ''));
$progressFile = '';
if (preg_match('/^[a-f0-9]{24}$/', $jobId)) {
    $project = realpath(__DIR__ . '/..') ?: __DIR__;
    $prefix = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ghm_rpi_' . substr(hash('sha256', $project), 0, 16);
    $access = [
        'at' => gmdate('c'), 'remote' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
        'method' => $requestMethod, 'range' => $range,
        'bytes' => $length, 'file' => $download_name,
    ];
    @file_put_contents($prefix . '_job_' . $jobId . '.json.access', json_encode($access, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
    $progressFile = $prefix . '_job_' . $jobId . '.json.progress';
    if ($requestMethod !== 'HEAD') {
        $progressHandle = @fopen($progressFile, 'c+');
        if ($progressHandle) {
            if (flock($progressHandle, LOCK_EX)) {
                $contents = stream_get_contents($progressHandle);
                $progress = json_decode((string)$contents, true);
                if (!is_array($progress)) $progress = ['bytes_served' => 0, 'bytes_total' => $size, 'requests' => 0, 'updated' => time()];
                $progress['bytes_total'] = $size;
                $progress['requests'] = (int)($progress['requests'] ?? 0) + 1;
                $progress['updated'] = time();
                ftruncate($progressHandle, 0); rewind($progressHandle);
                fwrite($progressHandle, json_encode($progress, JSON_UNESCAPED_SLASHES));
                fflush($progressHandle); flock($progressHandle, LOCK_UN);
            }
            fclose($progressHandle);
        }
    }
}
if ($requestMethod === 'HEAD') exit;

$handle = @fopen($path, 'rb');
if ($handle === false || ($start > 0 && fseek($handle, $start) !== 0)) {
    if (is_resource($handle)) fclose($handle);
    http_response_code(500);
    exit;
}
$remaining = $length;
$pendingProgressBytes = 0;
while ($remaining > 0 && !feof($handle)) {
    $chunk = fread($handle, min(1024 * 1024, $remaining));
    if ($chunk === false || $chunk === '') break;
    echo $chunk;
    $remaining -= strlen($chunk);
    $pendingProgressBytes += strlen($chunk);
    if ($progressFile !== '' && ($pendingProgressBytes >= 8 * 1024 * 1024 || $remaining === 0)) {
        $progressHandle = @fopen($progressFile, 'c+');
        if ($progressHandle) {
            if (flock($progressHandle, LOCK_EX)) {
                $contents = stream_get_contents($progressHandle);
                $progress = json_decode((string)$contents, true);
                if (!is_array($progress)) $progress = ['bytes_served' => 0, 'bytes_total' => $size, 'requests' => 1];
                $progress['bytes_served'] = (int)($progress['bytes_served'] ?? 0) + $pendingProgressBytes;
                $progress['updated'] = time();
                ftruncate($progressHandle, 0); rewind($progressHandle);
                fwrite($progressHandle, json_encode($progress, JSON_UNESCAPED_SLASHES));
                fflush($progressHandle); flock($progressHandle, LOCK_UN);
            }
            fclose($progressHandle);
        }
        $pendingProgressBytes = 0;
    }
    flush();
}
fclose($handle);
