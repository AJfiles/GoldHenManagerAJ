<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER - MÓDULO STORE V6 (GRILLA 2 COLUMNAS)
 * ====================================================================
 */
error_reporting(0);
$archivoSecreto = 'ps4_proxy_config.json';
$archivoCatalogo = 'juegos.json';

// ====================================================================
// 1. MOTOR PUENTE (Rastreo de IP)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['check_ip']) && file_exists($archivoSecreto)) {
    $config = json_decode(file_get_contents($archivoSecreto), true);
    
    if ($_SERVER['REMOTE_ADDR'] === $config['ip_ps4']) {
        $urlReal = $config['url_juego'];
        
        $ch = curl_init($urlReal);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36');

        if (isset($_SERVER['HTTP_RANGE'])) curl_setopt($ch, CURLOPT_HTTPHEADER, ['Range: ' . $_SERVER['HTTP_RANGE']]);
        if ($_SERVER['REQUEST_METHOD'] === 'HEAD') curl_setopt($ch, CURLOPT_NOBODY, true);
        else curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); 

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) {
            if (preg_match('/^HTTP\/(1\.[01]|2|3) (\d+) /i', $header, $matches)) http_response_code((int)$matches[2]);
            $h = strtolower(trim(explode(':', $header)[0]));
            if (in_array($h, ['content-type', 'content-length', 'content-range', 'accept-ranges', 'content-disposition'])) header(trim($header));
            return strlen($header);
        });

        header("Content-Type: application/octet-stream");
        header('Content-Disposition: attachment; filename="juego.pkg"');
        set_time_limit(0);
        while (ob_get_level()) ob_end_clean();
        curl_exec($ch);
        curl_close($ch);
        exit; 
    }
}

// ====================================================================
// 2. API DEL RADAR
// ====================================================================
if (isset($_GET['check_ip'])) {
    header('Content-Type: application/json');
    $ip = trim($_GET['check_ip']);
    $port = intval($_GET['port'] ?? 12800);
    $fp = @fsockopen($ip, $port, $errno, $errstr, 1.5);
    if ($fp) { fclose($fp); echo json_encode(['status' => 'online']); } 
    else { echo json_encode(['status' => 'offline']); }
    exit;
}

// ====================================================================
// 3. MOTOR EXTRACTOR UNIVERSAL
// ====================================================================
function extraerEnlaceDirecto($url) {
    if (preg_match('/\.pkg(\?.*)?$/i', $url) && strpos($url, '/d/') === false && strpos($url, '/file/') === false) return $url;
    if (strpos($url, 'X-Amz-Signature') !== false || strpos($url, 'cloudflarestorage') !== false || strpos($url, 'sig=') !== false || strpos($url, 'dlproxy') !== false) return $url;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    $urlEfectiva = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if (preg_match('/\.pkg(\?.*)?$/i', $urlEfectiva) && strpos($urlEfectiva, '/d/') === false) return $urlEfectiva;
    if (strpos($url, 'mediafire.com') !== false) {
        if (preg_match('/id="downloadButton"[^>]*href=["\']([^"\']+)["\']/i', $html, $m) || preg_match('/class="[^"]*popsok[^"]*"[^>]*href=["\']([^"\']+)["\']/i', $html, $m)) return $m[1];
    }
    if (preg_match('/href=["\']([^"\']+\.pkg)["\']/i', $html, $m)) return $m[1];
    return false;
}

