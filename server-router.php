<?php
// Enrutador del servidor LAN: impide descargar archivos internos de user/.
$uriPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if (strpos($uriPath, "\0") !== false || strpos($uriPath, '\\') !== false || in_array('..', explode('/', $uriPath), true)) {
    http_response_code(400);
    exit;
}

if (preg_match('#^/user(?:/|$)#i', $uriPath)) {
    // Solo imágenes de caché se sirven al navegador. PKG se entrega mediante
    // api/rpi_file.php, que valida el nombre y nunca lista la carpeta privada.
    if (!preg_match('#^/user/cache/.+\.(jpg|jpeg|png|webp|gif|avif)$#i', $uriPath)) {
        http_response_code(404);
        exit;
    }
    $cacheRoot = realpath(__DIR__ . '/user/cache');
    $cacheFile = realpath(__DIR__ . $uriPath);
    if ($cacheRoot === false || $cacheFile === false || strpos($cacheFile, $cacheRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($cacheFile)) {
        http_response_code(404);
        exit;
    }
    return false;
}

if (preg_match('#^/(?:\.git|store/admin)(?:/|$)#i', $uriPath) || preg_match('#\.sh$#i', $uriPath)) {
    http_response_code(404);
    exit;
}

// Enlace directo sin query string para RPI: algunos builds convierten `?id=`
// en parte de la ruta (%3F) y no pueden descargar el encabezado del PKG.
if (preg_match('#^/rpi/([a-f0-9]{24})/([a-f0-9]{32,64})/([A-Za-z0-9._-]+\.pkg)$#i', $uriPath, $rpiMatch)) {
    $_GET['job'] = strtolower($rpiMatch[1]);
    $_GET['id'] = strtolower($rpiMatch[2]);
    $_GET['path_name'] = $rpiMatch[3];
    require __DIR__ . '/api/rpi_file.php';
    exit;
}

$file = realpath(__DIR__ . $uriPath);
$root = realpath(__DIR__);
if ($file !== false && $root !== false && strpos($file, $root . DIRECTORY_SEPARATOR) === 0 && is_file($file)) {
    return false;
}
if ($uriPath === '/' || $uriPath === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}
http_response_code(404);
