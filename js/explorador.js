/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - CONTROLADOR DEL EXPLORADOR FTP
 * DEVELOPED By SeBaS - RUTA: js/explorador.js
 * ====================================================================
 */

let exploradorRutaActual = '/';
let ftpCurrentItems = []; 

// 🔥 NUEVO CLIPBOARD MÚLTIPLE
let ftpClipboard = { type: null, sourcePath: null, isDir: false, isMulti: false, sourcePaths: [] }; 
let ctxTargetItem = null; 

let isMultiSelectMode = false;
let multiSelectedPaths = new Map();

let longPressTimer;
let deleteStep = 1;
let deleteTarget = "";
let deleteCallback = null;

let uploadAbortController = null;
let isUploadingExplorer = false;

function abrirCapaExplorador() {
    document.querySelectorAll('.app-layer').forEach(layer => layer.classList.remove('active', 'flex'));
    document.querySelectorAll('.app-layer').forEach(layer => layer.classList.add('hidden'));
    
    const capa = document.getElementById('layer-explorador');
    if (capa) {
        capa.classList.remove('hidden');
        capa.classList.add('active', 'flex');
        cargarRutaFtp('/', true); 
        renderizarAccesosRapidos();
    }
}

function formatearTamanoBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function normalizePath(p) { return p.replace(/\/+/g, '/'); }

async function cargarRutaFtp(rutaDestino, esNavegacionAtras = false) {
    const ip = localStorage.getItem('sebas_ip_final_libre');
    const loader = document.getElementById('loader-explorador');
    const container = document.getElementById('ftp-list-container');
    const rutaBreadcrumb = document.getElementById('ftp-ruta-actual');

    if (!ip) { window.ps5Notification("ERROR", "No hay IP conectada.", "fas fa-wifi"); return; }

    rutaDestino = normalizePath(rutaDestino);
    if (!rutaDestino.endsWith('/')) rutaDestino += '/';

    loader.classList.remove('hidden'); loader.classList.add('flex');
    container.innerHTML = `<div class="w-full flex items-center p-2 rounded-xl bg-white/5 animate-pulse mb-1"><div class="w-8 h-8 rounded-lg bg-white/10 mr-3"></div><div class="h-3 bg-white/10 w-1/2 rounded-full"></div></div>`;

    try {
        let fd = new FormData();
        fd.append('action', 'listar_directorio');
        fd.append('host_ip', ip);
        fd.append('path', rutaDestino);

        let res = await fetch('api/explorador_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        if (data.status === 'success') {
            exploradorRutaActual = data.current_path;
            rutaBreadcrumb.innerText = exploradorRutaActual;
            setTimeout(() => { rutaBreadcrumb.scrollLeft = rutaBreadcrumb.scrollWidth; }, 100);
            
            if (!esNavegacionAtras && exploradorRutaActual !== '/') {
                history.pushState({ page: 'ftp_folder', ruta: exploradorRutaActual }, "Carpeta FTP", "");
            }

            ftpCurrentItems = [...data.data.carpetas, ...data.data.archivos];
            
            if(ftpClipboard.type) { document.getElementById('btn-paste-top').classList.remove('hidden'); } 
            else { document.getElementById('btn-paste-top').classList.add('hidden'); }

            renderizarListaExplorador(data.data.carpetas, data.data.archivos);
        } else {
            window.ps5Notification("ERROR", data.message, "fas fa-exclamation-triangle");
            container.innerHTML = `<div class="w-full p-4 text-center text-[10px] font-mono text-red-400 uppercase">Error al leer</div>`;
        }
    } catch (e) {
        window.ps5Notification("ERROR", "Fallo de conexión.", "fas fa-wifi");
    } finally {
        loader.classList.add('hidden'); loader.classList.remove('flex');
    }
}

