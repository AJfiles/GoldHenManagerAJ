<?php
/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - MÓDULO GESTOR DE MODS MULTIMOTOR
 * RUTA: modulos/mods.php
 * ====================================================================
 */
?>
<div id="layer-mods" class="app-layer flex flex-col p-4 h-[100dvh] w-full overflow-hidden bg-[#02040a] relative hidden">
    
    <div class="w-full flex items-center justify-between z-30 shrink-0 pt-1 mb-4">
        <div class="flex items-center gap-3">
            <button onclick="volverAlLauncher()" class="w-10 h-10 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-90 transition-all hover:bg-white/5">
                <i class="fas fa-arrow-left text-gray-300"></i>
            </button>
            <div class="flex flex-col">
                <h2 class="text-[17px] font-black tracking-tighter uppercase text-white leading-none">Game Modding</h2>
                <span class="text-[9px] font-mono text-indigo-400 tracking-widest mt-0.5">Multi-Engine Auto-Routing</span>
            </div>
        </div>
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.2)]">
            <i class="fas fa-cubes text-lg"></i>
        </div>
    </div>

    <div onclick="abrirSelectorJuegosModsGlobal()" class="glass-premium p-4 rounded-3xl border border-white/5 flex flex-col relative overflow-hidden group cursor-pointer mb-4 shrink-0 transition-all hover:border-indigo-500/30">
        <div id="mod-global-blur" class="absolute inset-0 bg-cover bg-center opacity-20 blur-xl transition-all duration-500"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#02040a] via-[#02040a]/80 to-transparent"></div>
        <div class="relative z-10 flex items-center gap-4">
            <div id="mod-global-avatar" class="w-14 h-14 rounded-xl bg-[#111827] border border-white/10 shadow-lg bg-cover bg-center flex items-center justify-center shrink-0"></div>
            <div class="flex flex-col flex-1 min-w-0">
                <span class="text-[8px] font-black tracking-widest text-indigo-400 uppercase mb-0.5">Target Game</span>
                <h3 id="mod-global-title" class="text-[14px] font-black tracking-widest text-white uppercase truncate">Seleccionar Juego</h3>
                <span id="mod-global-cusa" class="text-[10px] font-mono text-gray-500 mt-0.5">---</span>
            </div>
            <i class="fa-solid fa-chevron-down text-indigo-500/50 mr-2 shrink-0 group-hover:text-indigo-400"></i>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto hide-scrollbar flex flex-col pb-10 relative">

        <div id="entorno-vacio-placeholder" class="flex-1 w-full flex flex-col items-center justify-center opacity-80 mt-6 transition-opacity">
            <div class="relative w-32 h-32 mb-6">
                <div class="absolute inset-0 bg-indigo-500/20 rounded-full blur-2xl animate-pulse"></div>
                <div class="relative w-full h-full bg-[#0a0f1a] border border-white/10 rounded-full flex items-center justify-center text-5xl text-indigo-500/60 shadow-inner">
                    <i class="fas fa-gamepad"></i>
                </div>
            </div>
            <h3 class="text-[14px] font-black tracking-widest text-white uppercase mb-2">Módulo en Espera</h3>
        </div>

        <div id="entorno-minecraft-completo" class="hidden flex-col gap-3 animate-fade-in">
            <div class="w-full bg-red-500/10 border border-red-500/30 rounded-xl p-3 flex gap-3 shrink-0 shadow-sm items-start">
                <div class="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center shrink-0"><i class="fas fa-shield-alt"></i></div>
                <div class="flex flex-col flex-1">
                    <span class="text-[10px] font-black uppercase text-red-400 tracking-wider">Reglas Críticas de Minecraft</span>
                    <ul class="text-[8.5px] text-red-100/80 font-mono mt-1.5 space-y-1.5 leading-tight">
                        <li class="flex gap-1.5"><span class="text-red-500">▶</span> <span>Abre el juego para crear <span class="bg-black/50 px-1 rounded">app_tmp/</span></span></li>
                        <li class="flex gap-1.5"><span class="text-red-500">↻</span> <span><b>Reinicia el juego</b> tras activar un mod.</span></li>
                        <li class="flex gap-1.5"><span class="text-red-500">⏻</span> <span><b>Apaga todos los mods</b> antes de salir.</span></li>
                    </ul>
                </div>
            </div>
            <button onclick="abrirEnsambladorMod()" class="w-full py-3 rounded-xl bg-indigo-600 text-[11px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-md flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle text-base"></i> Ensamblar Nuevo Mod
            </button>
            <div class="w-full flex flex-col gap-2 mt-1">
                <div class="flex justify-between items-center pl-1 border-b border-white/5 pb-1.5">
                    <span class="text-[9px] font-black tracking-widest uppercase text-gray-400">Bóveda de Resguardo Interna (PS4):</span>
                    <span class="text-[8px] font-mono text-emerald-500"><i class="fas fa-microchip"></i> Zero-Delay</span>
                </div>
                <div id="lista-boveda-mods" class="flex flex-col gap-2"></div>
            </div>
        </div>

        <div id="entorno-afr-completo" class="hidden flex-col gap-4 animate-fade-in">
            <div class="flex flex-col gap-2 shrink-0 relative transition-all duration-300">
                <div class="flex justify-between items-center pl-2">
                    <span class="text-[9px] font-black uppercase tracking-widest text-gray-500">Controles Motor AFR:</span>
                    <button onclick="togglePluginButtonsAFR()" id="btn-toggle-plugins" class="text-[8px] font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full shadow-sm active:scale-95">
                        <i class="fa-solid fa-eye-slash" id="icon-toggle-plugins"></i> <span id="txt-toggle-plugins">Ocultar Sist. Plugins</span>
                    </button>
                </div>
                
                <div class="grid gap-2 transition-all duration-300 items-stretch" id="afr-btn-container" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                    <button id="btn-afr-backup" onclick="realizarBackupPlugins()" class="flex flex-col items-center justify-center rounded-xl bg-[#111827] border border-white/5 active:scale-95 transition-all p-2 h-full">
                        <div class="w-7 h-7 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400 mb-1"><i class="fa-solid fa-download text-xs"></i></div>
                        <span class="text-[8px] font-black tracking-widest text-gray-300 uppercase text-center leading-tight">Backup Plugins<br>Originales</span>
                    </button>
                    
                    <button id="btn-afr-upload" onclick="iniciarFlujoSubidaAFR()" class="col-span-1 flex flex-col items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 border border-white/20 active:scale-95 transition-all shadow-lg p-2 h-full">
                        <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-white mb-1"><i class="fa-solid fa-file-arrow-up text-xs"></i></div>
                        <span class="text-[8px] font-black tracking-widest text-white uppercase text-center leading-tight">Subir Mod<br>.PAK</span>
                    </button>

                    <input type="file" id="input-install-afr-zip" accept=".zip" class="hidden" onchange="procesarInstalacionPlugins(event)">
                    <button id="btn-afr-install" onclick="document.getElementById('input-install-afr-zip').click()" class="flex flex-col items-center justify-center rounded-xl bg-[#111827] border border-indigo-500/20 active:scale-95 transition-all p-2 opacity-40 pointer-events-none h-full">
                        <div class="w-7 h-7 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 mb-1"><i class="fa-solid fa-bolt text-xs"></i></div>
                        <span class="text-[8px] font-black tracking-widest text-indigo-300 uppercase text-center leading-tight">Instalar Plugins<br>Mods</span>
                    </button>
                </div>
            </div>

            <div class="glass-premium rounded-3xl border border-white/5 flex flex-col overflow-hidden shadow-lg mt-2">
                <div class="p-4 border-b border-white/5 bg-black/20 flex justify-between items-center">
                    <span class="text-[11px] font-black text-white tracking-widest uppercase"><i class="fa-solid fa-layer-group text-indigo-400 mr-2"></i> Bóveda AFR</span>
                    <button onclick="toggleModoEdicionAFR()" id="btn-edit-afr" class="text-[10px] font-black tracking-widest uppercase text-gray-300 bg-white/5 border border-white/10 px-4 py-2 rounded-xl active:scale-95 transition-all"><i class="fas fa-pen mr-1"></i> Editar</button>
                </div>
                <div id="contenedor-afr-liquido" class="flex flex-col w-full p-2 gap-4 pb-4"></div>
            </div>
            
            <button onclick="crearNuevaCategoriaFlujo()" class="w-full py-4 rounded-xl border-2 border-dashed border-white/10 bg-[#111827] text-[10px] font-black tracking-widest text-gray-400 uppercase active:scale-95 transition-all mt-2 flex items-center justify-center gap-2">
                <i class="fas fa-folder-plus text-lg"></i> Crear Nueva Categoría
            </button>
        </div>

        <div id="entorno-nosoportado" class="hidden flex-col items-center justify-center opacity-80 mt-10 text-center px-4">
            <i class="fa-solid fa-ban text-4xl text-gray-600 mb-4"></i>
            <h3 class="text-[14px] font-black tracking-widest text-white uppercase mb-2">Motor No Disponible</h3>
        </div>
    </div>
