<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER AJ 🚀 - API: RADAR DE RED DINÁMICO Y PARALELO
 * DEVELOPED By SeBaS - Mod AJ
 * RUTA: api/radar_api.php
 * ====================================================================
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// Configuración desde GET (permite ajustar desde el frontend)
$timeout_ms = isset($_GET['timeout']) ? (int)$_GET['timeout'] : 250;
$timeout_ms = max(100, min(1000, $timeout_ms)); // Entre 100ms y 1000ms
$port = isset($_GET['port']) ? (int)$_GET['port'] : 2121;
$max_ips = isset($_GET['max_ips']) ? (int)$_GET['max_ips'] : 254; // Máximo a escanear

// 1. AUTO-DETECCIÓN INTELIGENTE DE LA IP DEL CELULAR
$local_ip = '';

// Método 1: Extraer IP de HTTP_HOST (funciona en KSWEB, Termux, etc.)
if (isset($_SERVER['HTTP_HOST'])) {
    $host_parts = explode(':', $_SERVER['HTTP_HOST']);
    $local_ip = $host_parts[0];
}

// Método 2: Fallback con socket UDP (si HTTP_HOST devuelve localhost)
if (empty($local_ip) || $local_ip === '127.0.0.1' || $local_ip === 'localhost') {
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        @socket_connect($sock, "8.8.8.8", 53);
        @socket_getsockname($sock, $local_ip);
        @socket_close($sock);
    }
}

// Método 3: Si aún no hay IP, usar la IP del servidor (puede ser 0.0.0.0 en algunos casos)
if (empty($local_ip) || $local_ip === '127.0.0.1') {
    $local_ip = $_SERVER['SERVER_ADDR'] ?? '';
}

// Validar que la IP sea válida (formato IPv4)
if (!filter_var($local_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo obtener una dirección IP válida.']);
    exit;
}

// Extraer el segmento de red (los primeros 3 octetos)
$parts = explode('.', $local_ip);
if (count($parts) !== 4) {
    echo json_encode(['status' => 'error', 'message' => 'Dirección IP inválida.']);
    exit;
}
array_pop($parts);
$base_ip = implode('.', $parts);

// Evitar escanear localhost o redes inválidas
if ($base_ip === '127.0.0' || $base_ip === '0.0.0' || $base_ip === '') {
    echo json_encode(['status' => 'error', 'message' => 'Red local no detectable.']);
    exit;
}

// 2. MULTI-THREADING VIRTUAL: Crear conexiones simultáneas
$sockets = [];
$active_ips = [];
$timeout_sec = floor($timeout_ms / 1000);
$timeout_usec = ($timeout_ms % 1000) * 1000;

// Crear sockets para cada IP en la subred
$total_ips = min(254, $max_ips);
for ($i = 1; $i <= $total_ips; $i++) {
    $ip = "$base_ip.$i";
    if ($ip === $local_ip) continue; // Saltar la propia IP del dispositivo

    $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($sock) {
        @socket_set_nonblock($sock);
        @socket_connect($sock, $ip, $port);
        $sockets[$ip] = $sock;
    }
}

// Si no hay sockets, responder con error
if (empty($sockets)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudieron crear sockets de red. Verifica permisos.'
    ]);
    exit;
}

// 3. EL GOLPE DE RED: Esperar el tiempo configurado
$read = $sockets;
$write = $sockets;
$except = null;

$num_changed = @socket_select($read, $write, $except, $timeout_sec, $timeout_usec);

if ($num_changed === false) {
    // Si socket_select falla, cerrar y devolver error
    foreach ($sockets as $sock) { @socket_close($sock); }
    echo json_encode(['status' => 'error', 'message' => 'Error en el escaneo de red.']);
    exit;
}

if ($num_changed > 0) {
    foreach ($write as $sock) {
        $ip = array_search($sock, $sockets);
        if ($ip === false) continue;
        $error = @socket_get_option($sock, SOL_SOCKET, SO_ERROR);
        if ($error === 0) {
            $active_ips[] = $ip;
        }
    }
}

// 4. LIMPIEZA Y RESPUESTA
foreach ($sockets as $sock) {
    @socket_close($sock);
}

// Si se encontraron IPs, ordenarlas (por si acaso)
sort($active_ips);

echo json_encode([
    'status' => 'success',
    'local_ip' => $local_ip,
    'segmento' => "$base_ip.x",
    'timeout_ms' => $timeout_ms,
    'ps4_ips' => array_values(array_unique($active_ips))
]);
exit;
?>
