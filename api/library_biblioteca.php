<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 - API CORE DE LA BIBLIOTECA
 * DEVELOPED By SeBaS - RUTA: api/library_biblioteca.php
 * ====================================================================
 */
error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('memory_limit', '512M');
set_time_limit(300);

header('Content-Type: application/json; charset=utf-8');
$firma = chr(83).chr(101).chr(66).chr(97).chr(83); 
header('X-Author: ' . $firma);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$host_ip = $_POST['host_ip'] ?? $_GET['host_ip'] ?? '';
$cusa_id = strtoupper(trim($_POST['cusa_id'] ?? $_GET['cusa_id'] ?? ''));
$port = isset($_POST['port']) ? (int)$_POST['port'] : 2121;

// 🔥 RUTA CORREGIDA HACIA LA NUEVA ESTRUCTURA USER
$cache_dir = '../user/cache/biblioteca';
if (!file_exists($cache_dir)) { @mkdir($cache_dir, 0777, true); }
if (!file_exists($cache_dir . '/.nomedia')) { @file_put_contents($cache_dir . '/.nomedia', ''); }

$db_categorias_file = $cache_dir . '/custom_categories.json';
$custom_cats = [];
if (file_exists($db_categorias_file)) {
    $custom_cats = json_decode(@file_get_contents($db_categorias_file), true) ?: [];
}

/**
 * PARSEADOR SFO ORIGINAL DE SEBAS (RESTAURADO PARA NOMBRES EXACTOS)
 */
function parse_sfo_real($filepath) {
    $buffer = @file_get_contents($filepath);
    if (!$buffer) return false;
    
    $sfo_pos = strpos($buffer, "\0PSF");
    if ($sfo_pos !== false) {
        $sfo_data = substr($buffer, $sfo_pos, 65536);
        $magic = substr($sfo_data, 0, 4);
        if ($magic === "\0PSF") {
            $meta = [];
            $key_table_offset = unpack('V', substr($sfo_data, 8, 4))[1];
            $data_table_offset = unpack('V', substr($sfo_data, 12, 4))[1];
            $entries = unpack('V', substr($sfo_data, 16, 4))[1];
            
            for ($i = 0; $i < $entries; $i++) {
                $entry_offset = 20 + ($i * 16);
                if ($entry_offset + 16 > strlen($sfo_data)) break;
                $key_offset = unpack('v', substr($sfo_data, $entry_offset, 2))[1];
                $data_len = unpack('V', substr($sfo_data, $entry_offset + 4, 4))[1];
                $data_offset = unpack('V', substr($sfo_data, $entry_offset + 12, 4))[1];
                
                $key = '';
                $pos = $key_table_offset + $key_offset;
                while ($pos < strlen($sfo_data) && $sfo_data[$pos] !== "\0") {
                    $key .= $sfo_data[$pos];
                    $pos++;
                }
                
                if ($key === 'TITLE' || $key === 'APP_TITLE' || $key === 'APP_VER' || $key === 'CATEGORY') {
                    $val = substr($sfo_data, $data_table_offset + $data_offset, $data_len);
                    $val_clean = trim(str_replace("\0", '', $val));
                    $meta[$key] = preg_replace('/[\x00-\x1F\x7F]/u', '', $val_clean);
                }
            }
            return $meta;
        }
    }
    return false;
}

