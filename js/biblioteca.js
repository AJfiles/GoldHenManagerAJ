/**
 * ====================================================================
 * GOLDHEN MANAGER V2.1 🚀 - CONTROLADOR MAESTRO DE BIBLIOTECA
 * DEVELOPED By SeBaS - RUTA: js/biblioteca.js
 * ====================================================================
 */

let listadoJuegos = [];
let categoriasDinamicas = ['TODOS', 'JUEGOS', 'APPS'];
let vistaModo = 'grid'; 
let index3DActivo = 0;
let pillActiva = 'TODOS';
let criterioOrdenGlobal = 'nombre_az'; 
let juegoSeleccionadoLocal = null;

let startX = 0;
let currentX = 0;
let isDragging = false;
let bibliotecaAbortController = null;
let galeriaCacheData = [];
let urlImagenActualLightbox = "";
let nombreImagenActualLightbox = "";
let isGlobalGallery = false; 
let categoriaAEliminar = null;

function initBiblio() {
    const capaBib = document.getElementById('layer-biblioteca');
    if (capaBib) {
        const observadorCapa = new MutationObserver(() => {
            if (capaBib.classList.contains('active')) { 
                vistaModo = 'grid';
                const icono = document.getElementById('icono-vista');
                if (icono) icono.className = 'fa-solid fa-cube text-cyan-400';
                
                const target3D = document.getElementById('dom-3d-target');
                if (target3D) target3D.innerHTML = '';
                index3DActivo = 0;

                iniciarCargaInmediataYEscaneoSilencioso(); 
            }
        });
        observadorCapa.observe(capaBib, { attributes: true, attributeFilter: ['class'] });
        if (capaBib.classList.contains('active')) { 
            vistaModo = 'grid';
            const icono = document.getElementById('icono-vista');
            if (icono) icono.className = 'fa-solid fa-cube text-cyan-400';
            iniciarCargaInmediataYEscaneoSilencioso(); 
        }
    }
}
if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initBiblio); } 
else { initBiblio(); }

async function iniciarCargaInmediataYEscaneoSilencioso() {
    await levantarCacheLocalBiblioteca();
    ejecutarSincronizacionFantasmasFondo();
}

async function levantarCacheLocalBiblioteca() {
    try {
        let response = await fetch('api/library_biblioteca.php?action=get_cached_games');
        let json = await response.json();
        
        if (json && json.status === 'success' && json.data.length > 0) {
            listadoJuegos = json.data;
            let deFabrica = ['TODOS', 'JUEGOS', 'APPS'];
            categoriasDinamicas = [...deFabrica];
            listadoJuegos.forEach(j => {
                j.cover3d = 'user/portadas_custom/' + j.id + '.jpg';
                if (j.tipo && !categoriasDinamicas.includes(j.tipo)) { categoriasDinamicas.push(j.tipo); }
            });
            actualizarInterfazFiltros();
        } else {
            listadoJuegos = [];
        }
        recompilarTodo();
    } catch(e) {
        recompilarTodo();
    }
}

async function ejecutarSincronizacionFantasmasFondo() {
    if (bibliotecaAbortController) { bibliotecaAbortController.abort(); }
    bibliotecaAbortController = new AbortController();
    const { signal } = bibliotecaAbortController;

    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    const port = localStorage.getItem('sebas_port_libre') || '2121';

    try {
        let formDataScan = new FormData();
        formDataScan.append('action', 'scan');
        formDataScan.append('host_ip', ip);
        formDataScan.append('port', port);

        let responseScan = await fetch('api/library_biblioteca.php', { method: 'POST', body: formDataScan, signal: signal });
        let jsonScan = await responseScan.json();
        if (jsonScan.status !== 'success' || !jsonScan.games) return;

        const mapJuegosDetectados = jsonScan.games;
        let colaTrabajoCUSAs = Object.keys(mapJuegosDetectados);

        if (colaTrabajoCUSAs.length > 10) {
            listadoJuegos = listadoJuegos.filter(localG => {
                if (localG.id === 'APOLO0004') return true;
                return mapJuegosDetectados[localG.id] !== undefined;
            });
        }

        const CONCURRENCIA_MAXIMA = 3;
        let huboCambiosNuevos = false;

        while (colaTrabajoCUSAs.length > 0) {
            if (signal.aborted) break;
            let loteActual = colaTrabajoCUSAs.splice(0, CONCURRENCIA_MAXIMA);
            let promesasHilos = [];

            loteActual.forEach(cusa => {
                let fd = new FormData();
                fd.append('action', 'get_game_data');
                fd.append('host_ip', ip);
                fd.append('port', port);
                fd.append('cusa_id', cusa);
                fd.append('base_path', mapJuegosDetectados[cusa]);
                promesasHilos.push(fetch('api/library_biblioteca.php', { method: 'POST', body: fd, signal: signal }).then(r => r.json()).catch(() => null));
            });

            let respuestasLote = await Promise.all(promesasHilos);

            respuestasLote.forEach(res => {
                if (res && res.status === 'success' && res.game) {
                    let idx = listadoJuegos.findIndex(m => m.id === res.game.id);
                    if (idx === -1) {
                        res.game.cover3d = 'user/portadas_custom/' + res.game.id + '.jpg';
                        listadoJuegos.push(res.game);
                        huboCambiosNuevos = true;
                    } else if (listadoJuegos[idx].nombre !== res.game.nombre && res.game.nombre !== res.game.id) {
                        listadoJuegos[idx].nombre = res.game.nombre;
                        huboCambiosNuevos = true;
                    }
                }
            });
        }

        if (huboCambiosNuevos) {
            listadoJuegos.forEach(j => { if (j.tipo && !categoriasDinamicas.includes(j.tipo)) { categoriasDinamicas.push(j.tipo); } });
            actualizarInterfazFiltros();
            recompilarTodo();
        }
    } catch (errorGlobal) {}
}

