<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 (PS5/PS4) - API: INYECTOR DE PORTADAS FTP
 * DEVELOPED By SeBaS - RUTA: api/inyector_portadas.php
 * ====================================================================
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(120);

header('Content-Type: application/json; charset=utf-8');

$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? ''));
$port = 2121;

if (!$host_ip || !$cusa || !isset($_FILES['cover_image'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos de consola o imagen.']);
    exit;
}

if ($_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Error al recibir la imagen procesada en el servidor.']);
    exit;
}

$tmp_file = $_FILES['cover_image']['tmp_name'];

// 🔥 FIX CRÍTICO: Ruta estricta al entorno USER y creación de .nomedia
$cache_dir = __DIR__ . '/../user/cache/biblioteca';
if (!file_exists($cache_dir)) { @mkdir($cache_dir, 0777, true); }
if (!file_exists($cache_dir . '/.nomedia')) { @file_put_contents($cache_dir . '/.nomedia', ''); }

// ==========================================
// MOTOR FTP cURL (Especial para Termux)
// ==========================================
function check_folder_exists($ip, $port, $path) {
    $ch = curl_init("ftp://$ip:$port$path/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    curl_close($ch);
    return ($res !== false && strlen(trim($res)) > 0);
}

function curl_upload($ip, $port, $remote_path, $local_path) {
    $fp = fopen($local_path, 'r');
    $ch = curl_init("ftp://$ip:$port$remote_path");
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($local_path));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $res = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $res;
}

// Rutas de inyección en la PS4
$rutas_destino = [
    "/user/appmeta/$cusa/icon0.png",
    "/user/appmeta/external/$cusa/icon0.png",
    "/system_data/priv/appmeta/$cusa/icon0.png",
    "/user/app/$cusa/sce_sys/icon0.png"
];

$inyeccion_exitosa = false;

// Intentar inyectar en todas las rutas que existan
foreach ($rutas_destino as $ruta) {
    $dir = dirname($ruta);
    if (check_folder_exists($host_ip, $port, $dir)) {
        if (curl_upload($host_ip, $port, $ruta, $tmp_file)) {
            $inyeccion_exitosa = true;
        }
    }
}

if ($inyeccion_exitosa) {
    // Guardamos la copia en la ruta oficial protegida
    @copy($tmp_file, "$cache_dir/$cusa.png");
    
    ob_end_clean();
    echo json_encode(['status' => 'success']);
} else {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'No se hallaron las carpetas del juego en la PS4.']);
}
exit;
?>
