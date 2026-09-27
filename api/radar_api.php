<?php
/**
 * Radar de dispositivos que exponen el servicio solicitado en la LAN.
 * Escanea en lotes pequeños para evitar límites de socket_select en Windows.
 */
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

$timeout_ms = max(100, min(2000, (int)($_GET['timeout'] ?? 1200)));
$port = (int)($_GET['port'] ?? 2121);
$max_ips = max(1, min(254, (int)($_GET['max_ips'] ?? 254)));
if ($port < 1 || $port > 65535) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Puerto de radar no válido.']);
    exit;
}

// HTTP_HOST puede ser localhost en Termux. En ese caso la ruta UDP revela la
// interfaz local con salida a la red, que normalmente es la Wi-Fi compartida.
$local_ip = '';
$host_header = (string)($_SERVER['HTTP_HOST'] ?? '');
$host_candidate = preg_replace('/:\\d+$/', '', $host_header);
if (filter_var($host_candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
    && !in_array($host_candidate, ['127.0.0.1', '0.0.0.0'], true)) {
    $local_ip = $host_candidate;
}
if ($local_ip === '') {
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        @socket_connect($sock, '8.8.8.8', 53);
        @socket_getsockname($sock, $local_ip);
        @socket_close($sock);
    }
}
if (!filter_var($local_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    $local_ip = (string)($_SERVER['SERVER_ADDR'] ?? '');
}
if (!filter_var($local_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
    || strpos($local_ip, '127.') === 0 || $local_ip === '0.0.0.0') {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo identificar la interfaz local. Escribe la IP de la PS4 manualmente.']);
    exit;
}

$octets = explode('.', $local_ip);
$base_ip = implode('.', array_slice($octets, 0, 3));
$candidates = [];
for ($i = 1; $i <= $max_ips; $i++) {
    $ip = $base_ip . '.' . $i;
    if ($ip !== $local_ip) $candidates[] = $ip;
}

$active_ips = [];
$batch_size = 40;
$batches = max(1, (int)ceil(count($candidates) / $batch_size));
$wait_ms = max(80, (int)ceil($timeout_ms / $batches));
foreach (array_chunk($candidates, $batch_size) as $batch) {
    $sockets = [];
    foreach ($batch as $ip) {
        $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$sock) continue;
        @socket_set_nonblock($sock);
        @socket_connect($sock, $ip, $port);
        $sockets[$ip] = $sock;
    }
    if (!$sockets) continue;

    $write = array_values($sockets);
    $read = [];
    $except = [];
    @socket_select($read, $write, $except, (int)floor($wait_ms / 1000), ($wait_ms % 1000) * 1000);
    foreach ($write as $ready) {
        $ip = array_search($ready, $sockets, true);
        if ($ip === false) continue;
        $socket_error = @socket_get_option($ready, SOL_SOCKET, SO_ERROR);
        if ($socket_error === 0) $active_ips[] = $ip;
    }
    foreach ($sockets as $sock) @socket_close($sock);
}

sort($active_ips, SORT_NATURAL);
echo json_encode([
    'status' => 'success',
    'local_ip' => $local_ip,
    'segmento' => $base_ip . '.x',
    'port' => $port,
    'timeout_ms' => $timeout_ms,
    'ps4_ips' => array_values(array_unique($active_ips)),
]);