function curl_download_ftp_file($ip, $port, $remote_path, $local_path) {
    $ch = curl_init("ftp://$ip:$port" . $remote_path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    
    if ($data !== false && strlen($data) > 0) {
        @file_put_contents($local_path, $data);
        return true;
    }
    return false;
}

function clean_title_sfo($parsed_array, $cusa) {
    if (!$parsed_array) return $cusa;
    $title = $parsed_array['TITLE'] ?? $parsed_array['APP_TITLE'] ?? $cusa;
    if (preg_match('/^\d+$/', trim($title))) { return $cusa; }
    return trim(preg_replace('/\s+/', ' ', $title));
}

if ($action === 'get_cached_games') {
    $games = [];
    $sfos_locales = glob($cache_dir . '/*.sfo');
    
    if (is_array($sfos_locales)) {
        foreach ($sfos_locales as $sfo_path) {
            $cusa = pathinfo($sfo_path, PATHINFO_FILENAME);
            if (preg_match('/^[A-Z]{4}\d{5}$/i', $cusa) || $cusa === 'APOLO0004' || strpos($cusa, 'LAPY') === 0 || strpos($cusa, 'NPXS') === 0) {
                $icon_path = $cache_dir . '/' . $cusa . '.png';
                $title = $cusa; 
                $version = "1.00";
                $cat_key = 'g';
                
                if (@filesize($sfo_path) > 0) {
                    $sfo_data = parse_sfo_real($sfo_path);
                    if ($sfo_data) {
                        $title = clean_title_sfo($sfo_data, $cusa);
                        if (!empty($sfo_data['APP_VER'])) { $version = $sfo_data['APP_VER']; }
                        if (!empty($sfo_data['CATEGORY'])) { $cat_key = $sfo_data['CATEGORY']; }
                    }
                }
                
                $tipo_defecto = (strpos($cat_key, 'g') !== false) ? 'JUEGOS' : 'APPS';
                if (in_array($cusa, ['TOOL00001', 'NPXS29005', 'NPXS30017', 'APOLO0004']) || strpos($cusa, 'LAPY') === 0 || strpos($cusa, 'NPXS') === 0) {
                    $tipo_defecto = 'APPS';
                }
                $tipo_final = $custom_cats[$cusa] ?? $tipo_defecto;

                $games[] = [ 
                    'id' => $cusa, 
                    'nombre' => $title, 
                    'tipo' => $tipo_final,
                    'version' => $version,
                    'size' => '23.5 GB',
                    // 🔥 RUTA WEB CORREGIDA A USER/CACHE/BIBLIOTECA
                    'img' => 'user/cache/biblioteca/' . $cusa . '.png?v=' . @filemtime($icon_path)
                ];
            }
        }
    }
    usort($games, function($a, $b) { return strcasecmp($a['nombre'], $b['nombre']); });
    echo json_encode(['status' => 'success', 'data' => $games]);
    exit;
}

if ($action === 'scan') {
    $rutas_appmeta = [
        "/user/appmeta", "/system_data/priv/appmeta",
        "/user/appmeta/external", "/system_data/priv/appmeta/external"
    ];

    $cusa_detectados = [];
    $escaneo_exitoso = false;

    foreach ($rutas_appmeta as $base_path) {
        $ch = curl_init("ftp://$host_ip:$port" . $base_path . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "LIST");
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $res = curl_exec($ch);
        curl_close($ch);

        if ($res !== false) {
            $escaneo_exitoso = true;
            $lines = explode("\n", trim($res));
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $parts = preg_split('/\s+/', trim($line), 9);
                if (count($parts) >= 9) {
                    $folder_name = trim($parts[8]);
                    if (preg_match('/^[A-Z]{4}\d{5}$/i', $folder_name) || strpos($folder_name, 'LAPY') === 0 || strpos($folder_name, 'NPXS') === 0) {
                        $cusa_detectados[strtoupper($folder_name)] = $base_path;
                    }
                }
            }
        }
    }

    if ($escaneo_exitoso && count($cusa_detectados) > 0) {
        $sfos_existentes = glob($cache_dir . '/*.sfo');
        if (is_array($sfos_existentes)) {
            foreach ($sfos_existentes as $sfo_path) {
                $cusa_local = pathinfo($sfo_path, PATHINFO_FILENAME);
                if (!isset($cusa_detectados[$cusa_local]) && $cusa_local !== 'APOLO0004') {
                    @unlink($cache_dir . '/' . $cusa_local . '.png');
                    @unlink($sfo_path);
                    @unlink($cache_dir . '/sizes_' . $cusa_local . '.txt');
                    // 🔥 RUTA CORREGIDA PARA BORRAR EL ICONO CUSTOM SI EL JUEGO SE ELIMINA
                    @unlink('../user/.iconos/' . $cusa_local . '.jpg');
                    if (isset($custom_cats[$cusa_local])) { unset($custom_cats[$cusa_local]); }
                }
            }
            @file_put_contents($db_categorias_file, json_encode($custom_cats, JSON_PRETTY_PRINT));
        }
    }

    echo json_encode(['status' => 'success', 'games' => $cusa_detectados]);
    exit;
}

