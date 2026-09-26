<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 - API: EXCAVADOR DE IMÁGENES MEDIA (SEGURO)
 * Mantenido por AJ · Basado en proyecto original de SeBaS · RUTA: api/ps4_screenshots_api.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
set_time_limit(120);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/cache_helpers.php';

$action = $_POST['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? ''));
$port = isset($_POST['port']) ? (int)$_POST['port'] : 2121;
$force = $_POST['force'] ?? '0'; 

if (!filter_var($host_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { 
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos de consola.']); 
    exit; 
}

$cache_dir = __DIR__ . '/../user/cache/biblioteca';
$capturas_dir = __DIR__ . '/../user/cache/capturas'; 

if (!file_exists($cache_dir)) { @mkdir($cache_dir, 0777, true); }
if (!file_exists($capturas_dir)) { 
    @mkdir($capturas_dir, 0777, true); 
    @file_put_contents($capturas_dir . '/.nomedia', '');
}
limpiar_cache_antigua($cache_dir);
limpiar_cache_antigua($capturas_dir);

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
            $hasRemotePaths = true;
            foreach (($cached['images'] ?? []) as $image) if (empty($image['remote_path'])) { $hasRemotePaths = false; break; }
            if ($hasRemotePaths || empty($cached['images'])) { echo json_encode($cached); exit; }
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

if ($action === 'delete_capture') {
    $remote_path = (string)($_POST['remote_path'] ?? '');
    $pathParts = explode('/', $remote_path);
    if (strpos($remote_path, '/user/av_contents/photo/') !== 0 || in_array('..', $pathParts, true) || preg_match('/[\x00-\x1F\x7F]/', $remote_path) || !preg_match('/\.(jpg|jpeg|png)$/i', $remote_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Ruta de captura inválida.']); exit;
    }
    $remote_dir = dirname($remote_path);
    $remote_name = basename($remote_path);
    // Deja que libcurl entre primero al directorio indicado por la URL FTP y
    // ejecuta DELE como POSTQUOTE. QUOTE se dispara antes del CWD de la URL,
    // por lo que un CWD dentro de QUOTE puede fallar con 550 en GoldHEN.
    $encoded_dir = implode('/', array_map('rawurlencode', explode('/', trim($remote_dir, '/'))));
    $ftp_url = "ftp://$host_ip:$port/$encoded_dir/";
    $quoted_name = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $remote_name) . '"';
    $ch = curl_init($ftp_url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => 'LIST', CURLOPT_POSTQUOTE => ['DELE ' . $quoted_name], CURLOPT_TIMEOUT => 15]);
    $deleted = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($deleted === false) { echo json_encode(['status' => 'error', 'message' => 'GoldHEN rechazó el borrado FTP (550). Verifica que la ruta exista y que esa versión de FTP permita borrar capturas. Detalle: ' . $error]); exit; }
    $ext = strtolower(pathinfo($remote_path, PATHINFO_EXTENSION));
    @unlink($capturas_dir . '/' . md5($remote_path) . '.' . $ext);
    foreach (glob($cache_dir . '/galeria_*.json') ?: [] as $cache) @unlink($cache);
    echo json_encode(['status' => 'success']); exit;
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
    $game_id = preg_match('/CUSA\d{5}/i', $cap, $gameMatch) ? strtoupper($gameMatch[0]) : '';

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
        'url' => $url_final,
        'remote_path' => $cap,
        'game_id' => $game_id
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