function renderizarListaExplorador(carpetas, archivos) {
    const container = document.getElementById('ftp-list-container');
    container.innerHTML = '';

    if (carpetas.length === 0 && archivos.length === 0) {
        container.innerHTML = `<div class="w-full h-full flex flex-col items-center justify-center opacity-30 mt-10"><i class="fas fa-folder-open text-4xl mb-2 text-gray-400"></i><span class="text-[9px] font-mono tracking-widest text-gray-400 uppercase">Carpeta Vacía</span></div>`;
        return;
    }

    const appendItem = (item, iconCode, colorClasses, isDir) => {
        let div = document.createElement('div');
        let fullPath = normalizePath(exploradorRutaActual + '/' + item.name);
        let isSelected = multiSelectedPaths.has(fullPath);
        
        div.className = `item-hover flex items-center justify-between p-2.5 rounded-xl border transition-all cursor-pointer select-none ${isSelected ? 'selected-row' : 'border-transparent'}`;
        
        div.addEventListener('touchstart', (e) => {
            longPressTimer = setTimeout(() => {
                if(!isMultiSelectMode) abrirContextMenu(item, isDir, iconCode);
            }, 600); 
        }, {passive: true});
        
        div.addEventListener('touchend', () => clearTimeout(longPressTimer));
        div.addEventListener('touchmove', () => clearTimeout(longPressTimer));

        div.onclick = (e) => {
            clearTimeout(longPressTimer);
            if (isMultiSelectMode) {
                toggleSeleccion(fullPath, isDir, div);
            } else {
                if(isDir) entrarCarpeta(item.name);
                else window.ps5Notification("ARCHIVO", item.name, "fas fa-file");
            }
        };
        
        let metaTxt = isDir ? "DIR" : formatearTamanoBytes(item.size);
        let checkHTML = isMultiSelectMode ? `<div class="w-5 h-5 rounded-md border border-cyan-500/50 flex items-center justify-center ml-2 ${isSelected ? 'bg-cyan-500 text-black' : 'bg-transparent text-transparent'}"><i class="fas fa-check text-[10px]"></i></div>` : '';

        div.innerHTML = `
            <div class="flex items-center gap-3 flex-1 min-w-0 pointer-events-none">
                <div class="w-9 h-9 rounded-lg ${colorClasses} flex items-center justify-center shrink-0"><i class="${iconCode}"></i></div>
                <div class="flex flex-col flex-1 min-w-0">
                    <span class="text-[12px] font-bold text-gray-200 truncate">${item.name}</span>
                    <span class="text-[9px] font-mono text-gray-500">${item.date}</span>
                </div>
            </div>
            <div class="flex items-center pointer-events-none">
                <span class="text-[10px] font-mono font-black text-cyan-400 shrink-0">${metaTxt}</span>
                ${checkHTML}
            </div>
        `;
        container.appendChild(div);
    };

    carpetas.forEach(c => appendItem(c, "fas fa-folder", "text-blue-400 bg-blue-500/10", true));
    archivos.forEach(a => {
        let ext = a.name.split('.').pop().toLowerCase();
        let iconCode = "fas fa-file"; let colorClasses = "text-gray-400 bg-gray-500/10";
        if (['png', 'jpg', 'jpeg'].includes(ext)) { iconCode = "fas fa-image"; colorClasses = "text-emerald-400 bg-emerald-500/10"; }
        if (['json', 'sfo', 'ini'].includes(ext)) { iconCode = "fas fa-file-code"; colorClasses = "text-yellow-400 bg-yellow-500/10"; }
        if (['prx', 'bin', 'elf'].includes(ext)) { iconCode = "fas fa-microchip"; colorClasses = "text-purple-400 bg-purple-500/10"; }
        if (['pkg'].includes(ext)) { iconCode = "fas fa-box-open"; colorClasses = "text-cyan-400 bg-cyan-500/10"; }
        appendItem(a, iconCode, colorClasses, false);
    });
}

function entrarCarpeta(nombreCarpeta) { cargarRutaFtp(exploradorRutaActual + '/' + nombreCarpeta); }

function navegarArribaFtp(esNavegacionAtras = false) {
    if (exploradorRutaActual === '/' || exploradorRutaActual === '') return;
    history.back(); 
}

function abrirContextMenu(item, isDir, iconCode) {
    if ("vibrate" in navigator) navigator.vibrate(50); 
    ctxTargetItem = { ...item, isDir: isDir, fullPath: normalizePath(exploradorRutaActual + '/' + item.name) };
    document.getElementById('ctx-title').innerText = item.name;
    document.getElementById('ctx-subtitle').innerText = isDir ? "Carpeta" : formatearTamanoBytes(item.size);
    document.getElementById('ctx-icon').className = `w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xl`;
    document.getElementById('ctx-icon').innerHTML = `<i class="${iconCode}"></i>`;

    const menu = document.getElementById('ctx-menu-sheet');
    const content = document.getElementById('ctx-menu-content');
    menu.classList.remove('hidden');
    setTimeout(() => { menu.classList.add('opacity-100'); content.classList.remove('translate-y-full'); }, 10);
}

