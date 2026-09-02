<?php
/**
 * ====================================================================
 * GOLD HEN SUITE PRO 🚀 - API DOWNLOADER DE PORTADAS PREMIUM (HD)
 * DEVELOPED By SeBaS - RUTA: api/covers_db.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('memory_limit', '256M');

header('Content-Type: application/json; charset=utf-8');
$firma = chr(83).chr(101).chr(66).chr(97).chr(83); 
header('X-Author: ' . $firma);

$cusa = strtoupper(trim($_POST['cusa_id'] ?? $_GET['cusa_id'] ?? ''));
$nombre_juego = trim($_POST['nombre_juego'] ?? $_GET['nombre_juego'] ?? '');

if (!$cusa) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el identificador CUSA']);
    exit;
}

$cache_hd_dir = '../cache_portadas_hd';
if (!file_exists($cache_hd_dir)) { @mkdir($cache_hd_dir, 0777, true); }

$img_data = null;
$descarga_exitosa = false;

// 1. Repositorio Oficial GitHub - PlayStation Store Icons
$url_repo1 = "https://raw.githubusercontent.com/AnEmortalKid/PlayStation-Icon-Pack/master/icons/{$cusa}-icon.png";
$ch = curl_init($url_repo1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$img_data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 && strlen($img_data) > 1000) {
    $descarga_exitosa = true;
}

// 2. Repositorio DB Abierto si falla el primero (Busca por Nombre Limpio)
if (!$descarga_exitosa && !empty($nombre_juego)) {
    $query_encode = urlencode($nombre_juego);
    $url_repo2 = "https://tome.gg/api/v1/games/search?query={$query_encode}";
    
    $ch2 = curl_init($url_repo2);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
    $res2 = curl_exec($ch2);
    curl_close($ch2);
    
    $json2 = json_decode($res2, true);
    if (!empty($json2['results'][0]['image_url'])) {
        $img_data = @file_get_contents($json2['results'][0]['image_url']);
        if ($img_data && strlen($img_data) > 1000) {
            $descarga_exitosa = true;
        }
    }
}

// 3. Respaldo Final Open-Covers
if (!$descarga_exitosa) {
    $ch3 = curl_init("https://open-covers.org/api/v1/cover/ps4/{$cusa}");
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch3, CURLOPT_TIMEOUT, 5);
    $res3 = curl_exec($ch3);
    curl_close($ch3);
    
    $json3 = json_decode($res3, true);
    if (!empty($json3['cover_url'])) {
        $img_data = @file_get_contents($json3['cover_url']);
        if ($img_data && strlen($img_data) > 1000) {
            $descarga_exitosa = true;
        }
    }
}

if ($descarga_exitosa && $img_data) {
    $local_path = $cache_hd_dir . '/' . $cusa . '.jpg';
    @file_put_contents($local_path, $img_data);
    echo json_encode(['status' => 'success', 'path' => 'cache_portadas_hd/' . $cusa . '.jpg']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Sin coincidencia vertical en el servidor remoto.']);
}
exit;
?>
