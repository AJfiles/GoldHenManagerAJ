<?php
// Worker interno: se ejecuta desde CLI para no bloquear el servidor HTTP que sirve el PKG.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
error_reporting(0);
set_time_limit(0);

$id = strtolower((string)($argv[1] ?? ''));
if (!preg_match('/^[a-f0-9]{24}$/', $id)) exit(2);
$project = realpath(__DIR__ . '/..') ?: __DIR__;
$prefix = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ghm_rpi_' . substr(hash('sha256', $project), 0, 16);
$jobFile = $prefix . '_job_' . $id . '.json';
$registryFile = $prefix . '.json';
$job = is_file($jobFile) ? json_decode((string)@file_get_contents($jobFile), true) : null;
$registry = is_file($registryFile) ? json_decode((string)@file_get_contents($registryFile), true) : null;
$entry = is_array($job) ? ($registry['files'][$job['file_id'] ?? ''] ?? null) : null;
$update = static function (array $job) use ($jobFile): void {
    $job['updated'] = time();
    @file_put_contents($jobFile, json_encode($job, JSON_UNESCAPED_SLASHES), LOCK_EX);
};

if (!is_array($job) || !is_array($entry)) exit(3);
$path = realpath((string)($entry['path'] ?? ''));
if ($path === false || !is_file($path) || !is_readable($path)
    || basename($path) !== (string)($entry['name'] ?? '')
    || (int)@filesize($path) !== (int)($entry['size'] ?? -1)
    || (int)@filemtime($path) !== (int)($entry['mtime'] ?? -1)) {
    $job['state'] = 'error'; $job['message'] = 'El PKG cambió o ya no está disponible.'; $update($job); exit(4);
}

$job['state'] = 'sending';
$job['message'] = 'Enviando la orden al instalador de PS4.';
$update($job);
$fileUrl = 'http://' . $job['phone_ip'] . ':' . (int)$job['server_port'] . '/api/rpi_file.php?id=' . rawurlencode((string)$job['file_id']) . '&job=' . rawurlencode($id);
$endpoint = 'http://' . $job['host_ip'] . ':' . (int)$job['rpi_port'] . '/api/install';
$payload = json_encode(['type' => 'direct', 'packages' => [$fileUrl]], JSON_UNESCAPED_SLASHES);
$ch = @curl_init($endpoint);
if (!$ch) {
    $job['state'] = 'error'; $job['message'] = 'PHP no pudo preparar la solicitud HTTP hacia RPI.'; $update($job); exit(5);
}
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', 'Content-Length: ' . strlen((string)$payload)],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 8,
    // RPI puede mantener la solicitud abierta durante su fase inicial; el
    // worker independiente deja libre el servidor PHP para entregar el PKG.
    CURLOPT_TIMEOUT => 900,
    CURLOPT_NOSIGNAL => true,
]);
$body = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = (string)curl_error($ch);
curl_close($ch);
$decoded = json_decode((string)$body, true);
$ok = $body !== false && $httpCode >= 200 && $httpCode < 300
    && !(is_array($decoded) && ($decoded['status'] ?? '') === 'error');
$job['state'] = $ok ? 'success' : 'error';
$job['http_code'] = $httpCode;
$job['curl_error'] = $curlError;
$responseText = trim(strip_tags((string)$body));
$job['response'] = function_exists('mb_substr') ? mb_substr($responseText, 0, 500, 'UTF-8') : substr($responseText, 0, 500);
$job['message'] = $ok
    ? 'RPI aceptó la orden. Revisa el progreso de descarga/instalación en Package Installer.'
    : ($httpCode > 0
        ? "RPI respondió HTTP $httpCode. " . ($job['response'] !== '' ? $job['response'] : 'Revisa el estado de Package Installer.')
        : 'No llegó respuesta HTTP desde RPI. ' . ($curlError !== '' ? $curlError : 'Comprueba el puerto, Package Installer y la conexión.'));
$update($job);