function cerrarContextMenu() {
    const menu = document.getElementById('ctx-menu-sheet');
    const content = document.getElementById('ctx-menu-content');
    content.classList.add('translate-y-full');
    menu.classList.remove('opacity-100');
    setTimeout(() => menu.classList.add('hidden'), 300);
}

function ctxAction(actionStr) {
    cerrarContextMenu();
    setTimeout(() => {
        if (actionStr === 'copiar' || actionStr === 'cortar') {
            ftpClipboard = { type: actionStr, sourcePath: ctxTargetItem.fullPath, isDir: ctxTargetItem.isDir, isMulti: false, sourcePaths: [] };
            document.getElementById('btn-paste-top').classList.remove('hidden');
            window.ps5Notification("PORTAPAPELES", `${ctxTargetItem.name} copiado.`, "fas fa-paste");
        } 
        else if (actionStr === 'renombrar') {
            abrirPromptFtp(`Renombrar: ${ctxTargetItem.name}`, ctxTargetItem.name, async (nuevoNombre) => {
                let oldP = ctxTargetItem.fullPath;
                let newP = normalizePath(exploradorRutaActual + '/' + nuevoNombre);
                ejecutarAPI('renombrar_mover', { old_path: oldP, new_path: newP }, "Elemento renombrado.");
            });
        }
        else if (actionStr === 'duplicar') {
            let newName = ctxTargetItem.name + "_copia";
            let newP = normalizePath(exploradorRutaActual + '/' + newName);
            window.ps5Notification("DUPLICANDO", "Creando copia...", "fas fa-clone");
            ejecutarAPI('copiar', { source_path: ctxTargetItem.fullPath, dest_path: newP }, "Duplicado correctamente.");
        }
        else if (actionStr === 'eliminar') {
            abrirDeleteConfirm(ctxTargetItem.name, () => {
                window.ps5Notification("SISTEMA", "Iniciando borrado profundo...", "fas fa-trash");
                ejecutarAPI('eliminar', { target: ctxTargetItem.fullPath, is_dir: ctxTargetItem.isDir }, "Eliminado permanentemente.");
            });
        }
        else if (actionStr === 'seleccionar') {
            isMultiSelectMode = true;
            multiSelectedPaths.set(ctxTargetItem.fullPath, ctxTargetItem.isDir);
            activarModoMultiselect();
        }
        else if (actionStr === 'compartir') {
            if(ctxTargetItem.isDir) { window.ps5Notification("AVISO", "No se puede descargar carpetas enteras aún.", "fas fa-info"); return; }
            const ip = localStorage.getItem('sebas_ip_final_libre');
            let downloadUrl = `api/explorador_api.php?action=descargar_directo&host_ip=${ip}&path=${encodeURIComponent(ctxTargetItem.fullPath)}`;
            
            let iframe = document.getElementById('hidden-downloader-frame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'hidden-downloader-frame';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            window.ps5Notification("DESCARGANDO", "Revisa las descargas de tu celular.", "fas fa-download");
            iframe.src = downloadUrl;
        }
    }, 350);
}

// 🔥 EJECUTOR DE PEGADO CON BUCLE ASÍNCRONO MÚLTIPLE
async function ejecutarPegado() {
    if (!ftpClipboard.type) return;

    let pathsToProcess = ftpClipboard.isMulti ? ftpClipboard.sourcePaths : [ftpClipboard.sourcePath];
    window.ps5Notification(ftpClipboard.type === 'cortar' ? "MOVIENDO" : "COPIANDO", `Procesando ${pathsToProcess.length} elemento(s)...`, "fas fa-spinner fa-spin");

    for (let sourceP of pathsToProcess) {
        let nombreArchivo = sourceP.split('/').pop();
        let destPath = normalizePath(exploradorRutaActual + '/' + nombreArchivo);

        if (ftpClipboard.type === 'cortar') {
            await ejecutarAPI('renombrar_mover', { old_path: sourceP, new_path: destPath }, `Movido: ${nombreArchivo}`);
        } else {
            await ejecutarAPI('copiar', { source_path: sourceP, dest_path: destPath }, `Copiado: ${nombreArchivo}`);
        }
    }

    ftpClipboard = { type: null, sourcePath: null, isDir: false, isMulti: false, sourcePaths: [] };
    document.getElementById('btn-paste-top').classList.add('hidden');
}

async function ejecutarAPI(action, extraData, successMsg) {
    const ip = localStorage.getItem('sebas_ip_final_libre');
    let fd = new FormData();
    fd.append('action', action);
    fd.append('host_ip', ip);
    for (let key in extraData) { fd.append(key, extraData[key]); }

    try {
        document.getElementById('loader-explorador').classList.remove('hidden');
        document.getElementById('loader-explorador').classList.add('flex');
        
        let res = await fetch('api/explorador_api.php', { method: 'POST', body: fd });
        let raw = await res.text();
        try {
            let data = JSON.parse(raw);
            if (data.status === 'success') {
                window.ps5Notification("ÉXITO", successMsg, "fas fa-check");
                cargarRutaFtp(exploradorRutaActual, true); 
            } else { window.ps5Notification("ERROR", data.message, "fas fa-times"); }
        } catch(e) { window.ps5Notification("ERROR", "Fallo del servidor.", "fas fa-bug"); }
    } catch(e) { window.ps5Notification("ERROR", "Sin red.", "fas fa-wifi"); }
    finally {
        document.getElementById('loader-explorador').classList.add('hidden');
        document.getElementById('loader-explorador').classList.remove('flex');
    }
}

function activarModoMultiselect() {
    document.getElementById('explorador-header-normal').classList.add('hidden');
    document.getElementById('explorador-header-normal').classList.remove('flex');
    document.getElementById('explorador-header-multiselect').classList.remove('hidden');
    document.getElementById('explorador-header-multiselect').classList.add('flex');
    actualizarContadorMultiselect();
    cargarRutaFtp(exploradorRutaActual, true); 
}

function cancelarSeleccionMultiple() {
    isMultiSelectMode = false;
    multiSelectedPaths.clear();
    document.getElementById('explorador-header-multiselect').classList.add('hidden');
    document.getElementById('explorador-header-multiselect').classList.remove('flex');
    document.getElementById('explorador-header-normal').classList.remove('hidden');
    document.getElementById('explorador-header-normal').classList.add('flex');
    cargarRutaFtp(exploradorRutaActual, true);
}

function toggleSeleccion(fullPath, isDir, divElement) {
    if (multiSelectedPaths.has(fullPath)) { multiSelectedPaths.delete(fullPath); } 
    else { multiSelectedPaths.set(fullPath, isDir); }
    actualizarContadorMultiselect();
    
    divElement.classList.toggle('selected-row');
    let checkIcon = divElement.querySelector('.fa-check').parentElement;
    checkIcon.classList.toggle('bg-cyan-500'); checkIcon.classList.toggle('bg-transparent');
    checkIcon.classList.toggle('text-black'); checkIcon.classList.toggle('text-transparent');
}

function actualizarContadorMultiselect() {
    document.getElementById('txt-multiselect-count').innerText = `${multiSelectedPaths.size} Seleccionados`;
}

// 🔥 NUEVAS ACCIONES MÚLTIPLES: COPIAR, CORTAR Y DUPLICAR LOTES
function ejecutarAccionMultiple(accion) {
    if(multiSelectedPaths.size === 0) return;

    if (accion === 'copiar' || accion === 'cortar') {
        ftpClipboard = { type: accion, sourcePath: null, isDir: false, isMulti: true, sourcePaths: Array.from(multiSelectedPaths.keys()) };
        document.getElementById('btn-paste-top').classList.remove('hidden');
        window.ps5Notification("PORTAPAPELES", `${multiSelectedPaths.size} elementos seleccionados.`, accion === 'copiar' ? "fas fa-paste" : "fas fa-cut");
        cancelarSeleccionMultiple();
    }
    else if (accion === 'duplicar') {
        window.ps5Notification("DUPLICANDO", "Duplicando lote...", "fas fa-clone");
        (async () => {
            for (let path of multiSelectedPaths.keys()) {
                let newName = path.split('/').pop() + "_copia";
                let newP = normalizePath(exploradorRutaActual + '/' + newName);
                await ejecutarAPI('copiar', { source_path: path, dest_path: newP }, `Duplicado: ${newName}`);
            }
            cancelarSeleccionMultiple();
        })();
    }
    else if(accion === 'eliminar') {
        abrirDeleteConfirm(`${multiSelectedPaths.size} Elementos`, async () => {
            window.ps5Notification("ELIMINANDO", "Borrando lote...", "fas fa-trash");
            for (let [path, isDir] of multiSelectedPaths.entries()) {
                await ejecutarAPI('eliminar', { target: path, is_dir: isDir }, `Borrado exitoso.`);
            }
            cancelarSeleccionMultiple();
        });
    }
}

function abrirDeleteConfirm(targetName, onConfirmCallback) {
    deleteStep = 1; deleteTarget = targetName; deleteCallback = onConfirmCallback;
    actualizarUIDelete();
    const modal = document.getElementById('modal-delete-confirm');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('opacity-100'), 10);
}

