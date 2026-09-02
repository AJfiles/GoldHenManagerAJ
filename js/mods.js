/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 - LÓGICA MOTOR MINECRAFT
 * DEVELOPED By SeBaS - RUTA: js/mods.js
 * ====================================================================
 */

const MOD_CHUNK_SIZE = 1.5 * 1024 * 1024;
let archivosEnEnsamblador = [];
let ensambladorTargetFolder = "";
let moddingUploadAbortController = null;

// Obtener el ID del juego desde el Enrutador Global
function getCusaMinecraft() {
    return localStorage.getItem('mods_global_cusa');
}

function lanzarNotificacionMods(tag, mensaje, icono) {
    const panel = document.getElementById('notif-ps5-mods');
    if(!panel) return;
    document.getElementById('notif-tag-mods').innerText = tag;
    document.getElementById('notif-msg-mods').innerText = mensaje;
    document.getElementById('notif-icon-mods').innerHTML = `<i class="fas ${icono}"></i>`;

    panel.classList.remove('-translate-y-32', 'opacity-0');
    panel.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
        panel.classList.remove('translate-y-0', 'opacity-100');
        panel.classList.add('-translate-y-32', 'opacity-0');
    }, 4000);
}

// ==========================================
// ESCÁNER DE BÓVEDA MINECRAFT
// ==========================================
window.escanearBovedaMods = async function() {
    const ip = localStorage.getItem('sebas_ip_final_libre') || '192.168.1.28';
    const cusa = getCusaMinecraft();
    const container = document.getElementById('lista-boveda-mods');
    if (!ip || !cusa || !container) return;

    container.innerHTML = `<div class="w-full py-6 text-center text-indigo-400 opacity-50"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br><span class="text-[9px] font-bold uppercase tracking-widest">Leyendo Bóveda en PS4...</span></div>`;

    try {
        let fd = new FormData();
        fd.append('action', 'listar_boveda');
        fd.append('host_ip', ip);
        fd.append('cusa', cusa);

        let res = await fetch('api/mods_api.php', { method: 'POST', body: fd });
        let rawText = await res.text();
        let data;
        
        try { data = JSON.parse(rawText); } catch(err) { throw new Error("Respuesta inválida. Verifica la red."); }

        if (data.status === 'success') {
            container.innerHTML = '';
            if (data.data.length === 0) {
                container.innerHTML = `<div class="w-full flex flex-col items-center justify-center h-[80px] opacity-40 border border-dashed border-white/10 rounded-xl bg-black/20"><i class="fas fa-folder-open text-xl text-gray-500 mb-1"></i><span class="text-[9px] uppercase font-bold tracking-widest text-gray-400">Bóveda vacía</span></div>`;
                return;
            }

            data.data.forEach(mod => {
                localStorage.setItem(`mod_reg_${cusa}_${mod.name}`, mod.files_registry);
                
                let isChecked = localStorage.getItem(`mod_state_${cusa}_${mod.id}`) === 'true';
                let checkAttr = isChecked ? 'checked' : '';
                let badgeClass = isChecked ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/20' : 'bg-indigo-500/20 text-indigo-400 border-indigo-500/20';
                let badgeText = isChecked ? 'Activo en Consola' : 'Guardado en Bóveda';
                let rutaText = isChecked ? `<span class='text-emerald-400 font-mono'>En app_tmp/.../</span>` : `<span class='text-indigo-400 font-mono'>/Mods_Vault/</span>`;

                const div = document.createElement('div');
                div.className = "w-full flex flex-col p-3 bg-[#111827] rounded-xl border border-white/5 shadow-lg animate-fade-in";
                div.innerHTML = `
                    <div class="flex items-center justify-between overflow-hidden">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 shrink-0 border border-indigo-500/20">
                                <i class="fas ${mod.file_count > 1 ? 'fa-layer-group' : 'fa-file-code'} text-lg"></i>
                            </div>
                            <div class="flex flex-col overflow-hidden">
                                <span class="text-[12px] font-black text-gray-200 uppercase truncate">${mod.name}</span>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span id="badge-estado-${mod.id}" class="px-1.5 py-0.5 rounded text-[7.5px] font-mono font-black uppercase ${badgeClass}">${badgeText}</span>
                                </div>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 ml-2">
                            <input type="checkbox" class="sr-only peer" onchange="conmutarModConsola('${mod.name}', '${mod.id}', this, ${mod.file_count})" ${checkAttr}>
                            <div class="w-11 h-6 bg-gray-800 border border-white/5 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600 peer-checked:after:bg-white"></div>
                        </label>
                    </div>
                    <div class="mt-3 pt-2 border-t border-white/5 flex justify-between items-center text-[8px] font-mono text-gray-500 uppercase tracking-wider">
                        <span>${mod.file_count} Archivo(s) en PS4</span>
                        <span id="ruta-dinamica-${mod.id}" class="text-indigo-400 font-bold">${rutaText}</span>
                    </div>
                `;
                container.appendChild(div);
            });
        }
    } catch(e) {
        container.innerHTML = `<div class="text-center py-4"><span class="text-red-400 text-[10px] font-bold uppercase tracking-widest">Fallo de lectura: ${e.message}</span></div>`;
    }
};

