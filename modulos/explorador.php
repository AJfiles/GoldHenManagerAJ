<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - MÓDULO EXPLORADOR FTP
 * DEVELOPED By SeBaS - RUTA: modulos/explorador.php
 * ====================================================================
 */
?>
<style>
    .scroll-x-hide::-webkit-scrollbar { display: none; }
    .scroll-x-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .item-hover:active { transform: scale(0.98); background-color: rgba(255,255,255,0.05); }
    .fab-shadow { box-shadow: 0 10px 25px rgba(6, 182, 212, 0.4); }
    .selected-row { background-color: rgba(6, 182, 212, 0.15) !important; border-color: rgba(6, 182, 212, 0.4) !important; }
</style>

<div id="layer-explorador" class="app-layer flex flex-col p-4 h-[100dvh] w-full overflow-hidden bg-[#02040a] relative hidden">
    
    <div id="explorador-header-normal" class="w-full flex flex-col z-30 shrink-0 pt-1 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-90 transition-all hover:bg-white/5">
                    <i class="fas fa-arrow-left text-gray-300"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="text-[18px] font-black tracking-tighter uppercase text-white leading-none">Explorador FTP</h2>
                    <span class="text-[9px] font-mono text-cyan-400 tracking-widest mt-0.5">Gestor Raíz de Consola</span>
                </div>
            </div>
            <div id="loader-explorador" class="hidden items-center justify-center text-cyan-400">
                <i class="fas fa-circle-notch fa-spin text-xl"></i>
            </div>
        </div>

        <div class="w-full flex items-center bg-[#0a0f1a] border border-white/10 rounded-xl px-3 py-2.5 shadow-inner">
            <button onclick="navegarArribaFtp()" class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center text-gray-400 active:scale-90 mr-2 shrink-0">
                <i class="fas fa-level-up-alt"></i>
            </button>
            <div class="flex-1 overflow-x-auto scroll-x-hide whitespace-nowrap text-[11px] font-mono text-cyan-300 tracking-wider" id="ftp-ruta-actual">
                /
            </div>
            <button id="btn-paste-top" onclick="ejecutarPegado()" class="hidden ml-2 w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 active:scale-90 shrink-0">
                <i class="fas fa-paste"></i>
            </button>
        </div>
    </div>

    <div id="explorador-header-multiselect" class="hidden w-full items-center justify-between z-30 shrink-0 pt-1 mb-4 bg-cyan-900/40 border border-cyan-500/40 rounded-2xl p-3 shadow-lg">
        <div class="flex items-center gap-3">
            <button onclick="cancelarSeleccionMultiple()" class="w-8 h-8 rounded-lg bg-black/40 flex items-center justify-center text-white active:scale-90"><i class="fas fa-times"></i></button>
            <span id="txt-multiselect-count" class="text-sm font-black text-cyan-300">0 Seleccionados</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="ejecutarAccionMultiple('copiar')" class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center active:scale-90"><i class="far fa-copy"></i></button>
            <button onclick="ejecutarAccionMultiple('cortar')" class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center active:scale-90"><i class="fas fa-cut"></i></button>
            <button onclick="ejecutarAccionMultiple('duplicar')" class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center active:scale-90"><i class="far fa-clone"></i></button>
            <button onclick="ejecutarAccionMultiple('eliminar')" class="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center active:scale-90 shadow-[0_0_10px_rgba(239,68,68,0.3)]"><i class="fas fa-trash"></i></button>
        </div>
    </div>

    <div class="w-full shrink-0 mb-4 border-b border-white/5 pb-3">
        <div class="flex justify-between items-center mb-1.5 pl-1">
            <span class="text-[8px] font-black uppercase tracking-widest text-gray-500">Accesos Rápidos:</span>
        </div>
        <div id="ftp-shortcuts-container" class="flex overflow-x-auto scroll-x-hide gap-2 pb-1 items-center"></div>
    </div>

    <div class="flex-1 w-full bg-[#0a0f1a] border border-white/5 rounded-[1.5rem] shadow-xl overflow-hidden flex flex-col relative z-10">
        <div class="flex items-center px-4 py-2 border-b border-white/5 bg-white/[0.02]">
            <span class="flex-1 text-[9px] font-black text-gray-500 uppercase tracking-widest">Nombre</span>
            <span class="w-16 text-right text-[9px] font-black text-gray-500 uppercase tracking-widest">Tamaño</span>
        </div>
        <div id="ftp-list-container" class="flex-1 overflow-y-auto hide-scrollbar p-2 flex flex-col gap-1 pb-24"></div>
    </div>

    <div class="absolute bottom-6 right-6 z-[100] flex flex-col items-end gap-3">
        <div id="fab-menu" class="hidden flex-col items-end gap-3 mb-1 scale-95 opacity-0 transition-all duration-200 origin-bottom">
            <button onclick="promptCrear('carpeta')" class="w-44 flex items-center justify-end gap-3 bg-[#0a0f1a] border border-yellow-500/30 text-yellow-400 px-4 py-2.5 rounded-full shadow-lg active:scale-95 transition-all">
                <span class="text-[10px] font-black tracking-widest uppercase text-white shadow-md text-right w-full">Crear Carpeta</span>
                <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center shrink-0"><i class="fas fa-folder-plus"></i></div>
            </button>
            <button onclick="promptCrear('archivo')" class="w-44 flex items-center justify-end gap-3 bg-[#0a0f1a] border border-gray-500/30 text-gray-400 px-4 py-2.5 rounded-full shadow-lg active:scale-95 transition-all">
                <span class="text-[10px] font-black tracking-widest uppercase text-white shadow-md text-right w-full">Crear Archivo</span>
                <div class="w-8 h-8 rounded-full bg-gray-500/20 flex items-center justify-center shrink-0"><i class="fas fa-file-invoice"></i></div>
            </button>
            <button onclick="abrirModalNuevoAccesoFtp()" class="w-44 flex items-center justify-end gap-3 bg-[#0a0f1a] border border-emerald-500/30 text-emerald-400 px-4 py-2.5 rounded-full shadow-lg active:scale-95 transition-all">
                <span class="text-[10px] font-black tracking-widest uppercase text-white shadow-md text-right w-full">Guardar Ruta</span>
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0"><i class="fas fa-star"></i></div>
            </button>
            <button onclick="document.getElementById('explorador-upload-folder').click()" class="w-44 flex items-center justify-end gap-3 bg-[#0a0f1a] border border-indigo-500/30 text-indigo-400 px-4 py-2.5 rounded-full shadow-lg active:scale-95 transition-all">
                <span class="text-[10px] font-black tracking-widest uppercase text-white shadow-md text-right w-full">Subir Carpeta</span>
                <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center shrink-0"><i class="fas fa-folder-open"></i></div>
            </button>
            <button onclick="document.getElementById('explorador-upload-file').click()" class="w-44 flex items-center justify-end gap-3 bg-[#0a0f1a] border border-cyan-500/30 text-cyan-400 px-4 py-2.5 rounded-full shadow-lg active:scale-95 transition-all">
                <span class="text-[10px] font-black tracking-widest uppercase text-white shadow-md text-right w-full">Subir Archivo</span>
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 flex items-center justify-center shrink-0"><i class="fas fa-cloud-upload-alt"></i></div>
            </button>
        </div>
        <button onclick="toggleFabMenu()" id="fab-main-btn" class="w-14 h-14 rounded-full bg-gradient-to-r from-blue-600 to-cyan-500 text-white fab-shadow flex items-center justify-center text-xl active:scale-90 transition-transform"><i class="fas fa-plus transition-transform duration-300" id="fab-icon"></i></button>
        
        <input type="file" id="explorador-upload-file" class="hidden" onchange="procesarSubidaExplorador(event)">
        <input type="file" id="explorador-upload-folder" class="hidden" webkitdirectory directory multiple onchange="procesarSubidaCarpetaEstructurada(event)">
    </div>