function actualizarUIDelete() {
    const box = document.getElementById('del-modal-box');
    const icon = document.getElementById('del-modal-icon');
    const title = document.getElementById('del-modal-title');
    const desc = document.getElementById('del-modal-desc');
    const btn = document.getElementById('btn-real-delete');

    if (deleteStep === 1) {
        box.className = "w-full max-w-sm rounded-[2rem] border-2 border-orange-500/50 bg-[#0a0202]/95 p-6 shadow-2xl flex flex-col items-center text-center transition-colors duration-300";
        icon.className = "w-16 h-16 rounded-full bg-orange-500/20 text-orange-500 flex items-center justify-center text-3xl mb-4";
        title.className = "text-[16px] font-black tracking-widest text-orange-400 uppercase mb-2";
        title.innerText = "Advertencia de Sistema";
        desc.innerHTML = `¿Estás seguro que deseas eliminar <b>${deleteTarget}</b>?`;
        btn.className = "w-full py-4 rounded-xl bg-orange-600 text-[11px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_15px_rgba(249,115,22,0.4)]";
        btn.innerText = "Sí, Continuar al Paso 2";
        btn.onclick = () => { deleteStep = 2; actualizarUIDelete(); };
    } else {
        box.className = "w-full max-w-sm rounded-[2rem] border-2 border-red-500/80 bg-[#1a0505]/95 p-6 shadow-[0_0_60px_rgba(239,68,68,0.4)] flex flex-col items-center text-center transition-colors duration-300";
        icon.className = "w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center text-3xl mb-4 animate-pulse";
        title.className = "text-[16px] font-black tracking-widest text-red-500 uppercase mb-2 animate-pulse";
        title.innerText = "¡PELIGRO IRREVERSIBLE!";
        desc.innerHTML = `ÚLTIMA ADVERTENCIA. Vas a destruir <b>${deleteTarget}</b> por completo.`;
        btn.className = "w-full py-4 rounded-xl bg-red-600 text-[11px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_15px_rgba(239,68,68,0.5)]";
        btn.innerText = "⚠️ ELIMINAR DEFINITIVAMENTE";
        btn.onclick = () => { cerrarDeleteConfirm(); if(deleteCallback) deleteCallback(); };
    }
}

