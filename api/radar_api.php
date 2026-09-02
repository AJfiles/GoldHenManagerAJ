<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - API: RADAR DE RED DINÁMICO Y PARALELO
 * DEVELOPED By SeBaS - RUTA: api/radar_api.php
 * ====================================================================
 */
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

// 1. AUTO-DETECCIÓN INTELIGENTE DE LA IP DEL CELULAR (KSWEB FIX)
$local_ip = '';

// 🔥 Método Maestro: Extraer la IP directamente de la barra de tu navegador (Ej: 192.168.0.20)
if (isset($_SERVER['HTTP_HOST'])) {
    $host_parts = explode(':', $_SERVER['HTTP_HOST']);
    $local_ip = $host_parts[0];
}

// Respaldo de seguridad con Socket UDP por si el host falla
if (empty($local_ip) || $local_ip === '127.0.0.1' || $local_ip === 'localhost') {
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        @socket_connect($sock, "8.8.8.8", 53);
        @socket_getsockname($sock, $local_ip);
        @socket_close($sock);
    }
}

// Extraemos el segmento real (Ej: si es 192.168.0.20, se queda con 192.168.0)
$parts = explode('.', $local_ip);
array_pop($parts);
$base_ip = implode('.', $parts); 

// Evitamos que intente escanear localhost bajo cualquier circunstancia
if (!$base_ip || count($parts) !== 3 || $base_ip === '127.0.0') {
    echo json_encode(['status' => 'error', 'message' => 'KSWEB bloqueó la lectura de la IP de red local.']);
    exit;
}

$port = 2121; // Puerto FTP de GoldHEN
$timeout_sec = 0;
$timeout_usec = 250000; // Timeout hiper agresivo: 250 milisegundos

$sockets = [];
$active_ips = [];

// 2. MULTI-THREADING VIRTUAL: Crear 254 conexiones simultáneas
for ($i = 1; $i <= 254; $i++) {
    $ip = "$base_ip.$i";
    if ($ip === $local_ip) continue; 

    $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($sock) {
        @socket_set_nonblock($sock);
        @socket_connect($sock, $ip, $port);
        $sockets[$ip] = $sock;
    }
}

$read = $sockets;
$write = $sockets;
$except = null;

// 3. EL GOLPE DE RED: Esperamos un cuarto de segundo a ver quién responde
$num_changed = @socket_select($read, $write, $except, $timeout_sec, $timeout_usec);

if ($num_changed > 0) {
    foreach ($write as $sock) {
        $ip = array_search($sock, $sockets);
        $error = @socket_get_option($sock, SOL_SOCKET, SO_ERROR);
        if ($error === 0) {
            $active_ips[] = $ip;
        }
    }
}

// Cerramos para liberar memoria de Android
foreach ($sockets as $sock) {
    @socket_close($sock);
}

echo json_encode([
    'status' => 'success',
    'local_ip' => $local_ip,
    'segmento' => "$base_ip.x",
    'ps4_ips' => array_values(array_unique($active_ips))
]);
?>