</div>

<div id="modal-upload-progress" class="fixed inset-0 z-[980] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border border-cyan-500/30 bg-[#0a0f1a] p-6 shadow-[0_0_50px_rgba(6,182,212,0.15)] flex flex-col">
        <div class="w-16 h-16 rounded-full bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-3xl mx-auto mb-4 border border-cyan-500/30">
            <i id="progress-icon-explorador" class="fas fa-cloud-upload-alt animate-bounce"></i>
        </div>
        <h3 class="text-[14px] font-black tracking-widest text-white uppercase text-center mb-1" id="up-modal-title">Subiendo al FTP</h3>
        <p class="text-[10px] text-gray-400 text-center font-mono mb-5 truncate px-2" id="up-modal-filename">archivo.pkg</p>
        
        <div class="flex justify-between items-end mb-2 px-1">
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest" id="up-modal-speed">0 MB/s</span>
            <span class="text-[16px] font-black text-cyan-400" id="up-modal-percent">0%</span>
        </div>
        <div class="w-full h-3 bg-black rounded-full overflow-hidden border border-white/10 mb-4 shadow-inner">
            <div id="up-modal-bar" class="h-full bg-gradient-to-r from-blue-600 to-cyan-400 w-0 transition-all duration-300 relative">
                <div class="absolute inset-0 bg-white/20 w-full animate-[pulse_1.5s_ease-in-out_infinite]"></div>
            </div>
        </div>
        
        <div class="flex justify-between items-center text-[9px] font-mono text-gray-500 mb-8 px-1">
            <span id="up-modal-sent">0 MB / 0 MB</span>
            <span id="up-modal-eta" class="text-white font-bold">ETA: --:--</span>
        </div>
        
        <button onclick="cancelarSubidaExplorador()" class="w-full py-4 rounded-xl border border-red-500/30 bg-red-500/10 text-[11px] font-black tracking-widest uppercase text-red-400 active:scale-95 transition-all hover:bg-red-500/20">
            <i class="fas fa-ban mr-1"></i> Abortar Transferencia
        </button>
    </div>
