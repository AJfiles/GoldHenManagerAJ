<?php
/**
 * ====================================================================
 * GOLD HEN SUITE PRO 🚀 (PS5/PS4) - COMPONENTE CAPA BIBLIOTECA
 * DEVELOPED By SeBaS - RUTA: modulos/biblioteca.php
 * ====================================================================
 */
?>
<style>
    .pill-modern { border: 1px solid rgba(255, 255, 255, 0.05); background: rgba(255, 255, 255, 0.02); color: #9ca3af; transition: all 0.25s ease; }
    .pill-modern.active { border-color: #22d3ee; background: linear-gradient(135deg, rgba(34,211,238,0.1) 0%, rgba(147,51,234,0.1) 100%); color: #22d3ee; box-shadow: 0 0 15px rgba(34,211,238,0.15); }

    .grid-card-modern { border: 1px solid rgba(255, 255, 255, 0.03); background: #0b0f19; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); aspect-ratio: 1 / 1.15; }
    .grid-card-modern:active { transform: scale(0.96); border-color: #22d3ee/30; }

    /* 🪐 MOTOR 3D FLUIDO Y ADAPTABLE */
    .carousel-viewport-master {
        position: relative;
        width: 100%;
        max-width: 800px;
        flex: 1; 
        min-height: 240px; 
        display: flex;
        justify-content: center;
        align-items: center;
        perspective: 1200px;
        -webkit-perspective: 1200px;
        overflow: hidden;
        touch-action: none; 
    }

    .glow-background-master {
        position: absolute;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle, rgba(0, 162, 255, 0.6) 0%, rgba(0, 114, 230, 0.2) 40%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        z-index: 1;
        pointer-events: none;
        mix-blend-mode: screen;
        filter: blur(25px);
        opacity: 0.85;
        transition: background 0.5s ease;
    }

    .carousel-track-master {
        position: relative;
        width: 145px;
        height: 200px;
        transform-style: preserve-3d;
        -webkit-transform-style: preserve-3d;
        z-index: 2;
    }

    .ps4-box-case {
        position: absolute;
        width: 145px;
        height: 200px;
        background-color: #12151e;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.6s ease, box-shadow 0.5s ease, border 0.5s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transform-style: preserve-3d;
        -webkit-transform-style: preserve-3d;
        will-change: transform, opacity;
        -webkit-box-reflect: below 2px linear-gradient(transparent, transparent 65%, rgba(255,255,255,0.25));
    }

    .ps4-box-case.active {
        border: 1px solid rgba(0, 210, 255, 0.5);
        box-shadow: 0 0 35px rgba(0, 162, 255, 0.8), 0 15px 40px rgba(0, 0, 0, 0.7);
    }

    .ps4-top-ribbon {
        background: linear-gradient(180deg, #0056c6 0%, #003780 100%);
        height: 22px;
        display: flex;
        align-items: center;
        padding: 0 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 10;
        position: relative;
    }
    .ps4-ribbon-text { font-size: 8px; font-weight: 800; font-family: 'Outfit'; text-transform: uppercase; margin-left: 4px; letter-spacing: 1px; color: white; }
    
    .ps4-img-art { flex: 1; width: 100%; object-fit: cover; background-color: #0f121a; position: relative; }

    .sinc-overlay { background: rgba(3, 5, 11, 0.9); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(34, 211, 238, 0.3); border-radius: 4px; }
</style>

<div id="layer-biblioteca" class="app-layer flex flex-col p-4 h-screen w-full overflow-hidden bg-[#060913]">
    
    <div class="w-full flex flex-col gap-3 shrink-0 pt-1 z-30">
        <div class="flex justify-between items-center w-full">
            <h2 class="text-2xl font-black tracking-tighter uppercase text-white">Biblioteca</h2>
            <div class="flex items-center gap-2">
                <button onclick="abrirGaleriaGlobal()" class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center active:scale-95 transition-all">
                    <i class="fa-solid fa-images text-emerald-400 text-xs"></i>
                </button>
                <button onclick="conmutarModoVista()" class="w-8 h-8 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-95 transition-all">
                    <i class="fa-solid fa-cube text-cyan-400 text-xs" id="icono-vista"></i>
                </button>
                <button onclick="volverAlLauncher()" class="w-8 h-8 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-center active:scale-95 transition-all">
                    <i class="fa-solid fa-house text-gray-400 text-xs"></i>
                </button>
            </div>
        </div>

        <div class="relative w-full h-10 bg-black/40 border border-white/5 rounded-xl overflow-hidden focus-within:border-cyan-500/40 shadow-inner flex items-center">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 text-gray-600 text-[11px]"></i>
            <input type="text" id="engine-search" oninput="ejecutarFiltroGlobal()" placeholder="Buscar por título o CUSA..." class="w-full h-full bg-transparent pl-9 pr-12 text-[12px] font-bold text-white outline-none">
            <button onclick="forzarSincronizacionManual()" class="absolute right-2 w-7 h-7 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 active:scale-95 transition-all">
                <i class="fa-solid fa-rotate-right text-[10px]"></i>
            </button>
        </div>

        <div class="flex items-center justify-between w-full gap-2 mt-1">
            <button onclick="abrirModalFiltro()" class="flex-1 h-9 bg-white/[0.02] border border-white/5 rounded-xl flex items-center justify-between px-3 active:scale-95 transition-all">
                <div class="flex items-center gap-2 overflow-hidden">
                    <i class="fa-solid fa-layer-group text-cyan-400 text-[10px] shrink-0"></i>
                    <span id="label-filtro-actual" class="text-[9px] font-black uppercase tracking-widest text-white truncate">TODOS</span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-500 text-[9px] shrink-0 ml-1"></i>
            </button>
            
            <button onclick="abrirModalOrden()" class="flex-1 h-9 bg-white/[0.02] border border-white/5 rounded-xl flex items-center justify-between px-3 active:scale-95 transition-all">
                <div class="flex items-center gap-2 overflow-hidden">
                    <i id="icono-orden-actual" class="fa-solid fa-arrow-down-a-z text-emerald-400 text-[10px] shrink-0"></i>
                    <span id="label-orden-actual" class="text-[9px] font-black uppercase tracking-widest text-white truncate">Nombre A-Z</span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-500 text-[9px] shrink-0 ml-1"></i>
            </button>
        </div>
        
        <div class="flex items-center justify-start w-full px-1">
            <span class="text-gray-500 text-[8px] font-black uppercase tracking-widest">Estado: <span id="badge-total-txt" class="text-cyan-400">0 Títulos</span></span>
        </div>
    </div>

    <div class="flex-grow w-full relative overflow-hidden mt-1 flex flex-col">
        
        <div id="container-view-grid" class="absolute inset-0 overflow-y-auto hide-scrollbar w-full h-full pb-28 hidden">
            <div id="dom-grid-target" class="grid grid-cols-3 gap-3 w-full"></div>
        </div>

        <div id="container-view-3d" class="absolute inset-0 w-full h-full flex flex-col justify-start pt-6">
            
            <div class="carousel-viewport-master shrink-0" id="swipe-touch-zone">
                <div class="glow-background-master" id="dom-glow-target"></div>
                <div id="dom-3d-target" class="carousel-track-master"></div>
            </div>
            
            <div class="w-full shrink-0 flex flex-col items-center mt-6 mb-20 z-20">
                <h3 id="text-title-3d" class="text-[16px] font-black uppercase text-white truncate w-full px-4 text-center tracking-widest drop-shadow-md">---</h3>
                
                <div class="flex gap-2 mt-1.5">
                    <span id="text-cusa-3d" class="px-2 py-0.5 rounded border border-white/5 bg-[#0a0e14] text-[9px] font-mono font-bold text-cyan-400 tracking-widest">---</span>
                    <span id="text-version-3d" class="px-2 py-0.5 rounded border border-white/5 bg-[#0a0e14] text-[9px] font-mono font-bold text-gray-400 tracking-widest">---</span>
                </div>

                <div class="w-[80%] h-px bg-white/5 my-4"></div>

                <div class="grid grid-cols-4 gap-2 w-full max-w-md px-2">
                    <button onclick="ejecutarAccionRapidaJuego('saves')" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-[#0a0e14] border border-white/5 active:scale-95 transition-all shadow-lg hover:border-cyan-500/30">
                        <i class="fa-solid fa-floppy-disk text-cyan-400 text-lg mb-1.5"></i>
                        <span class="text-[7.5px] font-black tracking-widest text-gray-400 uppercase">Saves</span>
                    </button>
                    
                    <button onclick="ejecutarAccionRapidaJuego('galeria')" class="relative flex flex-col items-center justify-center p-3 rounded-2xl bg-[#0a0e14] border border-white/5 active:scale-95 transition-all shadow-lg hover:border-emerald-500/30">
                        <i class="fa-solid fa-images text-emerald-400 text-lg mb-1.5"></i>
                        <span class="text-[7.5px] font-black tracking-widest text-gray-400 uppercase">Galería</span>
                        <span id="sheet-count-capturas-badge-3d" class="absolute top-1 right-1 bg-emerald-500 text-black text-[7px] font-black px-1 rounded-md hidden">--</span>
                    </button>
                    
                    <button onclick="ejecutarAccionRapidaJuego('editar_portada')" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-[#0a0e14] border border-white/5 active:scale-95 transition-all shadow-lg hover:border-purple-500/30">
                        <i class="fa-solid fa-wand-magic-sparkles text-purple-400 text-lg mb-1.5"></i>
                        <span class="text-[7.5px] font-black tracking-widest text-gray-400 uppercase">Portada</span>
                    </button>
                    
                    <button onclick="ejecutarAccionRapidaJuego('dlcs')" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-[#0a0e14] border border-white/5 active:scale-95 transition-all shadow-lg hover:border-blue-500/30">
                        <i class="fa-solid fa-puzzle-piece text-blue-400 text-lg mb-1.5"></i>
                        <span class="text-[7.5px] font-black tracking-widest text-gray-400 uppercase">DLCs</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-sincronizacion-progreso" class="fixed inset-0 z-[500] sinc-overlay hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
    <div class="glass-premium w-full max-w-sm rounded-[2.5rem] p-6 shadow-2xl flex flex-col items-center border border-white/10 bg-[#070b14]/90 modal-pop">
        <div class="w-12 h-12 rounded-full border border-cyan-500/20 bg-cyan-500/5 flex items-center justify-center text-cyan-400 text-lg mb-3"><i class="fa-brands fa-playstation animate-pulse"></i></div>
        <h3 id="sinc-modal-title" class="text-xs font-black tracking-widest text-cyan-400 uppercase">Sincronizando</h3>
        <p id="sinc-modal-text" class="text-[9px] font-mono text-gray-400 uppercase tracking-wider text-center mt-1 truncate max-w-xs">Estableciendo Socket...</p>
        <div class="w-full bg-white/5 border border-white/5 h-2 rounded-full overflow-hidden mt-4 relative"><div id="sinc-modal-bar" class="absolute left-0 top-0 h-full bg-gradient-to-r from-cyan-500 to-purple-500 rounded-full transition-all duration-200" style="width: 0%;"></div></div>
        <div class="flex justify-between items-center w-full mt-2 text-[8px] font-black uppercase tracking-widest text-gray-500"><span id="sinc-modal-percentage">0%</span><span id="sinc-modal-bytes">0 / 0 Analizados</span></div>
        <button onclick="abortarSincronizacionDesdeBoton()" class="w-full py-2 rounded-xl border border-red-500/20 bg-red-500/10 text-red-400 font-black tracking-widest text-[9px] uppercase mt-4 active:scale-95 transition-all">Cancelar</button>
    </div>
</div>

<div id="modal-nueva-categoria" class="fixed inset-0 z-[800] sinc-overlay hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
    <div class="glass-premium w-full max-w-xs rounded-[2rem] p-5 shadow-2xl flex flex-col bg-[#070b14]/90 modal-pop">
        <h3 class="text-xs font-black tracking-widest text-cyan-400 uppercase mb-3">Nueva Categoría</h3>
        <div class="w-full h-10 bg-black/60 border border-white/10 rounded-xl overflow-hidden shadow-inner flex items-center px-3 focus-within:border-cyan-500/40"><input type="text" id="input-nueva-cat" placeholder="EJ: FAVORITOS..." class="w-full h-full bg-transparent text-[11px] font-black tracking-widest uppercase text-white outline-none"></div>
        <div class="flex gap-2 w-full mt-4 text-[9px] font-black tracking-widest uppercase"><button onclick="cerrarModalNuevaCategoria()" class="flex-1 py-2.5 rounded-xl border border-white/5 bg-white/5 text-gray-400">Cerrar</button><button onclick="crearCategoriaProcesar()" class="flex-1 py-2.5 rounded-xl bg-cyan-600 text-white shadow-lg">Crear</button></div>
    </div>
</div>

<div id="modal-filtro-categorias" onclick="cerrarModalFiltro()" class="fixed inset-0 z-[600] bg-black/60 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300">
    <div id="modal-filtro-content" onclick="event.stopPropagation()" class="w-full max-w-[400px] bg-[#0d131f] rounded-t-[2rem] border-t border-white/10 p-6 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-10px_40px_rgba(0,0,0,0.9)] pb-8">
        <div class="w-12 h-1.5 bg-white/20 rounded-full mx-auto mb-5"></div>
        <h3 class="text-[11px] font-black tracking-widest text-gray-400 uppercase mb-4 text-center">Filtrar por Categoría</h3>
        <div id="lista-filtros-custom" class="flex flex-col gap-2 max-h-[40vh] overflow-y-auto custom-scrollbar pr-2"></div>
        <button onclick="abrirModalNuevaCategoria()" class="mt-4 w-full h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center gap-2 text-cyan-400 text-[10px] font-black uppercase tracking-widest active:scale-[0.98] transition-all hover:bg-cyan-500/20"><i class="fa-solid fa-plus"></i> Añadir Categoría</button>
    </div>
</div>

<div id="modal-orden-custom" onclick="cerrarModalOrden()" class="fixed inset-0 z-[600] bg-black/60 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300">
    <div id="modal-orden-content" onclick="event.stopPropagation()" class="w-full max-w-[400px] bg-[#0d131f] rounded-t-[2rem] border-t border-white/10 p-6 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-10px_40px_rgba(0,0,0,0.9)] pb-8">
        <div class="w-12 h-1.5 bg-white/20 rounded-full mx-auto mb-5"></div>
        <h3 class="text-[11px] font-black tracking-widest text-gray-400 uppercase mb-4 text-center">Criterio de Orden</h3>
        <div id="lista-orden-custom" class="flex flex-col gap-2 pr-2"></div>
    </div>
</div>

<div id="modal-confirmar-eliminar" onclick="cerrarModalConfirmarEliminar()" class="fixed inset-0 z-[999] bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
    <div onclick="event.stopPropagation()" class="glass-premium w-full max-w-xs rounded-[2rem] p-6 shadow-2xl flex flex-col items-center bg-[#070b14]/95 border border-red-500/20 transform scale-95 transition-transform duration-300" id="modal-confirmar-content">
        <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center text-red-400 text-xl mb-4 border border-red-500/20"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 class="text-[13px] font-black tracking-widest text-white uppercase text-center mb-2">Eliminar Categoría</h3>
        <p id="texto-confirmar-eliminar" class="text-[10px] text-gray-400 text-center mb-6 leading-relaxed px-2">¿Seguro?</p>
        <div class="flex gap-3 w-full">
            <button onclick="cerrarModalConfirmarEliminar()" class="flex-1 py-3 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all hover:bg-white/10">Cancelar</button>
            <button onclick="ejecutarEliminacionCategoriaPendiente()" class="flex-1 py-3 rounded-xl bg-red-600/80 border border-red-500/50 text-[10px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_15px_rgba(220,38,38,0.4)]">Eliminar</button>
        </div>
    </div>
</div>

<div id="sheet-detalles-juego" onclick="cerrarBottomSheet(event)" class="fixed inset-0 z-[500] bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div id="sheet-content-card" onclick="event.stopPropagation()" class="relative w-full max-w-[400px] rounded-[2rem] border border-white/10 overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.9)] transform scale-95 transition-transform duration-300 flex flex-col bg-[#050810]">
        
        <div id="panel-bg-blur" class="absolute top-0 left-0 right-0 h-[220px] bg-cover bg-center opacity-30 blur-md pointer-events-none transition-all duration-500"></div>
        <div class="absolute top-0 left-0 right-0 h-[220px] bg-gradient-to-b from-[#050810]/10 via-[#050810]/70 to-[#050810] pointer-events-none"></div>

        <div class="relative z-10 flex-1 overflow-y-auto px-6 py-7 hide-scrollbar flex flex-col gap-6">
            <div class="flex gap-5 items-center">
                <div id="panel-avatar-art" class="w-[120px] h-[160px] rounded-2xl border border-white/10 bg-cover bg-center shrink-0 shadow-[0_15px_30px_rgba(0,0,0,0.6)]"></div>
                <div class="flex flex-col flex-1 min-w-0">
                    <h2 id="panel-game-title" class="text-[18px] font-black uppercase text-white leading-tight drop-shadow-md">---</h2>
                    <span id="panel-game-cusa" class="text-[13px] font-mono font-black text-cyan-400 tracking-widest mt-1.5 uppercase">---</span>
                    <p id="panel-game-version" class="text-[12px] font-medium text-gray-300 uppercase tracking-widest mt-1">---</p>
                    <div id="panel-game-size" class="text-[12px] font-medium text-gray-300 uppercase tracking-widest mt-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-server text-emerald-400"></i> ---
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col gap-2 mt-1">
                <span class="text-[9px] font-black tracking-widest text-gray-500 uppercase">Ubicado en categoría:</span>
                <button onclick="abrirSelectorCategoriasCustom()" class="w-full h-12 bg-[#0b121c]/80 border border-white/10 rounded-xl flex items-center justify-between px-4 focus-within:border-cyan-500/30 transition-colors shadow-lg active:scale-[0.98]">
                    <span id="label-categoria-actual" class="text-[12px] font-black uppercase tracking-widest text-cyan-400">---</span>
                    <i class="fa-solid fa-chevron-down text-gray-500 text-[11px]"></i>
                </button>
            </div>

            <div class="flex flex-col gap-3 w-full">
                <button onclick="ejecutarAccionRapidaJuego('saves')" class="group flex items-center gap-4 p-4 rounded-2xl bg-[#0b121c]/80 border border-cyan-500/20 hover:bg-cyan-500/10 active:scale-[0.98] transition-all relative overflow-hidden shadow-lg text-left backdrop-blur-sm">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-cyan-500/90 shadow-[0_0_15px_rgba(34,211,238,0.5)]"></div>
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 flex items-center justify-center border border-cyan-500/20 shrink-0">
                        <i class="fa-solid fa-floppy-disk text-cyan-400 text-lg"></i>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-[12px] font-black uppercase tracking-widest text-gray-100 group-active:text-cyan-300 transition-colors">Backup de Partidas</span>
                        <span class="text-[10px] text-gray-400 mt-1 leading-tight">Respaldar y restaurar tus partidas.</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-cyan-500/50 text-xs mr-1 group-hover:translate-x-1 transition-transform"></i>
                </button>

                <button onclick="ejecutarAccionRapidaJuego('galeria')" class="group flex items-center gap-4 p-4 rounded-2xl bg-[#0b1414]/80 border border-emerald-500/20 hover:bg-emerald-500/10 active:scale-[0.98] transition-all relative overflow-hidden shadow-lg text-left backdrop-blur-sm">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500/90 shadow-[0_0_15px_rgba(16,185,129,0.5)]"></div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20 shrink-0 relative">
                        <i class="fa-solid fa-images text-emerald-400 text-lg"></i>
                        <span id="sheet-count-capturas-badge" class="absolute -top-1 -right-1 bg-emerald-500 text-black text-[8px] font-black px-1.5 py-0.5 rounded-md hidden">--</span>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-[12px] font-black uppercase tracking-widest text-gray-100 group-active:text-emerald-300 transition-colors">Galería de Capturas</span>
                        <span class="text-[10px] text-gray-400 mt-1 leading-tight">Ver y administrar capturas y videos.</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-emerald-500/50 text-xs mr-1 group-hover:translate-x-1 transition-transform"></i>
                </button>

                <button onclick="ejecutarAccionRapidaJuego('editar_portada')" class="group flex items-center gap-4 p-4 rounded-2xl bg-[#110b1a]/80 border border-purple-500/20 hover:bg-purple-500/10 active:scale-[0.98] transition-all relative overflow-hidden shadow-lg text-left backdrop-blur-sm">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-purple-500/90 shadow-[0_0_15px_rgba(168,85,247,0.5)]"></div>
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 flex items-center justify-center border border-purple-500/20 shrink-0">
                        <i class="fa-solid fa-wand-magic-sparkles text-purple-400 text-lg"></i>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-[12px] font-black uppercase tracking-widest text-gray-100 group-active:text-purple-300 transition-colors">Editar Portada</span>
                        <span class="text-[10px] text-gray-400 mt-1 leading-tight">Ir a la sección Modding.</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-purple-500/50 text-xs mr-1 group-hover:translate-x-1 transition-transform"></i>
                </button>

                <button onclick="ejecutarAccionRapidaJuego('dlcs')" class="group flex items-center gap-4 p-4 rounded-2xl bg-[#0b101f]/80 border border-blue-500/20 hover:bg-blue-500/10 active:scale-[0.98] transition-all relative overflow-hidden shadow-lg text-left backdrop-blur-sm">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500/90 shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20 shrink-0">
                        <i class="fa-solid fa-puzzle-piece text-blue-400 text-lg"></i>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-[12px] font-black uppercase tracking-widest text-gray-100 group-active:text-blue-300 transition-colors">Gestionar DLCs / Updates</span>
                        <span class="text-[10px] text-gray-400 mt-1 leading-tight">Administrar contenido adicional.</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-blue-500/50 text-xs mr-1 group-hover:translate-x-1 transition-transform"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="modal-selector-categorias-custom" onclick="cerrarSelectorCategoriasCustom(event)" class="fixed inset-0 z-[700] bg-black/60 backdrop-blur-sm hidden flex items-end justify-center opacity-0 transition-opacity duration-300">
    <div id="modal-selector-categorias-content" onclick="event.stopPropagation()" class="w-full max-w-[400px] bg-[#0d131f] rounded-t-[2rem] border-t border-white/10 p-6 transform translate-y-full transition-transform duration-300 flex flex-col shadow-[0_-10px_40px_rgba(0,0,0,0.9)] pb-8">
        <div class="w-12 h-1.5 bg-white/20 rounded-full mx-auto mb-5"></div>
        <h3 class="text-[11px] font-black tracking-widest text-gray-400 uppercase mb-4 text-center">Mover a Categoría</h3>
        <div id="lista-categorias-custom-game" class="flex flex-col gap-2 max-h-[40vh] overflow-y-auto custom-scrollbar pr-2"></div>
    </div>
</div>

<div id="modal-galeria-juego" class="fixed inset-0 z-[800] bg-[#050810] hidden flex-col opacity-0 transition-opacity duration-300 transform translate-y-full">
    <div class="w-full flex items-center justify-between p-4 bg-[#0a0e14]/90 backdrop-blur-md border-b border-white/5 z-20 shrink-0 shadow-lg">
        <div class="flex items-center gap-3">
            <button onclick="cerrarGaleriaJuego()" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-300 active:scale-90 transition-all hover:bg-white/10"><i class="fa-solid fa-arrow-left"></i></button>
            <div class="flex flex-col">
                <h3 id="galeria-title" class="text-[13px] font-black uppercase text-white tracking-widest truncate max-w-[200px]">---</h3>
                <span id="galeria-count" class="text-[9px] font-mono text-emerald-400">0 Capturas</span>
            </div>
        </div>
        <button onclick="forzarRecargaGaleria()" class="w-10 h-10 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 active:rotate-180 transition-all"><i class="fa-solid fa-rotate-right text-xs"></i></button>
    </div>
    <div id="galeria-grid-container" class="flex-1 w-full h-full overflow-y-auto p-3 pb-24 hide-scrollbar touch-pan-y" style="-webkit-overflow-scrolling: touch;">
        <div id="galeria-loader" class="w-full h-full flex flex-col items-center justify-center text-emerald-500 opacity-60">
            <i class="fa-solid fa-satellite-dish animate-pulse text-4xl mb-3"></i>
            <p class="text-[10px] font-mono tracking-widest uppercase">Descargando Caché...</p>
        </div>
        <div id="galeria-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-2 hidden"></div>
    </div>
</div>

<div id="lightbox-visor" onclick="cerrarLightbox()" class="fixed inset-0 z-[900] bg-black/95 backdrop-blur-xl hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="absolute top-6 right-6 z-50 flex items-center gap-3">
        <button onclick="event.stopPropagation(); compartirImagenLightbox();" class="w-12 h-12 rounded-full bg-cyan-500/20 flex items-center justify-center text-cyan-400 backdrop-blur-md active:scale-90 transition-all border border-cyan-500/30 shadow-lg"><i class="fa-solid fa-share-nodes text-lg"></i></button>
        <button onclick="cerrarLightbox()" class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center text-white backdrop-blur-md active:scale-90 transition-all border border-white/20 shadow-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
    </div>
    <div class="w-full h-full flex items-center justify-center p-2">
        <img id="lightbox-img" src="" class="max-w-full max-h-full object-contain rounded-lg shadow-[0_0_50px_rgba(0,0,0,0.8)] transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
    </div>
    <div class="absolute bottom-6 w-full text-center px-4 pointer-events-none">
        <p id="lightbox-name" class="text-[10px] font-mono text-gray-400 tracking-widest bg-black/50 inline-block px-3 py-1 rounded-md backdrop-blur-sm border border-white/5"></p>
    </div>
</div>

<div id="modal-dlc-update" onclick="cerrarModalDLC()" class="fixed inset-0 z-[800] bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div id="modal-dlc-content" onclick="event.stopPropagation()" class="relative w-full max-w-[400px] rounded-[2rem] border border-white/10 overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.9)] transform scale-95 transition-transform duration-300 flex flex-col bg-[#050810]">
        
        <div id="dlc-bg-blur" class="absolute top-0 left-0 right-0 h-[150px] bg-cover bg-center opacity-30 blur-md pointer-events-none transition-all duration-500"></div>
        <div class="absolute top-0 left-0 right-0 h-[150px] bg-gradient-to-b from-[#050810]/10 via-[#050810]/80 to-[#050810] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="flex flex-col">
                    <h3 class="text-[10px] font-black tracking-widest text-blue-400 uppercase mb-1">Gestor de Contenido</h3>
                    <h2 id="dlc-game-title" class="text-[16px] font-black uppercase text-white leading-tight drop-shadow-md truncate max-w-[250px]">---</h2>
                    <span id="dlc-game-cusa" class="text-[10px] font-mono font-black text-gray-400 tracking-widest mt-1 uppercase">---</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="forzarRecargaDLCs()" class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 backdrop-blur-md active:rotate-180 transition-all border border-blue-500/20 shadow-lg shrink-0"><i class="fa-solid fa-rotate-right text-xs"></i></button>
                    <button onclick="cerrarModalDLC()" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white backdrop-blur-md active:scale-90 transition-all border border-white/20 shadow-lg shrink-0"><i class="fa-solid fa-xmark text-sm"></i></button>
                </div>
            </div>

            <div id="dlc-loader" class="w-full py-10 flex flex-col items-center justify-center text-blue-500 opacity-60">
                <i class="fa-solid fa-satellite-dish animate-pulse text-3xl mb-3"></i>
                <p class="text-[10px] font-mono tracking-widest uppercase">Calculando pesos...</p>
            </div>

            <div id="dlc-data-container" class="hidden flex flex-col gap-5 overflow-y-auto custom-scrollbar max-h-[50vh] pr-2">
                <div class="flex flex-col gap-2">
                    <span class="text-[9px] font-black tracking-widest text-gray-500 uppercase">Estado del Update:</span>
                    <div id="dlc-update-box" class="flex items-center gap-4 p-4 rounded-2xl bg-[#0b121c]/80 border border-white/5 shadow-lg"></div>
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <span class="text-[9px] font-black tracking-widest text-gray-500 uppercase">DLCs Instalados:</span>
                        <span id="dlc-count-badge" class="text-[9px] font-black tracking-widest text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-md">0</span>
                    </div>
                    <div id="dlc-list-box" class="flex flex-col gap-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-confirmar-eliminar-contenido" onclick="cerrarModalEliminarContenido()" class="fixed inset-0 z-[999] bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
    <div onclick="event.stopPropagation()" class="glass-premium w-full max-w-xs rounded-[2rem] p-6 shadow-2xl flex flex-col items-center bg-[#070b14]/95 border border-red-500/30 transform scale-95 transition-transform duration-300" id="modal-content-eliminar-contenido">
        
        <div class="relative w-16 h-16 rounded-full bg-red-500/10 flex items-center justify-center text-red-500 text-2xl mb-4 border border-red-500/30 shadow-[0_0_20px_rgba(239,68,68,0.2)]">
            <div class="absolute inset-0 rounded-full border border-red-500/50 animate-ping opacity-20"></div>
            <i class="fa-solid fa-fire-flame-curved"></i>
        </div>
        
        <h3 class="text-[14px] font-black tracking-widest text-white uppercase text-center mb-1">Peligro Crítico</h3>
        <p class="text-[10px] text-red-400 font-mono tracking-widest uppercase text-center mb-3">Borrado de Consola</p>
        
        <p id="texto-confirmar-contenido" class="text-[11px] text-gray-400 text-center mb-6 leading-relaxed px-2">
            ¿Estás seguro de eliminar el archivo <br>
            <span id="nombre-contenido-eliminar" class="text-white font-black text-[10px] bg-white/10 px-2 py-1 rounded-md mt-2 inline-block border border-white/10">---</span><br>
            <span class="text-[8px] text-red-500/70 mt-2 block tracking-widest">Esta acción es irreversible por FTP.</span>
        </p>
        
        <div class="flex gap-3 w-full">
            <button onclick="cerrarModalEliminarContenido()" class="flex-1 py-3 rounded-xl border border-white/10 bg-white/5 text-[10px] font-black tracking-widest uppercase text-gray-300 active:scale-95 transition-all hover:bg-white/10">Cancelar</button>
            <button onclick="ejecutarEliminacionContenidoFTP()" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-red-600 to-red-800 border border-red-500/50 text-[10px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_20px_rgba(220,38,38,0.5)] flex items-center justify-center gap-2">
                <i id="icono-btn-eliminar" class="fa-solid fa-skull"></i> Borrar
            </button>
        </div>
    </div>
</div>