// ====================================================================
// 4. API DE INSTALACIÓN AJAX (POST)
// ====================================================================
if (isset($_POST['action']) && $_POST['action'] === 'install') {
    header('Content-Type: application/json');
    $ipPs4 = trim($_POST['ip'] ?? '');
    $puertoPs4 = trim($_POST['puerto'] ?? '12800');
    $enlaceWeb = trim($_POST['url_web'] ?? '');

    if (empty($ipPs4) || empty($enlaceWeb)) { echo json_encode(['success' => false, 'title' => 'Faltan datos', 'message' => 'Revisa la IP y el enlace.']); exit; }

    $urlDirecta = extraerEnlaceDirecto($enlaceWeb);
    if ($urlDirecta) {
        $usarPuente = (strpos($urlDirecta, 'X-Amz-Signature') !== false || strpos($urlDirecta, 'cloudflarestorage') !== false || strpos($urlDirecta, 'sig=') !== false || strpos($urlDirecta, 'dlproxy') !== false);

        if ($usarPuente) {
            file_put_contents($archivoSecreto, json_encode(['url_juego' => $urlDirecta, 'ip_ps4' => $ipPs4]));
            $urlParaPs4 = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?proxy_streaming=1&fake_ext=/juego.pkg";
        } else {
            $urlParaPs4 = str_replace('https://', 'http://', $urlDirecta);
            if (!preg_match('/\.pkg$/i', $urlParaPs4)) $urlParaPs4 .= "#/juego.pkg";
        }
        
        $rpiUrl = "http://{$ipPs4}:{$puertoPs4}/api/install";
        $payload = json_encode(["type" => "direct", "packages" => [$urlParaPs4]], JSON_UNESCAPED_SLASHES);

        $chRpi = curl_init($rpiUrl);
        curl_setopt($chRpi, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chRpi, CURLOPT_POST, true);
        curl_setopt($chRpi, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($chRpi, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($payload)]);
        curl_setopt($chRpi, CURLOPT_TIMEOUT, 8); 
        $rpiRespuesta = curl_exec($chRpi);
        $httpCode = curl_getinfo($chRpi, CURLINFO_HTTP_CODE);
        $curlError = curl_error($chRpi);
        curl_close($chRpi);

        if ($httpCode >= 200 && $httpCode < 300 && strpos($rpiRespuesta, 'success') !== false) {
            echo json_encode(['success' => true, 'title' => '¡Enviado a PS4!', 'message' => 'Revisa las notificaciones de tu consola.']);
        } else {
            $errorDetalle = "Cód HTTP: $httpCode\n" . (!empty($curlError) ? "Red: $curlError\n" : "") . (!empty($rpiRespuesta) ? "Consola: ".strip_tags($rpiRespuesta) : "");
            echo json_encode(['success' => false, 'title' => 'Error Detallado', 'message' => $errorDetalle]);
        }
    } else {
        echo json_encode(['success' => false, 'title' => 'Extracción fallida', 'message' => 'No pudimos obtener el enlace final (.pkg).']);
    }
    exit;
}

// ====================================================================
// 5. CARGA DEL CATÁLOGO
// ====================================================================
$juegosJson = @file_get_contents($archivoCatalogo);
$catalogo = !empty($juegosJson) ? json_decode($juegosJson, true) : [];