</div>

<div id="ctx-menu-sheet" onclick="cerrarContextMenu()" class="fixed inset-0 z-[200] bg-black/80 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300 p-0">
    <div id="ctx-menu-content" onclick="event.stopPropagation()" class="w-full max-w-md bg-[#0a0f1a] rounded-t-[2rem] border-t border-cyan-500/30 p-6 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-20px_50px_rgba(0,0,0,0.8)] pb-8">
        <div class="w-12 h-1.5 bg-white/10 rounded-full mx-auto mb-5 shrink-0"></div>
        <div class="flex items-center gap-3 mb-6 px-2 border-b border-white/5 pb-4">
            <div id="ctx-icon" class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xl"><i class="fas fa-file"></i></div>
            <div class="flex flex-col overflow-hidden">
                <span id="ctx-title" class="text-[15px] font-black text-white truncate w-full">Nombre</span>
                <span id="ctx-subtitle" class="text-[10px] font-mono text-gray-500 uppercase tracking-widest mt-0.5">Detalles</span>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-y-6 gap-x-2">
            <button onclick="ctxAction('copiar')" class="flex flex-col items-center gap-2.5 text-gray-300 active:scale-90 transition-transform group"><div class="w-14 h-14 rounded-[1.2rem] bg-white/5 flex items-center justify-center text-xl group-hover:bg-blue-500/20 group-hover:text-blue-400 border border-white/5 transition-all"><i class="far fa-copy"></i></div><span class="text-[9px] font-black uppercase tracking-widest">Copiar</span></button>
            <button onclick="ctxAction('cortar')" class="flex flex-col items-center gap-2.5 text-gray-300 active:scale-90 transition-transform group"><div class="w-14 h-14 rounded-[1.2rem] bg-white/5 flex items-center justify-center text-xl group-hover:bg-orange-500/20 group-hover:text-orange-400 border border-white/5 transition-all"><i class="fas fa-cut"></i></div><span class="text-[9px] font-black uppercase tracking-widest">Cortar</span></button>
            <button onclick="ctxAction('renombrar')" class="flex flex-col items-center gap-2.5 text-gray-300 active:scale-90 transition-transform group"><div class="w-14 h-14 rounded-[1.2rem] bg-white/5 flex items-center justify-center text-xl group-hover:bg-purple-500/20 group-hover:text-purple-400 border border-white/5 transition-all"><i class="fas fa-edit"></i></div><span class="text-[9px] font-black uppercase tracking-widest">Renombrar</span></button>
            <button onclick="ctxAction('duplicar')" class="flex flex-col items-center gap-2.5 text-gray-300 active:scale-90 transition-transform group"><div class="w-14 h-14 rounded-[1.2rem] bg-white/5 flex items-center justify-center text-xl group-hover:bg-emerald-500/20 group-hover:text-emerald-400 border border-white/5 transition-all"><i class="far fa-clone"></i></div><span class="text-[9px] font-black uppercase tracking-widest">Duplicar</span></button>
            <button onclick="ctxAction('compartir')" class="flex flex-col items-center gap-2.5 text-gray-300 active:scale-90 transition-transform group"><div class="w-14 h-14 rounded-[1.2rem] bg-white/5 flex items-center justify-center text-xl group-hover:bg-cyan-500/20 group-hover:text-cyan-400 border border-white/5 transition-all"><i class="fas fa-download"></i></div><span class="text-[9px] font-black uppercase tracking-widest text-center leading-tight">Al Celular</span></button>
            <button onclick="ctxAction('seleccionar')" class="flex flex-col items-center gap-2.5 text-gray-300 active:scale-90 transition-transform group"><div class="w-14 h-14 rounded-[1.2rem] bg-white/5 flex items-center justify-center text-xl group-hover:bg-indigo-500/20 group-hover:text-indigo-400 border border-white/5 transition-all"><i class="fas fa-check-double"></i></div><span class="text-[9px] font-black uppercase tracking-widest text-center leading-tight">Múltiple</span></button>
        </div>
        <div class="w-full mt-6 pt-5 border-t border-white/5 flex justify-center">
            <button onclick="ctxAction('eliminar')" class="w-full py-3.5 flex items-center justify-center gap-3 text-red-400 active:scale-95 transition-transform group bg-red-950/30 rounded-xl border border-red-500/20 hover:bg-red-900/50"><i class="far fa-trash-alt text-lg group-hover:scale-110 transition-transform"></i><span class="text-[11px] font-black uppercase tracking-widest">Eliminar de la Consola</span></button>
        </div>
    </div>