</div>

<div id="modal-afr-texto" class="fixed inset-0 z-[999] bg-black/95 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm bg-[#0a0f1a] rounded-[2rem] border border-indigo-500/50 p-6 shadow-2xl flex flex-col transform scale-95 transition-transform duration-300">
        <h3 id="modal-texto-titulo" class="text-[14px] font-black tracking-widest text-white uppercase mb-4 text-center">Título</h3>
        <input type="text" id="modal-texto-input" class="w-full bg-[#111827] border-2 border-white/10 rounded-xl px-4 py-4 text-[13px] font-bold text-white outline-none mb-6 uppercase tracking-wider focus:border-indigo-500/50 text-center shadow-inner">
        <div class="flex gap-3">
            <button onclick="cerrarModalTexto()" class="flex-1 py-4 rounded-xl border border-white/10 bg-white/5 text-[11px] font-black tracking-widest text-gray-300 uppercase active:scale-95 transition-all">Cancelar</button>
            <button onclick="confirmarModalTexto()" class="flex-1 py-4 rounded-xl bg-indigo-600 text-[11px] font-black tracking-widest text-white uppercase active:scale-95 transition-all shadow-[0_0_15px_rgba(79,70,229,0.3)]">Aceptar</button>
        </div>
    </div>
</div>