// ==========================================
// ENSAMBLADOR MODAL
// ==========================================
window.abrirEnsambladorMod = function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    document.getElementById('input-nombre-mod').value = '';
    archivosEnEnsamblador = [];
    actualizarListaEnsamblador();
    
    const modal = document.getElementById('modal-ensamblador');
    const content = document.getElementById('modal-content-ensamblador');
    if(modal && content) {
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.add('opacity-100'); content.classList.remove('translate-y-full'); }, 10);
    }
};

window.cerrarEnsambladorMod = function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const modal = document.getElementById('modal-ensamblador');
    const content = document.getElementById('modal-content-ensamblador');
    if(modal && content) {
        content.classList.add('translate-y-full');
        modal.classList.remove('opacity-100');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
};

window.triggerFileSelect = function(folderName, type) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    ensambladorTargetFolder = folderName;
    if (type === 'folder') {
        document.getElementById('input-carpeta-ensamblador').click();
    } else {
        document.getElementById('input-archivo-ensamblador').click();
    }
};

window.procesarArchivoEnsamblador = function(event) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;
    files.forEach(file => {
        archivosEnEnsamblador.push({ id: Date.now() + Math.random(), name: file.name, folder: ensambladorTargetFolder, fileRef: file });
    });
    actualizarListaEnsamblador();
    event.target.value = '';
}

window.procesarCarpetaEnsamblador = function(event) {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;
    files.forEach(file => {
        archivosEnEnsamblador.push({ id: Date.now() + Math.random(), name: file.webkitRelativePath || file.name, folder: ensambladorTargetFolder, fileRef: file });
    });
    actualizarListaEnsamblador();
    event.target.value = '';
}

window.eliminarArchivoEnsamblador = function(id) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    archivosEnEnsamblador = archivosEnEnsamblador.filter(f => f.id !== id);
    actualizarListaEnsamblador();
}