function cerrarDeleteConfirm() {
    const modal = document.getElementById('modal-delete-confirm');
    modal.classList.remove('opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function abrirPromptFtp(title, defaultValue, onConfirmCallback) {
    document.getElementById('prompt-title').innerText = title;
    const input = document.getElementById('prompt-input');
    input.value = defaultValue;
    const modal = document.getElementById('modal-prompt-ftp');
    modal.classList.remove('hidden');
    setTimeout(() => { modal.classList.add('opacity-100'); input.focus(); }, 10);
    document.getElementById('prompt-btn-confirm').onclick = () => {
        if(input.value.trim() !== '') { cerrarPromptFtp(); onConfirmCallback(input.value.trim()); }
    };
}
function cerrarPromptFtp() {
    const modal = document.getElementById('modal-prompt-ftp');
    modal.classList.remove('opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function toggleFabMenu() {
    const menu = document.getElementById('fab-menu');
    const icon = document.getElementById('fab-icon');
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        setTimeout(() => { menu.classList.remove('scale-95', 'opacity-0'); menu.classList.add('scale-100', 'opacity-100'); icon.style.transform = 'rotate(45deg)'; }, 10);
    } else {
        menu.classList.remove('scale-100', 'opacity-100'); menu.classList.add('scale-95', 'opacity-0'); icon.style.transform = 'rotate(0deg)';
        setTimeout(() => menu.classList.add('hidden'), 200);
    }
}

function promptCrear(tipo) {
    toggleFabMenu();
    let isDir = (tipo === 'carpeta');
    abrirPromptFtp(`Nuevo ${isDir ? 'Directorio' : 'Archivo'}`, '', (nombre) => {
        let fullP = normalizePath(exploradorRutaActual + '/' + nombre);
        ejecutarAPI(isDir ? 'crear_carpeta' : 'crear_archivo', { target: fullP }, `Creado correctamente.`);
    });
}

async function procesarSubidaExplorador(event) {
    toggleFabMenu(); 
    const file = event.target.files[0];
    if (!file) return;

    const ip = localStorage.getItem('sebas_ip_final_libre');
    if (!ip) { window.ps5Notification("ERROR", "No hay IP conectada.", "fas fa-wifi"); return; }

    isUploadingExplorer = true;
    uploadAbortController = new AbortController();

    document.getElementById('up-modal-title').innerText = "Subiendo al FTP";
    document.getElementById('progress-icon-explorador').className = "fas fa-cloud-upload-alt animate-bounce";
    document.getElementById('up-modal-filename').innerText = file.name;
    document.getElementById('up-modal-percent').innerText = "0%";
    document.getElementById('up-modal-bar').style.width = "0%";
    document.getElementById('up-modal-sent').innerText = `0 B / ${formatearTamanoBytes(file.size)}`;
    document.getElementById('up-modal-speed').innerText = "0 MB/s";
    document.getElementById('up-modal-eta').innerText = "ETA: --:--";
    
    const modal = document.getElementById('modal-upload-progress');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('opacity-100'), 10);

    const CHUNK_SIZE = 1.5 * 1024 * 1024; 
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    let bytesSent = 0;

    for (let currentChunk = 0; currentChunk < totalChunks; currentChunk++) {
        if (uploadAbortController.signal.aborted) {
            cerrarModalProgresoExplorador();
            window.ps5Notification("AVISO", "Subida cancelada por el usuario.", "fas fa-ban");
            event.target.value = '';
            return;
        }

        const start = currentChunk * CHUNK_SIZE;
        const end = Math.min(start + CHUNK_SIZE, file.size);
        const chunkBlob = file.slice(start, end);

        let fd = new FormData();
        fd.append('action', 'upload_explorer_chunk');
        fd.append('host_ip', ip);
        fd.append('chunk_index', currentChunk);
        fd.append('filename', file.name);
        fd.append('target_dir', exploradorRutaActual); 
        fd.append('file_chunk', chunkBlob, file.name);

        try {
            const chunkStartTime = Date.now();
            let res = await fetch('api/explorador_api.php', { method: 'POST', body: fd, signal: uploadAbortController.signal });
            let data = await res.json();

            if (data.status === 'success') {
                bytesSent += (end - start);
                actualizarProgresoExplorador(bytesSent, file.size, chunkStartTime, end - start);
            } else { throw new Error(data.message); }
        } catch (err) {
            cerrarModalProgresoExplorador();
            window.ps5Notification("ERROR CRÍTICO", err.message || "Fallo en Termux al subir.", "fas fa-triangle-exclamation");
            event.target.value = '';
            return;
        }
    }

    cerrarModalProgresoExplorador();
    window.ps5Notification("ÉXITO", "Archivo inyectado correctamente.", "fas fa-check-double");
    cargarRutaFtp(exploradorRutaActual, true);
    event.target.value = '';
}