async function forzarSincronizacionManual() {
    const modal = document.getElementById('modal-sincronizacion-progreso');
    const title = document.getElementById('sinc-modal-title');
    const text = document.getElementById('sinc-modal-text');
    const bar = document.getElementById('sinc-modal-bar');
    const pctLabel = document.getElementById('sinc-modal-percentage');
    const bytesLabel = document.getElementById('sinc-modal-bytes');

    if (modal) { modal.classList.remove('hidden'); setTimeout(() => { modal.classList.add('opacity-100'); }, 10); }
    if (title) title.innerText = "Sincronizando";
    if (bar) bar.style.width = "0%";
    if (pctLabel) pctLabel.innerText = "0%";
    if (bytesLabel) bytesLabel.innerText = "Iniciando escaneo...";

    if (bibliotecaAbortController) { bibliotecaAbortController.abort(); }
    bibliotecaAbortController = new AbortController();
    const { signal } = bibliotecaAbortController;

    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    const port = localStorage.getItem('sebas_port_libre') || '2121';

    try {
        let fdScan = new FormData();
        fdScan.append('action', 'scan');
        fdScan.append('host_ip', ip);
        fdScan.append('port', port);

        let resScan = await fetch('api/library_biblioteca.php', { method: 'POST', body: fdScan, signal: signal });
        let jsonScan = await resScan.json();
        if (jsonScan.status !== 'success' || !jsonScan.games) { closeSincronizacionModal(); return; }

        let mapJuegos = jsonScan.games;
        let colaCUSA = Object.keys(mapJuegos);
        const total = colaCUSA.length;

        if (total === 0) { closeSincronizacionModal(); listadoJuegos = []; recompilarTodo(); return; }

        let procesados = 0;
        listadoJuegos = listadoJuegos.filter(lg => lg.id === 'APOLO0004' || mapJuegos[lg.id] !== undefined);

        while (colaCUSA.length > 0) {
            if (signal.aborted) break;
            let lote = colaCUSA.splice(0, 3);
            let promesas = [];

            lote.forEach(cusa => {
                let f = new FormData();
                f.append('action', 'get_game_data');
                f.append('host_ip', ip);
                f.append('port', port);
                f.append('cusa_id', cusa);
                f.append('base_path', mapJuegos[cusa]);
                promesas.push(fetch('api/library_biblioteca.php', { method: 'POST', body: f, signal: signal }).then(r => r.json()).catch(() => null));
            });

            procesados += lote.length;
            if (title) title.innerText = `LEYENDO (${procesados}/${total})`;
            if (text) text.innerText = lote[lote.length - 1];

            let calcPct = (procesados / total) * 100;
            if (bar) bar.style.width = calcPct + '%';
            if (pctLabel) pctLabel.innerText = Math.round(calcPct) + '%';
            if (bytesLabel) bytesLabel.innerText = `${procesados} / ${total} Analizados`;

            let respuestasLote = await Promise.all(promesas);
            respuestasLote.forEach(res => {
                if (res && res.status === 'success' && res.game) {
                    let idx = listadoJuegos.findIndex(m => m.id === res.game.id);
                    if (idx === -1) {
                        res.game.cover3d = 'user/portadas_custom/' + res.game.id + '.jpg';
                        listadoJuegos.push(res.game);
                    } else if (listadoJuegos[idx].nombre !== res.game.nombre && res.game.nombre !== res.game.id) {
                        listadoJuegos[idx].nombre = res.game.nombre;
                    }
                }
            });
        }

        closeSincronizacionModal();
        listadoJuegos.forEach(j => { if (j.tipo && !categoriasDinamicas.includes(j.tipo)) { categoriasDinamicas.push(j.tipo); } });
        actualizarInterfazFiltros();
        recompilarTodo();
    } catch (e) {
        closeSincronizacionModal(); 
        levantarCacheLocalBiblioteca();
    }
}

function abortarSincronizacionDesdeBoton() { if (bibliotecaAbortController) { bibliotecaAbortController.abort(); } closeSincronizacionModal(); }
function closeSincronizacionModal() { const modal = document.getElementById('modal-sincronizacion-progreso'); if (modal) { modal.classList.remove('opacity-100'); setTimeout(()=>modal.classList.add('hidden'), 300); } }

function aplicarOrdenamientoInterno() {
    if (criterioOrdenGlobal === 'nombre_az') {
        listadoJuegos.sort((a, b) => a.nombre.localeCompare(b.nombre));
    } else if (criterioOrdenGlobal === 'nombre_za') {
        listadoJuegos.sort((a, b) => b.nombre.localeCompare(a.nombre));
    } else if (criterioOrdenGlobal === 'recientes') {
        listadoJuegos.sort((a, b) => b.id.localeCompare(a.id));
    } else if (criterioOrdenGlobal === 'tamano') {
        listadoJuegos.sort((a, b) => {
            let sizeA = a.realSizeBytes || 0;
            let sizeB = b.realSizeBytes || 0;
            if (sizeA === sizeB) return a.nombre.localeCompare(b.nombre);
            return sizeB - sizeA;
        });
    }
}

function recompilarTodo() {
    aplicarOrdenamientoInterno();
    
    const visibles = obtenerVisiblesFiltrados();
    const badgeText = document.getElementById('badge-total-txt');
    if (badgeText) badgeText.innerText = `${visibles.length} Títulos`;

    const gridBox = document.getElementById('container-view-grid');
    const box3D = document.getElementById('container-view-3d');

    if (vistaModo === 'grid') {
        if(gridBox) gridBox.classList.remove('hidden'); 
        if(box3D) box3D.classList.add('hidden');
        renderizarGridDOM(visibles);
    } else {
        if(gridBox) gridBox.classList.add('hidden'); 
        if(box3D) box3D.classList.remove('hidden');
        construirCoverflowDOMEstatico(visibles);
    }
}

function renderizarGridDOM(visibles) {
    const gridDom = document.getElementById('dom-grid-target');
    if (!gridDom) return;
    gridDom.innerHTML = "";
    
    if (visibles.length === 0) {
        gridDom.innerHTML = `<div class="col-span-full py-12 text-center opacity-40"><p class="text-[9px] uppercase font-black tracking-widest text-gray-400">Sin títulos encontrados</p></div>`;
        return;
    }
    
    visibles.forEach((jg) => {
        const divCard = document.createElement('div');
        divCard.className = "grid-card-modern rounded-[1.2rem] p-1.5 flex flex-col justify-between cursor-pointer shadow-lg animate-fade-in";
        divCard.onclick = () => { abrirOpcionesJuegoDirecto(jg.id); };
        divCard.innerHTML = `
            <div class="w-full h-[84%] rounded-[1rem] overflow-hidden border border-white/5 bg-cover bg-center" style="background-image: url('${jg.img}');"></div>
            <p class="text-[9px] font-bold text-center truncate pt-1 text-gray-300 px-0.5 uppercase tracking-wide">${jg.nombre}</p>
        `;
        gridDom.appendChild(divCard);
    });
}

