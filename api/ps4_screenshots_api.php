<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 - API: EXCAVADOR DE IMÁGENES MEDIA (SEGURO)
 * DEVELOPED By SeBaS - RUTA: api/ps4_screenshots_api.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
set_time_limit(120);

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? ''));
$port = isset($_POST['port']) ? (int)$_POST['port'] : 2121;
$force = $_POST['force'] ?? '0'; 

if (!$host_ip) { 
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos de consola.']); 
    exit; 
}

$cache_dir = '../user/cache/biblioteca';
$capturas_dir = '../user/cache/capturas'; 

if (!file_exists($cache_dir)) { @mkdir($cache_dir, 0777, true); }
if (!file_exists($capturas_dir)) { 
    @mkdir($capturas_dir, 0777, true); 
    @file_put_contents($capturas_dir . '/.nomedia', '');
}

$is_global = ($action === 'get_all_caps');
$cache_file = $is_global ? $cache_dir . "/galeria_ALL.json" : $cache_dir . "/galeria_{$cusa}.json";

if ($force === '0' && file_exists($cache_file)) {
    $cached = json_decode(@file_get_contents($cache_file), true);
    if ($cached && isset($cached['status']) && $cached['status'] === 'success') {
        if ($action === 'count_only') {
            $count = isset($cached['images']) ? count($cached['images']) : 0;
            echo json_encode(['status' => 'success', 'count' => $count]);
            exit;
        } else if ($action === 'get_caps' || $action === 'get_all_caps') {
            echo json_encode($cached);
            exit;
        }
    }
}

// ====================================================================
// 🔥 ESCÁNER SEGURO: Conexión cURL 1 a 1 (Evita crashear el GoldHEN)
// ====================================================================
$ch_test = curl_init();
curl_setopt($ch_test, CURLOPT_URL, "ftp://$host_ip:$port/user/");
curl_setopt($ch_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_test, CURLOPT_CUSTOMREQUEST, "LIST");
curl_setopt($ch_test, CURLOPT_TIMEOUT, 4);
$test_ftp = curl_exec($ch_test);
curl_close($ch_test);

if ($test_ftp === false) {
    if (file_exists($cache_file)) {
        $cached = json_decode(@file_get_contents($cache_file), true);
        if ($action === 'count_only') { echo json_encode(['status' => 'success', 'count' => count($cached['images'] ?? [])]); }
        else { echo json_encode($cached); }
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => "Consola apagada o FTP inactivo."]);
    exit;
}

function get_ftp_list($ip, $port, $dir) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "ftp://$ip:$port" . rtrim($dir, '/') . '/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $res = curl_exec($ch);
    curl_close($ch);
    
    $files = [];
    if ($res) {
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9) {
                $name = trim($parts[8]);
                if ($name === '.' || $name === '..') continue;
                $files[] = ['name' => $name, 'is_dir' => (substr($parts[0], 0, 1) === 'd')];
            }
        }
    }
    return $files;
}

function excavar_fotos($ip, $port, $dir, &$capturas, $profundidad = 0) {
    if ($profundidad > 4) return;
    $items = get_ftp_list($ip, $port, $dir);
    foreach ($items as $item) {
        $ruta_completa = rtrim($dir, '/') . '/' . $item['name'];
        if ($item['is_dir']) {
            excavar_fotos($ip, $port, $ruta_completa, $capturas, $profundidad + 1);
        } else {
            $ext = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) { $capturas[] = $ruta_completa; }
        }
    }
}

$base_photo = "/user/av_contents/photo";
$rutas_cusa = [];
$items_nivel1 = get_ftp_list($host_ip, $port, $base_photo);

if ($is_global) {
    foreach ($items_nivel1 as $item1) {
        if ($item1['is_dir']) {
            $rutas_cusa[] = "$base_photo/{$item1['name']}";
        }
    }
} else {
    if (!$cusa) { echo json_encode(['status' => 'error', 'message' => 'Falta CUSA.']); exit; }
    foreach ($items_nivel1 as $item1) {
        if ($item1['is_dir']) {
            $ruta1 = "$base_photo/{$item1['name']}";
            if (stripos($item1['name'], $cusa) !== false) {
                $rutas_cusa[] = $ruta1;
            } else {
                $items_nivel2 = get_ftp_list($host_ip, $port, $ruta1);
                foreach ($items_nivel2 as $item2) {
                    if ($item2['is_dir'] && stripos($item2['name'], $cusa) !== false) {
                        $rutas_cusa[] = "$ruta1/{$item2['name']}";
                    }
                }
            }
        }
    }
}

$capturas = [];
foreach ($rutas_cusa as $ruta) { excavar_fotos($host_ip, $port, $ruta, $capturas); }

if (empty($capturas)) {
    $resultado_final = ['status' => 'success', 'images' => []];
    @file_put_contents($cache_file, json_encode($resultado_final));
    if ($action === 'count_only') { echo json_encode(['status' => 'success', 'count' => 0]); }
    else { echo json_encode(['status' => 'error', 'message' => "No hay fotos en la consola."]); }
    exit;
}

sort($capturas);
$capturas = array_reverse($capturas); 

$images_format = [];
foreach ($capturas as $cap) {
    if ($is_global) {
        $parts = explode('/', $cap);
        $name = (count($parts) > 2) ? "[" . $parts[count($parts)-2] . "] " . end($parts) : end($parts);
    } else {
        $name = basename($cap);
    }

    $ext = strtolower(pathinfo($cap, PATHINFO_EXTENSION));
    $hash = md5($cap);
    $ruta_fisica_local = $capturas_dir . '/' . $hash . '.' . $ext;

    // 🔥 URL estática si ya existe, Streaming si es nueva
    if (file_exists($ruta_fisica_local) && filesize($ruta_fisica_local) > 0) {
        $url_final = "user/cache/capturas/" . $hash . "." . $ext;
    } else {
        $url_final = "api/stream_image_api.php?ip=$host_ip&path=" . urlencode($cap);
    }

    $images_format[] = [
        'name' => $name,
        'url' => $url_final
    ];
}

$resultado_final = ['status' => 'success', 'images' => $images_format];
@file_put_contents($cache_file, json_encode($resultado_final));

if ($action === 'count_only') {
    echo json_encode(['status' => 'success', 'count' => count($images_format)]);
} else {
    echo json_encode($resultado_final);
}
exit;
?>
