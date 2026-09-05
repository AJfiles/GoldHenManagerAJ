<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function store_admin_bootstrap(): void {
    foreach ([STORE_DATA_DIR, STORE_COVERS_DIR] as $dir) if (!is_dir($dir)) mkdir($dir, 0775, true);
    if (!is_file(STORE_CATALOG_FILE)) file_put_contents(STORE_CATALOG_FILE, "[]\n", LOCK_EX);
}
function store_admin_catalog(): array {
    store_admin_bootstrap();
    $data = json_decode((string) file_get_contents(STORE_CATALOG_FILE), true);
    return is_array($data) ? $data : [];
}
function store_admin_save(array $catalog): void {
    usort($catalog, fn($a, $b) => strcasecmp((string)($a['titulo'] ?? ''), (string)($b['titulo'] ?? '')));
    $temp = STORE_CATALOG_FILE . '.tmp';
    file_put_contents($temp, json_encode(array_values($catalog), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
    rename($temp, STORE_CATALOG_FILE);
}
function store_admin_text(string $name, int $max = 800): string { $value = trim((string)($_POST[$name] ?? '')); return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max); }
function store_admin_id(string $id): string {
    $id = strtoupper(trim($id));
    /* CUSA, SLUS/SLES, NP y otros identificadores de aplicaciones/homebrew. */
    if (!preg_match('/^[A-Z0-9][A-Z0-9._-]{2,31}$/', $id)) throw new RuntimeException('Usa un ID de 3 a 32 caracteres: letras, números, punto, guion o guion bajo.');
    return $id;
}
function store_admin_not_applicable(string $value): bool { return in_array(strtolower(trim($value)), ['', 'n/a', 'na', 'no aplica', 'none', '-'], true); }
function store_admin_url(string $url, bool $optional = false): ?string {
    $url = trim($url); if ($optional && store_admin_not_applicable($url)) return null;
    $parts = parse_url($url);
    if (!$parts || !in_array(strtolower((string)($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host']) || !preg_match('/\.pkg(?:$|[?#])/i', $url)) throw new RuntimeException('Usa una URL directa http(s) que termine en .pkg.');
    return $url;
}
function store_admin_dlc(string $value): array {
    if (store_admin_not_applicable($value)) return [];
    $out = []; foreach (preg_split('/[\r\n,]+/', $value) as $url) { $url = trim($url); if (!store_admin_not_applicable($url)) $out[] = store_admin_url($url); } return array_values(array_unique($out));
}
/* Comprueba y aplica únicamente avances lineales del repositorio. No sobrescribe
   cambios locales del mantenedor, incluidas ediciones manuales del panel. */
function store_admin_repository_update(bool $apply): string {
    if (!function_exists('exec')) throw new RuntimeException('PHP no permite ejecutar Git en este servidor.');
    $root = dirname(STORE_ROOT);
    $run = static function (string $arguments) use ($root): array {
        $output = []; $code = 1;
        exec('git -C ' . escapeshellarg($root) . ' ' . $arguments . ' 2>&1', $output, $code);
        return [$code, trim(implode("\n", $output))];
    };
    [$code, $output] = $run('rev-parse --is-inside-work-tree');
    if ($code !== 0 || $output !== 'true') throw new RuntimeException('La instalación no es un repositorio Git válido.');
    if ($apply) {
        [$code, $dirty] = $run('status --porcelain');
        if ($code !== 0) throw new RuntimeException('No se pudo consultar el estado de Git.');
        if ($dirty !== '') throw new RuntimeException('Hay cambios locales. Súbelos o resuélvelos antes de actualizar; no se sobrescribieron.');
    }
    [$code, $fetch] = $run('fetch --quiet origin main');
    if ($code !== 0) throw new RuntimeException('No se pudo consultar GitHub: ' . $fetch);
    [$code, $counts] = $run('rev-list --left-right --count HEAD...origin/main');
    if ($code !== 0 || !preg_match('/^(\d+)\s+(\d+)$/', $counts, $matches)) throw new RuntimeException('No se pudo comparar la versión local.');
    $behind = (int)$matches[2];
    if (!$apply) return $behind > 0 ? "Hay $behind actualización(es) disponible(s)." : 'Ya tienes la última versión disponible.';
    if ($behind === 0) return 'Ya tienes la última versión disponible.';
    [$code, $pull] = $run('pull --ff-only origin main');
    if ($code !== 0) throw new RuntimeException('La actualización no se aplicó: ' . $pull);
    return "Actualización aplicada ($behind cambio(s)). Reinicia store-admin para usar los archivos nuevos.";
}
function store_admin_cover(string $id, ?string $previous): ?string {
    if (empty($_FILES['cover']) || $_FILES['cover']['error'] === UPLOAD_ERR_NO_FILE) return $previous;
    $upload = $_FILES['cover'];
    if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > STORE_ADMIN_MAX_IMAGE_BYTES) throw new RuntimeException('La carátula debe ser una imagen válida de hasta 5 MB.');
    $info = @getimagesize($upload['tmp_name']); $mime = $info['mime'] ?? '';
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) throw new RuntimeException('La carátula debe ser JPG, PNG o WebP.');
    /* En Termux, GD puede estar ausente o compilarse sin WebP. Si eso ocurre,
       se conserva la imagen original en formato seguro en vez de bloquear el
       alta del catálogo. */
    if (!function_exists('imagecreatetruecolor')) {
        $extension = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
        $destination = STORE_COVERS_DIR . '/' . $id . '.' . $extension;
        if (!move_uploaded_file($upload['tmp_name'], $destination)) throw new RuntimeException('No se pudo guardar la carátula.');
        return '/store/covers/' . basename($destination);
    }
    $source = match ($mime) { 'image/jpeg' => @imagecreatefromjpeg($upload['tmp_name']), 'image/png' => @imagecreatefrompng($upload['tmp_name']), 'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($upload['tmp_name']) : false };
    if (!$source) throw new RuntimeException('No se pudo abrir la carátula. Instala php-gd para convertirla o usa JPG/PNG.');
    $width = imagesx($source); $height = imagesy($source); $scale = min(400 / $width, 400 / $height, 1); $nw = max(1, (int) round($width * $scale)); $nh = max(1, (int) round($height * $scale));
    $canvas = imagecreatetruecolor($nw, $nh); imagealphablending($canvas, false); imagesavealpha($canvas, true); imagecopyresampled($canvas, $source, 0, 0, 0, 0, $nw, $nh, $width, $height);
    if (function_exists('imagewebp')) { $destination = STORE_COVERS_DIR . '/' . $id . '.webp'; $ok = @imagewebp($canvas, $destination, 82); }
    else { $destination = STORE_COVERS_DIR . '/' . $id . '.jpg'; $ok = @imagejpeg($canvas, $destination, 86); }
    if (!$ok) throw new RuntimeException('No se pudo convertir ni guardar la carátula.');
    return '/store/covers/' . basename($destination);
}
function store_admin_entry(?array $previous = null): array {
    $id = store_admin_id(store_admin_text('id', 20));
    $title = store_admin_text('titulo', 160);
    if ($title === '') throw new RuntimeException('El título es obligatorio.');
    $weight = store_admin_text('peso_gb', 20); if ($weight !== '' && !is_numeric($weight)) throw new RuntimeException('El peso debe ser numérico o quedar vacío.');
    if (empty($_POST['licencia_confirmada'])) throw new RuntimeException('Debes confirmar que puedes publicar el contenido y sus enlaces.');
    return ['id' => $id, 'titulo' => $title, 'subtitulo' => store_admin_text('subtitulo', 160), 'categoria' => store_admin_text('categoria', 40) ?: 'PS4', 'version' => store_admin_text('version', 32) ?: '1.00', 'peso_gb' => $weight === '' ? null : (float)$weight, 'descripcion' => store_admin_text('descripcion', 1000), 'creditos' => store_admin_text('creditos', 160), 'servidor' => store_admin_text('servidor', 80), 'licencia_confirmada' => true, 'enlaces' => ['pkg' => store_admin_url(store_admin_text('pkg', 2048)), 'update' => store_admin_url(store_admin_text('update', 2048), true), 'dlc' => store_admin_dlc((string)($_POST['dlc'] ?? ''))], 'cover' => store_admin_cover($id, $previous['cover'] ?? null)];
}
function store_admin_zip(array $updated, array $deleted): void {
    $files = ['store/data/catalogo.json' => STORE_CATALOG_FILE]; foreach ($updated as $relative) { $path = dirname(STORE_ROOT) . '/' . $relative; if (is_file($path)) $files[$relative] = $path; }
    $manifest = ['updated' => array_keys($files), 'deleted' => array_values(array_unique($deleted)), 'generated_at' => gmdate('c')];
    $temp = tempnam(sys_get_temp_dir(), 'ghm_store_') . '.zip';
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive(); if ($zip->open($temp, ZipArchive::CREATE) !== true) throw new RuntimeException('No se pudo crear el ZIP.');
        foreach ($files as $relative => $path) $zip->addFile($path, $relative); $zip->addFromString('store-changes.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); $zip->close();
    } elseif (function_exists('shell_exec') && trim((string) shell_exec('command -v zip'))) {
        $dir = sys_get_temp_dir() . '/ghm_store_' . bin2hex(random_bytes(6)); mkdir($dir, 0700, true);
        foreach ($files as $relative => $path) { $target = $dir . '/' . $relative; if (!is_dir(dirname($target))) mkdir(dirname($target), 0700, true); copy($path, $target); }
        file_put_contents($dir . '/store-changes.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $command = 'cd ' . escapeshellarg($dir) . ' && zip -qr ' . escapeshellarg($temp) . ' .'; $status = 1; system($command, $status);
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()); rmdir($dir);
        if ($status !== 0) throw new RuntimeException('No se pudo crear el ZIP con la utilidad zip.');
    } else throw new RuntimeException('No hay soporte ZIP. Instala el paquete zip desde Termux y vuelve a abrir el administrador.');
    header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="goldhen-store-changes-' . date('Ymd-His') . '.zip"'); header('Content-Length: ' . filesize($temp)); readfile($temp); unlink($temp); exit;
}