function construirCoverflowDOMEstatico(visibles) {
    const target3D = document.getElementById('dom-3d-target');
    if (!target3D) return;

    if (visibles.length === 0) {
        target3D.innerHTML = `
            <div class="text-center mt-10">
                <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-500 mb-3"></i>
                <p class="text-[10px] text-gray-400 font-mono tracking-widest uppercase">SIN TÍTULOS EN ESTE FILTRO.</p>
            </div>`;
        juegoSeleccionadoLocal = null;
        return;
    }

    let html3D = "";
    visibles.forEach((jg) => {
        html3D += `
        <div class="ps4-box-case">
            <div class="ps4-top-ribbon">
                <i class="fa-brands fa-playstation text-white text-[10px]"></i>
                <span class="ps4-ribbon-text">PS4</span>
            </div>
            <img src="${jg.cover3d}" class="ps4-img-art" draggable="false" onerror="this.src='${jg.img}'">
        </div>`;
    });
    target3D.innerHTML = html3D;

    const tarjetas = target3D.querySelectorAll('.ps4-box-case');
    tarjetas.forEach((card, idx) => {
        card.onclick = () => {
            if (idx !== index3DActivo) { index3DActivo = idx; actualizarCoverflow3DStyles(); }
            else { abrirOpcionesDesde3D(); }
        };
    });

    configurarRastreadorSwipe();
    actualizarCoverflow3DStyles();
}

function actualizarCoverflow3DStyles() {
    const target3D = document.getElementById('dom-3d-target');
    const glowBg = document.getElementById('dom-glow-target');
    if (!target3D) return;

    const visibles = obtenerVisiblesFiltrados();
    const totalCards = visibles.length;

    if (totalCards === 0) return;
    if (index3DActivo >= totalCards) index3DActivo = totalCards - 1;
    if (index3DActivo < 0) index3DActivo = 0;

    const tarjetas = target3D.querySelectorAll('.ps4-box-case');
    
    tarjetas.forEach((card, i) => {
        let offset = i - index3DActivo;
        if (offset < -Math.floor(totalCards / 2)) { offset += totalCards; } 
        else if (offset > Math.floor(totalCards / 2)) { offset -= totalCards; }

        const absOffset = Math.abs(offset);
        card.classList.remove('active');

        if (absOffset === 0) {
            card.classList.add('active');
            card.style.transform = `translateX(0px) translateZ(100px) rotateY(0deg) scale(1.15)`;
            card.style.opacity = '1';
            card.style.zIndex = '100';
            card.style.pointerEvents = 'auto';
            
            const currentJg = visibles[i];
            juegoSeleccionadoLocal = currentJg; 
            
            const tTitle = document.getElementById('text-title-3d');
            const tCusa = document.getElementById('text-cusa-3d');
            const tVersion = document.getElementById('text-version-3d');

            if (tTitle) tTitle.innerText = currentJg.nombre;
            if (tCusa) tCusa.innerText = currentJg.id;
            if (tVersion) tVersion.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-gray-600"></i> LEYENDO...`;
            
            consultarPesoRealConsola(currentJg.id, currentJg.tipo, currentJg.version);
            actualizarConteoCapturasDock(currentJg.id, 'sheet-count-capturas-badge-3d');

            if (glowBg) glowBg.style.background = `radial-gradient(circle, rgba(0, 162, 255, 0.5) 0%, rgba(0, 114, 230, 0.15) 45%, rgba(0,0,0,0) 70%)`;
        } else if (offset > 0) {
            card.style.transform = `translateX(${145 + (offset - 1) * 35}px) translateZ(${-30 - (offset - 1) * 30}px) rotateY(-40deg) scale(0.85)`;
            card.style.opacity = absOffset > 2 ? '0' : '0.55';
            card.style.zIndex = `${50 - absOffset}`;
            card.style.pointerEvents = absOffset === 1 ? 'auto' : 'none';
        } else {
            card.style.transform = `translateX(${-145 + (offset + 1) * 35}px) translateZ(${-30 - (absOffset - 1) * 30}px) rotateY(40deg) scale(0.85)`;
            card.style.opacity = absOffset > 2 ? '0' : '0.55';
            card.style.zIndex = `${50 - absOffset}`;
            card.style.pointerEvents = absOffset === 1 ? 'auto' : 'none';
        }
    });
}

function configurarRastreadorSwipe() {
    const viewPort = document.getElementById('swipe-touch-zone');
    if (!viewPort) return;
    viewPort.replaceWith(viewPort.cloneNode(true));
    const newViewPort = document.getElementById('swipe-touch-zone');

    newViewPort.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; isDragging = true; }, { passive: true });
    newViewPort.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        currentX = e.touches[0].clientX;
        const diffX = startX - currentX;

        if (Math.abs(diffX) > 50) {
            const visibles = obtenerVisiblesFiltrados();
            if (visibles.length === 0) { isDragging = false; return; }
            if (diffX > 0) index3DActivo = (index3DActivo + 1) % visibles.length;
            else index3DActivo = (index3DActivo - 1 + visibles.length) % visibles.length;
            actualizarCoverflow3DStyles();
            isDragging = false; 
        }
    }, { passive: true });
    newViewPort.addEventListener('touchend', () => { isDragging = false; });
}

function obtenerVisiblesFiltrados() {
    const searchInput = document.getElementById('engine-search');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
    return listadoJuegos.filter(j => {
        return (j.nombre.toLowerCase().includes(query) || j.id.toLowerCase().includes(query)) && 
               (pillActiva === 'TODOS' || j.tipo === pillActiva);
    });
}

function actualizarInterfazFiltros() {
    const lbl = document.getElementById('label-filtro-actual');
    if (lbl) lbl.innerText = pillActiva;
}