async function procesarSubidaCarpetaEstructurada(event) {
    toggleFabMenu();
    const files = event.target.files;
    if (!files || files.length === 0) return;

    const ip = localStorage.getItem('sebas_ip_final_libre');
    if (!ip) { window.ps5Notification("ERROR", "No hay IP conectada.", "fas fa-wifi"); return; }

    isUploadingExplorer = true;
    uploadAbortController = new AbortController();

    let totalBytes = 0;
    for (let i = 0; i < files.length; i++) totalBytes += files[i].size;
    let folderNameBase = files[0].webkitRelativePath ? files[0].webkitRelativePath.split('/')[0] : 'Carpeta';

    const modal = document.getElementById('modal-upload-progress');
    document.getElementById('up-modal-title').innerText = "Subiendo Carpeta";
    document.getElementById('progress-icon-explorador').className = "fas fa-folder-open text-indigo-400 animate-pulse";
    document.getElementById('up-modal-filename').innerText = folderNameBase;
    document.getElementById('up-modal-percent').innerText = "0%";
    document.getElementById('up-modal-bar').style.width = "0%";
    document.getElementById('up-modal-sent').innerText = `0 B / ${formatearTamanoBytes(totalBytes)}`;
    document.getElementById('up-modal-speed').innerText = "Calculando...";
    document.getElementById('up-modal-eta').innerText = "ETA: --:--";

    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('opacity-100'), 10);

    const CHUNK_SIZE = 1.5 * 1024 * 1024;
    let bytesSentTotal = 0;
    let chunkStartTime = Date.now();

    try {
        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            if (uploadAbortController.signal.aborted) break;

            let relativePath = file.webkitRelativePath || file.name;
            let parts = relativePath.split('/');
            let filename = parts.pop();
            let subDir = parts.join('/');

            let finalTargetDir = exploradorRutaActual;
            if (!finalTargetDir.endsWith('/')) finalTargetDir += '/';
            if (subDir !== "") finalTargetDir += subDir + '/';

            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            if(totalChunks === 0) continue; 

            for (let currentChunk = 0; currentChunk < totalChunks; currentChunk++) {
                if (uploadAbortController.signal.aborted) break;

                const start = currentChunk * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, file.size);
                const chunkBlob = file.slice(start, end);

                let fd = new FormData();
                fd.append('action', 'upload_explorer_chunk');
                fd.append('host_ip', ip);
                fd.append('chunk_index', currentChunk);
                fd.append('filename', filename);
                fd.append('target_dir', finalTargetDir);
                fd.append('file_chunk', chunkBlob, filename);

                chunkStartTime = Date.now();
                let res = await fetch('api/explorador_api.php', { method: 'POST', body: fd, signal: uploadAbortController.signal });
                let data = await res.json();

                if (data.status === 'success') {
                    bytesSentTotal += (end - start);
                    actualizarProgresoExplorador(bytesSentTotal, totalBytes, chunkStartTime, end - start);
                    document.getElementById('up-modal-filename').innerText = `${i+1}/${files.length}: ${filename}`;
                } else {
                    throw new Error(data.message);
                }
            }
        }

        if (!uploadAbortController.signal.aborted) {
            cerrarModalProgresoExplorador();
            window.ps5Notification("ÉXITO", "Estructura de carpeta subida a la consola.", "fas fa-check-double");
            cargarRutaFtp(exploradorRutaActual, true);
        }
    } catch (err) {
        cerrarModalProgresoExplorador();
        window.ps5Notification("ERROR", err.message || "Fallo en la transferencia.", "fas fa-exclamation-triangle");
    } finally {
        event.target.value = '';
    }
}