<!-- 🔥 FIX: MODAL DE ADVERTENCIA/ERROR CON BOTONES DINÁMICOS -->
<div id="modal-afr-peligro" class="fixed inset-0 z-[999] bg-black/95 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm bg-[#1a0505] rounded-[2rem] border-2 border-red-500/50 p-6 shadow-[0_0_50px_rgba(239,68,68,0.2)] flex flex-col transform scale-95 transition-transform duration-300 text-center">
        <div class="w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center text-3xl mx-auto mb-4"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 id="modal-peligro-titulo" class="text-[15px] font-black tracking-widest text-red-400 uppercase mb-2">Advertencia</h3>
        <p id="modal-peligro-msg" class="text-[11px] text-gray-300 mb-6 leading-relaxed">¿Seguro?</p>
        <div class="flex flex-col gap-3">
            <button id="btn-peligro-action" onclick="confirmarModalPeligro()" class="w-full py-4 rounded-xl bg-red-600 text-[12px] font-black tracking-widest text-white uppercase shadow-[0_0_15px_rgba(220,38,38,0.4)] active:scale-95 transition-all">SÍ, DESTRUIR</button>
            <button id="btn-peligro-cancel" onclick="cancelarModalPeligro()" class="w-full py-4 rounded-xl border border-white/10 bg-white/5 text-[11px] font-black tracking-widest text-gray-300 uppercase active:scale-95 transition-all">CANCELAR</button>
        </div>
    </div>
</div>

<input type="file" id="input-afr-file" accept=".pak" multiple class="hidden" onchange="procesarArchivosAFRUnificado(event)">

<div id="modal-afr-upload-config" class="fixed inset-0 z-[960] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm bg-[#0a0f1a] rounded-[2rem] border-2 border-indigo-500/30 p-6 transform scale-95 transition-transform duration-300 flex flex-col shadow-[0_15px_50px_rgba(0,0,0,0.9)] max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center gap-3 mb-5 border-b border-white/5 pb-4">
            <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400"><i class="fas fa-box-open text-lg"></i></div>
            <div class="flex flex-col">
                <h3 class="text-[14px] font-black tracking-widest text-white uppercase">Inyectar Paquete</h3>
                <p id="afr-upload-file-count" class="text-[9px] text-gray-400 font-mono">0 archivos seleccionados</p>
            </div>
        </div>
        <div class="flex flex-col gap-2 mb-4">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Nombre Visual:</label>
            <input type="text" id="afr-mod-name" placeholder="Ej: Jill S.T.A.R.S..." class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[12px] font-bold text-white outline-none placeholder:text-gray-700 uppercase tracking-wider focus:border-indigo-500/50 shadow-inner">
        </div>
        <div class="flex flex-col gap-2 mb-4">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Categoría Destino:</label>
            <div class="flex gap-2">
                <select id="afr-mod-cat" class="flex-1 bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[11px] font-bold text-white outline-none uppercase tracking-wider focus:border-indigo-500/50 appearance-none shadow-inner" onchange="actualizarDesplegableDestino()"></select>
                <button onclick="crearNuevaCategoriaFlujo()" class="w-12 h-auto rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center active:scale-90 transition-all"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="flex flex-col gap-2 mb-5">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Ubicación Estructural:</label>
            <select id="afr-mod-destino" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[11px] font-bold text-white outline-none uppercase tracking-wider focus:border-indigo-500/50 appearance-none shadow-inner" onchange="verificarInputNuevoGrupo()"></select>
        </div>
        <div id="box-nuevo-grupo" class="flex flex-col gap-2 mb-5 hidden">
            <label class="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Nombre del Nuevo Grupo:</label>
            <input type="text" id="afr-mod-nuevo-grupo" placeholder="Ej: Paquete Armas..." class="w-full bg-indigo-900/20 border border-indigo-500/30 rounded-xl px-4 py-4 text-[12px] font-bold text-white outline-none uppercase tracking-wider shadow-inner">
        </div>
        <div class="flex flex-col gap-3 w-full mt-2">
            <button onclick="confirmarSubidaUnificadaAFR()" class="w-full py-4 rounded-xl bg-indigo-600 text-[12px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_20px_rgba(79,70,229,0.4)]">Comenzar Inyección</button>
            <button onclick="cerrarModalAFRUpload()" class="w-full py-4 rounded-xl border border-white/10 bg-white/5 text-[11px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">Cancelar</button>
        </div>
    </div>
</div>