function abrirModalFiltro() {
    const container = document.getElementById('lista-filtros-custom');
    if (!container) return;
    container.innerHTML = "";
    
    categoriasDinamicas.forEach(cat => {
        const isActive = (cat === pillActiva);
        const iconHtml = isActive 
            ? `<i class="fa-solid fa-circle-check text-cyan-400 text-lg"></i>` 
            : `<i class="fa-regular fa-circle text-gray-600 text-lg"></i>`;
            
        const divOption = document.createElement('div');
        divOption.className = `flex items-center justify-between p-3.5 rounded-xl cursor-pointer transition-all active:scale-[0.98] ${isActive ? 'bg-cyan-500/10 border border-cyan-500/30' : 'bg-[#111827] border border-white/5 hover:bg-white/5'}`;
        divOption.onclick = () => { seleccionarFiltro(cat); };
        
        let deleteBtnHtml = '';
        if (!['TODOS', 'JUEGOS', 'APPS'].includes(cat)) {
            deleteBtnHtml = `<button onclick="event.stopPropagation(); confirmarEliminarCategoria('${cat}');" class="w-7 h-7 rounded-full bg-red-500/10 text-red-400 flex items-center justify-center mr-3 hover:bg-red-500/20 active:scale-90 transition-all border border-red-500/20"><i class="fa-solid fa-xmark text-[10px]"></i></button>`;
        }

        divOption.innerHTML = `
            <div class="flex items-center flex-1 overflow-hidden">
                <span class="text-[12px] font-black uppercase tracking-widest ${isActive ? 'text-cyan-400' : 'text-gray-300'} truncate">${cat}</span>
            </div>
            <div class="flex items-center shrink-0">
                ${deleteBtnHtml}
                ${iconHtml}
            </div>
        `;
        container.appendChild(divOption);
    });

    const modal = document.getElementById('modal-filtro-categorias');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.add('opacity-100'); 
            document.getElementById('modal-filtro-content').classList.remove('translate-y-full'); 
        }, 10);
    }
}

function cerrarModalFiltro(e) {
    if (e && e.target !== document.getElementById('modal-filtro-categorias')) return;
    document.getElementById('modal-filtro-content').classList.add('translate-y-full');
    const modal = document.getElementById('modal-filtro-categorias');
    if (modal) { modal.classList.remove('opacity-100'); setTimeout(() => modal.classList.add('hidden'), 300); }
}

function seleccionarFiltro(tipo) {
    pillActiva = tipo;
    index3DActivo = 0;
    actualizarInterfazFiltros();
    cerrarModalFiltro();
    recompilarTodo();
}