function actualizarListaEnsamblador() {
    const container = document.getElementById('lista-archivos-ensamblador');
    const btnGuardar = document.getElementById('btn-guardar-ensamble');
    if (!container || !btnGuardar) return;

    container.innerHTML = '';
    
    if (archivosEnEnsamblador.length === 0) {
        container.innerHTML = `<div id="ensamblador-vacio" class="w-full py-6 text-center opacity-40 border border-dashed border-white/10 rounded-xl bg-black/20"><span class="text-[9px] uppercase font-bold tracking-widest text-gray-500">Ningún archivo agregado</span></div>`;
        btnGuardar.disabled = true;
        btnGuardar.className = "w-full py-4 mt-2 rounded-xl bg-gray-800 text-gray-500 text-[12px] font-black tracking-widest uppercase transition-all flex items-center justify-center gap-2 pointer-events-none shrink-0 border border-transparent";
        return;
    }

    archivosEnEnsamblador.forEach(f => {
        const div = document.createElement('div');
        div.className = "flex items-center justify-between p-3 bg-black/40 border border-white/5 rounded-xl animate-fade-in";
        div.innerHTML = `
            <div class="flex items-center gap-3 overflow-hidden flex-1">
                <i class="fas fa-file-code text-indigo-400 text-lg"></i>
                <div class="flex flex-col overflow-hidden">
                    <span class="text-[11px] font-black text-gray-200 uppercase truncate">${f.name}</span>
                    <span class="text-[8.5px] font-mono text-cyan-400 uppercase tracking-widest mt-0.5"><i class="fas fa-arrow-right text-[7px] mr-1"></i> ${f.folder}</span>
                </div>
            </div>
            <button onclick="eliminarArchivoEnsamblador(${f.id})" class="text-red-400 hover:text-red-300 ml-2 active:scale-90 w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0 border border-red-500/20"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(div);
    });

    btnGuardar.disabled = false;
    btnGuardar.className = "w-full py-4 mt-2 rounded-xl bg-indigo-600 text-[12px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_20px_rgba(99,102,241,0.4)] flex items-center justify-center gap-2 shrink-0 border border-transparent cursor-pointer pointer-events-auto";
}

// ==========================================
// TRANSFERENCIA FTP DEL ENSAMBLADOR CON PROGRESO
// ==========================================
window.confirmarEnsambleMod = async function() {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const nombre = document.getElementById('input-nombre-mod').value.trim();
    if (!nombre) { lanzarNotificacionMods("ERROR", "Ponle un identificador al Mod", "fa-times"); return; }
    
    const ip = localStorage.getItem('sebas_ip_final_libre');
    const cusa = getCusaMinecraft();
    if (!ip || !cusa) return;

    const btn = document.getElementById('btn-guardar-ensamble');
    const closeBtn = document.getElementById('btn-cerrar-ensamblador');
    
    btn.disabled = true;
    if(closeBtn) closeBtn.style.pointerEvents = 'none';
    
    moddingUploadAbortController = new AbortController();

    try {
        for (let i = 0; i < archivosEnEnsamblador.length; i++) {
            const archivoData = archivosEnEnsamblador[i];
            const file = archivoData.fileRef; 
            const totalChunks = Math.ceil(file.size / MOD_CHUNK_SIZE);
            
            for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                if (moddingUploadAbortController.signal.aborted) throw new Error("Abortado por usuario");

                let percent = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                btn.className = "w-full py-4 mt-2 rounded-xl bg-indigo-900 text-[11px] font-black tracking-widest uppercase text-indigo-300 transition-all shadow-inner flex items-center justify-center gap-2 shrink-0 cursor-not-allowed border border-indigo-500/30";
                btn.innerHTML = `<i class="fas fa-spinner fa-spin text-lg text-indigo-400"></i> Archivo ${i+1} de ${archivosEnEnsamblador.length} - ${percent}%`;

                const start = chunkIndex * MOD_CHUNK_SIZE;
                const end = Math.min(start + MOD_CHUNK_SIZE, file.size);
                const chunkBlob = file.slice(start, end);

                let fd = new FormData();
                fd.append('action', 'upload_chunk');
                fd.append('host_ip', ip);
                fd.append('cusa', cusa);
                fd.append('mod_name', nombre);
                fd.append('target_folder', archivoData.folder);
                fd.append('filename', archivoData.name);
                fd.append('chunk_index', chunkIndex);
                fd.append('file_chunk', chunkBlob, archivoData.name);

                let res = await fetch('api/mods_api.php', { method: 'POST', body: fd, signal: moddingUploadAbortController.signal });
                let rawText = await res.text();
                let data;
                try { data = JSON.parse(rawText); } catch(e) { throw new Error("Fallo en el servidor"); }

                if (data.status !== 'success') throw new Error(data.message);
            }
        }
        
        cerrarEnsambladorMod();
        lanzarNotificacionMods("ÉXITO", "Archivos alojados a salvo dentro de la bóveda en PS4.", "fa-check-circle");
        escanearBovedaMods(); 

    } catch (err) {
        lanzarNotificacionMods("ERROR", err.message || "Fallo en la subida", "fa-exclamation-triangle");
    } finally {
        if(closeBtn) closeBtn.style.pointerEvents = 'auto';
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-cloud-upload-alt text-lg"></i> Subir a Bóveda en PS4`;
        btn.className = "w-full py-4 mt-2 rounded-xl bg-indigo-600 text-[12px] font-black tracking-widest uppercase text-white active:scale-95 transition-all shadow-[0_0_20px_rgba(99,102,241,0.4)] flex items-center justify-center gap-2 shrink-0 border border-transparent";
    }
};

// ==========================================
// INTERRUPTOR: COPIA DESDE BÓVEDA A APP_TMP 
// ==========================================
window.conmutarModConsola = async function(modName, modId, checkbox, countFiles) {
    if(typeof emitirEfectoSonidoNativo === 'function') emitirEfectoSonidoNativo('click');
    const badge = document.getElementById(`badge-estado-${modId}`);
    const textRuta = document.getElementById(`ruta-dinamica-${modId}`);
    const ip = localStorage.getItem('sebas_ip_final_libre');
    const cusa = getCusaMinecraft();
    
    const estado = checkbox.checked ? 'activar' : 'desactivar';
    const registroArchivosStr = localStorage.getItem(`mod_reg_${cusa}_${modName}`);

    if (!registroArchivosStr || registroArchivosStr === '[]') {
        lanzarNotificacionMods("ERROR", "El registro del mod está corrupto.", "fa-times");
        checkbox.checked = !checkbox.checked;
        return;
    }

    const modalCarga = document.getElementById('modal-cargando-switch');
    const tituloCarga = document.getElementById('switch-titulo-cargando');
    const txtArchivos = document.getElementById('switch-file-count');
    const iconCarga = document.getElementById('switch-icon-cargando');

    checkbox.disabled = true;

    try {
        if (estado === 'activar') {
            tituloCarga.innerText = "Inyectando Mod";
            tituloCarga.className = "text-[16px] font-black tracking-widest text-emerald-400 uppercase mb-2";
            iconCarga.className = "fas fa-bolt text-2xl text-emerald-400 animate-pulse";
            txtArchivos.innerText = `Copiando ${countFiles} archivo(s) a app_tmp...`;
        } else {
            tituloCarga.innerText = "Retirando Mod";
            tituloCarga.className = "text-[16px] font-black tracking-widest text-indigo-400 uppercase mb-2";
            iconCarga.className = "fas fa-shield-alt text-2xl text-indigo-400 animate-pulse";
            txtArchivos.innerText = `Borrando ${countFiles} archivo(s) de la consola...`;
        }
        
        if (modalCarga) {
            modalCarga.classList.remove('hidden');
            setTimeout(() => modalCarga.classList.add('opacity-100'), 10);
        }

        let fd = new FormData();
        fd.append('action', 'conmutar_mod');
        fd.append('host_ip', ip);
        fd.append('cusa', cusa);
        fd.append('mod_name', modName);
        fd.append('estado', estado);
        fd.append('archivos', registroArchivosStr);

        let res = await fetch('api/mods_api.php', { method: 'POST', body: fd });
        let rawText = await res.text();
        let data;
        try { data = JSON.parse(rawText); } catch(e) { throw new Error("Servidor no respondió. Reintenta."); }

        if (data.status === 'success') {
            localStorage.setItem(`mod_state_${cusa}_${modId}`, estado === 'activar' ? 'true' : 'false');

            if (estado === 'activar') {
                badge.className = "px-1.5 py-0.5 rounded text-[7.5px] font-mono font-black uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/20";
                badge.innerText = "Activo en Consola";
                textRuta.innerHTML = `<span class='text-emerald-400 font-mono'>En app_tmp/.../</span>`;
                setTimeout(() => lanzarNotificacionMods("¡LISTO!", "Mod inyectado exitosamente. ¡Reinicia el juego!", "fa-sync-alt"), 500);
            } else {
                badge.className = "px-1.5 py-0.5 rounded text-[7.5px] font-mono font-black uppercase bg-indigo-500/20 text-indigo-400 border border-indigo-500/20";
                badge.innerText = "Guardado en Bóveda";
                textRuta.innerHTML = "<span class='text-indigo-400 font-mono'>/Mods_Vault/</span>";
                setTimeout(() => lanzarNotificacionMods("ÉXITO", "Archivos retirados con seguridad.", "fa-check-circle"), 500);
            }
        } else {
            throw new Error(data.message);
        }
    } catch (e) {
        lanzarNotificacionMods("ERROR", e.message || "Fallo FTP de Termux", "fa-triangle-exclamation");
        checkbox.checked = !checkbox.checked; 
    } finally {
        if (modalCarga) {
            modalCarga.classList.remove('opacity-100');
            setTimeout(() => modalCarga.classList.add('hidden'), 300);
        }
        checkbox.disabled = false;
    }
};