function actualizarProgresoExplorador(bytesSent, totalBytes, chunkStartTime, chunkSize) {
    const percent = ((bytesSent / totalBytes) * 100).toFixed(1);
    const chunkTimeSecs = (Date.now() - chunkStartTime) / 1000;
    const speedBytesPerSec = chunkTimeSecs > 0 ? (chunkSize / chunkTimeSecs) : 0;
    const speedMB = (speedBytesPerSec / (1024 * 1024)).toFixed(2);
    
    const bytesRemaining = totalBytes - bytesSent;
    const secondsRemaining = speedBytesPerSec > 0 ? Math.round(bytesRemaining / speedBytesPerSec) : 0;
    
    let etaString = "--:--";
    if (secondsRemaining > 0 && isFinite(secondsRemaining)) {
        const m = Math.floor(secondsRemaining / 60).toString().padStart(2, '0');
        const s = (secondsRemaining % 60).toString().padStart(2, '0');
        etaString = `${m}:${s}`;
    }

    document.getElementById('up-modal-percent').innerText = `${percent}%`;
    document.getElementById('up-modal-bar').style.width = `${percent}%`;
    document.getElementById('up-modal-sent').innerText = `${formatearTamanoBytes(bytesSent)} / ${formatearTamanoBytes(totalBytes)}`;
    document.getElementById('up-modal-speed').innerText = `${speedMB} MB/s`;
    document.getElementById('up-modal-eta').innerText = `ETA: ${etaString}`;
}