<div id="modal-afr-edit-avanzado" class="fixed inset-0 z-[970] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm bg-[#0a0f1a] rounded-[2rem] border-2 border-indigo-500/30 p-6 transform scale-95 transition-transform duration-300 flex flex-col shadow-[0_15px_50px_rgba(0,0,0,0.9)] max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center gap-3 mb-5 border-b border-white/5 pb-4">
            <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400"><i class="fas fa-pen text-lg"></i></div>
            <div class="flex flex-col">
                <h3 class="text-[14px] font-black tracking-widest text-white uppercase">Editar Paquete</h3>
                <p class="text-[9px] text-gray-400 font-mono">Modificando estructura...</p>
            </div>
        </div>

        <input type="hidden" id="edit-avanzado-id">
        <input type="hidden" id="edit-avanzado-is-suelto">

        <div class="flex flex-col gap-2 mb-4">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Nombre Visual:</label>
            <input type="text" id="edit-avanzado-name" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[12px] font-bold text-white outline-none uppercase tracking-wider focus:border-indigo-500/50 shadow-inner">
        </div>
        <div class="flex flex-col gap-2 mb-4">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Categoría:</label>
            <select id="edit-avanzado-cat" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[11px] font-bold text-white outline-none uppercase tracking-wider focus:border-indigo-500/50 appearance-none shadow-inner" onchange="actualizarDesplegableGruposEdit()"></select>
        </div>
        <div class="flex flex-col gap-2 mb-4">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Ubicación (Grupo):</label>
            <select id="edit-avanzado-grupo" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[11px] font-bold text-white outline-none uppercase tracking-wider focus:border-indigo-500/50 appearance-none shadow-inner"></select>
        </div>
        <div class="flex flex-col gap-2 mb-5">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 pl-1">Tipo de Mod:</label>
            <select id="edit-avanzado-tipo" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-4 text-[11px] font-bold text-white outline-none uppercase tracking-wider focus:border-indigo-500/50 appearance-none shadow-inner">
                <option value="base" class="font-bold text-indigo-400">📦 MOD BASE</option>
                <option value="variante" class="font-bold text-rose-400">🎨 VARIANTE (Textura/Color)</option>
            </select>
        </div>
        <div class="flex flex-col gap-3 w-full mt-2">
            <button onclick="guardarEdicionAvanzadaMod()" class="w-full py-4 rounded-xl bg-indigo-600 text-[12px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_20px_rgba(79,70,229,0.4)]">Guardar Cambios</button>
            <button onclick="cerrarModalEdicionAvanzada()" class="w-full py-4 rounded-xl border border-white/10 bg-white/5 text-[11px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">Cancelar</button>
        </div>
    </div>
</div>

<div id="modal-afr-popup-grupo" onclick="cerrarPopupGrupoAFR(event)" class="fixed inset-0 z-[950] bg-black/80 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300 p-0">
    <div id="content-afr-popup-grupo" onclick="event.stopPropagation()" class="w-full max-w-md bg-[#0a0f1a] rounded-t-[2.5rem] border-t border-indigo-500/30 p-6 pt-8 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-15px_50px_rgba(0,0,0,0.9)] max-h-[85vh] relative">
        <button onclick="cerrarPopupGrupoAFR(null, true)" class="absolute top-5 right-5 w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white transition-colors active:scale-90 text-lg"><i class="fas fa-times"></i></button>
        <div class="flex flex-col mb-6 pr-12 border-b border-white/5 pb-4">
            <span class="text-[9px] font-mono tracking-widest text-indigo-400 uppercase mb-1" id="popup-grupo-categoria">CATEGORÍA</span>
            <h3 class="text-[18px] font-black tracking-widest text-white uppercase leading-tight" id="popup-grupo-titulo">Nombre</h3>
        </div>
        <div class="flex-1 overflow-y-auto hide-scrollbar flex flex-col gap-6 pb-6">
            <div class="flex flex-col gap-3">
                <span class="text-[10px] font-black tracking-widest text-gray-400 uppercase pl-1 bg-[#0a0f1a] sticky top-0"><i class="fas fa-cube text-indigo-400 mr-2"></i> Mods Base</span>
                <div id="popup-lista-base" class="flex flex-col gap-3"></div>
            </div>
            <div class="flex flex-col gap-3 mt-2">
                <span class="text-[10px] font-black tracking-widest text-gray-400 uppercase pl-1 bg-[#0a0f1a] sticky top-0"><i class="fas fa-palette text-rose-400 mr-2"></i> Variantes</span>
                <div id="popup-lista-variantes" class="flex flex-col gap-3"></div>
            </div>
        </div>
    </div>
</div>

