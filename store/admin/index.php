<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';
store_admin_bootstrap();
if (!store_admin_authorized()) { http_response_code(403); ?>
<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><script src="https://cdn.tailwindcss.com"></script><body class="min-h-screen bg-[#060913] text-white grid place-items-center p-5"><main class="max-w-md rounded-3xl border border-white/10 bg-white/5 p-7 text-center"><h1 class="text-xl font-black">Administración de Store</h1><p class="mt-3 text-sm text-gray-400">Este panel solo se abre mediante el comando <code class="text-cyan-300">store-admin</code> en Termux. El enlace local generado por ese comando concede una sesión temporal.</p></main></body></html><?php exit; }

// ============================================================
// FUNCIÓN DE EXTRACCIÓN DE ENLACES MEDIAFIRE (MEJORADA)
// ============================================================
/**
 * Extrae enlace directo de MediaFire (.pkg) desde una URL de página web
 */
function extraerEnlaceMediaFire($url) {
    // Si ya es un enlace directo .pkg, devolverlo
    if (preg_match('/\.pkg($|\?)/i', $url)) {
        return $url;
    }
    
    // Si no es MediaFire, devolver la URL original
    if (strpos($url, 'mediafire.com') === false) {
        return $url;
    }
    
    // Intentar extraer el enlace directo con cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    
    $html = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if (!$html || !empty($error)) {
        return $url;
    }
    
    // Buscar el enlace directo (patrón de MediaFire)
    // Ejemplo: https://downloadXXXX.mediafire.com/.../archivo.pkg
    if (preg_match('/https?:\/\/download[0-9]+\.mediafire\.com\/[^"\'\s]+\.pkg/i', $html, $matches)) {
        return $matches[0];
    }
    
    // Buscar cualquier enlace .pkg
    if (preg_match('/https?:\/\/[^"\'\s]+\.pkg/i', $html, $matches)) {
        if ($matches[0] !== $url) {
            return $matches[0];
        }
    }
    
    // Buscar en href de botones de descarga
    if (preg_match('/href=["\']([^"\']+\.pkg[^"\']*)["\']/i', $html, $matches)) {
        return $matches[1];
    }
    
    // Buscar en JavaScript (window.location.href)
    if (preg_match('/window\.location\.href\s*=\s*["\']([^"\']+)["\']/i', $html, $matches)) {
        return $matches[1];
    }
    
    // Buscar en el atributo data-link (a veces usado por MediaFire)
    if (preg_match('/data-link=["\']([^"\']+\.pkg[^"\']*)["\']/i', $html, $matches)) {
        return $matches[1];
    }
    
    // Si no se encontró nada, devolver la URL original
    return $url;
}