function abrirModalOrden() {
    const container = document.getElementById('lista-orden-custom');
    if (!container) return;
    container.innerHTML = "";

    const opcionesOrden = [
        { id: 'nombre_az', label: 'Nombre A-Z', icon: 'fa-arrow-down-a-z' },
        { id: 'nombre_za', label: 'Nombre Z-A', icon: 'fa-arrow-up-z-a' },
        { id: 'recientes', label: 'Más Recientes', icon: 'fa-clock-rotate-left' },
        { id: 'tamano', label: 'Tamaño de Juego', icon: 'fa-hard-drive' }
    ];

    opcionesOrden.forEach(opt => {
        const isActive = (opt.id === criterioOrdenGlobal);
        const iconSelect = isActive 
            ? `<i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>` 
            : `<i class="fa-regular fa-circle text-gray-600 text-lg"></i>`;
            
        const divOption = document.createElement('div');
        divOption.className = `flex items-center justify-between p-4 rounded-xl cursor-pointer transition-all active:scale-[0.98] ${isActive ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-[#111827] border border-white/5 hover:bg-white/5'}`;
        divOption.onclick = () => { seleccionarOrden(opt.id, opt.label, opt.icon); };
        
        divOption.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fa-solid ${opt.icon} ${isActive ? 'text-emerald-400' : 'text-gray-500'}"></i>
                <span class="text-[12px] font-black uppercase tracking-widest ${isActive ? 'text-emerald-400' : 'text-gray-300'}">${opt.label}</span>
            </div>
            ${iconSelect}
        `;
        container.appendChild(divOption);
    });

    const modal = document.getElementById('modal-orden-custom');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.add('opacity-100'); 
            document.getElementById('modal-orden-content').classList.remove('translate-y-full'); 
        }, 10);
    }
}

function cerrarModalOrden(e) {
    if (e && e.target !== document.getElementById('modal-orden-custom')) return;
    document.getElementById('modal-orden-content').classList.add('translate-y-full');
    const modal = document.getElementById('modal-orden-custom');
    if (modal) { modal.classList.remove('opacity-100'); setTimeout(() => modal.classList.add('hidden'), 300); }
}

function seleccionarOrden(id, label, iconClass) {
    criterioOrdenGlobal = id;
    document.getElementById('label-orden-actual').innerText = label;
    document.getElementById('icono-orden-actual').className = `fa-solid ${iconClass} text-emerald-400 text-[10px] shrink-0`;
    index3DActivo = 0;
    cerrarModalOrden();
    recompilarTodo();
}

function confirmarEliminarCategoria(nombreCat) {
    categoriaAEliminar = nombreCat;
    const modal = document.getElementById('modal-confirmar-eliminar');
    const content = document.getElementById('modal-confirmar-content');
    const texto = document.getElementById('texto-confirmar-eliminar');
    
    texto.innerHTML = `¿Seguro que deseas eliminar la categoría <br><span class="text-white font-bold text-xs mt-1 inline-block">'${nombreCat}'</span> ?`;
    
    if (modal && content) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }
}

function cerrarModalConfirmarEliminar() {
    const modal = document.getElementById('modal-confirmar-eliminar');
    const content = document.getElementById('modal-confirmar-content');
    if (modal && content) {
        modal.classList.remove('opacity-100');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); categoriaAEliminar = null; }, 300);
    }
}

async function ejecutarEliminacionCategoriaPendiente() {
    if (!categoriaAEliminar) return;
    const nombreCat = categoriaAEliminar;
    cerrarModalConfirmarEliminar();

    categoriasDinamicas = categoriasDinamicas.filter(c => c !== nombreCat);
    if (pillActiva === nombreCat) pillActiva = 'TODOS';
    listadoJuegos.forEach(jg => { if (jg.tipo === nombreCat) { jg.tipo = 'JUEGOS'; } });
    
    try {
        let fd = new FormData();
        fd.append('action', 'eliminar_categoria_global');
        fd.append('categoria', nombreCat);
        await fetch('api/library_biblioteca.php', { method: 'POST', body: fd });
    } catch(e) {}
    
    abrirModalFiltro(); 
    actualizarInterfazFiltros(); 
    recompilarTodo();
    window.ps5Notification("SISTEMA", `Categoría '${nombreCat}' eliminada.`, "fa-trash-can");
}

function ejecutarFiltroGlobal() { recompilarTodo(); }

function conmutarModoVista() {
    vistaModo = (vistaModo === 'grid') ? '3d' : 'grid';
    const icono = document.getElementById('icono-vista');
    if (icono) icono.className = vistaModo === '3d' ? 'fa-solid fa-table-cells text-purple-400' : 'fa-solid fa-cube text-cyan-400';
    recompilarTodo();
}

async function consultarPesoRealConsola(cusa, tipo, version) {
    const tVersion = document.getElementById('text-version-3d');
    const sheetSize = document.getElementById('panel-game-size');
    
    if (juegoSeleccionadoLocal && juegoSeleccionadoLocal.id === cusa && juegoSeleccionadoLocal.realSizeHtml) {
        if (tVersion) tVersion.innerHTML = juegoSeleccionadoLocal.realSizeHtml;
        if (sheetSize) sheetSize.innerHTML = juegoSeleccionadoLocal.sheetSizeHtml;
        return;
    }

    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';

    try {
        let fd = new FormData();
        fd.append('host_ip', ip);
        fd.append('cusa_id', cusa);
        let res = await fetch('api/tech_info_biblioteca.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        if (data && data.status === 'success') {
            const iconLoc = data.location.includes('Ampliado') ? '<i class="fa-solid fa-server text-emerald-500 mr-1"></i> Alm. Ampliado' : '<i class="fa-solid fa-hdd text-gray-500 mr-1"></i> Alm. Interno';
            
            const sizeStr3D = `${iconLoc}  •  ${data.size} (aprox.)`;
            const sizeStrSheet = `${data.location.includes('Ampliado') ? '<i class="fa-solid fa-server text-emerald-400"></i>' : '<i class="fa-solid fa-hdd text-gray-400"></i>'} ${data.size} (aprox.)`;
            
            if (juegoSeleccionadoLocal && juegoSeleccionadoLocal.id === cusa) {
                juegoSeleccionadoLocal.realSize = data.size;
                juegoSeleccionadoLocal.realSizeBytes = data.bytes; 
                juegoSeleccionadoLocal.realSizeHtml = sizeStr3D;
                juegoSeleccionadoLocal.sheetSizeHtml = sizeStrSheet;
            }

            if(tVersion) tVersion.innerHTML = sizeStr3D;
            if(sheetSize) sheetSize.innerHTML = sizeStrSheet;
        }
    } catch(e) {
        if (tVersion) tVersion.innerText = `--- (aprox.)`;
    }
}

async function actualizarConteoCapturasDock(cusa, targetBadgeId = 'sheet-count-capturas-badge') {
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    const badge = document.getElementById(targetBadgeId);
    if (!badge) return;
    badge.classList.add('hidden'); 

    try {
        let fd = new FormData();
        fd.append('action', 'count_only');
        fd.append('host_ip', ip);
        fd.append('cusa_id', cusa);
        let res = await fetch('api/ps4_screenshots_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        if(juegoSeleccionadoLocal && juegoSeleccionadoLocal.id === cusa && data && data.status === 'success' && data.count > 0) {
            badge.innerText = data.count;
            badge.classList.remove('hidden');
        }
    } catch(e) { console.error("Error Dock:", e); }
}

async function ejecutarAccionRapidaJuego(accion) {
    if (!juegoSeleccionadoLocal) return;
    
    if (accion === 'editar_portada') {
        window.ps5Notification("MODDING", `Abriendo editor para ${juegoSeleccionadoLocal.nombre}...`, "fa-wand-magic-sparkles");
        localStorage.setItem('modding_cusa_activo', juegoSeleccionadoLocal.id);
        cerrarBottomSheet(null);
        
        setTimeout(() => { 
            if (typeof abrirModuloNativo === 'function') {
                abrirModuloNativo('modding');
            } else {
                abrirModulo('modding'); 
            }
        }, 300);
    } 
    else if (accion === 'saves') { 
        window.ps5Notification("PARTIDAS", `Sincronizando saves de: ${juegoSeleccionadoLocal.nombre}`, "fa-floppy-disk"); 
        cerrarBottomSheet(null);
    } 
    else if (accion === 'galeria') { 
        cerrarBottomSheet(null);
        abrirGaleriaJuego();
    } 
    else if (accion === 'dlcs') { 
        cerrarBottomSheet(null);
        abrirModalDLC();
    }
    else if (accion === 'quitar') { 
        if(confirm(`¿Quitar ${juegoSeleccionadoLocal.nombre} de la biblioteca?`)) window.ps5Notification("SISTEMA", `Eliminado.`, "fa-trash-can"); 
        cerrarBottomSheet(null);
    }
}

function abrirOpcionesDesde3D() {
    abrirOpcionesJuegoDirecto(juegoSeleccionadoLocal.id);
}

function abrirOpcionesJuegoDirecto(id) {
    juegoSeleccionadoLocal = listadoJuegos.find(j => j.id === id);
    if (!juegoSeleccionadoLocal) return;
    
    document.getElementById('panel-game-title').innerText = juegoSeleccionadoLocal.nombre;
    document.getElementById('panel-game-cusa').innerText = juegoSeleccionadoLocal.id;
    document.getElementById('panel-game-version').innerText = `v${juegoSeleccionadoLocal.version}`;
    
    const labelCat = document.getElementById('label-categoria-actual');
    if (labelCat) labelCat.innerText = juegoSeleccionadoLocal.tipo;
    
    const sizeElem = document.getElementById('panel-game-size');
    if(sizeElem) sizeElem.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-cyan-500"></i>`;
    
    const panelBg = document.getElementById('panel-bg-blur');
    if(panelBg) panelBg.style.backgroundImage = `url('${juegoSeleccionadoLocal.img}')`;

    const avatarArt = document.getElementById('panel-avatar-art');
    if (avatarArt) avatarArt.style.backgroundImage = `url('${juegoSeleccionadoLocal.img}')`;

    consultarPesoRealConsola(juegoSeleccionadoLocal.id, juegoSeleccionadoLocal.tipo, juegoSeleccionadoLocal.version);
    actualizarConteoCapturasDock(juegoSeleccionadoLocal.id, 'sheet-count-capturas-badge');
    
    const sheet = document.getElementById('sheet-detalles-juego');
    if (sheet) {
        sheet.classList.remove('hidden');
        setTimeout(() => { 
            sheet.classList.add('opacity-100'); 
            const cardContent = document.getElementById('sheet-content-card');
            if (cardContent) cardContent.classList.remove('translate-y-full'); 
        }, 10);
    }
}

function cerrarBottomSheet(e) {
    if (e && e.target !== document.getElementById('sheet-detalles-juego')) return;
    const cardContent = document.getElementById('sheet-content-card');
    if (cardContent) cardContent.classList.add('translate-y-full');
    const sheet = document.getElementById('sheet-detalles-juego');
    if (sheet) { sheet.classList.remove('opacity-100'); setTimeout(() => sheet.classList.add('hidden'), 300); }
}

function abrirModalNuevaCategoria() { 
    cerrarModalFiltro(null); 
    const m = document.getElementById('modal-nueva-categoria'); 
    if (m) { m.classList.remove('hidden'); setTimeout(() => m.classList.add('opacity-100'), 10); } 
}

function cerrarModalNuevaCategoria() { 
    const m = document.getElementById('modal-nueva-categoria'); 
    if (m) { m.classList.remove('opacity-100'); setTimeout(() => m.classList.add('hidden'), 300); } 
}

function crearCategoriaProcesar() {
    const val = document.getElementById('input-nueva-cat').value.trim().toUpperCase();
    if (val && !categoriasDinamicas.includes(val)) {
        categoriasDinamicas.push(val);
        actualizarInterfazFiltros();
        cerrarModalNuevaCategoria();
        document.getElementById('input-nueva-cat').value = "";
    }
}

function abrirSelectorCategoriasCustom() {
    const contenedorLista = document.getElementById('lista-categorias-custom-game');
    if (!contenedorLista) return;
    
    contenedorLista.innerHTML = '';
    
    categoriasDinamicas.forEach(cat => {
        const isActive = juegoSeleccionadoLocal && juegoSeleccionadoLocal.tipo === cat;
        const iconHtml = isActive 
            ? `<i class="fa-solid fa-circle-check text-cyan-400 text-lg"></i>` 
            : `<i class="fa-regular fa-circle text-gray-600 text-lg"></i>`;
            
        const divOption = document.createElement('div');
        divOption.className = `flex items-center justify-between p-4 rounded-xl cursor-pointer transition-all active:scale-[0.98] ${isActive ? 'bg-cyan-500/10 border border-cyan-500/30' : 'bg-[#111827] border border-white/5 hover:bg-white/5'}`;
        divOption.onclick = () => { seleccionarCategoriaCustomGame(cat); };
        divOption.innerHTML = `
            <span class="text-[12px] font-black uppercase tracking-widest ${isActive ? 'text-cyan-400' : 'text-gray-300'}">${cat}</span>
            ${iconHtml}
        `;
        contenedorLista.appendChild(divOption);
    });

    const modal = document.getElementById('modal-selector-categorias-custom');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => { 
            modal.classList.add('opacity-100'); 
            const modContent = document.getElementById('modal-selector-categorias-content');
            if (modContent) modContent.classList.remove('translate-y-full'); 
        }, 10);
    }
}