if (empty($catalogo)) {
    $catalogo = [
        [
            "titulo" => "Beach Buggy Racing 2", "subtitulo" => "Island Adventure", "categoria" => "PS4",
            "portada" => "https://www.superpsx.com/wp-content/uploads/2026/08/Beach-Buggy-Racing-2-Island-Adventure-PS4-PKG.jpg",
            "servidor" => "VikingFile", "creditos" => "SuperPSX",
            "resena" => "Divertido juego de carreras de karts con power-ups, pantalla dividida y multijugador local ideal.",
            "enlace" => "https://vikingfile.04b3d96d52475741e6b10f97f0a84a16.r2.cloudflarestorage.com/QTvSDfBnQ6?response-content-disposition=attachment%3Bfilename%3D%22%5BSuperPSX%5D-Beach.Buggy.Racing.2.Island.Adventure-CUSA26789%20%E2%80%93%20USA-Game-PS4.pkg%22&X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=3719509459615e2573a6f081c551ec99%2F20260903%2Fauto%2Fs3%2Faws4_request&X-Amz-Date=20260903T081258Z&X-Amz-SignedHeaders=host&X-Amz-Expires=21600&X-Amz-Signature=13cb86ece3f7982722c64a59c0e4aced61fb5df21de4522b5cdf0fd178944db3"
        ],
        [
            "titulo" => "Kizuna AI: Touch the Beat!", "subtitulo" => "VR Compatible", "categoria" => "PSVR",
            "portada" => "https://www.superpsx.com/wp-content/uploads/2026/08/Kizuna-AI-Touch-the-Beat-PS4-PKG.jpg",
            "servidor" => "Filekeeper", "creditos" => "Comunidad",
            "resena" => "Juego de ritmo musical protagonizado por la VTuber Kizuna AI. Usa tus gafas VR para máxima inmersión.",
            "enlace" => "https://tunnel1.dlproxy.uk/download/s0Jb9lsw8n53AYLRv03-3WFfzaRBP-rGdhATFXGf2M4vv3ah83EyUWVLH_R8-3ACI6nsEnQT4bwlVD28kfN0-GwLFoySKiZQBpG4h_p0TWgZyOVu4Wr26mFafBNG47-Cv2Ae1Eay3KUVG0IZWF97keGswG3g3M8ayoIBzG5xl5zVHf2Ia0ibFwqaSom9RMyPXfaoH2i0hMhCQ5GOHULAR9l_P-RC1oh9gPZF-gL5TWgnsNdCRGuZYEnCCGDMwza0ZsgEE_X7QkH2ZV1oJOlm0ndch2MWuTL5dudnrelgu2WHLoXXvKROpo25_RdEC1tokaZ-lQ_WpRKRlWPawN7Cpkr05hOHUiYgF808DfVmwRzqwHXhiG_swNIx-SKQDijYqAl_jnn08odIjC94PPUfFK1SLSyLpQz8q8eSE1VxzekT-SeN_cfU2Cvcr7PJilhPHp1g-3T8E-wYnQwak-ZzZ7fGuwgqTiI6honYzA9JlwJOnclox2A0gxmqswn_BdpsrdSJ1R8p_1Zi_2nWk2R0fEYcT0jQydP77McSW5S_969caHG0laYQpImydO7bycnyYZ01oAPPTReboRSCyACGxqQXFYevgjKhUdpvUgw4SE7w1xB1c4n62oNWVbEZzl7yopUqL79tJoyOY-TKRhvEggio_SxjUEd_OJMvLmRD-ms?sig=DismnaZ274zJtCHmmBLeaSyf7zVF4_Ci_h0mfZXRqrc"
        ],
        [
            "titulo" => "Lego Batman Remaster", "subtitulo" => "PS2 Remaster", "categoria" => "PS2",
            "portada" => "https://i.ibb.co/hxYnyP59/img-3-1788423869958.jpg",
            "servidor" => "Mediafire", "creditos" => "marceloviveros",
            "resena" => "Aventura clásica adaptada a PS4. Recorre Gotham City como Batman o Robin.",
            "enlace" => "https://www.mediafire.com/file/94huh7iknp4fvx2/Lego_Batman_SETH_VIVEROS.pkg/file"
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="referrer" content="no-referrer">
    <title>GoldHen Manager - Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #050711; color: white; min-height: 100vh; overflow-x: hidden; }
        .glass-panel { background: rgba(10, 14, 28, 0.7); backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .modal-overlay { background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); }
    </style>
</head>
<body class="relative pb-10">

    <div id="toast-notification" class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[95%] max-w-sm transition-all duration-500 transform -translate-y-32 opacity-0 pointer-events-none">
        <div id="toast-bg" class="glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-white/10">
            <div id="toast-icon-bg" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-1">
                <i id="toast-icon" class="text-xl"></i>
            </div>
            <div class="flex-1 overflow-hidden">
                <h4 id="toast-title" class="text-sm font-black uppercase tracking-wider text-white">Titulo</h4>
                <p id="toast-msg" class="text-[11px] text-gray-300 mt-1 leading-relaxed whitespace-pre-wrap break-words font-mono">Mensaje</p>
            </div>
        </div>
    </div>

    <!-- FONDO -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <img src="https://images.unsplash.com/photo-1552820728-8b83bb6b773f?q=80&w=1080&auto=format&fit=crop" alt="Fondo" class="w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-b from-[#050711]/80 via-[#050711]/95 to-[#050711] z-10"></div>
    </div>

    <!-- HEADER FLOTANTE -->
    <div class="fixed top-0 left-0 right-0 z-40 glass-panel border-b border-white/10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="bg-cyan-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                <i class="fa-solid fa-store text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-sm font-black tracking-widest uppercase leading-none">GoldHen Store</h1>
                <span class="text-[9px] text-cyan-400 font-bold uppercase tracking-widest">Catálogo Premium</span>
            </div>
        </div>
        <button onclick="toggleModal('modal-config')" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all relative">
            <i class="fa-solid fa-gear text-gray-300"></i>
            <div id="luz-radar-mini" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500 border-2 border-[#050711]"></div>
        </button>
    </div>

    <!-- CONTENEDOR PRINCIPAL DEL CATÁLOGO -->
    <div class="w-full max-w-xl mx-auto px-4 pt-20 relative z-20">
        
        <!-- BARRA DE BÚSQUEDA Y FILTROS -->
        <div class="flex gap-2 mb-6 sticky top-16 z-30 pt-2 pb-2 bg-[#050711]/90 backdrop-blur-md">
            <div class="flex-1 relative bg-black/50 border border-white/10 rounded-xl overflow-hidden flex items-center px-3 focus-within:border-cyan-400 transition-colors">
                <i class="fa-solid fa-magnifying-glass text-gray-500 text-xs"></i>
                <input type="text" id="buscador" placeholder="Buscar juego..." class="w-full bg-transparent text-white text-xs py-3 pl-3 outline-none">
            </div>
            <div class="w-28 relative bg-black/50 border border-white/10 rounded-xl overflow-hidden flex items-center focus-within:border-cyan-400 transition-colors">
                <select id="filtro-categoria" class="w-full bg-transparent text-white text-[10px] font-bold py-3 pl-2 pr-4 outline-none appearance-none cursor-pointer">
                    <option value="ALL">Todo</option>
                    <option value="PS4">PS4 Nativo</option>
                    <option value="PS2">PS2 Remaster</option>
                    <option value="PSVR">PS VR</option>
                </select>
                <i class="fa-solid fa-filter absolute right-2 text-gray-500 text-[9px] pointer-events-none"></i>
            </div>
        </div>

        <!-- LISTA DE JUEGOS (GRILLA 2 COLUMNAS) -->
        <div class="grid grid-cols-2 gap-3 pb-8" id="lista-catalogo">
            <?php foreach ($catalogo as $juego): ?>
                <div class="game-card glass-panel rounded-xl overflow-hidden border border-white/5 shadow-lg flex flex-col" 
                     data-titulo="<?= strtolower(htmlspecialchars($juego['titulo'])) ?>" 
                     data-categoria="<?= htmlspecialchars($juego['categoria'] ?? 'ALL') ?>">
                    
                    <!-- Imagen adaptada para tarjeta estrecha -->
                    <div class="h-28 w-full bg-black/60 relative p-1.5 flex items-center justify-center border-b border-white/5">
                        <img src="<?= htmlspecialchars($juego['portada']) ?>" alt="Portada" class="max-w-full max-h-full object-contain drop-shadow-md">
                        
                        <div class="absolute top-1.5 left-1.5 bg-cyan-950/80 border border-cyan-800 backdrop-blur text-cyan-400 px-1.5 py-0.5 rounded text-[7px] font-black tracking-widest uppercase">
                            <?= htmlspecialchars($juego['categoria'] ?? 'PS4') ?>
                        </div>
                        <div class="absolute top-1.5 right-1.5 bg-black/80 border border-white/10 backdrop-blur px-1.5 py-0.5 rounded text-[7px] font-black tracking-widest uppercase flex items-center gap-1">
                            <i class="fa-solid fa-server text-gray-400"></i> <span class="truncate max-w-[40px]"><?= htmlspecialchars($juego['servidor']) ?></span>
                        </div>
                    </div>
                    
                    <!-- Información comprimida -->
                    <div class="p-2.5 flex flex-col flex-1">
                        <h3 class="text-[11px] font-black uppercase tracking-wider leading-tight text-white mb-0.5 line-clamp-2">
                            <?= htmlspecialchars($juego['titulo']) ?>
                        </h3>
                        <?php if(!empty($juego['subtitulo'])): ?>
                            <p class="text-[8px] text-cyan-400 font-bold uppercase tracking-widest mb-1.5 truncate">
                                <?= htmlspecialchars($juego['subtitulo']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if(!empty($juego['resena'])): ?>
                            <p class="text-[8px] text-gray-400 font-light leading-snug line-clamp-2 mb-2">
                                <?= htmlspecialchars($juego['resena']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Créditos y Botón al fondo -->
                        <div class="mt-auto pt-2 border-t border-white/5">
                            <div class="flex items-center gap-1 mb-2">
                                <i class="fa-solid fa-user-astronaut text-[8px] text-gray-500"></i>
                                <span class="text-[8px] text-gray-400 font-bold truncate">Por: <?= htmlspecialchars($juego['creditos'] ?? 'Anónimo') ?></span>
                            </div>

                            <button class="btn-install-game w-full bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-black text-[9px] py-2 rounded-lg tracking-widest uppercase transition-all flex items-center justify-center gap-1.5 shadow-md active:scale-95" 
                                    data-url="<?= htmlspecialchars($juego['enlace']) ?>">
                                <i class="fa-solid fa-download"></i> Instalar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div id="no-results" class="hidden col-span-2 glass-panel p-8 text-center rounded-2xl border border-white/5">
                <i class="fa-solid fa-ghost text-3xl text-gray-600 mb-2"></i>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">No se encontraron juegos</p>
            </div>
        </div>
    </div>

    <!-- MODAL DE CONFIGURACIÓN Y RED -->
    <div id="modal-config" class="fixed inset-0 z-50 modal-overlay flex flex-col justify-end transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="glass-panel w-full rounded-t-[2rem] border-t border-white/10 p-6 transform translate-y-full transition-transform duration-300" id="modal-content">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 text-white">
                    <i class="fa-solid fa-gear text-cyan-400"></i> Ajustes de Conexión
                </h2>
                <button onclick="toggleModal('modal-config')" class="w-8 h-8 bg-white/5 rounded-full flex items-center justify-center hover:bg-white/10 active:scale-90">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 mb-5 flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-400 text-sm mt-0.5"></i>
                <p class="text-[10px] text-amber-200 font-medium leading-relaxed">
                    Debes abrir la aplicación <span class="font-black text-amber-400">Remote Package Installer</span> en tu consola antes de enviar juegos.
                </p>
            </div>

            <div class="flex justify-between items-center mb-2 px-1">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">IP de tu Consola</span>
                <span id="estado-texto-modal" class="text-gray-500 text-[9px] uppercase tracking-widest font-bold">Buscando...</span>
            </div>
            
            <div class="grid grid-cols-5 gap-2 mb-6">
                <div class="col-span-3 bg-black/60 border border-white/10 rounded-xl overflow-hidden flex items-center px-3 focus-within:border-cyan-400 transition-colors">
                    <i class="fa-solid fa-gamepad text-gray-500 text-[10px]"></i>
                    <input type="text" id="ip-input" value="192.168.0.29" placeholder="192.168..." class="w-full bg-transparent text-white text-[11px] py-3.5 pl-3 outline-none font-mono">
                </div>
                <div class="col-span-2 bg-black/60 border border-white/10 rounded-xl overflow-hidden relative flex items-center focus-within:border-cyan-400 transition-colors">
                    <select id="puerto-input" class="w-full bg-transparent text-white text-[10px] font-bold py-3.5 pl-3 pr-6 outline-none appearance-none cursor-pointer">
                        <option value="12800" class="bg-gray-900">RPI Orig</option>
                        <option value="12801" class="bg-gray-900" selected>RPI Nova</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-3 text-gray-500 text-[10px] pointer-events-none"></i>
                </div>
            </div>

            <div class="border-t border-white/5 pt-5 mb-2">
                <h3 class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-3">Instalación Manual</h3>
                <div class="relative flex items-center bg-black/60 border border-white/10 rounded-xl overflow-hidden focus-within:border-fuchsia-400 transition-colors mb-3">
                    <i class="fa-solid fa-link absolute left-3 text-gray-500 text-xs"></i>
                    <input type="url" id="manual-url" placeholder="Pega el enlace externo aquí..." class="w-full bg-transparent text-white text-[11px] py-3 pl-8 pr-3 outline-none">
                </div>
                <button id="btn-manual-install" class="w-full bg-white/5 hover:bg-white/10 border border-white/10 active:scale-95 text-white font-black text-[10px] py-3.5 rounded-xl tracking-widest uppercase transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Enviar enlace externo
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            const content = document.getElementById('modal-content');
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                setTimeout(() => content.classList.remove('translate-y-full'), 10);
            } else {
                content.classList.add('translate-y-full');
                setTimeout(() => modal.classList.add('opacity-0', 'pointer-events-none'), 300);
            }
        }

        const buscador = document.getElementById('buscador');
        const filtroCat = document.getElementById('filtro-categoria');
        const cards = document.querySelectorAll('.game-card');
        const noResults = document.getElementById('no-results');

        function filtrarJuegos() {
            const texto = buscador.value.toLowerCase();
            const cat = filtroCat.value;
            let visibles = 0;

            cards.forEach(card => {
                const titulo = card.getAttribute('data-titulo');
                const categoria = card.getAttribute('data-categoria');
                const coincideTexto = titulo.includes(texto);
                const coincideCat = (cat === 'ALL' || categoria === cat);

                if (coincideTexto && coincideCat) {
                    card.style.display = 'flex';
                    visibles++;
                } else {
                    card.style.display = 'none';
                }
            });
            noResults.style.display = visibles === 0 ? 'block' : 'none';
        }

        buscador.addEventListener('input', filtrarJuegos);
        filtroCat.addEventListener('change', filtrarJuegos);

        const inputIp = document.getElementById('ip-input');
        const selectPuerto = document.getElementById('puerto-input');
        const luzMini = document.getElementById('luz-radar-mini');
        const estadoModal = document.getElementById('estado-texto-modal');
        let timer;

        function verificarConexion() {
            const ip = inputIp.value.trim();
            const puerto = selectPuerto.value;

            if (!ip) {
                luzMini.className = 'absolute top-2 right-2 w-2 h-2 rounded-full bg-gray-600 border-2 border-[#050711]';
                estadoModal.innerText = "ESPERANDO IP...";
                estadoModal.className = "text-gray-500 text-[9px] uppercase tracking-widest font-bold";
                return;
            }

            luzMini.className = 'absolute top-2 right-2 w-2 h-2 rounded-full bg-yellow-400 border-2 border-[#050711] animate-pulse';
            estadoModal.innerText = "BUSCANDO...";
            estadoModal.className = "text-yellow-500 text-[9px] uppercase tracking-widest font-bold";

            fetch(`?check_ip=${encodeURIComponent(ip)}&port=${encodeURIComponent(puerto)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'online') {
                        luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]';
                        estadoModal.innerText = "¡CONECTADO!";
                        estadoModal.className = "text-emerald-400 text-[9px] uppercase tracking-widest font-bold";
                    } else {
                        luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)]';
                        estadoModal.innerText = "OFFLINE";
                        estadoModal.className = "text-red-400 text-[9px] uppercase tracking-widest font-bold";
                    }
                }).catch(() => {
                    luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)]';
                    estadoModal.innerText = "ERROR DE RED";
                    estadoModal.className = "text-red-400 text-[9px] uppercase tracking-widest font-bold";
                });
        }

        inputIp.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(verificarConexion, 800); });
        selectPuerto.addEventListener('change', verificarConexion); 
        if (inputIp.value.trim() !== '') verificarConexion();

        const toast = document.getElementById('toast-notification');
        const toastBg = document.getElementById('toast-bg');
        const toastIconBg = document.getElementById('toast-icon-bg');
        const toastIcon = document.getElementById('toast-icon');
        const toastTitle = document.getElementById('toast-title');
        const toastMsg = document.getElementById('toast-msg');

        function showToast(isSuccess, title, msg) {
            toastBg.className = isSuccess 
                ? "glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-emerald-500/30"
                : "glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-red-500/30";
            toastIconBg.className = isSuccess
                ? "w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-emerald-500/20 text-emerald-400 mt-1"
                : "w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-500/20 text-red-400 mt-1";
            toastIcon.className = isSuccess ? "fa-solid fa-check text-xl" : "fa-solid fa-bug text-xl";
            toastTitle.innerText = title;
            toastMsg.innerText = msg;

            toast.classList.remove('-translate-y-32', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-32', 'opacity-0', 'pointer-events-none');
            }, 8000); 
        }

        function enviarA_PS4(urlJuego, btnElement) {
            const ip = inputIp.value.trim();
            const puerto = selectPuerto.value;

            if (!ip) { showToast(false, "Falta la IP", "Por favor ve a ajustes y escribe la IP."); return; }
            if (!urlJuego) { showToast(false, "Falta el Enlace", "No hay ningún enlace."); return; }

            const textoOriginal = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ...';
            btnElement.disabled = true;
            btnElement.classList.add('opacity-50');

            const formData = new FormData();
            formData.append('action', 'install');
            formData.append('ip', ip);
            formData.append('puerto', puerto);
            formData.append('url_web', urlJuego);

            fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => { showToast(data.success, data.title, data.message); })
            .catch(err => { showToast(false, 'Error', 'Fallo de conexión interna.'); })
            .finally(() => {
                btnElement.innerHTML = textoOriginal;
                btnElement.disabled = false;
                btnElement.classList.remove('opacity-50');
            });
        }

        document.querySelectorAll('.btn-install-game').forEach(btn => {
            btn.addEventListener('click', function() { enviarA_PS4(this.getAttribute('data-url'), this); });
        });

        document.getElementById('btn-manual-install').addEventListener('click', function() {
            enviarA_PS4(document.getElementById('manual-url').value.trim(), this);
            toggleModal('modal-config'); 
        });
    </script>
</body>
</html>