function cancelarSubidaExplorador() {
    if(uploadAbortController) uploadAbortController.abort();
    isUploadingExplorer = false;
}

function cerrarModalProgresoExplorador() {
    isUploadingExplorer = false;
    const modal = document.getElementById('modal-upload-progress');
    modal.classList.remove('opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function obtenerAccesosGuardados() {
    let base = [
        { nombre: 'Raíz (/)', ruta: '/', icon: 'fas fa-hdd', color: 'blue' },
        { nombre: 'GoldHen', ruta: '/data/GoldHEN/', icon: 'fas fa-crown', color: 'yellow' },
        { nombre: 'USB 0', ruta: '/mnt/usb0/', icon: 'fab fa-usb', color: 'purple' }
    ];
    let custom = JSON.parse(localStorage.getItem('ftp_custom_shortcuts')) || [];
    return base.concat(custom);
}

function renderizarAccesosRapidos() {
    const container = document.getElementById('ftp-shortcuts-container');
    if (!container) return;
    container.innerHTML = '';
    obtenerAccesosGuardados().forEach((acc, index) => {
        let btn = document.createElement('button');
        btn.className = `shrink-0 px-4 py-2 rounded-xl bg-${acc.color}-500/10 border border-${acc.color}-500/20 text-${acc.color}-400 text-[9px] font-black tracking-widest uppercase active:scale-95 transition-all flex items-center gap-1.5 shadow-sm`;
        if (index > 2) {
            btn.innerHTML = `<i class="${acc.icon}"></i> ${acc.nombre} <div class="ml-1 w-4 h-4 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 hover:bg-red-500 hover:text-white transition-colors" onclick="event.stopPropagation(); borrarAccesoDirecto(${index - 3})"><i class="fas fa-times text-[8px]"></i></div>`;
        } else { btn.innerHTML = `<i class="${acc.icon}"></i> ${acc.nombre}`; }
        btn.onclick = () => cargarRutaFtp(acc.ruta);
        container.appendChild(btn);
    });
}

function abrirModalNuevoAccesoFtp() {
    toggleFabMenu(); 
    document.getElementById('ftp-shortcut-btn-name').value = '';
    document.getElementById('txt-ruta-a-guardar').innerText = exploradorRutaActual;
    const modal = document.getElementById('modal-nuevo-acceso-ftp');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('opacity-100'), 10);
}

function cerrarModalNuevoAccesoFtp() {
    const modal = document.getElementById('modal-nuevo-acceso-ftp');
    modal.classList.remove('opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function guardarNuevoAccesoFtp() {
    let btnName = document.getElementById('ftp-shortcut-btn-name').value.trim();
    if (!btnName) { window.ps5Notification("AVISO", "Ponle un nombre al acceso directo.", "fas fa-exclamation-triangle"); return; }
    let custom = JSON.parse(localStorage.getItem('ftp_custom_shortcuts')) || [];
    custom.push({ nombre: btnName, ruta: exploradorRutaActual, icon: 'fas fa-star', color: 'emerald' });
    localStorage.setItem('ftp_custom_shortcuts', JSON.stringify(custom));
    cerrarModalNuevoAccesoFtp();
    renderizarAccesosRapidos();
    window.ps5Notification("LISTO", "Ruta guardada en Favoritos.", "fas fa-check");
}

function borrarAccesoDirecto(index) {
    let custom = JSON.parse(localStorage.getItem('ftp_custom_shortcuts')) || [];
    custom.splice(index, 1);
    localStorage.setItem('ftp_custom_shortcuts', JSON.stringify(custom));
    renderizarAccesosRapidos();
    window.ps5Notification("ELIMINADO", "Acceso directo borrado.", "fas fa-trash");
}
