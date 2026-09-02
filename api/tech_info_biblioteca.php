<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER AJ 🚀 - API: DETECTOR DE PESO Y ESPACIO
 * DEVELOPED By SeBaS - Mod AJ
 * RUTA: api/tech_info_biblioteca.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
set_time_limit(300);

header('Content-Type: application/json; charset=utf-8');

// Firma actualizada para reflejar la autoría original y la modificación
$firma = chr(83).chr(101).chr(66).chr(97).chr(83) . " (Mod AJ)"; 
header('X-Author: ' . $firma);

$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? ''));
$port = isset($_POST['port']) ? (int)$_POST['port'] : 2121;

if (!$host_ip || !$cusa) {
    echo json_encode(['status' => 'error', 'size' => 'S/N', 'location' => 'Desconectado', 'bytes' => 0]);
    exit;
}

// ==========================================
// RUTA DE CACHÉ UNIFICADA
// ==========================================
$cache_dir = __DIR__ . '/../user/cache/biblioteca';
if (!file_exists($cache_dir)) { 
    @mkdir($cache_dir, 0777, true); 
}
if (!file_exists($cache_dir . '/.nomedia')) { 
    @file_put_contents($cache_dir . '/.nomedia', ''); 
}
$cache_file = $cache_dir . "/size_{$cusa}.json";

// LECTURA INSTANTÁNEA EN CACHÉ PERMANENTE
if (file_exists($cache_file)) {
    $cached_data = json_decode(@file_get_contents($cache_file), true);
    if ($cached_data && isset($cached_data['status']) && $cached_data['status'] === 'success') {
        // Renovar el caché si ha pasado más de 1 hora (3600 segundos)
        if (isset($cached_data['timestamp']) && (time() - $cached_data['timestamp'] < 3600)) {
            echo json_encode($cached_data);
            exit;
        }
    }
}

// ==========================================
// FUNCIONES AUXILIARES FTP
// ==========================================
function curl_get_dir_list($ip, $port, $path) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "ftp://$ip:$port" . rtrim($path, '/') . '/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $parsed_items = [];
    if ($res && $http_code === 200) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $chunks = preg_split("/[\s]+/", trim($line), 9);
            if (count($chunks) >= 9) {
                $name = trim($chunks[8]);
                if ($name === '.' || $name === '..') continue;
                $parsed_items[] = [
                    'name' => $name,
                    'is_dir' => ($chunks[0][0] === 'd'),
                    'size' => (int)$chunks[4]
                ];
            }
        }
    }
    return $parsed_items;
}

function calcular_peso_recursivo_curl($ip, $port, $dir, &$profundidad = 0, $max_profundidad = 20) {
    if ($profundidad > $max_profundidad) return 0; // Evita bucles infinitos por symlinks
    $acumulador = 0;
    $items = curl_get_dir_list($ip, $port, $dir);
    if (empty($items)) return 0;
    $profundidad++;
    foreach ($items as $item) {
        $full_route = rtrim($dir, '/') . '/' . $item['name'];
        if ($item['is_dir']) {
            $acumulador += calcular_peso_recursivo_curl($ip, $port, $full_route, $profundidad, $max_profundidad);
        } else {
            $acumulador += $item['size'];
        }
    }
    $profundidad--;
    return $acumulador;
}

function format_bytes_v2($bytes) {
    if ($bytes == 0) return '0 KB';
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    return number_format($bytes / 1024, 2) . ' KB';
}

// ==========================================
// LÓGICA PRINCIPAL DE DETECCIÓN
// ==========================================
$ubicacion_label = 'No hallado';
$bytes_calculados = 0;

// 1. PRIORIDAD USB: Evitar falsos positivos del symlink interno
$rutas_usb = [
    "/mnt/ext0/user/app/$cusa", 
    "/mnt/ext1/user/app/$cusa"
];
$es_usb = false;

foreach ($rutas_usb as $rx) {
    $peso_usb = calcular_peso_recursivo_curl($host_ip, $port, $rx);
    // Si pesa más de 100KB, el juego real está en el USB
    if ($peso_usb > 100000) {
        $bytes_calculados += $peso_usb;
        $ubicacion_label = 'Alm. Ampliado';
        $es_usb = true;
        break;
    }
}

// 2. Si no está en USB, leer el almacenamiento interno
if (!$es_usb) {
    $base_interna = "/user/app/$cusa";
    $peso_base = calcular_peso_recursivo_curl($host_ip, $port, $base_interna);
    if ($peso_base > 0) {
        $bytes_calculados += $peso_base;
        $ubicacion_label = 'Alm. Interno';
    }
}

// 3. Sumar Parches y DLCs (Solo en la ruta real para no duplicar bytes)
if ($es_usb) {
    $rutas_adicionales = [
        "/mnt/ext0/user/patch/$cusa", 
        "/mnt/ext0/user/addcont/$cusa"
    ];
} else {
    $rutas_adicionales = [
        "/user/patch/$cusa", 
        "/user/addcont/$cusa"
    ];
}

foreach ($rutas_adicionales as $ra) {
    $bytes_calculados += calcular_peso_recursivo_curl($host_ip, $port, $ra);
}

// 4. Formatear y guardar en caché
if ($bytes_calculados > 0) {
    $resultado_final = [
        'status' => 'success',
        'size' => format_bytes_v2($bytes_calculados),
        'location' => $ubicacion_label,
        'bytes' => $bytes_calculados,
        'timestamp' => time() // Añadimos timestamp para control de caché
    ];
    @file_put_contents($cache_file, json_encode($resultado_final));
    echo json_encode($resultado_final);
} else {
    // Si no se encontró nada, devolvemos un estado de "pendiente" para que el frontend sepa que debe reintentar más tarde
    echo json_encode([
        'status' => 'success', 
        'size' => 'Calculando...', 
        'location' => 'Pendiente', 
        'bytes' => 0,
        'timestamp' => time()
    ]);
}
exit;
?>