function cerrarSelectorCategoriasCustom(e) {
    if (e && e.target !== document.getElementById('modal-selector-categorias-custom')) return;
    const modContent = document.getElementById('modal-selector-categorias-content');
    if (modContent) modContent.classList.add('translate-y-full');
    const modal = document.getElementById('modal-selector-categorias-custom');
    if (modal) { modal.classList.remove('opacity-100'); setTimeout(() => modal.classList.add('hidden'), 300); }
}

async function seleccionarCategoriaCustomGame(nuevaCat) {
    cerrarSelectorCategoriasCustom();
    if (!juegoSeleccionadoLocal || juegoSeleccionadoLocal.tipo === nuevaCat) return;
    
    const labelCat = document.getElementById('label-categoria-actual');
    if (labelCat) labelCat.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i>GUARDANDO`;
    
    try {
        let formData = new FormData();
        formData.append('action', 'cambiar_categoria');
        formData.append('cusa_id', juegoSeleccionadoLocal.id);
        formData.append('categoria', nuevaCat);

        let response = await fetch('api/library_biblioteca.php', { method: 'POST', body: formData });
        let resultado = await response.json();
        
        if (resultado && resultado.status === 'success') {
            juegoSeleccionadoLocal.tipo = nuevaCat;
            if (labelCat) labelCat.innerText = nuevaCat;
            window.ps5Notification("CATEGORÍA", `Movido a ${nuevaCat}`, "fa-folder-open");
            await iniciarCargaInmediataYEscaneoSilencioso();
        }
    } catch (err) {
        if (labelCat) labelCat.innerText = juegoSeleccionadoLocal.tipo;
        window.ps5Notification("ERROR", "No se pudo cambiar la categoría", "fa-circle-xmark");
    }
}

function abrirGaleriaGlobal() {
    isGlobalGallery = true;
    juegoSeleccionadoLocal = null; 

    document.getElementById('galeria-title').innerText = "CAPTURAS GLOBALES";
    document.getElementById('galeria-count').innerText = "Conectando...";
    document.getElementById('galeria-grid').innerHTML = '';
    document.getElementById('galeria-grid').classList.add('hidden');
    document.getElementById('galeria-loader').classList.remove('hidden');

    const modal = document.getElementById('modal-galeria-juego');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('translate-y-full');
            modal.classList.add('opacity-100');
        }, 10);
    }

    cargarFotosGaleriaGlobal(false);
}

async function cargarFotosGaleriaGlobal(force = false) {
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    try {
        let fd = new FormData();
        fd.append('action', 'get_all_caps');
        fd.append('host_ip', ip);
        fd.append('force', force ? '1' : '0'); 
        
        let res = await fetch('api/ps4_screenshots_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        document.getElementById('galeria-loader').classList.add('hidden');
        const grid = document.getElementById('galeria-grid');
        
        if (data && data.status === 'success' && data.images.length > 0) {
            galeriaCacheData = data.images;
            document.getElementById('galeria-count').innerText = `${data.images.length} Capturas`;
            
            let htmlGrid = "";
            data.images.forEach((img, idx) => {
                htmlGrid += `
                <div onclick="abrirLightbox(${idx})" class="relative w-full aspect-video rounded-xl overflow-hidden bg-[#111621] border border-white/5 shadow-md cursor-pointer active:scale-95 transition-transform hover:border-emerald-500/40 group">
                    <img src="${img.url}" loading="lazy" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                </div>`;
            });
            grid.innerHTML = htmlGrid;
            grid.classList.remove('hidden');
        } else {
            document.getElementById('galeria-count').innerText = "0 Capturas";
            grid.innerHTML = `<div class="col-span-full py-16 text-center text-gray-500 font-bold text-[10px] tracking-widest uppercase">${data.message || 'Sin fotos en la consola'}</div>`;
            grid.classList.remove('hidden');
        }
    } catch(e) {
        document.getElementById('galeria-loader').classList.add('hidden');
        document.getElementById('galeria-count').innerText = "Error";
    }
}

function abrirGaleriaJuego() {
    if (!juegoSeleccionadoLocal) return;
    isGlobalGallery = false; 
    
    document.getElementById('galeria-title').innerText = juegoSeleccionadoLocal.nombre;
    document.getElementById('galeria-count').innerText = "Conectando...";
    document.getElementById('galeria-grid').innerHTML = '';
    document.getElementById('galeria-grid').classList.add('hidden');
    document.getElementById('galeria-loader').classList.remove('hidden');

    const modal = document.getElementById('modal-galeria-juego');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('translate-y-full');
            modal.classList.add('opacity-100');
        }, 10);
    }

    cargarFotosGaleria(false); 
}

function cerrarGaleriaJuego() {
    const modal = document.getElementById('modal-galeria-juego');
    if (modal) {
        modal.classList.remove('opacity-100');
        modal.classList.add('translate-y-full');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
}

async function cargarFotosGaleria(force = false) {
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    try {
        let fd = new FormData();
        fd.append('action', 'get_caps');
        fd.append('host_ip', ip);
        fd.append('cusa_id', juegoSeleccionadoLocal.id);
        fd.append('force', force ? '1' : '0'); 
        
        let res = await fetch('api/ps4_screenshots_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        document.getElementById('galeria-loader').classList.add('hidden');
        const grid = document.getElementById('galeria-grid');
        
        if (data && data.status === 'success' && data.images.length > 0) {
            galeriaCacheData = data.images;
            document.getElementById('galeria-count').innerText = `${data.images.length} Capturas`;
            
            let htmlGrid = "";
            data.images.forEach((img, idx) => {
                htmlGrid += `
                <div onclick="abrirLightbox(${idx})" class="relative w-full aspect-video rounded-xl overflow-hidden bg-[#111621] border border-white/5 shadow-md cursor-pointer active:scale-95 transition-transform hover:border-emerald-500/40 group">
                    <img src="${img.url}" loading="lazy" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                </div>`;
            });
            grid.innerHTML = htmlGrid;
            grid.classList.remove('hidden');
        } else {
            document.getElementById('galeria-count').innerText = "0 Capturas";
            grid.innerHTML = `<div class="col-span-full py-16 text-center text-gray-500 font-bold text-[10px] tracking-widest uppercase">${data.message || 'Sin fotos en la consola'}</div>`;
            grid.classList.remove('hidden');
        }
    } catch(e) {
        document.getElementById('galeria-loader').classList.add('hidden');
        document.getElementById('galeria-count').innerText = "Error";
    }
}

function forzarRecargaGaleria() {
    document.getElementById('galeria-grid').classList.add('hidden');
    document.getElementById('galeria-loader').classList.remove('hidden');
    
    if (isGlobalGallery) {
        cargarFotosGaleriaGlobal(true);
    } else {
        cargarFotosGaleria(true);
    }
}

function abrirLightbox(index) {
    const visor = document.getElementById('lightbox-visor');
    const imgElement = document.getElementById('lightbox-img');
    const nameLabel = document.getElementById('lightbox-name');
    
    if (visor && imgElement && nameLabel) {
        urlImagenActualLightbox = galeriaCacheData[index].url;
        nombreImagenActualLightbox = galeriaCacheData[index].name;

        imgElement.src = urlImagenActualLightbox;
        nameLabel.innerText = nombreImagenActualLightbox;

        visor.classList.remove('hidden');
        setTimeout(() => { 
            visor.classList.add('opacity-100'); 
            imgElement.classList.remove('scale-95');
            imgElement.classList.add('scale-100');
        }, 10);
    }
}

function cerrarLightbox() {
    const visor = document.getElementById('lightbox-visor');
    const imgElement = document.getElementById('lightbox-img');
    
    if (visor && imgElement) {
        visor.classList.remove('opacity-100');
        imgElement.classList.remove('scale-100');
        imgElement.classList.add('scale-95');
        
        setTimeout(() => { 
            visor.classList.add('hidden'); 
            imgElement.src = ""; 
        }, 300);
    }
}

// 🔥 SISTEMA DE COMPARTIR Y DESCARGAR RESTAURADO
async function compartirImagenLightbox() {
    if (!urlImagenActualLightbox) return;
    try {
        window.ps5Notification("PREPARANDO", "Convirtiendo imagen para enviar...", "fa-share-nodes");
        
        const response = await fetch(urlImagenActualLightbox);
        const blob = await response.blob();
        const file = new File([blob], nombreImagenActualLightbox, { type: blob.type });
        
        // 1. Intenta abrir el menú nativo de Compartir de Android
        if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: nombreImagenActualLightbox,
                text: `Captura de ${juegoSeleccionadoLocal ? juegoSeleccionadoLocal.nombre : 'PS4 (Global)'}`,
                files: [file]
            });
        } 
        // 2. Si el navegador lo bloquea, activa la descarga forzada a la memoria interna
        else {
            window.ps5Notification("DESCARGANDO", "Guardando imagen localmente...", "fa-download");
            
            const urlDescarga = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = urlDescarga;
            a.download = nombreImagenActualLightbox;
            document.body.appendChild(a);
            a.click();
            
            setTimeout(() => {
                document.body.removeChild(a);
                URL.revokeObjectURL(urlDescarga);
                window.ps5Notification("ÉXITO", "Imagen guardada en tus Descargas.", "fa-check");
            }, 100);
        }
    } catch (err) {
        // 3. Fallback de emergencia si falla la conversión
        const a = document.createElement('a');
        a.href = urlImagenActualLightbox;
        a.download = nombreImagenActualLightbox;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}

let contenidoAEliminarData = null; 

function abrirModalDLC() {
    if (!juegoSeleccionadoLocal) return;
    
    document.getElementById('dlc-game-title').innerText = juegoSeleccionadoLocal.nombre;
    document.getElementById('dlc-game-cusa').innerText = juegoSeleccionadoLocal.id;
    
    const panelBg = document.getElementById('dlc-bg-blur');
    if(panelBg) panelBg.style.backgroundImage = `url('${juegoSeleccionadoLocal.img}')`;

    document.getElementById('dlc-data-container').classList.add('hidden');
    document.getElementById('dlc-loader').classList.remove('hidden');

    const modal = document.getElementById('modal-dlc-update');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            const content = document.getElementById('modal-dlc-content');
            if (content) {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }
        }, 10);
    }

    escanearDLCsYUpdates(false);
}

function cerrarModalDLC() {
    const modal = document.getElementById('modal-dlc-update');
    const content = document.getElementById('modal-dlc-content');
    if (modal && content) {
        modal.classList.remove('opacity-100');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }
}

function forzarRecargaDLCs() {
    document.getElementById('dlc-data-container').classList.add('hidden');
    document.getElementById('dlc-loader').classList.remove('hidden');
    escanearDLCsYUpdates(true);
}

async function escanearDLCsYUpdates(force = false) {
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    try {
        let fd = new FormData();
        fd.append('action', 'scan');
        fd.append('host_ip', ip);
        fd.append('cusa_id', juegoSeleccionadoLocal.id);
        fd.append('force', force ? '1' : '0');
        
        let res = await fetch('api/dlc_update_biblioteca.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        document.getElementById('dlc-loader').classList.add('hidden');
        document.getElementById('dlc-data-container').classList.remove('hidden');
        
        if (data && data.status === 'success') {
            const updateBox = document.getElementById('dlc-update-box');
            
            if (data.update.installed) {
                const iconLoc = data.update.location.includes('Ampliado') ? '<i class="fa-solid fa-server text-emerald-500"></i>' : '<i class="fa-solid fa-hdd text-gray-400"></i>';
                updateBox.innerHTML = `
                    <div class="flex items-center flex-1 gap-4 overflow-hidden">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20 shrink-0">
                            <i class="fa-solid fa-check text-emerald-400 text-lg"></i>
                        </div>
                        <div class="flex flex-col flex-1 truncate">
                            <span class="text-[12px] font-black uppercase tracking-widest text-emerald-400">Parche / Update</span>
                            <span class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1.5">${iconLoc} ${data.update.size_label}</span>
                        </div>
                    </div>
                    <button onclick="prepararEliminacionContenido('${data.update.path}', 'Update Parche')" class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center text-red-500 border border-red-500/20 active:scale-90 transition-all hover:bg-red-500/20 shrink-0">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                `;
            } else {
                updateBox.innerHTML = `
                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/10 shrink-0">
                        <i class="fa-solid fa-minus text-gray-500 text-lg"></i>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-[12px] font-black uppercase tracking-widest text-gray-400">Sin Update</span>
                        <span class="text-[10px] text-gray-500 mt-0.5">Versión Base (v1.00)</span>
                    </div>
                `;
            }

            const dlcBox = document.getElementById('dlc-list-box');
            const badge = document.getElementById('dlc-count-badge');
            badge.innerText = data.dlcs.length;
            
            if (data.dlcs.length > 0) {
                let dlcHtml = '';
                data.dlcs.forEach(dlc => {
                    const iconLoc = dlc.location.includes('Ampliado') ? '<i class="fa-solid fa-server text-blue-400"></i>' : '<i class="fa-solid fa-hdd text-gray-400"></i>';
                    dlcHtml += `
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-[#111827] border border-white/5 hover:border-blue-500/30 transition-colors">
                        <div class="flex items-center flex-1 gap-3 overflow-hidden">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center border border-blue-500/20 shrink-0">
                                <i class="fa-solid fa-puzzle-piece text-blue-400 text-sm"></i>
                            </div>
                            <div class="flex flex-col flex-1 overflow-hidden">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-200 truncate">${dlc.id}</span>
                                <span class="text-[8px] text-gray-500 mt-0.5 flex items-center gap-1">${iconLoc} ${dlc.size_label}</span>
                            </div>
                        </div>
                        <button onclick="prepararEliminacionContenido('${dlc.path}', '${dlc.id}')" class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-500 border border-red-500/20 active:scale-90 transition-all hover:bg-red-500/20 shrink-0">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`;
                });
                dlcBox.innerHTML = dlcHtml;
            } else {
                dlcBox.innerHTML = `
                    <div class="p-4 rounded-xl border border-dashed border-white/10 text-center">
                        <span class="text-[10px] font-black tracking-widest text-gray-500 uppercase">Ningún DLC detectado</span>
                    </div>
                `;
            }

        } else {
            window.ps5Notification("ERROR", "Fallo al leer la consola", "fa-triangle-exclamation");
        }
    } catch(e) {
        document.getElementById('dlc-loader').classList.add('hidden');
        window.ps5Notification("ERROR", "No hay conexión con FTP", "fa-wifi");
    }
}