$message = ''; $changedUpdated = $_SESSION['store_updated'] ?? []; $changedDeleted = $_SESSION['store_deleted'] ?? [];
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        // ============================================================
        // MANEJADOR PARA EL BOTÓN "EXTRAER" (AJAX)
        // DEBE IR ANTES DE CUALQUIER OTRA ACCIÓN
        // ============================================================
        if ($action === 'extract') {
            header('Content-Type: application/json');
            $url = store_admin_url((string)($_POST['url'] ?? ''));
            if (empty($url)) {
                echo json_encode(['success' => false, 'message' => 'URL vacía']);
                exit;
            }
            $directo = extraerEnlaceMediaFire($url);
            if ($directo !== $url) {
                echo json_encode(['success' => true, 'direct_link' => $directo]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo extraer el enlace directo. Intenta manualmente.']);
            }
            exit;
        }
        
        // ============================================================
        // LAS DEMÁS ACCIONES (download, save, delete, check)
        // ============================================================
        if ($action === 'download') store_admin_zip($changedUpdated, $changedDeleted);
        $catalog = store_admin_catalog();
        
        if ($action === 'save') {
            $original = strtoupper(trim((string)($_POST['original_id'] ?? ''))); $position = null; foreach ($catalog as $i => $row) if (($row['id'] ?? '') === $original) $position = $i;
            $before = $position === null ? null : $catalog[$position];
            
            // ============================================================
            // EXTRACCIÓN AUTOMÁTICA AL GUARDAR
            // ============================================================
            $pkg_url = store_admin_url((string)($_POST['pkg'] ?? ''));
            // Si es MediaFire, extraer automáticamente
            if (strpos($pkg_url, 'mediafire.com') !== false) {
                $extraido = extraerEnlaceMediaFire($pkg_url);
                if ($extraido !== $pkg_url) {
                    $_POST['pkg'] = $extraido;
                    $message = '✅ Enlace extraído automáticamente. ';
                }
            }
            
            $entry = store_admin_entry($before);
            foreach ($catalog as $row) if (($row['id'] ?? '') === $entry['id'] && $row !== $before) throw new RuntimeException('Ya existe otro elemento con ese ID.');
            if ($position === null) $catalog[] = $entry; else $catalog[$position] = $entry; store_admin_save($catalog);
            $changedUpdated = []; if (($entry['cover'] ?? '') !== ($before['cover'] ?? '') && !empty($entry['cover'])) $changedUpdated[] = $entry['cover'];
            $_SESSION['store_updated'] = $changedUpdated; $_SESSION['store_deleted'] = []; $changedDeleted = [];
            $message = ($message ?? '') . ($position === null ? 'Elemento añadido. Descarga el paquete de cambios.' : 'Elemento actualizado. Descarga el paquete de cambios.');
        }
        if ($action === 'delete') {
            $id = store_admin_id((string)($_POST['id'] ?? '')); $removed = null; $catalog = array_values(array_filter($catalog, function ($row) use ($id, &$removed) { if (($row['id'] ?? '') === $id) { $removed = $row; return false; } return true; }));
            if (!$removed) throw new RuntimeException('No se encontró el elemento.'); store_admin_save($catalog); $deleted = !empty($removed['cover']) ? [$removed['cover']] : [];
            $_SESSION['store_updated'] = []; $_SESSION['store_deleted'] = $deleted; $changedUpdated = []; $changedDeleted = $deleted;
            $message = 'Elemento retirado. El ZIP incluye un manifiesto con cualquier carátula que debes borrar del repositorio.';
        }
        if ($action === 'check') {
            $url = store_admin_url((string)($_POST['pkg'] ?? '')); $ch = curl_init($url); curl_setopt_array($ch, [CURLOPT_NOBODY=>true, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_MAXREDIRS=>3, CURLOPT_TIMEOUT=>15, CURLOPT_RETURNTRANSFER=>true]); curl_exec($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $length = (int)curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD); $error = curl_error($ch); curl_close($ch);
            $message = $status >= 200 && $status < 400 ? 'Enlace disponible' . ($length > 0 ? ' · ' . number_format($length / 1073741824, 2) . ' GB detectados.' : ' · el servidor no publicó su tamaño.') : 'No se pudo verificar: ' . ($error ?: 'HTTP ' . $status);
        }
    }
} catch (Throwable $e) { $message = 'Error: ' . $e->getMessage(); }
$catalog = store_admin_catalog(); $editId = strtoupper(trim((string)($_GET['edit'] ?? ''))); $editing = null; foreach ($catalog as $row) if (($row['id'] ?? '') === $editId) $editing = $row;
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GoldHen Store · Admin</title><script src="https://cdn.tailwindcss.com"></script><style>body{background:#060913}.field{width:100%;border:1px solid rgb(255 255 255/.1);border-radius:.7rem;background:#02040a;padding:.65rem .75rem;color:#fff;font-size:.82rem}.field:focus{outline:none;border-color:#22d3ee}</style></head>
<body class="min-h-screen text-white p-4"><main class="mx-auto max-w-6xl"><header class="mb-5 flex flex-wrap items-center justify-between gap-3"><div><h1 class="text-xl font-black">GoldHen Store · Administrador</h1><p class="text-xs text-cyan-300">Catálogo local · paquetes autorizados · puerto 8081</p></div><div class="flex gap-2"><a href="../../index.php" class="rounded-xl bg-white/5 px-3 py-2 text-xs">Manager</a><a href="logout.php" class="rounded-xl bg-white/5 px-3 py-2 text-xs">Cerrar</a></div></header>
<?php if ($message): ?><div class="mb-4 rounded-xl border border-cyan-500/20 bg-cyan-500/10 p-3 text-sm text-cyan-100"><?= h($message) ?></div><?php endif; ?>
<div class="mb-5 flex flex-wrap gap-2"><form method="post"><input name="action" value="download" type="hidden"><button class="rounded-xl bg-emerald-500/20 px-4 py-2 text-xs text-emerald-200">Descargar cambios ZIP</button></form><span class="self-center text-xs text-gray-500">Incluye catálogo, carátulas modificadas y un manifiesto de eliminaciones.</span></div>
<section class="grid gap-5 lg:grid-cols-[390px_1fr]"><form method="post" enctype="multipart/form-data" class="rounded-3xl border border-white/10 bg-white/5 p-4 space-y-3"><input type="hidden" name="action" value="save"><input type="hidden" name="original_id" value="<?= h($editing['id'] ?? '') ?>"><div class="flex items-center justify-between"><h2 class="font-bold"><?= $editing ? 'Editar elemento' : 'Añadir elemento' ?></h2><?php if ($editing): ?><a href="index.php" class="text-xs text-cyan-300">Nuevo</a><?php endif; ?></div><label class="block text-xs text-gray-400">ID de título o aplicación<input required name="id" value="<?= h($editing['id'] ?? '') ?>" class="field mt-1" placeholder="CUSA12345, SLUS21004, NPXX…"><small class="text-gray-500">Admite CUSA, SLUS/SLES, aplicaciones y homebrew.</small></label><label class="block text-xs text-gray-400">Título<input required name="titulo" value="<?= h($editing['titulo'] ?? '') ?>" class="field mt-1"></label><div class="grid grid-cols-2 gap-2"><label class="text-xs text-gray-400">Categoría<input name="categoria" value="<?= h($editing['categoria'] ?? 'PS4') ?>" class="field mt-1" placeholder="PS4 / Homebrew"></label><label class="text-xs text-gray-400">Versión<input name="version" value="<?= h($editing['version'] ?? '1.00') ?>" class="field mt-1"></label></div><label class="block text-xs text-gray-400">Subtítulo<input name="subtitulo" value="<?= h($editing['subtitulo'] ?? '') ?>" class="field mt-1"></label><label class="block text-xs text-gray-400">Peso aproximado (GB)<input name="peso_gb" inputmode="decimal" value="<?= h($editing['peso_gb'] ?? '') ?>" class="field mt-1"></label><label class="block text-xs text-gray-400">Descripción<textarea name="descripcion" class="field mt-1" rows="3"><?= h($editing['descripcion'] ?? '') ?></textarea></label><div class="grid grid-cols-2 gap-2"><label class="text-xs text-gray-400">Créditos/licencia<input name="creditos" value="<?= h($editing['creditos'] ?? '') ?>" class="field mt-1"></label><label class="text-xs text-gray-400">Proveedor<input name="servidor" value="<?= h($editing['servidor'] ?? '') ?>" class="field mt-1" placeholder="Servidor autorizado"></label></div>

<!-- ============================================================ -->
<!-- CAMPO PKG CON BOTÓN "EXTRAER" MEJORADO -->
<!-- ============================================================ -->
<div class="flex gap-2 items-end">
    <label class="block text-xs text-gray-400 flex-1">
        URL directa del PKG
        <input required type="url" name="pkg" id="pkg_url" value="<?= h($editing['enlaces']['pkg'] ?? '') ?>" class="field mt-1" placeholder="https://servidor/archivo.pkg o enlace MediaFire">
    </label>
    <button type="button" onclick="extraerEnlace()" class="mb-0.5 rounded-xl bg-purple-500/20 px-4 py-2 text-xs text-purple-200 hover:bg-purple-500/30 transition-colors">
        <i class="fa-solid fa-magic"></i> Extraer
    </button>
</div>
<div id="extract-result" class="text-xs mt-1"></div>

<label class="block text-xs text-gray-400">URL update (opcional)<input type="text" name="update" value="<?= h($editing['enlaces']['update'] ?? '') ?>" class="field mt-1" placeholder="Déjalo vacío o escribe No aplica"></label><label class="block text-xs text-gray-400">URLs DLC (opcional)<textarea name="dlc" class="field mt-1" rows="2" placeholder="Déjalo vacío o escribe No aplica"><?= h(implode("\n", $editing['enlaces']['dlc'] ?? [])) ?></textarea></label><label class="block text-xs text-gray-400">Carátula JPG/PNG/WebP (máx. 5 MB)<input name="cover" type="file" accept="image/jpeg,image/png,image/webp" class="field mt-1"></label><label class="flex gap-2 text-xs text-amber-200"><input required name="licencia_confirmada" value="1" type="checkbox" <?= $editing ? 'checked' : '' ?>> Confirmo que tengo autorización para publicar este contenido y sus enlaces.</label><div class="grid grid-cols-2 gap-2"><button class="rounded-xl bg-cyan-500 px-3 py-3 text-xs font-bold text-black">Guardar</button><button formmethod="post" formaction="index.php" name="action" value="check" class="rounded-xl bg-white/10 px-3 py-3 text-xs">Verificar enlace</button></div></form>
<section class="rounded-3xl border border-white/10 bg-white/5 p-4"><div class="mb-3 flex items-center justify-between"><h2 class="font-bold">Catálogo publicado</h2><span class="text-xs text-gray-400"><?= count($catalog) ?> elementos</span></div><div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3"><?php foreach ($catalog as $row): ?><article class="rounded-2xl bg-black/25 p-3"><div class="mb-2 aspect-[3/2] overflow-hidden rounded-xl bg-black/30"><?php if (!empty($row['cover'])): ?><img class="h-full w-full object-cover" src="../<?= h($row['cover']) ?>" onerror="this.remove()"><?php endif; ?></div><b class="block truncate text-sm"><?= h($row['titulo'] ?? '') ?></b><span class="text-xs text-cyan-300"><?= h($row['id'] ?? '') ?> · v<?= h($row['version'] ?? '1.00') ?></span><p class="mt-1 text-xs text-gray-400"><?= h($row['peso_gb'] ?? '?') ?> GB · <?= h($row['servidor'] ?? '') ?></p><div class="mt-3 flex gap-2"><a class="rounded-lg bg-white/10 px-3 py-2 text-xs" href="?edit=<?= urlencode((string)$row['id']) ?>">Editar</a><form method="post" onsubmit="return confirm('¿Retirar este elemento?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($row['id']) ?>"><button class="rounded-lg bg-red-500/15 px-3 py-2 text-xs text-red-200">Eliminar</button></form></div></article><?php endforeach; ?><?php if (!$catalog): ?><p class="text-sm text-gray-500">El catálogo está vacío.</p><?php endif; ?></div></section></section></main>

<!-- ============================================================ -->
<!-- JAVASCRIPT PARA EL BOTÓN "EXTRAER" (CORREGIDO) -->
<!-- ============================================================ -->
<script>
function extraerEnlace() {
    const input = document.getElementById('pkg_url');
    const resultado = document.getElementById('extract-result');
    const url = input.value.trim();
    
    if (!url) {
        resultado.innerHTML = '<span class="text-yellow-400">⚠️ Introduce un enlace primero</span>';
        return;
    }
    
    resultado.innerHTML = '<span class="text-cyan-400">⏳ Extrayendo enlace...</span>';
    
    const formData = new FormData();
    formData.append('action', 'extract');
    formData.append('url', url);
    
    // Usamos 'index.php' en lugar de '' para asegurar que la petición vaya al script correcto
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        // Verificar si la respuesta es JSON válido
        return res.text().then(text => {
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error('Respuesta no válida:', text);
                throw new Error('El servidor devolvió una respuesta inválida');
            }
        });
    })
    .then(data => {
        if (data.success) {
            input.value = data.direct_link;
            resultado.innerHTML = '<span class="text-green-400">✅ Enlace extraído correctamente</span>';
        } else {
            resultado.innerHTML = '<span class="text-red-400">❌ ' + data.message + '</span>';
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        resultado.innerHTML = '<span class="text-red-400">❌ ' + error.message + '</span>';
    });
}
</script>
</body>
</html>
