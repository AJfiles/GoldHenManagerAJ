<?php
/**
 * ====================================================================
 * GOLD HEN SUITE PRO 🚀 (PS5/PS4) - MÓDULO DE MODDING (PORTADAS)
 * DEVELOPED By SeBaS - RUTA: modulos/modding.php
 * ====================================================================
 */

// 🔥 MOTOR DE AUTO-LIMPIEZA Y ESCUDO ANTI-GALERÍA
// Esto se ejecuta de forma invisible cada vez que arranca la app

// 1. Crear carpetas seguras y clavar el archivo .nomedia
$rutas_proteger = [
    __DIR__ . '/../user/cache/biblioteca',
    __DIR__ . '/../user/cache/backups_portadas',
    __DIR__ . '/../user/portadas_custom'
];

foreach ($rutas_proteger as $ruta) {
    if (!file_exists($ruta)) { @mkdir($ruta, 0777, true); }
    if (!file_exists($ruta . '/.nomedia')) { @file_put_contents($ruta . '/.nomedia', ''); }
}

// 2. Destruir la carpeta vieja y molesta de la raíz automáticamente
$carpeta_vieja = __DIR__ . '/../cache_biblioteca';
if (is_dir($carpeta_vieja)) {
    $archivos = glob($carpeta_vieja . '/*');
    if (is_array($archivos)) {
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) { @unlink($archivo); }
        }
    }
    @rmdir($carpeta_vieja);
}
?>
<style>
    .skeleton-modding { position: relative; overflow: hidden; background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); }
    .skeleton-modding::after { content: ""; position: absolute; inset: 0; transform: translateX(-100%); background: linear-gradient(90deg, transparent, rgba(147, 51, 234, 0.08), transparent); animation: skeleton-ripple 1.5s infinite; }
    @keyframes skeleton-ripple { 100% { transform: translateX(100%); } }
    .galeria-scroll::-webkit-scrollbar { height: 3px; }
    .galeria-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.01); border-radius: 3px; }
    .galeria-scroll::-webkit-scrollbar-thumb { background: rgba(147, 51, 234, 0.3); border-radius: 3px; }

    /* Efecto animado para el botón central de Inyectar */
    .btn-gradient-animate {
        background-size: 200% auto;
        transition: 0.5s;
        animation: gradient-flow 3s ease infinite;
    }
    @keyframes gradient-flow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
</style>