if ($action === 'get_game_data') {
    if (!$host_ip || !$cusa_id) { echo json_encode(['status' => 'error']); exit; }

    $base_route = $_POST['base_path'] ?? $_GET['base_path'] ?? '';
    $local_icon = $cache_dir . '/' . $cusa_id . '.png';
    $local_sfo = $cache_dir . '/' . $cusa_id . '.sfo';

    $has_icon = (file_exists($local_icon) && @filesize($local_icon) > 0);
    $has_sfo = (file_exists($local_sfo) && @filesize($local_sfo) > 0);

    if ($has_icon && $has_sfo) {
        $parsed = parse_sfo_real($local_sfo);
        if ($parsed) {
            $title = clean_title_sfo($parsed, $cusa_id);
            $version = $parsed['APP_VER'] ?? 'v1.00';
            $category_key = $parsed['CATEGORY'] ?? 'g';
            $tipo_por_defecto = (strpos($category_key, 'g') !== false) ? 'JUEGOS' : 'APPS';
            if ($cusa_id === 'APOLO0004' || strpos($cusa_id, 'LAPY') === 0 || strpos($cusa_id, 'NPXS') === 0) { $tipo_por_defecto = 'APPS'; }
            $tipo_final = $custom_cats[$cusa_id] ?? $tipo_por_defecto;

            echo json_encode([
                'status' => 'success',
                'skipped' => true,
                'game' => [
                    'id' => $cusa_id,
                    'nombre' => $title,
                    'tipo' => $tipo_final,
                    'version' => $version,
                    'size' => '23.5 GB',
                    // 🔥 RUTA WEB CORREGIDA
                    'img' => 'user/cache/biblioteca/' . $cusa_id . '.png?v=' . @filemtime($local_icon)
                ]
            ]);
            exit;
        }
    }

    if (!$has_sfo) {
        $rutas_sfo_posibles = [];
        if (!empty($base_route)) { $rutas_sfo_posibles[] = rtrim($base_route, '/') . '/' . $cusa_id . '/param.sfo'; }
        $rutas_sfo_posibles = array_merge($rutas_sfo_posibles, [
            "/user/appmeta/$cusa_id/param.sfo",
            "/system_data/priv/appmeta/$cusa_id/param.sfo",
            "/user/appmeta/external/$cusa_id/param.sfo",
            "/user/app/$cusa_id/sce_sys/param.sfo"
        ]);
        foreach ($rutas_sfo_posibles as $r_sfo) {
            if (curl_download_ftp_file($host_ip, $port, $r_sfo, $local_sfo)) { $has_sfo = true; break; }
        }
    }

    if (!file_exists($local_sfo) || @filesize($local_sfo) == 0) {
        @unlink($local_sfo);
        echo json_encode(['status' => 'error', 'message' => 'Archivo no legible en la consola']);
        exit;
    }

    $parsed = parse_sfo_real($local_sfo);
    if (!$parsed || empty($parsed['TITLE'])) {
        @unlink($local_sfo);
        echo json_encode(['status' => 'error', 'message' => 'Datos de metadatos no viables']);
        exit;
    }

    if (!$has_icon) {
        $rutas_maestras_iconos = [];
        if (!empty($base_route)) {
            $rutas_maestras_iconos[] = rtrim($base_route, '/') . '/' . $cusa_id . '/icon0.png';
            $rutas_maestras_iconos[] = rtrim($base_route, '/') . '/' . $cusa_id . '/icono.png';
            $rutas_maestras_iconos[] = rtrim($base_route, '/') . '/' . $cusa_id . '/ICON0.PNG';
        }
        $rutas_maestras_iconos = array_merge($rutas_maestras_iconos, [
            "/user/appmeta/$cusa_id/icon0.png",
            "/user/appmeta/$cusa_id/icono.png",
            "/user/appmeta/$cusa_id/ICON0.PNG",
            "/system_data/priv/appmeta/$cusa_id/icon0.png",
            "/user/appmeta/external/$cusa_id/icon0.png",
            "/user/app/$cusa_id/sce_sys/icon0.png",
            "/user/app/$cusa_id/sce_sys/icono.png",
            "/user/app/$cusa_id/sce_sys/ICON0.PNG"
        ]);
        $rutas_maestras_iconos = array_unique($rutas_maestras_iconos);

        foreach ($rutas_maestras_iconos as $r_icon) {
            if (curl_download_ftp_file($host_ip, $port, $r_icon, $local_icon)) { $has_icon = true; break; }
        }
    }

    $title = clean_title_sfo($parsed, $cusa_id);
    $version = $parsed['APP_VER'] ?? 'v1.00';
    $category_key = $parsed['CATEGORY'] ?? 'g';
    
    $tipo_por_defecto = (strpos($category_key, 'g') !== false) ? 'JUEGOS' : 'APPS';
    if (strpos($cusa_id, 'LAPY') === 0 || $cusa_id === 'APOLO0004') { $tipo_por_defecto = 'APPS'; }
    $tipo_final = $custom_cats[$cusa_id] ?? $tipo_por_defecto;

    // 🔥 RUTA WEB CORREGIDA
    $final_img = (file_exists($local_icon) && @filesize($local_icon) > 0) 
        ? 'user/cache/biblioteca/' . $cusa_id . '.png?v=' . @filemtime($local_icon)
        : 'https://images.unsplash.com/photo-1612287230202-1bf1d85d1bdf?w=400&q=80';

    echo json_encode([
        'status' => 'success',
        'skipped' => false,
        'game' => [
            'id' => $cusa_id,
            'nombre' => $title,
            'tipo' => $tipo_final,
            'version' => $version,
            'size' => '23.5 GB',
            'img' => $final_img
        ]
    ]);
    exit;
}

if ($action === 'cambiar_categoria') {
    $nueva_cat = strtoupper(trim($_POST['categoria'] ?? ''));
    if (!$cusa_id || !$nueva_cat) { echo json_encode(['status' => 'error']); exit; }
    $custom_cats[$cusa_id] = $nueva_cat;
    @file_put_contents($db_categorias_file, json_encode($custom_cats, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'eliminar_categoria_global') {
    $cat_a_borrar = strtoupper(trim($_POST['categoria'] ?? ''));
    if (empty($cat_a_borrar) || in_array($cat_a_borrar, ['TODOS', 'JUEGOS', 'APPS'])) {
        echo json_encode(['status' => 'error']);
        exit;
    }
    foreach ($custom_cats as $cusa_key => $valor_cat) {
        if ($valor_cat === $cat_a_borrar) { unset($custom_cats[$cusa_key]); }
    }
    @file_put_contents($db_categorias_file, json_encode($custom_cats, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}
?>