<div id="modal-afr-upload-progress" class="fixed inset-0 z-[980] bg-black/95 backdrop-blur-sm hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border-2 border-indigo-500/50 bg-[#0a0f1a] p-8 shadow-[0_0_80px_rgba(99,102,241,0.2)] flex flex-col items-center text-center">
        <div class="relative w-20 h-20 mb-6 flex items-center justify-center">
            <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-indigo-400 rounded-full border-t-transparent animate-spin"></div>
            <i class="fa-solid fa-cloud-arrow-up text-3xl text-indigo-400 animate-pulse"></i>
        </div>
        <h3 class="text-[18px] font-black tracking-widest text-white uppercase mb-2">Subiendo</h3>
        <p id="afr-progress-text" class="text-[11px] text-gray-400 font-mono mb-6">Procesando...</p>
        
        <div class="flex justify-between w-full text-[9px] font-bold font-mono text-indigo-400 mb-1">
            <span>PROGRESO</span><span id="afr-upload-pct">0%</span>
        </div>
        
        <div class="w-full bg-black rounded-full overflow-hidden border border-white/10 shadow-inner h-3">
            <div id="afr-upload-bar" class="h-full bg-gradient-to-r from-indigo-600 to-purple-400 w-0 transition-all duration-300"></div>
        </div>
    </div>
</div>

<div id="modal-afr-calibracion" class="fixed inset-0 z-[990] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border-2 border-emerald-500/50 bg-[#051a11]/95 p-6 flex flex-col items-center text-center">
        <div class="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-4xl mb-4"><i class="fa-solid fa-radar"></i></div>
        <h3 class="text-[18px] font-black tracking-widest text-emerald-400 uppercase mb-2">Calibración</h3>
        <p class="text-[11px] text-gray-300 mb-6">Abre el juego una vez y presiona calibrar para no tener que abrirlo nunca más.</p>
        <button onclick="ejecutarCalibracionAFR()" class="w-full py-4 rounded-xl bg-emerald-600 text-[13px] font-black tracking-widest text-white uppercase shadow-[0_0_20px_rgba(16,185,129,0.4)] mb-3"><i class="fas fa-search mr-2"></i> Calibrar</button>
        <button onclick="cerrarModalCalibracion(true)" class="w-full py-4 rounded-xl bg-white/5 text-gray-300 text-[11px] uppercase font-black tracking-widest">Ignorar</button>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALES Y COMPONENTES MINECRAFT            -->