<div id="layer-modding" class="app-layer flex flex-col p-4 h-screen w-full overflow-hidden bg-[#060913]">
    
    <div class="w-full flex items-center justify-between z-30 shrink-0 pt-1 mb-5">
        <div class="flex items-center gap-3">
            <button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-90 transition-all hover:bg-white/5">
                <i class="fa-solid fa-arrow-left text-gray-300"></i>
            </button>
            <div class="flex flex-col">
                <h2 class="text-[18px] font-black tracking-tighter uppercase text-white leading-none">Centro de Modding</h2>
                <span class="text-[9px] font-mono text-purple-400 tracking-widest mt-0.5">Inyector de Portadas</span>
            </div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
    </div>

    <div class="flex-1 w-full overflow-y-auto hide-scrollbar flex flex-col gap-4 pb-8">
        
        <div class="flex gap-2 w-full shrink-0">
            <button onclick="switchModdingTab('manual')" id="tab-mod-manual" class="flex-1 p-3 rounded-xl bg-purple-600/20 border border-purple-500/50 text-purple-400 text-[10px] font-black uppercase tracking-wider transition-all shadow-[0_0_10px_rgba(168,85,247,0.1)]">Manual</button>
            <button onclick="switchModdingTab('smart')" id="tab-mod-smart" class="flex-1 p-3 rounded-xl bg-[#111827] border border-white/5 text-gray-400 text-[10px] font-black uppercase tracking-wider transition-all">Gestor Inteligente</button>
        </div>

        <div id="modding-view-manual" class="flex flex-col gap-5 w-full transition-opacity duration-300 opacity-100">
            <div onclick="abrirSelectorJuegosModding('manual')" class="relative w-full rounded-[2rem] p-5 border border-white/10 overflow-hidden shadow-xl flex items-center gap-4 bg-[#0a0e14] cursor-pointer active:scale-[0.98] transition-all group hover:border-purple-500/30 shrink-0">
                <div id="modding-bg-blur" class="absolute inset-0 bg-cover bg-center opacity-20 blur-md pointer-events-none transition-all duration-500"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#0a0e14] via-[#0a0e14]/90 to-transparent pointer-events-none"></div>
                
                <div id="modding-avatar" class="relative z-10 w-[65px] h-[65px] rounded-xl bg-cover bg-center border border-white/20 shadow-lg shrink-0 transition-all duration-500"></div>
                
                <div class="relative z-10 flex flex-col flex-1 min-w-0">
                    <span class="text-[8px] font-black uppercase tracking-widest text-purple-400 mb-1">Juego Seleccionado</span>
                    <h3 id="modding-title" class="text-[13px] font-black uppercase text-white truncate leading-tight drop-shadow-md transition-all duration-300">Ningún Juego</h3>
                    <span id="modding-cusa" class="text-[10px] font-mono font-bold text-gray-400 tracking-widest mt-0.5 transition-all duration-300">---</span>
                </div>
                <div class="relative z-10 w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-gray-400 shrink-0 group-hover:bg-purple-500/20 group-hover:text-purple-400 transition-colors border border-transparent group-hover:border-purple-500/30">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>

            <div id="modding-preview-container" class="flex flex-col items-center shrink-0 mt-1 mb-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20 mb-3 shadow-[0_0_15px_rgba(16,185,129,0.15)]">Preview (512x512)</span>
                
                <div class="relative w-[200px] h-[200px] rounded-3xl border border-white/10 p-1 shadow-2xl bg-[#060913]">
                    <img id="modding-preview-img" src="" class="w-full h-full object-cover rounded-[1.3rem] shadow-inner" style="display: none;">
                    
                    <div id="modding-preview-placeholder" class="absolute inset-0 flex flex-col items-center justify-center opacity-30">
                        <i class="fa-regular fa-image text-4xl mb-2 text-gray-400"></i>
                        <span class="text-[9px] font-mono tracking-widest text-gray-400 uppercase">Sin Imagen</span>
                    </div>
                    
                    <div id="modding-inyectando-loader" class="absolute inset-0 bg-black/90 backdrop-blur-md rounded-[1.3rem] hidden flex-col items-center justify-center text-cyan-400 z-10">
                        <i class="fa-solid fa-microchip animate-pulse text-4xl mb-3"></i>
                        <span class="text-[9px] font-black tracking-widest uppercase animate-pulse">Inyectando...</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2 shrink-0">
                <span class="text-[9px] font-black uppercase tracking-widest text-gray-500 pl-2">Controles de Modding:</span>
                
                <div class="grid grid-cols-3 gap-2 h-20">
                    <button onclick="respaldarPortadaActual()" class="flex flex-col items-center justify-center rounded-[1.5rem] bg-[#111827] border border-white/5 active:scale-95 transition-all shadow-lg hover:border-emerald-500/30 group p-1">
                        <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 mb-1.5 group-hover:scale-110 transition-transform"><i class="fa-solid fa-download text-sm"></i></div>
                        <span class="text-[8px] font-black tracking-widest text-gray-300 uppercase text-center leading-tight">Extraer<br>Actual</span>
                    </button>
                    
                    <button id="btn-inyectar-portada" onclick="inyectarPortadaEnConsola()" class="flex flex-col items-center justify-center rounded-[1.5rem] bg-gradient-to-r from-cyan-500 via-purple-500 to-emerald-500 btn-gradient-animate border border-white/20 active:scale-95 transition-all shadow-[0_0_25px_rgba(6,182,212,0.4)] group p-1 relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors"></div>
                        <div class="relative z-10 w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white mb-1 shadow-inner backdrop-blur-sm"><i class="fa-solid fa-bolt text-lg group-hover:scale-110 transition-transform drop-shadow-md"></i></div>
                        <span class="relative z-10 text-[9px] font-black tracking-widest text-white uppercase text-center leading-tight drop-shadow-md">Inyectar<br>Portada</span>
                    </button>

                    <button onclick="document.getElementById('input-modding-file').click()" class="flex flex-col items-center justify-center rounded-[1.5rem] bg-[#111827] border border-white/5 active:scale-95 transition-all shadow-lg hover:border-cyan-500/30 group p-1">
                        <div class="w-8 h-8 rounded-full bg-cyan-500/10 flex items-center justify-center text-cyan-400 mb-1.5 group-hover:scale-110 transition-transform"><i class="fa-solid fa-image text-sm"></i></div>
                        <span class="text-[8px] font-black tracking-widest text-gray-300 uppercase text-center leading-tight">Archivo<br>Suelto</span>
                    </button>
                </div>
                
                <input type="file" id="input-modding-file" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="procesarImagenSubida(event)">
            </div>

            <div id="contenedor-galeria-modding" class="flex flex-col mt-2 bg-[#0a0e14] border border-white/5 rounded-[1.5rem] p-4 shadow-xl shrink-0 transition-all duration-300">
                
                <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-4 relative">
                    <span id="contador-galeria-modding" class="text-[8px] font-mono font-black text-gray-400 bg-white/5 border border-white/10 px-2.5 py-1 rounded-md">0</span>
                    
                    <div class="absolute left-1/2 -translate-x-1/2 flex gap-1 bg-[#060913] p-1 rounded-xl border border-white/5 shadow-inner">
                        <button onclick="cambiarTabGaleria('backups')" id="tab-backups" class="text-[9px] font-black tracking-widest uppercase px-4 py-1.5 rounded-lg bg-purple-500/20 text-purple-400 transition-all shadow-sm">Backups</button>
                        <button onclick="cambiarTabGaleria('local')" id="tab-local" class="text-[9px] font-black tracking-widest uppercase px-4 py-1.5 rounded-lg text-gray-500 hover:text-gray-300 transition-all">Carpeta</button>
                    </div>
                    
                    <button onclick="forzarRecargaGaleriasModding()" class="w-7 h-7 rounded-md bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white transition-all active:rotate-180">
                        <i class="fa-solid fa-rotate-right text-[10px]"></i>
                    </button>
                </div>
                
                <div id="galeria-modding-grid" class="flex overflow-x-auto gap-3 pb-2 galeria-scroll items-center min-h-[90px] touch-pan-x"></div>
            </div>
        </div>

        <div id="modding-view-smart" class="flex flex-col gap-4 w-full hidden opacity-0 transition-opacity duration-300">
            <div class="w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] p-4 shadow-lg flex flex-col gap-4">
                
                <input type="file" id="input-smart-files" accept="image/png, image/jpeg, application/zip" multiple class="hidden" onchange="processSmartFilesUI(event)">

                <button onclick="document.getElementById('input-smart-files').click()" class="w-full p-3.5 rounded-xl border border-purple-500/30 bg-purple-900/20 hover:bg-purple-900/40 text-purple-400 flex items-center justify-center gap-3 transition-all shadow-[0_0_15px_rgba(168,85,247,0.05)] active:scale-95">
                    <i class="fa-solid fa-file-zipper text-xl"></i>
                    <div class="flex flex-col items-start text-left">
                        <span class="text-[11px] font-black uppercase tracking-widest text-white leading-none">Cargar Archivos</span>
                        <span class="text-[8px] font-mono text-purple-400/80 mt-1">Múltiples Fotos simultáneas</span>
                    </div>
                </button>

                <div class="h-[1px] w-full bg-white/5 my-0.5"></div>

                <div class="flex flex-col gap-2 flex-1">
                    <span class="text-[9px] font-black uppercase text-gray-400 tracking-wider mb-1 flex items-center gap-2">
                        <i class="fa-solid fa-link text-purple-400"></i> Emparejamiento de Portadas
                    </span>

                    <div id="smart-list-container" class="flex flex-col gap-2 h-[45vh] min-h-[250px] overflow-y-auto custom-scrollbar pr-1 pb-1">
                        <div class="w-full py-6 flex flex-col items-center justify-center text-gray-600 border border-dashed border-white/10 rounded-xl bg-black/20">
                            <i class="fa-solid fa-box-open text-2xl mb-2 opacity-50"></i>
                            <span class="text-[9px] font-mono uppercase tracking-widest">No hay archivos cargados</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 mt-1">
                    
                    <div class="flex gap-2 w-full">
                        <button onclick="limpiarGestorInteligente()" class="w-12 rounded-xl bg-[#111827] border border-red-500/30 text-red-400 flex items-center justify-center active:scale-95 transition-all hover:bg-red-500/10 shadow-sm" title="Limpiar Todo">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                        
                        <button onclick="inyectarLoteEnConsola()" class="flex-1 p-3.5 rounded-xl bg-gradient-to-r from-purple-600 to-cyan-600 text-white font-black uppercase tracking-widest text-[10px] shadow-[0_0_20px_rgba(168,85,247,0.2)] active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-microchip"></i> Auto-Renombrar e Inyectar
                        </button>
                    </div>
                    
                    <button onclick="crearPackZIP()" class="w-full p-3 rounded-xl bg-[#111827] border border-purple-500/30 text-purple-400 font-black uppercase tracking-widest text-[9px] active:scale-95 transition-all flex items-center justify-center gap-2 hover:bg-white/5">
                        <i class="fa-solid fa-box-archive"></i> Crear Pack y Guardar
                    </button>
                </div>

                <div id="smart-progress-container" class="hidden flex-col gap-2 w-full mt-2">
                    <div class="flex justify-between text-[9px] font-bold font-mono text-purple-400">
                        <span id="smart-progress-status">INICIANDO...</span>
                        <span id="smart-progress-txt">0%</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-800 rounded-full overflow-hidden shadow-inner">
                        <div id="smart-progress-bar" class="h-full bg-gradient-to-r from-purple-500 to-cyan-500 w-0 transition-all duration-300"></div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<div id="modal-selector-juegos-modding" onclick="cerrarSelectorJuegosModding(event)" class="fixed inset-0 z-[900] bg-black/80 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300 p-0">
    <div id="modal-selector-juegos-content" onclick="event.stopPropagation()" class="w-full max-w-[400px] bg-[#0d131f] rounded-t-[2.5rem] border-t border-purple-500/20 p-6 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-15px_50px_rgba(0,0,0,0.9)] pb-10 max-h-[90vh]">
        <div class="w-12 h-1.5 bg-white/10 rounded-full mx-auto mb-6 shrink-0"></div>
        <h3 class="text-[12px] font-black tracking-widest text-purple-400 uppercase mb-5 text-center shrink-0">Seleccionar Título</h3>

        <div class="relative w-full h-11 mb-5 shrink-0 bg-[#060913] border border-white/10 rounded-xl overflow-hidden focus-within:border-purple-500/40 shadow-inner flex items-center px-4 transition-colors">
            <i class="fa-solid fa-magnifying-glass text-gray-600 text-[11px] mr-3"></i>
            <input type="text" id="buscador-juegos-modding" oninput="filtrarSelectorJuegosModding()" placeholder="Buscar juego o app..." class="w-full h-full bg-transparent text-[11px] font-bold text-white outline-none uppercase tracking-wider">
        </div>

        <div id="lista-juegos-modding" class="flex flex-col gap-2 overflow-y-auto custom-scrollbar pr-2 flex-1"></div>
    </div>
</div>

<script>
    // Script Original
    const observerPreview = new MutationObserver(() => {
        const img = document.getElementById('modding-preview-img');
        const placeholder = document.getElementById('modding-preview-placeholder');
        if (img && placeholder) {
            const src = img.getAttribute('src');
            if (src && src.trim() !== "") {
                img.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                img.style.display = 'none';
                placeholder.style.display = 'flex';
            }
        }
    });
    
    document.addEventListener("DOMContentLoaded", () => {
        const imgTarget = document.getElementById('modding-preview-img');
        if (imgTarget) {
            observerPreview.observe(imgTarget, { attributes: true, attributeFilter: ['src'] });
        }
    });
</script>
