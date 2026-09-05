<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER v3.3 - MÓDULO STORE (PÚBLICO)
 * ====================================================================
 */
error_reporting(0);
$archivoCatalogo = __DIR__ . '/data/catalogo.json';
$catalogo = [];
if (file_exists($archivoCatalogo)) {
    $catalogo = json_decode(file_get_contents($archivoCatalogo), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GoldHen Store AJ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #050711; color: white; min-height: 100vh; }
        .glass-panel { background: rgba(10,14,28,0.75); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.05); }
        .modal-overlay { background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); }
        .game-card { transition: transform 0.2s; cursor: pointer; }
        .game-card:hover { transform: translateY(-4px); border-color: rgba(34,211,238,0.3); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(34,211,238,0.3); border-radius: 4px; }
        .ps4-box { box-shadow: inset 0 0 20px rgba(0,0,0,0.8), 0 10px 20px rgba(0,0,0,0.5); }
    </style>
</head>
<body>

    <!-- TOAST NOTIFICACIONES -->
    <div id="toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[95%] max-w-sm transition-all duration-500 transform -translate-y-32 opacity-0 pointer-events-none">
        <div id="toast-bg" class="glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-white/10">
            <div id="toast-icon-bg" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-1">
                <i id="toast-icon" class="text-xl"></i>
            </div>
            <div class="flex-1 overflow-hidden">
                <h4 id="toast-title" class="text-sm font-black uppercase tracking-wider text-white">Titulo</h4>
                <p id="toast-msg" class="text-[12px] text-gray-300 mt-1 leading-relaxed whitespace-pre-wrap break-words font-mono">Mensaje</p>
            </div>
        </div>
    </div>

    <!-- FONDO -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <img src="https://images.unsplash.com/photo-1552820728-8b83bb6b773f?q=80&w=1080&auto=format&fit=crop" alt="Fondo" class="w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-b from-[#050711]/80 via-[#050711]/95 to-[#050711] z-10"></div>
    </div>

    <!-- HEADER -->
    <div class="fixed top-0 left-0 right-0 z-40 glass-panel border-b border-white/10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="bg-cyan-500 w-8 h-8 rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                <i class="fa-solid fa-store text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-sm font-black tracking-widest uppercase leading-none">GoldHen Store</h1>
                <span class="text-[10px] text-cyan-400 font-bold uppercase tracking-widest">v3.3</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="cargarCatalogo()" class="w-10 h-10 rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center hover:bg-cyan-500/20 active:scale-90 transition-all shadow-lg" title="Actualizar catálogo">
                <i class="fa-solid fa-rotate-right text-cyan-300"></i>
            </button>
            <button onclick="toggleConfig()" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all relative shadow-lg">
                <i class="fa-solid fa-gear text-gray-300"></i>
                <div id="luz-radar-mini" class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-red-500 border-[2.5px] border-[#050711]"></div>
            </button>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="w-full max-w-xl mx-auto px-3 pt-20 relative z-20">

        <!-- BANNER DE AVISO RPI -->
        <div class="mb-4 bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 flex items-start gap-3 text-xs text-amber-200">
            <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base mt-0.5"></i>
            <div>
                <span class="font-bold">⚠️ Recuerda:</span> Debes tener abierta la aplicación <span class="font-bold text-amber-400">Remote Package Installer (RPI)</span> en tu PS4 para instalar juegos. El puerto por defecto es <span class="font-bold">12801</span> (Nova) o <span class="font-bold">12800</span> (Original).
            </div>
        </div>

        <!-- BARRA DE BÚSQUEDA Y FILTROS -->
        <div class="flex gap-2 mb-6 sticky top-16 z-30 pt-1 pb-2 bg-[#050711]/90 backdrop-blur-md">
            <div class="flex-1 relative bg-black/50 border border-white/10 rounded-xl overflow-hidden flex items-center px-3 focus-within:border-cyan-400 transition-colors shadow-inner">
                <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
                <input type="text" id="buscador" placeholder="Buscar juego..." class="w-full bg-transparent text-white text-xs py-3.5 pl-3 outline-none">
            </div>
            <div class="w-32 relative bg-black/50 border border-white/10 rounded-xl overflow-hidden flex items-center focus-within:border-cyan-400 transition-colors shadow-inner">
                <select id="filtro-categoria" class="w-full bg-transparent text-white text-[11px] font-bold py-3.5 pl-3 pr-6 outline-none appearance-none cursor-pointer">
                    <option value="ALL">Todas</option>
                    <option value="PS4">PS4</option>
                    <option value="PS2">PS2</option>
                    <option value="PS1">PS1</option>
                    <option value="RETRO">Retro</option>
                </select>
                <i class="fa-solid fa-filter absolute right-3 text-gray-400 text-[10px] pointer-events-none"></i>
            </div>
        </div>

        <!-- ESTADO -->
        <div id="status" class="text-xs text-gray-400 mb-3 text-center"></div>

        <!-- LISTA DE JUEGOS -->
        <div class="grid grid-cols-2 gap-4 pb-8" id="lista-catalogo">
            <?php foreach ($catalogo as $juego): ?>
                <div class="game-card glass-panel rounded-2xl p-2.5 border border-white/10 shadow-lg flex flex-col"
                     data-id="<?= htmlspecialchars($juego['id'] ?? '') ?>"
                     data-titulo="<?= strtolower(htmlspecialchars($juego['titulo'] ?? '')) ?>"
                     data-categoria="<?= htmlspecialchars($juego['categoria'] ?? 'ALL') ?>"
                     onclick="abrirDetalle('<?= htmlspecialchars($juego['id'] ?? '') ?>')">
                    <!-- CAJA ESTILO PS4 -->
                    <div class="relative w-full aspect-[3/4] rounded-lg ps4-box border border-gray-700/60 overflow-hidden group mb-3 transform transition-transform duration-300 hover:scale-[1.03]">
                        <div class="absolute top-0 left-0 right-0 h-[12%] min-h-[18px] bg-gradient-to-r from-blue-700 via-blue-500 to-blue-800 flex items-center px-2 border-b border-white/30 z-20 shadow-md">
                            <span class="text-[8px] text-white font-black tracking-widest italic drop-shadow-sm">PS4</span>
                        </div>
                        <div class="absolute inset-0 top-[12%] bg-gradient-to-br from-slate-800 to-slate-950 flex flex-col items-center justify-center text-center overflow-hidden">
                            <div class="absolute inset-0 bg-black/20 bg-cover bg-center group-hover:scale-110 transition-transform duration-700" style="background-image: url('<?= htmlspecialchars($juego['cover'] ?? 'https://via.placeholder.com/400x400/0a0f1a/22d3ee?text=No+Cover') ?>'); background-size: cover;"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10 pointer-events-none"></div>
                            <span class="absolute top-2 right-2 text-[8px] text-cyan-400 font-black uppercase mt-1.5 z-20 drop-shadow-md bg-black/60 px-1.5 py-0.5 rounded border border-white/10">
                                <?= htmlspecialchars($juego['categoria'] ?? 'PS4') ?>
                            </span>
                        </div>
                    </div>
                    <!-- INFORMACIÓN -->
                    <div class="flex flex-col flex-1 px-1">
                        <h3 class="text-[13px] font-black uppercase tracking-wider leading-tight text-white mb-1 line-clamp-2">
                            <?= htmlspecialchars($juego['titulo'] ?? 'Sin título') ?>
                        </h3>
                        <?php if(!empty($juego['subtitulo'])): ?>
                            <p class="text-[10px] text-cyan-400 font-bold uppercase tracking-widest mb-2 truncate">
                                <?= htmlspecialchars($juego['subtitulo']) ?>
                            </p>
                        <?php endif; ?>
                        <div class="mt-auto pt-3 border-t border-white/10">
                            <button class="btn-install-game w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-black text-[11px] py-2.5 rounded-xl tracking-widest uppercase transition-all flex items-center justify-center gap-2 shadow-[0_5px_15px_rgba(6,182,212,0.3)] active:scale-95"
                                    data-url="<?= htmlspecialchars($juego['enlaces']['pkg'] ?? '') ?>"
                                    onclick="event.stopPropagation(); instalarJuego(this)">
                                <i class="fa-solid fa-download"></i> Instalar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="no-results" class="hidden col-span-2 glass-panel p-10 text-center rounded-3xl border border-white/10 mt-4">
                <i class="fa-solid fa-ghost text-4xl text-gray-600 mb-3"></i>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">No se encontraron juegos</p>
            </div>
        </div>
    </div>

    <!-- MODAL DE DETALLES DEL JUEGO -->
    <div id="modal-detalle" class="fixed inset-0 z-50 modal-overlay flex items-center justify-center transition-opacity duration-300 opacity-0 pointer-events-none p-4">
        <div class="glass-panel w-full max-w-sm rounded-3xl border border-white/10 p-6 transform scale-95 transition-transform duration-300 shadow-[0_20px_60px_rgba(0,0,0,0.8)] max-h-[90vh] overflow-y-auto custom-scrollbar" id="modal-detalle-content">
            <div class="flex justify-between items-start mb-4">
                <h2 id="detalle-titulo" class="text-lg font-black uppercase text-white">Título</h2>
                <button onclick="cerrarDetalle()" class="w-8 h-8 bg-white/5 rounded-full flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all border border-white/10">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="detalle-contenido"></div>
        </div>
    </div>

    <!-- MODAL DE CONFIGURACIÓN (IP, PUERTO, RADAR) -->
    <div id="modal-config" class="fixed inset-0 z-50 modal-overlay flex flex-col justify-end md:justify-center transition-opacity duration-300 opacity-0 pointer-events-none p-4">
        <div class="glass-panel w-full max-w-sm mx-auto rounded-[2rem] border border-white/10 p-6 transform translate-y-8 md:translate-y-0 transition-transform duration-300 shadow-[0_10px_40px_rgba(0,0,0,0.8)]" id="modal-config-content">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-sm font-black uppercase tracking-widest flex items-center gap-2 text-white">
                    <i class="fa-solid fa-gear text-cyan-400"></i> Configuración RPI
                </h2>
                <button onclick="toggleConfig()" class="w-8 h-8 bg-white/5 rounded-full flex items-center justify-center hover:bg-white/10 active:scale-90 transition-all border border-white/10">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- RADAR -->
            <div class="flex justify-between items-center mb-2 px-1">
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">IP de tu Consola</span>
                <span id="estado-texto-modal" class="text-gray-500 text-[9px] uppercase tracking-widest font-black">Buscando...</span>
            </div>
            <div class="grid grid-cols-5 gap-2 mb-4">
                <div class="col-span-3 bg-black/60 border border-white/10 rounded-xl overflow-hidden flex items-center px-3 focus-within:border-cyan-400 transition-colors shadow-inner">
                    <i class="fa-solid fa-gamepad text-gray-500 text-sm"></i>
                    <input type="text" id="ip-input" placeholder="192.168..." class="w-full bg-transparent text-white text-xs py-3.5 pl-2 outline-none font-mono" value="<?= $_COOKIE['store_ip'] ?? '' ?>">
                </div>
                <div class="col-span-2 bg-black/60 border border-white/10 rounded-xl overflow-hidden relative flex items-center focus-within:border-cyan-400 transition-colors shadow-inner">
                    <select id="puerto-input" class="w-full bg-transparent text-white text-[10px] font-bold py-3.5 pl-2 pr-6 outline-none appearance-none cursor-pointer">
                        <option value="12800" <?= ($_COOKIE['store_port'] ?? '12801') == '12800' ? 'selected' : '' ?>>RPI Orig</option>
                        <option value="12801" <?= ($_COOKIE['store_port'] ?? '12801') == '12801' ? 'selected' : '' ?>>RPI Nova</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-3 text-gray-400 text-[10px] pointer-events-none"></i>
                </div>
            </div>

            <div class="flex gap-2">
                <button onclick="verificarConexion()" class="flex-1 bg-white/10 hover:bg-white/20 border border-white/10 text-center py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-300 transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-satellite-dish text-cyan-400"></i> Radar
                </button>
                <button onclick="guardarConfig()" class="flex-1 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-95 text-white font-black text-[10px] py-2.5 rounded-xl tracking-widest uppercase transition-all shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <script>
        // ============================================================
        // CONFIGURACIÓN DE RED (IP + PUERTO)
        // ============================================================
        const ipInput = document.getElementById('ip-input');
        const puertoInput = document.getElementById('puerto-input');
        const luzMini = document.getElementById('luz-radar-mini');
        const estadoModal = document.getElementById('estado-texto-modal');

        // Cargar IP guardada (desde localStorage de la app principal)
        function cargarIPGuardada() {
            const ipGuardada = localStorage.getItem('sebas_ip_final_libre') || '';
            const portGuardado = localStorage.getItem('sebas_port_rpi') || '12801';
            if (ipGuardada) ipInput.value = ipGuardada;
            puertoInput.value = portGuardado;
            verificarConexion();
        }

        // Guardar configuración
        function guardarConfig() {
            const ip = ipInput.value.trim();
            const puerto = puertoInput.value;
            if (ip) {
                localStorage.setItem('sebas_ip_final_libre', ip);
                localStorage.setItem('sebas_port_rpi', puerto);
                // Guardar en cookies por si acaso
                document.cookie = `store_ip=${ip}; path=/; max-age=31536000`;
                document.cookie = `store_port=${puerto}; path=/; max-age=31536000`;
                toggleConfig();
                mostrarToast(true, 'Configuración guardada', 'IP y puerto RPI guardados correctamente.');
            } else {
                mostrarToast(false, 'Error', 'Introduce una IP válida.');
            }
        }

        // Radar: verificar conexión a la PS4
        function verificarConexion() {
            const ip = ipInput.value.trim();
            const puerto = puertoInput.value;
            if (!ip) {
                luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-gray-600 border-[2.5px] border-[#050711]';
                estadoModal.innerText = "ESPERANDO IP...";
                estadoModal.className = "text-gray-500 text-[10px] uppercase tracking-widest font-black";
                return;
            }
            luzMini.className = 'absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-yellow-400 border-[2.5px] border-[#050711] animate-pulse';
            estadoModal.innerText = "BUSCANDO...";
            estadoModal.className = "text-yellow-500 text-[10px] uppercase tracking-widest font-black";

            fetch(`?check_ip=${encodeURIComponent(ip)}&port=${encodeURIComponent(puerto)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'online') {
                        luzMini.className = 'absolute top-2 right-2 w-3 h-3 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)] border-[2.5px] border-[#050711]';
                        estadoModal.innerText = "¡CONECTADO!";
                        estadoModal.className = "text-emerald-400 text-[10px] uppercase tracking-widest font-black";
                    } else {
                        luzMini.className = 'absolute top-2 right-2 w-3 h-3 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)] border-[2.5px] border-[#050711]';
                        estadoModal.innerText = "OFFLINE";
                        estadoModal.className = "text-red-400 text-[10px] uppercase tracking-widest font-black";
                    }
                }).catch(() => {
                    luzMini.className = 'absolute top-2 right-2 w-3 h-3 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)] border-[2.5px] border-[#050711]';
                    estadoModal.innerText = "ERROR DE RED";
                    estadoModal.className = "text-red-400 text-[10px] uppercase tracking-widest font-black";
                });
        }

        ipInput.addEventListener('input', () => verificarConexion());
        puertoInput.addEventListener('change', verificarConexion);

        // Toggle modal de configuración
        function toggleConfig() {
            const modal = document.getElementById('modal-config');
            const content = document.getElementById('modal-config-content');
            if (modal.classList.contains('opacity-0')) {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                setTimeout(() => content.classList.remove('translate-y-8', 'md:translate-y-0'), 10);
                cargarIPGuardada();
            } else {
                content.classList.add('translate-y-8', 'md:translate-y-0');
                setTimeout(() => modal.classList.add('opacity-0', 'pointer-events-none'), 300);
            }
        }

        // ============================================================
        // TOAST NOTIFICACIONES
        // ============================================================
        function mostrarToast(exito, titulo, mensaje) {
            const toast = document.getElementById('toast');
            const bg = document.getElementById('toast-bg');
            const iconBg = document.getElementById('toast-icon-bg');
            const icon = document.getElementById('toast-icon');
            const title = document.getElementById('toast-title');
            const msg = document.getElementById('toast-msg');

            bg.className = exito
                ? "glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-emerald-500/30"
                : "glass-panel p-4 rounded-2xl flex items-start gap-4 shadow-2xl border border-red-500/30";
            iconBg.className = exito
                ? "w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-emerald-500/20 text-emerald-400 mt-1"
                : "w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-500/20 text-red-400 mt-1";
            icon.className = exito ? "fa-solid fa-check text-xl" : "fa-solid fa-bug text-xl";
            title.innerText = titulo;
            msg.innerText = mensaje;

            toast.classList.remove('-translate-y-32', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-32', 'opacity-0', 'pointer-events-none');
            }, 6000);
        }

        // ============================================================
        // INSTALAR JUEGO (RPI)
        // ============================================================
        function instalarJuego(btn) {
            const url = btn.getAttribute('data-url');
            const ip = localStorage.getItem('sebas_ip_final_libre');
            const puerto = localStorage.getItem('sebas_port_rpi') || '12801';

            if (!ip) {
                mostrarToast(false, 'Falta IP', 'Configura la IP de tu PS4 en el icono de ajustes (⚙️).');
                toggleConfig();
                return;
            }
            if (!url) {
                mostrarToast(false, 'Error', 'Este juego no tiene enlace disponible.');
                return;
            }

            const textoOriginal = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ...';
            btn.disabled = true;
            btn.classList.add('opacity-50');

            const formData = new FormData();
            formData.append('action', 'install');
            formData.append('ip', ip);
            formData.append('puerto', puerto);
            formData.append('url_web', url);

            fetch('store.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    mostrarToast(data.success, data.title || (data.success ? 'Éxito' : 'Error'), data.message || '');
                })
                .catch(() => {
                    mostrarToast(false, 'Error', 'Fallo de conexión con el servidor.');
                })
                .finally(() => {
                    btn.innerHTML = textoOriginal;
                    btn.disabled = false;
                    btn.classList.remove('opacity-50');
                });
        }

        // ============================================================
        // CATÁLOGO Y FILTROS
        // ============================================================
        function cargarCatalogo() {
            const status = document.getElementById('status');
            const container = document.getElementById('lista-catalogo');
            status.textContent = '🔄 Actualizando catálogo...';

            fetch('store.php?action=get_catalog')
                .then(res => res.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        // Recargar la página para mostrar los cambios
                        location.reload();
                    } else {
                        status.textContent = 'No hay juegos en el catálogo.';
                    }
                })
                .catch(() => {
                    status.textContent = '❌ Error al actualizar el catálogo.';
                });
        }

        // Filtros
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
                const coincide = titulo.includes(texto) && (cat === 'ALL' || categoria === cat);
                card.style.display = coincide ? 'flex' : 'none';
                if (coincide) visibles++;
            });
            noResults.style.display = visibles === 0 ? 'block' : 'none';
        }
        buscador.addEventListener('input', filtrarJuegos);
        filtroCat.addEventListener('change', filtrarJuegos);

        // ============================================================
        // MODAL DE DETALLES
        // ============================================================
        function abrirDetalle(id) {
            const modal = document.getElementById('modal-detalle');
            const content = document.getElementById('detalle-contenido');
            const title = document.getElementById('detalle-titulo');

            const card = document.querySelector(`.game-card[data-id="${id}"]`);
            if (!card) return;

            const titulo = card.querySelector('h3').textContent;
            const subtitulo = card.querySelector('p')?.textContent || '';
            const categoria = card.querySelector('.text-cyan-400')?.textContent || 'PS4';
            const peso = card.querySelector('.text-emerald-400')?.textContent || '? GB';
            const servidor = card.querySelector('.text-gray-400')?.textContent || '';

            title.textContent = titulo;

            content.innerHTML = `
                <div class="flex flex-col items-center gap-3">
                    <div class="w-32 h-32 rounded-xl bg-cover bg-center border border-white/10" style="background-image: url('${card.querySelector('div[style*="background-image"]')?.style?.backgroundImage || ''}');"></div>
                    <p class="text-xs text-gray-400">${subtitulo}</p>
                    <div class="grid grid-cols-2 gap-2 w-full text-xs">
                        <div><span class="text-gray-500">Categoría:</span> ${categoria}</div>
                        <div><span class="text-gray-500">Peso:</span> ${peso}</div>
                        <div class="col-span-2"><span class="text-gray-500">Servidor:</span> ${servidor}</div>
                    </div>
                    <button onclick="event.stopPropagation(); document.querySelector('.btn-install-game[data-url]')?.click();" class="w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-black text-xs py-3 rounded-xl tracking-widest uppercase transition-all shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-download"></i> Instalar
                    </button>
                </div>
            `;

            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                modal.querySelector('#modal-detalle-content').classList.remove('scale-95');
                modal.querySelector('#modal-detalle-content').classList.add('scale-100');
            }, 10);
        }

        function cerrarDetalle() {
            const modal = document.getElementById('modal-detalle');
            const content = modal.querySelector('#modal-detalle-content');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 300);
        }

        // ============================================================
        // INICIALIZAR
        // ============================================================
        document.addEventListener('DOMContentLoaded', () => {
            cargarIPGuardada();
            // Aplicar filtros iniciales
            filtrarJuegos();
            // Mostrar estado del catálogo
            const total = document.querySelectorAll('.game-card').length;
            document.getElementById('status').textContent = total > 0 ? `${total} juegos disponibles.` : 'Catálogo vacío.';
        });
    </script>
</body>
</html>