<!-- ========================================== -->
<div id="modal-ensamblador" class="fixed inset-0 z-[960] bg-black/90 backdrop-blur-md hidden flex items-end justify-center opacity-0 transition-opacity duration-300">
    <div id="modal-content-ensamblador" class="w-full max-w-md bg-[#0a0f1a] rounded-t-[2rem] border-t border-indigo-500/30 p-5 pt-6 transform translate-y-full transition-transform duration-300 flex flex-col h-[90vh]">
        <div class="flex items-center justify-between mb-4 shrink-0">
            <h3 class="text-[14px] font-black tracking-widest text-white uppercase"><i class="fas fa-box-open mr-2 text-indigo-400"></i> Ensamblar Mod</h3>
            <button id="btn-cerrar-ensamblador" onclick="cerrarEnsambladorMod()" class="w-8 h-8 rounded-full bg-white/5 text-gray-400 flex items-center justify-center hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto hide-scrollbar flex flex-col gap-4 pb-4">
            <div class="flex flex-col gap-1.5 shrink-0">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-500 pl-1">Identificador del Paquete:</label>
                <input type="text" id="input-nombre-mod" placeholder="Ej: Modo Dios + Shaders" class="w-full bg-[#111827] border border-white/10 rounded-xl px-4 py-3.5 text-[12px] font-bold text-white outline-none placeholder:text-gray-700 uppercase tracking-wider focus:border-indigo-500/50 transition-colors">
            </div>
            <div class="flex flex-col shrink-0">
                <span class="text-[9px] font-black uppercase tracking-widest text-gray-500 pl-1 border-b border-white/5 pb-2 mb-2">Añadir recursos a las rutas:</span>
                
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2 bg-[#111827] border border-white/5 rounded-xl flex flex-col justify-between transition-all">
                        <div class="flex items-center gap-1.5 text-yellow-500 text-[10px] font-bold mb-2"><i class="fas fa-folder"></i> <span class="text-[7.5px] text-gray-300 font-mono truncate">behavior_packs</span></div>
                        <div class="flex gap-1 w-full">
                            <button onclick="triggerFileSelect('behavior_packs', 'file')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Archivo</button>
                            <button onclick="triggerFileSelect('behavior_packs', 'folder')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Carpeta</button>
                        </div>
                    </div>
                    
                    <div class="p-2 bg-[#111827] border border-white/5 rounded-xl flex flex-col justify-between transition-all">
                        <div class="flex items-center gap-1.5 text-orange-500 text-[10px] font-bold mb-2"><i class="fas fa-folder"></i> <span class="text-[7.5px] text-gray-300 font-mono truncate">dev_behavior_packs</span></div>
                        <div class="flex gap-1 w-full">
                            <button onclick="triggerFileSelect('development_behavior_packs', 'file')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Archivo</button>
                            <button onclick="triggerFileSelect('development_behavior_packs', 'folder')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Carpeta</button>
                        </div>
                    </div>
                    
                    <div class="p-2 bg-[#111827] border border-white/5 rounded-xl flex flex-col justify-between transition-all">
                        <div class="flex items-center gap-1.5 text-emerald-500 text-[10px] font-bold mb-2"><i class="fas fa-folder"></i> <span class="text-[7.5px] text-gray-300 font-mono truncate">resource_packs</span></div>
                        <div class="flex gap-1 w-full">
                            <button onclick="triggerFileSelect('resource_packs', 'file')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Archivo</button>
                            <button onclick="triggerFileSelect('resource_packs', 'folder')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Carpeta</button>
                        </div>
                    </div>
                    
                    <div class="p-2 bg-[#111827] border border-white/5 rounded-xl flex flex-col justify-between transition-all">
                        <div class="flex items-center gap-1.5 text-cyan-500 text-[10px] font-bold mb-2"><i class="fas fa-folder"></i> <span class="text-[7.5px] text-gray-300 font-mono truncate">dev_resource_packs</span></div>
                        <div class="flex gap-1 w-full">
                            <button onclick="triggerFileSelect('development_resource_packs', 'file')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Archivo</button>
                            <button onclick="triggerFileSelect('development_resource_packs', 'folder')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Carpeta</button>
                        </div>
                    </div>
                    
                    <div class="p-2 bg-[#111827] border border-white/5 rounded-xl flex flex-col justify-between transition-all">
                        <div class="flex items-center gap-1.5 text-pink-500 text-[10px] font-bold mb-2"><i class="fas fa-folder"></i> <span class="text-[7.5px] text-gray-300 font-mono truncate">dev_skin_packs</span></div>
                        <div class="flex gap-1 w-full">
                            <button onclick="triggerFileSelect('development_skin_packs', 'file')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Archivo</button>
                            <button onclick="triggerFileSelect('development_skin_packs', 'folder')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Carpeta</button>
                        </div>
                    </div>
                    
                    <div class="p-2 bg-[#111827] border border-white/5 rounded-xl flex flex-col justify-between transition-all">
                        <div class="flex items-center gap-1.5 text-blue-500 text-[10px] font-bold mb-2"><i class="fas fa-folder"></i> <span class="text-[7.5px] text-gray-300 font-mono truncate">world_templates</span></div>
                        <div class="flex gap-1 w-full">
                            <button onclick="triggerFileSelect('world_templates', 'file')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Archivo</button>
                            <button onclick="triggerFileSelect('world_templates', 'folder')" class="flex-1 py-1.5 rounded-md bg-white/5 hover:bg-indigo-500/20 text-[7px] font-black uppercase tracking-widest text-gray-300 hover:text-indigo-400 active:scale-95 transition-all text-center">Carpeta</button>
                        </div>
                    </div>
                </div>

                <input type="file" id="input-carpeta-ensamblador" class="hidden" webkitdirectory directory multiple onchange="procesarCarpetaEnsamblador(event)">
                <input type="file" id="input-archivo-ensamblador" class="hidden" multiple onchange="procesarArchivoEnsamblador(event)">
            </div>
            <div class="flex flex-col shrink-0 mt-1">
                <span class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1 pl-1">Elementos agregados al lote:</span>
                <div id="lista-archivos-ensamblador" class="flex flex-col gap-2">
                    <div id="ensamblador-vacio" class="w-full py-6 text-center opacity-40 border border-dashed border-white/10 rounded-xl bg-black/20">
                        <span class="text-[9px] uppercase font-bold tracking-widest text-gray-500">Ningún recurso cargado</span>
                    </div>
                </div>
            </div>
        </div>
        <button id="btn-guardar-ensamble" onclick="confirmarEnsambleMod()" disabled class="w-full py-4 mt-2 rounded-xl bg-gray-800 text-gray-500 text-[12px] font-black tracking-widest uppercase transition-all shadow-lg flex items-center justify-center gap-2 pointer-events-none shrink-0 border border-transparent">
            <i class="fas fa-cloud-upload-alt text-lg"></i> Subir a Bóveda en PS4
        </button>
    </div>
</div>

<div id="modal-delete-mod-confirm" class="fixed inset-0 z-[970] bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border-2 border-red-500/50 bg-[#0a0202]/95 p-6 shadow-[0_0_50px_rgba(239,68,68,0.2)] flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center text-3xl mb-4"><i class="fas fa-trash-alt"></i></div>
        <h3 class="text-[16px] font-black tracking-widest text-red-400 uppercase mb-2">Destruir Mod</h3>
        <p id="del-mod-desc" class="text-[10px] text-gray-300 mb-6 px-2 leading-relaxed">...</p>
        <div class="flex flex-col gap-3 w-full">
            <button id="btn-real-delete-mod" class="w-full py-4 rounded-xl bg-red-600 text-[11px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_15px_rgba(239,68,68,0.4)]">Sí, Eliminar Permanentemente</button>
            <button onclick="cerrarDeleteModConfirm()" class="w-full py-3 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all">Cancelar</button>
        </div>
    </div>
</div>

<div id="modal-cargando-switch" class="fixed inset-0 z-[999] bg-black/95 backdrop-blur-sm hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="w-full max-w-sm rounded-[2rem] border-2 border-indigo-500/50 bg-[#0a0f1a] p-8 shadow-[0_0_80px_rgba(99,102,241,0.2)] flex flex-col items-center text-center">
        <div class="relative w-20 h-20 mb-6 flex items-center justify-center">
            <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-indigo-400 rounded-full border-t-transparent animate-spin"></div>
            <i id="switch-icon-cargando" class="fas fa-bolt text-2xl text-indigo-400 animate-pulse"></i>
        </div>
        <h3 id="switch-titulo-cargando" class="text-[16px] font-black tracking-widest text-white uppercase mb-2">Inyectando Mod</h3>
        <p class="text-[10px] text-gray-400 font-mono mb-6 leading-relaxed">
            Transfiriendo datos en la memoria de la consola. Dependiendo del peso, esto puede demorar varios segundos.<br>
            <b class="text-amber-400 mt-2 block">⚠️ NO CIERRES LA APP NI REINICIES EL JUEGO AÚN</b>
        </p>
        <div class="w-full bg-black rounded-full overflow-hidden border border-white/10 shadow-inner h-2">
            <div class="h-full bg-gradient-to-r from-indigo-600 to-purple-400 w-full animate-[pulse_1s_ease-in-out_infinite]"></div>
        </div>
        <span class="text-[8px] font-mono text-gray-500 uppercase tracking-widest mt-3" id="switch-file-count">Procesando archivos...</span>
    </div>
</div>

<div id="notif-ps5-mods" class="fixed top-4 left-4 right-4 bg-[#0a0f1a]/95 border-l-4 border-indigo-500 rounded-r-xl p-3 shadow-2xl z-[999] flex items-center gap-3 transition-all duration-300 transform -translate-y-32 opacity-0 pointer-events-none">
    <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-sm shrink-0" id="notif-icon-mods"><i class="fas fa-info"></i></div>
    <div class="flex flex-col overflow-hidden">
        <span class="text-[8px] font-mono tracking-widest uppercase text-indigo-400 font-black leading-none" id="notif-tag-mods">SISTEMA</span>
        <span class="text-[10.5px] text-gray-200 font-bold truncate mt-0.5" id="notif-msg-mods">Mensaje</span>
    </div>
</div>

<div id="modal-selector-juegos-mods" onclick="cerrarSelectorJuegosModsGlobal(event)" class="fixed inset-0 z-[950] bg-black/80 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300 p-0">
    <div id="modal-content-juegos-mods" onclick="event.stopPropagation()" class="w-full max-w-md bg-[#0d131f] rounded-t-[2.5rem] border-t border-indigo-500/20 p-6 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-15px_50px_rgba(0,0,0,0.9)] pb-10 max-h-[90vh]">
        <div class="w-12 h-1.5 bg-white/10 rounded-full mx-auto mb-6 shrink-0"></div>
        <h3 class="text-[12px] font-black tracking-widest text-indigo-400 uppercase mb-5 text-center shrink-0">Seleccionar Título</h3>

        <div class="relative w-full h-11 mb-5 shrink-0 bg-[#060913] border border-white/10 rounded-xl overflow-hidden focus-within:border-indigo-500/40 shadow-inner flex items-center px-4 transition-colors">
            <i class="fa-solid fa-magnifying-glass text-gray-600 text-[11px] mr-3"></i>
            <input type="text" id="buscador-juegos-mods" oninput="filtrarSelectorModsGlobal()" placeholder="Buscar juego o app..." class="w-full h-full bg-transparent text-[11px] font-bold text-white outline-none uppercase tracking-wider">
        </div>

        <div id="lista-juegos-para-mods" class="flex flex-col gap-2 overflow-y-auto custom-scrollbar pr-2 flex-1"></div>
    </div>
</div>

<script>
    let globalModJuegoActivo = null;
    let moduloModsAbierto = false; 

    document.addEventListener("DOMContentLoaded", () => {
        const capaMods = document.getElementById('layer-mods');
        if (capaMods) {
            const observadorMods = new MutationObserver(() => {
                if (capaMods.classList.contains('active')) {
                    if (!moduloModsAbierto) {
                        moduloModsAbierto = true;
                        globalModJuegoActivo = null;
                        localStorage.removeItem('mods_global_cusa');
                    }
                    inicializarEnrutadorMods();
                } else {
                    moduloModsAbierto = false;
                }
            });
            observadorMods.observe(capaMods, { attributes: true, attributeFilter: ['class'] });
        }
    });

    async function inicializarEnrutadorMods() {
        if (typeof listadoJuegos === 'undefined' || listadoJuegos.length === 0) {
            if (typeof levantarCacheLocalBiblioteca === 'function') await levantarCacheLocalBiblioteca();
        }

        const cusaActivo = localStorage.getItem('mods_global_cusa');
        if (cusaActivo && typeof listadoJuegos !== 'undefined') {
            globalModJuegoActivo = listadoJuegos.find(j => j.id === cusaActivo);
        } else {
            globalModJuegoActivo = null;
        }

        ['entorno-vacio-placeholder', 'entorno-minecraft-completo', 'entorno-afr-completo', 'entorno-nosoportado'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
        });

        if (!globalModJuegoActivo) {
            document.getElementById('mod-global-title').innerText = "Seleccionar Juego";
            document.getElementById('mod-global-cusa').innerText = "---";
            document.getElementById('mod-global-avatar').style.backgroundImage = 'none';
            document.getElementById('mod-global-blur').style.backgroundImage = 'none';
            document.getElementById('entorno-vacio-placeholder').classList.remove('hidden');
            document.getElementById('entorno-vacio-placeholder').classList.add('flex');
            return;
        }

        document.getElementById('mod-global-title').innerText = globalModJuegoActivo.nombre;
        document.getElementById('mod-global-cusa').innerText = globalModJuegoActivo.id;
        const freshUrl = globalModJuegoActivo.img.includes('?v=') ? globalModJuegoActivo.img : `${globalModJuegoActivo.img}?v=${new Date().getTime()}`;
        document.getElementById('mod-global-avatar').style.backgroundImage = `url('${freshUrl}')`;
        document.getElementById('mod-global-blur').style.backgroundImage = `url('${freshUrl}')`;

        const nombreGame = globalModJuegoActivo.nombre.toLowerCase();
        
        if (nombreGame.includes('minecraft')) {
            document.getElementById('entorno-minecraft-completo').classList.remove('hidden');
            document.getElementById('entorno-minecraft-completo').classList.add('flex');
            if (typeof escanearBovedaMods === 'function') escanearBovedaMods();
        } 
        else if (nombreGame.includes('resident evil')) {
            document.getElementById('entorno-afr-completo').classList.remove('hidden');
            document.getElementById('entorno-afr-completo').classList.add('flex');
            
            if (typeof cargarBovedaAFR_V3 === 'function') cargarBovedaAFR_V3();
            if (typeof verificarEstadoBackupAFR === 'function') verificarEstadoBackupAFR();
            if (typeof aplicarEstadoBotonesPlugins === 'function') aplicarEstadoBotonesPlugins();
        } 
        else {
            document.getElementById('entorno-nosoportado').classList.remove('hidden');
            document.getElementById('entorno-nosoportado').classList.add('flex');
        }
    }

    async function abrirSelectorJuegosModsGlobal() {
        if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
        if (typeof listadoJuegos === 'undefined' || listadoJuegos.length === 0) {
            if (typeof levantarCacheLocalBiblioteca === 'function') await levantarCacheLocalBiblioteca();
        }
        document.getElementById('buscador-juegos-mods').value = '';
        renderizarListaSelectorMods([...(listadoJuegos || [])].sort((a, b) => a.nombre.localeCompare(b.nombre)));

        const modal = document.getElementById('modal-selector-juegos-mods');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            document.getElementById('modal-content-juegos-mods').classList.remove('translate-y-full');
        }, 10);
    }

    function cerrarSelectorJuegosModsGlobal(e) {
        if (e && e.target !== document.getElementById('modal-selector-juegos-mods')) return;
        document.getElementById('modal-content-juegos-mods').classList.add('translate-y-full');
        const modal = document.getElementById('modal-selector-juegos-mods');
        modal.classList.remove('opacity-100');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function renderizarListaSelectorMods(juegos) {
        const container = document.getElementById('lista-juegos-para-mods');
        container.innerHTML = '';

        juegos.forEach(jg => {
            const isActive = globalModJuegoActivo && globalModJuegoActivo.id === jg.id;
            const activeClasses = isActive ? 'bg-indigo-500/10 border-indigo-500/30' : 'bg-[#111827] border-white/5 hover:bg-white/5 hover:border-indigo-500/20';
            const textClasses = isActive ? 'text-indigo-400' : 'text-gray-200';

            const div = document.createElement('div');
            div.className = `flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all active:scale-[0.98] border ${activeClasses}`;
            div.onclick = () => {
                if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
                localStorage.setItem('mods_global_cusa', jg.id);
                cerrarSelectorJuegosModsGlobal();
                inicializarEnrutadorMods();
            };

            div.innerHTML = `
                <div class="w-10 h-10 rounded-lg bg-cover bg-center border border-white/10 shrink-0" style="background-image: url('${jg.img}')"></div>
                <div class="flex flex-col flex-1 overflow-hidden">
                    <span class="text-[11px] font-black uppercase tracking-widest ${textClasses} truncate">${jg.nombre}</span>
                    <span class="text-[9px] font-mono text-gray-500 mt-0.5">${jg.id}</span>
                </div>
                ${isActive ? '<i class="fa-solid fa-check-circle text-indigo-400 text-lg shrink-0"></i>' : ''}
            `;
            container.appendChild(div);
        });
    }

    function filtrarSelectorModsGlobal() {
        const query = document.getElementById('buscador-juegos-mods').value.toLowerCase().trim();
        const filtrados = (listadoJuegos || []).filter(j => 
            j.nombre.toLowerCase().includes(query) || j.id.toLowerCase().includes(query)
        ).sort((a, b) => a.nombre.localeCompare(b.nombre));
        renderizarListaSelectorMods(filtrados);
    }
</script>