</div>

<div id="modal-prompt-ftp" class="fixed inset-0 z-[960] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border border-white/10 bg-[#070b14]/95 p-6 shadow-2xl flex flex-col">
        <h3 id="prompt-title" class="text-[14px] font-black tracking-widest text-white uppercase text-center mb-4">Acción</h3>
        <input type="text" id="prompt-input" class="w-full bg-[#0a0f1a] border border-cyan-500/30 rounded-xl px-4 py-3.5 mb-6 text-[12px] font-bold text-white outline-none focus:border-cyan-400 transition-colors">
        <div class="flex gap-3 w-full">
            <button onclick="cerrarPromptFtp()" class="flex-1 py-3 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">Cancelar</button>
            <button id="prompt-btn-confirm" class="flex-1 py-3 rounded-xl bg-cyan-600 text-[10px] font-black tracking-widest uppercase text-black active:scale-95 transition-all">Confirmar</button>
        </div>
    </div>
</div>

<div id="modal-delete-confirm" class="fixed inset-0 z-[970] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border-2 border-orange-500/50 bg-[#0a0202]/95 p-6 shadow-2xl flex flex-col items-center text-center transition-colors duration-300" id="del-modal-box">
        <div id="del-modal-icon" class="w-16 h-16 rounded-full bg-orange-500/20 text-orange-500 flex items-center justify-center text-3xl mb-4"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 id="del-modal-title" class="text-[16px] font-black tracking-widest text-orange-400 uppercase mb-2">Advertencia</h3>
        <p id="del-modal-desc" class="text-[10px] text-gray-300 mb-6 px-2 leading-relaxed">...</p>
        <div class="flex flex-col gap-3 w-full">
            <button id="btn-real-delete" class="w-full py-4 rounded-xl bg-orange-600 text-[11px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_15px_rgba(249,115,22,0.4)]">Siguiente Paso</button>
            <button onclick="cerrarDeleteConfirm()" class="w-full py-3 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">Cancelar Acción</button>
        </div>
    </div>
</div>

<div id="modal-nuevo-acceso-ftp" class="fixed inset-0 z-[950] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border border-white/10 bg-[#070b14]/95 p-6 shadow-2xl flex flex-col">
        <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xl mx-auto mb-3 shadow-[0_0_15px_rgba(16,185,129,0.3)]"><i class="fas fa-star"></i></div>
        <h3 class="text-[14px] font-black tracking-widest text-white uppercase text-center mb-1">Añadir a Favoritos</h3>
        <p class="text-[9px] text-gray-400 text-center font-mono mb-4 px-2 truncate" id="txt-ruta-a-guardar"></p>
        <label class="text-[9px] font-black uppercase tracking-widest text-gray-500 mb-1 pl-1">Nombre del acceso directo:</label>
        <input type="text" id="ftp-shortcut-btn-name" placeholder="Ej: Mods Minecraft" class="w-full bg-[#0a0f1a] border border-white/10 rounded-xl px-4 py-3.5 mb-6 text-[11px] font-bold text-white outline-none placeholder:text-gray-700 uppercase tracking-wider focus:border-emerald-500/50 transition-colors">
        <div class="flex gap-3 w-full">
            <button onclick="cerrarModalNuevoAccesoFtp()" class="flex-1 py-3.5 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">Cancelar</button>
            <button onclick="guardarNuevoAccesoFtp()" class="flex-1 py-3.5 rounded-xl bg-emerald-600 text-[10px] font-black tracking-widest uppercase text-black active:scale-95 transition-all shadow-[0_0_15px_rgba(16,185,129,0.4)]">Guardar</button>
        </div>
    </div>
</div>
