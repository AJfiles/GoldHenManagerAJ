<?php
/** Descubre equipos que exponen servicios conocidos de GoldHEN en interfaces LAN activas. */
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
set_time_limit(15);

$timeoutMs = max(100, min(2500, (int)($_GET['timeout'] ?? 1200)));
$requestedPort = (int)($_GET['port'] ?? 2121);
$ports = array_values(array_unique(array_filter([$requestedPort, 2121, 2122, 12800, 12801, 9090], static fn($p) => $p >= 1 && $p <= 65535)));
$ports = array_slice($ports, 0, 6);
if ($requestedPort < 1 || $requestedPort > 65535) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Puerto de radar no válido.']);
    exit;
}

// Consultar todas las interfaces activas evita escoger por error la ruta de
// datos móviles, una interfaz virtual o un adaptador distinto al de la PS4.
$interfaces = function_exists('net_get_interfaces') ? @net_get_interfaces() : false;
$segments = [];
if (is_array($interfaces)) {
    foreach ($interfaces as $name => $interface) {
        if (empty($interface['up'])) continue;
        foreach (($interface['unicast'] ?? []) as $address) {
            $ip = (string)($address['address'] ?? '');
            $mask = (string)($address['netmask'] ?? '');
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !filter_var($mask, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) continue;
            $long = ip2long($ip); $maskLong = ip2long($mask);
            if ($long === false || $maskLong === false) continue;
            $network = $long & $maskLong;
            $broadcast = $network | (~$maskLong & 0xFFFFFFFF);
            $count = $broadcast - $network - 1;
            // Solo redes privadas y rangos razonables para que el escaneo no
            // se convierta en un barrido masivo de una LAN empresarial.
            $private = preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[01])\.)/', $ip) === 1;
            if (!$private || $count < 2 || $count > 4094) continue;
            $prefix = substr_count(decbin((int)$maskLong), '1');
            $label = $count === 254 ? implode('.', array_slice(explode('.', long2ip($network)), 0, 3)) . '.x' : long2ip($network) . '/' . $prefix;
            $segments[$network . '/' . $maskLong] = [
                'network' => $network, 'broadcast' => $broadcast,
                'local_ip' => $ip, 'mask' => $mask,
                'label' => $label, 'interface' => (string)$name,
            ];
        }
    }
}
// Fallback para instalaciones PHP sin la extensión sockets.
if (!$segments) {
    $sock = @stream_socket_client('udp://8.8.8.8:53', $errno, $errstr, 1);
    if ($sock) {
        $local = stream_socket_get_name($sock, false);
        fclose($sock);
        $ip = explode(':', (string)$local)[0];
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip); $parts[3] = '0';
            $network = ip2long(implode('.', $parts));
            $segments['fallback'] = ['network' => $network, 'broadcast' => $network + 255, 'local_ip' => $ip, 'mask' => '255.255.255.0', 'label' => implode('.', array_slice($parts, 0, 3)) . '.x', 'interface' => 'default'];
        }
    }
}
if (!$segments) {
    echo json_encode(['status' => 'error', 'message' => 'No se detectaron interfaces LAN activas. Conecta el teléfono y la PS4 a la misma red local.']);
    exit;
}

$ips = [];
foreach ($segments as $segment) {
    for ($host = $segment['network'] + 1; $host < $segment['broadcast']; $host++) {
        $ip = long2ip($host);
        if ($ip === $segment['local_ip']) continue;
        $ips[$ip] = $segment;
    }
}
$found = [];
$deadline = microtime(true) + ($timeoutMs / 1000);
foreach ($ports as $port) {
    foreach (array_chunk($ips, 48, true) as $batch) {
        if (microtime(true) >= $deadline) break 2;
        $sockets = [];
        foreach ($batch as $ip => $segment) {
            $sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$sock) continue;
            @socket_set_nonblock($sock);
            @socket_connect($sock, $ip, $port);
            $sockets[$ip] = $sock;
        }
        if ($sockets) {
            $write = array_values($sockets); $read = []; $except = [];
            $remaining = max(1, (int)(($deadline - microtime(true)) * 1000));
            @socket_select($read, $write, $except, 0, min(60000, $remaining * 1000));
            foreach ($write as $ready) {
                $ip = array_search($ready, $sockets, true);
                if ($ip === false || @socket_get_option($ready, SOL_SOCKET, SO_ERROR) !== 0) continue;
                if (!isset($found[$ip])) $found[$ip] = ['ip' => $ip, 'ports' => [], 'segment' => $batch[$ip]['label']];
                $found[$ip]['ports'][] = (int)$port;
            }
            foreach ($sockets as $sock) @socket_close($sock);
        }
        if (count($found) && isset($_GET['first']) && $_GET['first'] === '1') break 2;
    }
}
$localIps = array_values(array_unique(array_column($segments, 'local_ip')));
$labels = array_values(array_unique(array_column($segments, 'label')));
echo json_encode([
    'status' => 'success', 'local_ip' => $localIps[0] ?? '', 'local_ips' => $localIps,
    'segmento' => implode(', ', $labels), 'ports_scanned' => $ports,
    'devices' => array_values($found), 'ps4_ips' => array_keys($found),
]);
