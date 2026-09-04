<?php
/* Configuración privada del administrador local de la tienda. */
declare(strict_types=1);

define('STORE_ROOT', dirname(__DIR__));
define('STORE_DATA_DIR', STORE_ROOT . '/data');
define('STORE_COVERS_DIR', STORE_ROOT . '/covers');
define('STORE_CATALOG_FILE', STORE_DATA_DIR . '/catalogo.json');
define('STORE_ADMIN_MAX_IMAGE_BYTES', 5 * 1024 * 1024);

function store_admin_start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('ghm_store_admin');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
        session_start();
    }
}

function store_admin_authorized(): bool {
    store_admin_start_session();
    $token = (string) getenv('STORE_ADMIN_TOKEN');
    if (isset($_GET['token']) && $token !== '' && hash_equals($token, (string) $_GET['token'])) {
        session_regenerate_id(true);
        $_SESSION['store_admin_ok'] = true;
    }
    return !empty($_SESSION['store_admin_ok']);
}