function prepararEliminacionContenido(path, nombreVisual) {
    contenidoAEliminarData = { path: path, nombre: nombreVisual };
    document.getElementById('nombre-contenido-eliminar').innerText = nombreVisual;
    
    const modal = document.getElementById('modal-confirmar-eliminar-contenido');
    const content = document.getElementById('modal-content-eliminar-contenido');
    
    if (modal && content) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }
}

function cerrarModalEliminarContenido() {
    const modal = document.getElementById('modal-confirmar-eliminar-contenido');
    const content = document.getElementById('modal-content-eliminar-contenido');
    if (modal && content) {
        modal.classList.remove('opacity-100');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(() => { 
            modal.classList.add('hidden'); 
            contenidoAEliminarData = null; 
            const iconBtn = document.getElementById('icono-btn-eliminar');
            if (iconBtn) iconBtn.className = "fa-solid fa-skull";
        }, 300);
    }
}

async function ejecutarEliminacionContenidoFTP() {
    if (!contenidoAEliminarData) return;
    
    const iconBtn = document.getElementById('icono-btn-eliminar');
    if (iconBtn) iconBtn.className = "fa-solid fa-spinner fa-spin";

    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    
    try {
        let fd = new FormData();
        fd.append('action', 'delete_content');
        fd.append('host_ip', ip);
        fd.append('cusa_id', juegoSeleccionadoLocal.id);
        fd.append('target_path', contenidoAEliminarData.path);
        
        let res = await fetch('api/dlc_update_biblioteca.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        cerrarModalEliminarContenido();
        
        if (data && data.status === 'success') {
            window.ps5Notification("SISTEMA", `Contenido eliminado con éxito de la consola.`, "fa-trash-can");
            document.getElementById('dlc-data-container').classList.add('hidden');
            document.getElementById('dlc-loader').classList.remove('hidden');
            setTimeout(() => { escanearDLCsYUpdates(true); }, 500); 
            consultarPesoRealConsola(juegoSeleccionadoLocal.id, juegoSeleccionadoLocal.tipo, juegoSeleccionadoLocal.version);
        } else {
            window.ps5Notification("ERROR", "No se pudo borrar por permisos de FTP.", "fa-circle-xmark");
        }
    } catch(e) {
        cerrarModalEliminarContenido();
        window.ps5Notification("ERROR", "Se perdió la conexión.", "fa-wifi");
    }
}
