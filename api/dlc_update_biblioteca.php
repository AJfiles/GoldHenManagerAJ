<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 (PS5/PS4) - API: DETECTOR Y GESTOR DE DLCS
 * DEVELOPED By SeBaS - RUTA: api/dlc_update_biblioteca.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
set_time_limit(180); // Elevado para calcular pesos precisos sin cortes

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? 'scan';
$host_ip = $_POST['host_ip'] ?? '';
$cusa = strtoupper(trim($_POST['cusa_id'] ?? ''));
$port = isset($_POST['port']) ? (int)$_POST['port'] : 2121;
$force = $_POST['force'] ?? '0';

if (!$host_ip || !$cusa) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos de consola.']);
    exit;
}

// 1. SISTEMA DE CACHÉ JSON (Lectura en 1 milisegundo)
$cache_dir = '../cache_biblioteca';
if (!file_exists($cache_dir)) { @mkdir($cache_dir, 0777, true); }
$cache_file = $cache_dir . "/dlc_info_{$cusa}.json";

// ==========================================
// ACCIÓN: ELIMINAR CONTENIDO QUIRÚRGICO (FTP)
// ==========================================
if ($action === 'delete_content') {
    $target_path = $_POST['target_path'] ?? '';
    
    // Medida de seguridad extrema: Solo permitimos borrar dentro de patch o addcont
    if (!$target_path || (strpos($target_path, '/patch/') === false && strpos($target_path, '/addcont/') === false)) {
        echo json_encode(['status' => 'error', 'message' => 'Ruta protegida por el sistema.']);
        exit;
    }
    
    function curl_ftp_delete_recursive($ip, $port, $dir) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "ftp://$ip:$port" . rtrim($dir, '/') . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        curl_close($ch);
        
        if ($res !== false) {
            $lines = explode("\n", trim($res));
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $parts = preg_split('/\s+/', trim($line), 9);
                if (count($parts) >= 9) {
                    $name = trim($parts[8]);
                    if ($name !== '.' && $name !== '..') {
                        $path = rtrim($dir, '/') . '/' . $name;
                        if ($parts[0][0] === 'd') {
                            curl_ftp_delete_recursive($ip, $port, $path);
                        } else {
                            $ch_del = curl_init("ftp://$ip:$port/");
                            curl_setopt($ch_del, CURLOPT_QUOTE, ["DELE $path"]);
                            curl_setopt($ch_del, CURLOPT_RETURNTRANSFER, true);
                            curl_exec($ch_del);
                            curl_close($ch_del);
                        }
                    }
                }
            }
            // Borrar la carpeta contenedora final
            $ch_rmd = curl_init("ftp://$ip:$port/");
            curl_setopt($ch_rmd, CURLOPT_QUOTE, ["RMD $dir"]);
            curl_setopt($ch_rmd, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch_rmd);
            curl_close($ch_rmd);
            return true;
        }
        return false;
    }
    
    $exito = curl_ftp_delete_recursive($host_ip, $port, $target_path);
    if ($exito) {
        @unlink($cache_file); // DESTRUIMOS EL CACHÉ PARA FORZAR ESCANEO LA PRÓXIMA VEZ
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

// ==========================================
// ACCIÓN: ESCANEO DE RUTAS Y PESOS CON CACHÉ
// ==========================================
if ($action === 'scan') {
    
    // Si no forzamos la recarga y existe el caché, lo enviamos al instante
    if ($force === '0' && file_exists($cache_file)) {
        $cached = json_decode(@file_get_contents($cache_file), true);
        if ($cached && isset($cached['status']) && $cached['status'] === 'success') {
            echo json_encode($cached);
            exit;
        }
    }

    function ftp_check_folder($ip, $port, $path) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "ftp://$ip:$port" . rtrim($path, '/') . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res === false) return false;
        
        $items = [];
        $lines = explode("\n", trim($res));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $parts = preg_split('/\s+/', trim($line), 9);
            if (count($parts) >= 9) {
                $name = trim($parts[8]);
                if ($name !== '.' && $name !== '..') {
                    $items[] = ['name' => $name, 'is_dir' => ($parts[0][0] === 'd'), 'size' => (int)$parts[4]];
                }
            }
        }
        return $items;
    }

    function calcular_peso_recursivo_curl($ip, $port, $dir) {
        $acumulador = 0;
        $items = ftp_check_folder($ip, $port, $dir);
        if ($items === false) return 0;
        foreach ($items as $item) {
            $full_route = rtrim($dir, '/') . '/' . $item['name'];
            if ($item['is_dir']) {
                $acumulador += calcular_peso_recursivo_curl($ip, $port, $full_route);
            } else {
                $acumulador += $item['size'];
            }
        }
        return $acumulador;
    }

    function format_bytes_v2($bytes) {
        if ($bytes == 0) return '0 KB';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        return number_format($bytes / 1024, 2) . ' KB';
    }

    $response = [
        'status' => 'success',
        'update' => ['installed' => false, 'location' => '', 'size_label' => '', 'path' => ''],
        'dlcs' => []
    ];

    // 1. RASTREAR UPDATES (PARCHES) Y SU PESO
    $rutas_patch = [
        ['loc' => 'Alm. Interno', 'path' => "/user/patch/$cusa"],
        ['loc' => 'Alm. Ampliado', 'path' => "/mnt/ext0/user/patch/$cusa"],
        ['loc' => 'Alm. Ampliado', 'path' => "/mnt/ext1/user/patch/$cusa"]
    ];

    foreach ($rutas_patch as $rp) {
        $p_items = ftp_check_folder($host_ip, $port, $rp['path']);
        if ($p_items !== false && count($p_items) > 0) {
            $response['update']['installed'] = true;
            $response['update']['location'] = $rp['loc'];
            $response['update']['path'] = $rp['path'];
            $bytes = calcular_peso_recursivo_curl($host_ip, $port, $rp['path']);
            $response['update']['size_label'] = format_bytes_v2($bytes);
            break;
        }
    }

    // 2. RASTREAR CONTENIDO ADICIONAL (DLCs) Y SU PESO
    $dlcs_found = [];
    $rutas_addcont = [
        ['loc' => 'Alm. Interno', 'path' => "/user/addcont/$cusa"],
        ['loc' => 'Alm. Ampliado', 'path' => "/mnt/ext0/user/addcont/$cusa"],
        ['loc' => 'Alm. Ampliado', 'path' => "/mnt/ext1/user/addcont/$cusa"]
    ];

    foreach ($rutas_addcont as $ra) {
        $a_items = ftp_check_folder($host_ip, $port, $ra['path']);
        if ($a_items !== false) {
            foreach ($a_items as $item) {
                if ($item['is_dir']) {
                    $dlc_path = $ra['path'] . '/' . $item['name'];
                    $bytes = calcular_peso_recursivo_curl($host_ip, $port, $dlc_path);
                    
                    // Evitar duplicados por symlinks
                    $exists = false;
                    foreach($dlcs_found as $d) { if($d['id'] === $item['name']) $exists = true; }
                    
                    if(!$exists) {
                        $dlcs_found[] = [
                            'id' => $item['name'], 
                            'location' => $ra['loc'],
                            'path' => $dlc_path,
                            'size_label' => format_bytes_v2($bytes)
                        ];
                    }
                }
            }
        }
    }

    $response['dlcs'] = $dlcs_found;
    
    // GUARDADO DEFINITIVO DEL CACHÉ EN JSON
    @file_put_contents($cache_file, json_encode($response));
    
    echo json_encode($response);
    exit;
}
?>
