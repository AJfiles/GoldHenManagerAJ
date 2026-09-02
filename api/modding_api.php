<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 - API: RESPALDOS Y GALERÍA LOCAL
 * DEVELOPED By SeBaS - RUTA: api/modding_api.php
 * ====================================================================
 */
error_reporting(0);
set_time_limit(60);

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? ''));
$port = 2121;

$backup_dir = '../user/cache/backups_portadas';
$iconos_dir = '../user/portadas_custom';

if (!file_exists($backup_dir)) { @mkdir($backup_dir, 0777, true); }
// 🔥 FIX: Blindaje para que Android ignore los backups de portadas
@file_put_contents($backup_dir . '/.nomedia', ''); 

if (!file_exists($iconos_dir)) { @mkdir($iconos_dir, 0777, true); }
// 🔥 FIX: Blindaje para que Android ignore las portadas editadas
@file_put_contents($iconos_dir . '/.nomedia', ''); 

function curl_download($ip, $port, $remote_path, $local_path) {
    $ch = curl_init("ftp://$ip:$port$remote_path");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $data = curl_exec($ch);
    curl_close($ch);
    if ($data !== false && strlen($data) > 1000) {
        file_put_contents($local_path, $data);
        return true;
    }
    return false;
}

if ($action === 'respaldar') {
    if (!$host_ip || !$cusa) { echo json_encode(['status' => 'error']); exit; }
    
    $local_path = $backup_dir . '/' . $cusa . '_backup.png';
    $rutas = [
        "/user/appmeta/$cusa/icon0.png", 
        "/system_data/priv/appmeta/$cusa/icon0.png",
        "/user/appmeta/external/$cusa/icon0.png",
        "/user/app/$cusa/sce_sys/icon0.png"
    ];
    
    $exito = false;
    foreach ($rutas as $ruta) {
        if (curl_download($host_ip, $port, $ruta, $local_path)) {
            $exito = true; break;
        }
    }
    echo json_encode(['status' => $exito ? 'success' : 'error']);
    exit;
}

if ($action === 'listar_backups' || $action === 'listar_local') {
    $dir = ($action === 'listar_backups') ? $backup_dir : $iconos_dir;
    $url_base = ($action === 'listar_backups') ? 'user/cache/backups_portadas/' : 'user/portadas_custom/';
    
    $archivos = glob("$dir/*.{png,jpg,jpeg}", GLOB_BRACE);
    $resultado = [];
    
    if (is_array($archivos)) {
        usort($archivos, function($a, $b) { return filemtime($b) - filemtime($a); });
        foreach($archivos as $f) {
            $resultado[] = [
                'name' => basename($f),
                'path_relativo' => $url_base . basename($f), 
                'full_url' => $url_base . basename($f) . '?v=' . time() 
            ];
        }
    }
    echo json_encode(['status' => 'success', 'data' => $resultado]);
    exit;
}
?>
