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
$safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$entry['name']);
if ($safeName === '' || !preg_match('/\.pkg$/i', $safeName)) $safeName = 'package.pkg';
$fileUrl = 'http://' . $job['phone_ip'] . ':' . (int)$job['server_port'] . '/rpi/' . rawurlencode($id) . '/' . rawurlencode((string)$job['file_id']) . '/' . $safeName;
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
    && !(is_array($decoded) && in_array(strtolower((string)($decoded['status'] ?? '')), ['error', 'fail'], true));
$job['state'] = $ok ? 'success' : 'error';
$job['http_code'] = $httpCode;
$job['curl_error'] = $curlError;
$responseText = trim(strip_tags((string)$body));
$job['response'] = function_exists('mb_substr') ? mb_substr($responseText, 0, 500, 'UTF-8') : substr($responseText, 0, 500);
$job['message'] = $ok
    ? 'RPI aceptó la orden. Consultando el progreso de descarga e instalación.'
    : ($httpCode > 0
        ? "RPI respondió HTTP $httpCode. " . ($job['response'] !== '' ? $job['response'] : 'Revisa el estado de Package Installer.')
        : 'No llegó respuesta HTTP desde RPI. ' . ($curlError !== '' ? $curlError : 'Comprueba el puerto, Package Installer y la conexión.'));
$job['package_url'] = $fileUrl;
$taskId = is_array($decoded) ? (int)($decoded['task_id'] ?? 0) : 0;
if ($ok && $taskId > 0) {
    $job['task_id'] = $taskId;
    $job['state'] = 'downloading';
    $job['progress'] = ['percent' => 0, 'transferred' => 0, 'total' => (int)($entry['size'] ?? 0), 'remaining_seconds' => null];
    $job['message'] = 'RPI aceptó el PKG. Esperando el progreso de BGFT.';
    $update($job);

    // Nova expone el estado de BGFT en /api/get_task_progress. `length` y
    // `transferred` suelen venir como hex sin comillas, por lo que no son JSON válido.
    $readNumber = static function (string $json, string $key): ?int {
        if (!preg_match('/"' . preg_quote($key, '/') . '"\s*:\s*(0x[0-9a-f]+|-?\d+)/i', $json, $m)) return null;
        $value = $m[1];
        return stripos($value, '0x') === 0 ? (int)hexdec(substr($value, 2)) : (int)$value;
    };
    $progressEndpoint = 'http://' . $job['host_ip'] . ':' . (int)$job['rpi_port'] . '/api/get_task_progress';
    $lastProgress = 0;
    $completeSamples = 0;
    $pollStarted = time();
    while (time() - $pollStarted < 21600) {
        sleep(2);
        $progressBody = false;
        $progressHttp = 0;
        $progressCurlError = '';
        $poll = @curl_init($progressEndpoint);
        if ($poll) {
            curl_setopt_array($poll, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['task_id' => $taskId]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_NOSIGNAL => true,
            ]);
            $progressBody = curl_exec($poll);
            $progressHttp = (int)curl_getinfo($poll, CURLINFO_HTTP_CODE);
            $progressCurlError = (string)curl_error($poll);
            curl_close($poll);
        }
        if ($progressBody === false || $progressHttp < 200 || $progressHttp >= 300) {
            // BGFT puede retirar tareas completadas; solo cerramos si ya se
            // observó el 100%, de lo contrario conservamos un estado honesto.
            if ($completeSamples >= 2) {
                $job['state'] = 'success';
                $job['message'] = 'BGFT completó la transferencia del PKG.';
                $job['progress']['percent'] = 100;
                $job['progress']['remaining_seconds'] = 0;
                $update($job);
                exit;
            }
            $job['progress_error'] = $progressCurlError !== '' ? $progressCurlError : 'BGFT no devolvió progreso todavía.';
            $update($job);
            continue;
        }
        $json = (string)$progressBody;
        $apiStatus = json_decode($json, true);
        $errorCode = $readNumber($json, 'error');
        $transferred = $readNumber($json, 'transferred_total') ?? $readNumber($json, 'transferred');
        $total = $readNumber($json, 'length_total') ?? $readNumber($json, 'length') ?? (int)($entry['size'] ?? 0);
        $remaining = $readNumber($json, 'rest_sec_total') ?? $readNumber($json, 'rest_sec');
        $phasePercent = $readNumber($json, 'local_copy_percent') ?? $readNumber($json, 'preparing_percent');
        if (is_array($apiStatus) && in_array(strtolower((string)($apiStatus['status'] ?? '')), ['error', 'fail'], true)) {
            $job['state'] = 'error';
            $job['message'] = 'BGFT reportó un error: ' . (string)($apiStatus['error'] ?? $json);
            $job['response'] = substr($json, 0, 500);
            $update($job);
            exit;
        }
        if ($errorCode !== null && $errorCode !== 0) {
            $job['state'] = 'error';
            $job['message'] = 'BGFT reportó el código de error ' . $errorCode . '.';
            $job['response'] = substr($json, 0, 500);
            $update($job);
            exit;
        }
        $percent = ($transferred !== null && $total > 0)
            ? min(100, max(0, (int)floor(($transferred / $total) * 100)))
            : ($phasePercent !== null ? min(100, max(0, $phasePercent)) : $lastProgress);
        $lastProgress = max($lastProgress, $percent);
        $completeSamples = $lastProgress >= 100 ? $completeSamples + 1 : 0;
        $job['state'] = 'downloading';
        $job['message'] = $lastProgress >= 100 ? 'Transferencia terminada; BGFT está cerrando la tarea.' : 'Descargando e instalando en la PS4.';
        $job['progress'] = [
            'percent' => $lastProgress,
            'transferred' => max(0, $transferred ?? 0),
            'total' => max(0, $total),
            'remaining_seconds' => $remaining !== null && $remaining >= 0 ? $remaining : null,
            'preparing_percent' => $readNumber($json, 'preparing_percent'),
            'local_copy_percent' => $readNumber($json, 'local_copy_percent'),
        ];
        $job['progress_updated'] = time();
        $update($job);
        if ($completeSamples >= 2) {
            // Dos lecturas separadas evitan declarar fin por un único pico de progreso.
            $job['state'] = 'success';
            $job['message'] = 'BGFT completó la transferencia del PKG. Confirma que aparece instalado en la PS4.';
            $job['progress']['percent'] = 100;
            $job['progress']['remaining_seconds'] = 0;
            $update($job);
            exit;
        }
    }
    $job['state'] = 'error';
    $job['message'] = 'La tarea RPI excedió el límite de seguimiento de 6 horas.';
    $update($job);
    exit;
}
if ($ok) {
    $job['state'] = 'accepted';
    $job['message'] = 'RPI aceptó la orden, pero no entregó un task_id para consultar su progreso.';
}
$update($job);
