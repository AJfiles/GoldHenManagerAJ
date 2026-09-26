/**
 * ====================================================================
 * GOLD HEN SUITE PRO 🚀 - CONTROLADOR TRANSFERENCIAS Y RPI
 * Mantenido por AJ · Basado en proyecto original de SeBaS · RUTA: js/transferir.js
 * ====================================================================
 */

const CHUNK_SIZE = 1.5 * 1024 * 1024; 

let isTransferring = false;
let transferAbortController = null;
let currentXhr = null; // Para poder abortar el XHR activo

let colaDeArchivos = [];
let totalArchivosEnCola = 0;
let archivoActualBlob = null;
let archivoActualNombreFinal = "";
let transferenciaInicio = 0;

document.addEventListener("DOMContentLoaded", () => {
    const capaTrans = document.getElementById('layer-transferir');
    if (capaTrans) {
        const observador = new MutationObserver(() => {
            if (capaTrans.classList.contains('active')) { cambiarModoTransferencia('rpi'); }
        });
        observador.observe(capaTrans, { attributes: true, attributeFilter: ['class'] });
    }
});

function formatearTamanoBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function cambiarModoTransferencia(modo) {
    const tabFtp = document.getElementById('tab-trans-ftp');
    const tabRpi = document.getElementById('tab-trans-rpi');
    const vistaFtp = document.getElementById('vista-trans-ftp');
    const vistaRpi = document.getElementById('vista-trans-rpi');

    const claseActiva = "flex-1 py-2.5 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-black tracking-widest uppercase transition-all shadow-md";
    const claseInactiva = "flex-1 py-2.5 rounded-xl bg-transparent text-gray-500 border border-transparent text-[10px] font-black tracking-widest uppercase transition-all hover:text-white";

    if (modo === 'ftp') {
        tabFtp.className = claseActiva; tabRpi.className = claseInactiva;
        vistaFtp.classList.remove('hidden'); vistaFtp.classList.add('flex');
        vistaRpi.classList.remove('flex'); vistaRpi.classList.add('hidden');
    } else {
        tabRpi.className = claseActiva; tabFtp.className = claseInactiva;
        vistaRpi.classList.remove('hidden'); vistaRpi.classList.add('flex');
        vistaFtp.classList.remove('flex'); vistaFtp.classList.add('hidden');
        detectarIPCelular();
        const savedPs4 = localStorage.getItem('sebas_ip_final_libre') || '';
        const ps4Input = document.getElementById('rpi-console-ip');
        if (ps4Input && !ps4Input.value) ps4Input.value = savedPs4;
        escanearCarpetaRPI();
    }
}

// =======================================================
// RPI
// =======================================================
async function detectarIPCelular() {
    const inputIp = document.getElementById('rpi-phone-ip');
    inputIp.placeholder = "Detectando IP...";
    try {
        let fd = new FormData();
        fd.append('action', 'get_phone_ip');
        let res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        if (data.status === 'success' && data.ip) { inputIp.value = data.ip; } 
        else {
            inputIp.placeholder = "Escribe tu IP Local WiFi";
            if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                inputIp.value = window.location.hostname;
            }
        }
    } catch(e) {
        inputIp.placeholder = "Escribe tu IP Local WiFi";
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            inputIp.value = window.location.hostname;
        }
    }
}

async function detectarPS4ParaRPI() {
    const status = document.getElementById('rpi-radar-status');
    const ipInput = document.getElementById('rpi-console-ip');
    const portInput = document.getElementById('rpi-install-port');
    if (status) status.textContent = 'Buscando PS4 en la red…';
    try {
        const portFTP = parseInt(localStorage.getItem('sebas_port_libre') || '2121', 10);
        const response = await fetch(`api/radar_api.php?timeout=1200&port=${portFTP}&max_ips=254`);
        const data = await response.json();
        if (!response.ok || data.status !== 'success') throw new Error(data.message || 'No se pudo explorar la red.');
        if (!data.ps4_ips?.length) throw new Error(`No se encontró FTP en ${data.segmento}. Escribe la IP manualmente.`);
        const ip = data.ps4_ips[0];
        if (ipInput) ipInput.value = ip;
        if (portInput && !['12800', '12801'].includes(portInput.value)) portInput.value = '12800';
        localStorage.setItem('sebas_ip_final_libre', ip);
        if (typeof globalAppConfig !== 'undefined') globalAppConfig.ipConsola = ip;
        const mainIp = document.getElementById('ps-ip-full-input');
        if (mainIp) mainIp.value = ip;
        if (status) status.textContent = `Consola detectada: ${ip}`;
        window.ps5Notification('RADAR PS4', `Consola encontrada en ${ip}.`, 'fa-satellite-dish');
    } catch (error) {
        if (status) status.textContent = error.message || 'Radar no disponible';
        window.ps5Notification('RADAR PS4', error.message || 'No se encontró la consola.', 'fa-triangle-exclamation');
    }
}

async function escanearCarpetaRPI() {
    const container = document.getElementById('rpi-list-container');
    container.innerHTML = `<div class="w-full py-6 text-center text-cyan-400 opacity-50"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i><br><span class="text-[9px] font-bold uppercase tracking-widest">Escaneando user/pkgs_rpi...</span></div>`;

    try {
        let fd = new FormData();
        fd.append('action', 'scan_local_pkgs');
        let res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        let data = await res.json();

        if (data.status === 'success') {
            container.innerHTML = '';
            if (data.data.length === 0) {
                container.innerHTML = `<div class="w-full p-6 text-center border-2 border-dashed border-white/5 rounded-xl opacity-50"><i class="fa-solid fa-box-open text-2xl text-gray-500 mb-2"></i><br><span class="text-[9px] uppercase font-bold tracking-widest text-gray-500">Carpeta vacía.</span></div>`;
                return;
            }
            data.data.forEach(pkg => {
                let div = document.createElement('div');
                div.className = "w-full flex items-center justify-between p-3 bg-[#111827] rounded-xl border border-white/5 hover:border-cyan-500/30 transition-all";
                div.innerHTML = `
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center text-cyan-400 shrink-0 border border-cyan-500/20"><i class="fa-solid fa-box text-lg"></i></div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="text-[11px] font-black text-gray-200 uppercase truncate">${pkg.name}</span>
                            <span class="text-[9px] font-mono text-cyan-500">${formatearTamanoBytes(pkg.size)}</span>
                        </div>
                    </div>
                    <button onclick="instalarRPIDirecto('${pkg.name}')" class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center hover:bg-emerald-500/20 active:scale-90 border border-emerald-500/20 shrink-0 ml-2">
                        <i class="fa-solid fa-download"></i>
                    </button>`;
                container.appendChild(div);
            });
        }
    } catch(e) { container.innerHTML = `<div class="w-full py-4 text-center text-red-400 text-[10px] uppercase font-bold">Error al escanear</div>`; }
}

async function instalarRPIDirecto(nombrePkg) {
    const ps4Ip = document.getElementById('rpi-console-ip')?.value.trim();
    if (!ps4Ip) { window.ps5Notification("ERROR", "No hay PS4 conectada.", "fa-wifi"); return; }
    if (typeof validarEstructuraIP === 'function' && !validarEstructuraIP(ps4Ip)) { window.ps5Notification('ERROR', 'La IP de PS4 no es válida.', 'fa-triangle-exclamation'); return; }

    const phoneIp = document.getElementById('rpi-phone-ip').value.trim();
    if (!phoneIp || phoneIp === '127.0.0.1') { 
        window.ps5Notification("ERROR", "El servidor debe usar la IP de WiFi.", "fa-exclamation-triangle"); return; 
    }

    const serverPort = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
    if (window.location.protocol !== 'http:') { window.ps5Notification('ERROR RPI', 'El servidor local debe abrirse por HTTP para que la PS4 pueda descargar el PKG.', 'fa-triangle-exclamation'); return; }
    const rpiPort = parseInt(document.getElementById('rpi-install-port')?.value || '12800', 10);
    if (![12800, 12801].includes(rpiPort)) { window.ps5Notification('ERROR', 'Usa el puerto RPI 12800 o Nova 12801.', 'fa-triangle-exclamation'); return; }
    localStorage.setItem('sebas_ip_final_libre', ps4Ip);

    window.ps5Notification("RPI", "Enviando orden al instalador de PS4...", "fa-paper-plane");
    
    try {
        let fd = new FormData();
        fd.append('action', 'rpi_install');
        fd.append('host_ip', ps4Ip);
        fd.append('phone_ip', phoneIp);
        fd.append('server_port', serverPort);
        fd.append('filename', nombrePkg);
        fd.append('rpi_port', String(rpiPort));
        let res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        
        if (data.status === 'success') { 
            window.ps5Notification("¡ÉXITO!", "Instalación iniciada en tu PS4.", "fa-check"); 
        } 
        else { 
            window.ps5Notification("ERROR RPI", data.message, "fa-times"); 
        }
    } catch(e) { 
        window.ps5Notification("ERROR", "Fallo de comunicación RPI.", "fa-wifi"); 
    }
}

// =======================================================
// FTP INTELIGENTE (MULTISUBIDA Y COLISIONES)
// =======================================================

function prepararArchivoTransferencia(event) {
    colaDeArchivos = Array.from(event.target.files);
    totalArchivosEnCola = colaDeArchivos.length;
    
    if (totalArchivosEnCola === 0) return;
    document.getElementById('btn-limpiar-transferencia')?.classList.add('hidden');

    if(totalArchivosEnCola === 1) {
        document.getElementById('transfer-filename').innerText = colaDeArchivos[0].name;
    } else {
        document.getElementById('transfer-filename').innerText = `${totalArchivosEnCola} Archivos en Cola`;
    }
    document.getElementById('transfer-queue-status').innerText = "Listo para iniciar";

    document.getElementById('transfer-total').innerText = '0 B';
    document.getElementById('transfer-sent').innerText = '0 B';
    document.getElementById('transfer-percent').innerText = '0%';
    document.getElementById('transfer-bar').style.width = '0%';
    document.getElementById('transfer-speed').innerText = '0.00 MB/s';
    document.getElementById('transfer-eta').innerText = '--:--';
    const duracion = document.getElementById('transfer-duration');
    if (duracion) duracion.innerText = '00:00';

    const btn = document.getElementById('btn-iniciar-transferencia');
    btn.disabled = false;
    btn.className = "flex-1 py-4 rounded-[1.5rem] bg-gradient-to-r from-amber-600 to-yellow-500 text-black text-[12px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-[0_0_20px_rgba(245,158,11,0.4)] flex items-center justify-center gap-3";
}

function iniciarGestorDeCola() {
    if (colaDeArchivos.length === 0 || isTransferring) return;
    
    isTransferring = true;
    
    const btn = document.getElementById('btn-iniciar-transferencia');
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-lg"></i> Transfiriendo...`;
    btn.className = "flex-1 py-4 rounded-[1.5rem] bg-amber-900/50 text-amber-500 text-[12px] font-black uppercase tracking-widest flex items-center justify-center gap-3 cursor-not-allowed border border-amber-500/30";
    
    document.getElementById('btn-abortar-transferencia').classList.remove('hidden');

    procesarSiguienteEnLaCola();
}

function abortarTransferenciaActiva() {
    // Abortar el XHR actual si existe
    if (currentXhr) {
        currentXhr.abort();
        currentXhr = null;
    }
    if (transferAbortController) { transferAbortController.abort(); }
    isTransferring = false;
    colaDeArchivos = []; 
    
    window.ps5Notification("ABORTADO", "Cancelado por el usuario.", "fa-ban");
    
    document.getElementById('transfer-queue-status').innerText = "Transferencia Cancelada";
    document.getElementById('transfer-filename').innerText = "Archivo Abortado";
    resetTransferUI(false);
}

function procesarSiguienteEnLaCola() {
    if (colaDeArchivos.length === 0) {
        window.ps5Notification("FTP MULTIPLE", "Todos los archivos procesados.", "fa-check-double");
        if (typeof notificacionNavegador === 'function') notificacionNavegador('Transferencia terminada', 'Todos los archivos se enviaron a la PS4.');
        document.getElementById('transfer-queue-status').innerText = "Cola Finalizada";
        document.getElementById('transfer-filename').innerText = "Transferencia Exitosa";
        resetTransferUI(true);
        document.getElementById('btn-limpiar-transferencia')?.classList.remove('hidden');
        return;
    }

    archivoActualBlob = colaDeArchivos.shift();
    archivoActualNombreFinal = archivoActualBlob.name;

    const faltantes = totalArchivosEnCola - colaDeArchivos.length;
    document.getElementById('transfer-queue-status').innerText = `Subiendo ${faltantes} de ${totalArchivosEnCola}...`;
    document.getElementById('transfer-filename').innerText = archivoActualNombreFinal;

    verificarColisionConsola(archivoActualNombreFinal);
}

async function verificarColisionConsola(nombreAProbar) {
    const ip = localStorage.getItem('sebas_ip_final_libre');
    if (!ip) { window.ps5Notification("ERROR", "No hay IP conectada.", "fa-wifi"); resetTransferUI(); return; }

    let targetDir = document.getElementById('transfer-target-path').value.trim() || '/data/';
    if (!targetDir.endsWith('/')) targetDir += '/';

    try {
        let fd = new FormData();
        fd.append('action', 'check_exists');
        fd.append('host_ip', ip);
        fd.append('file_path', targetDir + nombreAProbar);

        let res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        let data = await res.json();

        if (data.status === 'success' && data.exists === true) {
            document.getElementById('colision-filename').innerText = nombreAProbar;
            document.getElementById('colision-input-rename').value = nombreAProbar;
            
            const modal = document.getElementById('modal-colision-ftp');
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('opacity-100'), 10);
        } else {
            ejecutarSubidaUnica();
        }
    } catch (e) {
        window.ps5Notification("ERROR", "Fallo al verificar el archivo en la consola.", "fa-bug");
        resetTransferUI();
    }
}

function accionColision(accion) {
    const modal = document.getElementById('modal-colision-ftp');
    modal.classList.remove('opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300);

    if (accion === 'omitir') {
        window.ps5Notification("OMITIDO", `Saltando ${archivoActualNombreFinal}...`, "fa-step-forward");
        procesarSiguienteEnLaCola();
    } 
    else if (accion === 'reemplazar') {
        ejecutarSubidaUnica(); 
    } 
    else if (accion === 'renombrar') {
        const nuevoNombre = document.getElementById('colision-input-rename').value.trim();
        if(nuevoNombre === "") {
            window.ps5Notification("ERROR", "Debes escribir un nombre válido.", "fa-times");
            procesarSiguienteEnLaCola(); return;
        }
        archivoActualNombreFinal = nuevoNombre;
        document.getElementById('transfer-filename').innerText = archivoActualNombreFinal;
        verificarColisionConsola(archivoActualNombreFinal);
    }
}

// =======================================================
// 🔥 NUEVA VERSIÓN CON XMLHttpRequest Y PROGRESO REAL
// =======================================================
async function ejecutarSubidaUnica() {
    const ip = localStorage.getItem('sebas_ip_final_libre');
    let targetDir = document.getElementById('transfer-target-path').value.trim() || '/data/';
    if (!targetDir.endsWith('/')) targetDir += '/';

    transferAbortController = new AbortController();
    const totalChunks = Math.ceil(archivoActualBlob.size / CHUNK_SIZE);
    let bytesSent = 0; // bytes ya enviados en chunks completos
    transferenciaInicio = Date.now();

    document.getElementById('transfer-total').innerText = formatearTamanoBytes(archivoActualBlob.size);

    for (let currentChunk = 0; currentChunk < totalChunks; currentChunk++) {
        if (transferAbortController.signal.aborted) {
            // Salir si se abortó
            return;
        }

        const start = currentChunk * CHUNK_SIZE;
        const end = Math.min(start + CHUNK_SIZE, archivoActualBlob.size);
        const chunkBlob = archivoActualBlob.slice(start, end);

        let fd = new FormData();
        fd.append('host_ip', ip);
        fd.append('chunk_index', currentChunk);
        fd.append('filename', archivoActualNombreFinal);
        fd.append('target_dir', targetDir);
        fd.append('file_chunk', chunkBlob, archivoActualNombreFinal);

        // Crear XHR para este chunk
        const xhr = new XMLHttpRequest();
        currentXhr = xhr; // guardar referencia para abortar
        xhr.open('POST', 'api/transferir_api.php', true);

        // Promesa para esperar la finalización del chunk
        const chunkPromise = new Promise((resolve, reject) => {
            xhr.onload = function() {
                currentXhr = null;
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (data.status === 'success') {
                            resolve();
                        } else {
                            reject(new Error(data.message || 'Error en el servidor'));
                        }
                    } catch (e) {
                        reject(new Error('Respuesta inválida del servidor'));
                    }
                } else {
                    reject(new Error(`HTTP ${xhr.status}`));
                }
            };
            xhr.onerror = function() {
                currentXhr = null;
                reject(new Error('Error de red'));
            };
            xhr.onabort = function() {
                currentXhr = null;
                reject(new Error('Abortado'));
            };
        });

        // Escuchar progreso de subida de este chunk
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                // bytes enviados en este chunk hasta ahora
                const chunkBytesSent = e.loaded;
                const totalSent = bytesSent + chunkBytesSent;
                // Actualizar UI con el total acumulado
                actualizarMetricas(totalSent, archivoActualBlob.size, Date.now(), chunkBytesSent);
            }
        };

        // Enviar el chunk
        xhr.send(fd);

        // Esperar a que termine el chunk
        try {
            await chunkPromise;
            // Chunk completado, sumar al total
            bytesSent += (end - start);
            // Actualizar una última vez para asegurar 100% si no llegó el evento
            actualizarMetricas(bytesSent, archivoActualBlob.size, Date.now(), 0);
        } catch (err) {
            if (err.message !== 'Abortado') {
                window.ps5Notification("ERROR FTP", err.message || "Fallo en la subida.", "fa-exclamation-triangle");
            }
            resetTransferUI();
            return;
        }
    }

    // Todos los chunks completados
    procesarSiguienteEnLaCola();
}

function actualizarMetricas(bytesSent, totalBytes, chunkStartTime, chunkSize) {
    const percent = ((bytesSent / totalBytes) * 100).toFixed(1);
    // Velocidad media del archivo completo: es estable incluso entre fragmentos.
    let speedMB = 0;
    const timeElapsed = (Date.now() - transferenciaInicio) / 1000;
    if (bytesSent > 0 && timeElapsed > 0) {
        const speedBytesPerSec = bytesSent / timeElapsed;
        speedMB = speedBytesPerSec / (1024 * 1024);
    }
    const speedText = speedMB > 0 ? `${speedMB.toFixed(2)} MB/s` : 'Calculando...';

    const bytesRemaining = totalBytes - bytesSent;
    const secondsRemaining = speedMB > 0 ? Math.round(bytesRemaining / (speedMB * 1024 * 1024)) : 0;
    let etaString = "--:--";
    if (secondsRemaining > 0 && isFinite(secondsRemaining)) {
        const m = Math.floor(secondsRemaining / 60).toString().padStart(2, '0');
        const s = (secondsRemaining % 60).toString().padStart(2, '0');
        etaString = `${m}:${s}`;
    }

    document.getElementById('transfer-percent').innerText = `${percent}%`;
    document.getElementById('transfer-bar').style.width = `${percent}%`;
    document.getElementById('transfer-sent').innerText = formatearTamanoBytes(bytesSent);
    document.getElementById('transfer-speed').innerText = speedText;
    document.getElementById('transfer-eta').innerText = etaString;
    const duracion = document.getElementById('transfer-duration');
    if (duracion) {
        const segundos = Math.floor(timeElapsed || 0);
        duracion.innerText = `${Math.floor(segundos / 60).toString().padStart(2, '0')}:${(segundos % 60).toString().padStart(2, '0')}`;
    }
}

function resetTransferUI(success = false) {
    isTransferring = false;
    currentXhr = null;
    document.getElementById('input-archivo-pesado').value = '';

    const btn = document.getElementById('btn-iniciar-transferencia');
    const btnAbort = document.getElementById('btn-abortar-transferencia');
    
    btnAbort.classList.add('hidden'); 

    if (success) {
        btn.innerHTML = `<i class="fa-solid fa-check"></i> Transferencia Exitosa`;
        btn.className = "flex-1 py-4 rounded-[1.5rem] bg-emerald-600 text-black text-[12px] font-black uppercase tracking-widest flex items-center justify-center gap-3 cursor-not-allowed";
    } else {
        btn.innerHTML = `<i class="fa-solid fa-rocket"></i> Enviar por FTP`;
        btn.disabled = false;
        btn.className = "flex-1 py-4 rounded-[1.5rem] bg-gradient-to-r from-amber-600 to-yellow-500 text-black text-[12px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-[0_0_20px_rgba(245,158,11,0.4)] flex items-center justify-center gap-3";
    }
}

function limpiarEstadoTransferencia() {
    colaDeArchivos = [];
    totalArchivosEnCola = 0;
    archivoActualBlob = null;
    archivoActualNombreFinal = '';
    const fileInput = document.getElementById('input-archivo-pesado');
    if (fileInput) fileInput.value = '';
    document.getElementById('transfer-filename').innerText = 'Elegir Archivos...';
    document.getElementById('transfer-queue-status').innerText = 'Cola de Envío Vacía';
    document.getElementById('transfer-total').innerText = '0 B';
    document.getElementById('transfer-sent').innerText = '0 B';
    document.getElementById('transfer-percent').innerText = '0%';
    document.getElementById('transfer-bar').style.width = '0%';
    document.getElementById('transfer-speed').innerText = '0.00 MB/s';
    document.getElementById('transfer-eta').innerText = '--:--';
    document.getElementById('transfer-duration').innerText = '00:00';
    document.getElementById('btn-limpiar-transferencia').classList.add('hidden');
    const button = document.getElementById('btn-iniciar-transferencia');
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-rocket"></i> Enviar por FTP';
    button.className = 'flex-1 py-4 rounded-[1.5rem] bg-gray-800 text-gray-500 text-[12px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-3 pointer-events-none';
}

// =======================================================
// 🔥 EXPLORADOR FTP INTERACTIVO (CORREGIDO FINAL)
// =======================================================

window.exploradorRutaActual = "/";

function abrirExploradorFTP() {
    var ps4Ip = localStorage.getItem('sebas_ip_final_libre');
    if (!ps4Ip) { window.ps5Notification("ERROR", "No hay conexión FTP.", "fa-wifi"); return; }

    window.exploradorRutaActual = '/'; 
    
    var modal = document.getElementById('modal-explorador-ftp');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(function() {
            modal.classList.add('opacity-100');
            var content = document.getElementById('modal-content-explorador');
            if (content) content.classList.remove('translate-y-full');
        }, 10);
    }
    cargarCarpetasFTP(window.exploradorRutaActual);
}

function cerrarExploradorFTP() {
    var modal = document.getElementById('modal-explorador-ftp');
    var content = document.getElementById('modal-content-explorador');
    if (content) content.classList.add('translate-y-full');
    if (modal) {
        modal.classList.remove('opacity-100');
        setTimeout(function() { modal.classList.add('hidden'); }, 300);
    }
}

async function cargarCarpetasFTP(ruta) {
    var ip = localStorage.getItem('sebas_ip_final_libre');
    var lista = document.getElementById('explorador-lista');
    if (!lista) return;

    var lblRuta = document.getElementById('explorador-ruta-actual');
    if (lblRuta) lblRuta.innerText = ruta;

    lista.innerHTML = '<div class="w-full py-10 text-center text-amber-500 opacity-50"><i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i><br><span class="text-[10px] font-bold uppercase tracking-widest">Conectando...</span></div>';

    try {
        var fd = new FormData();
        fd.append('action', 'list_ftp_dirs');
        fd.append('host_ip', ip);
        fd.append('path', ruta);

        var res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        var data = await res.json();

        if (data.status === 'success') {
            lista.innerHTML = '';
            
            if (ruta !== '/' && ruta !== '') {
                var btnBack = document.createElement('div');
                btnBack.className = "flex items-center gap-3 p-3 bg-amber-500/10 rounded-xl cursor-pointer hover:bg-amber-500/20 active:scale-95 transition-all border border-amber-500/20 shrink-0";
                btnBack.onclick = function() { subirNivelCarpetaFTP(); };
                btnBack.innerHTML = '<div class="w-8 h-8 rounded-lg bg-black/40 flex items-center justify-center text-amber-400"><i class="fa-solid fa-level-up-alt"></i></div> <span class="text-[11px] font-black text-amber-400 uppercase tracking-widest">NIVEL ANTERIOR</span>';
                lista.appendChild(btnBack);
            }

            if (data.dirs.length === 0) {
                var divVacia = document.createElement('div');
                divVacia.className = "text-center py-10 opacity-50";
                divVacia.innerHTML = '<i class="fa-solid fa-folder-open text-3xl text-gray-500 mb-3"></i><br><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Carpeta Vacía</span>';
                lista.appendChild(divVacia);
            } else {
                data.dirs.forEach(function(dir) {
                    var div = document.createElement('div');
                    div.className = "flex items-center gap-3 p-3 bg-black/30 rounded-xl cursor-pointer hover:border-amber-500/50 hover:bg-amber-500/5 active:scale-95 transition-all border border-white/5 shrink-0";
                    div.onclick = function() { navegarAdentroCarpetaFTP(dir); };
                    div.innerHTML = '<i class="fa-solid fa-folder text-gray-500 text-lg group-hover:text-amber-500 transition-colors"></i> <span class="text-[11px] font-bold text-gray-300 uppercase truncate">' + dir + '</span>';
                    lista.appendChild(div);
                });
            }
        } else {
            lista.innerHTML = '<div class="text-center py-10"><span class="text-red-400 text-[10px] font-bold uppercase tracking-widest">Error al leer consola</span></div>';
        }
    } catch (e) {
        lista.innerHTML = '<div class="text-center py-10"><span class="text-red-400 text-[10px] font-bold uppercase tracking-widest">Se perdió conexión</span></div>';
    }
}

function navegarAdentroCarpetaFTP(carpeta) {
    window.exploradorRutaActual += carpeta + '/';
    cargarCarpetasFTP(window.exploradorRutaActual);
}

function subirNivelCarpetaFTP() {
    var partes = window.exploradorRutaActual.split('/');
    var partesLimpias = [];
    
    for(var i = 0; i < partes.length; i++) {
        if(partes[i] !== '') {
            partesLimpias.push(partes[i]);
        }
    }
    
    partesLimpias.pop(); 
    
    if (partesLimpias.length === 0) {
        window.exploradorRutaActual = '/'; 
    } else {
        window.exploradorRutaActual = '/' + partesLimpias.join('/') + '/';
    }
    
    cargarCarpetasFTP(window.exploradorRutaActual);
}

function confirmarRutaFTP() {
    var inputRuta = document.getElementById('transfer-target-path');
    if (inputRuta) inputRuta.value = window.exploradorRutaActual;
    cerrarExploradorFTP();
    window.ps5Notification("SISTEMA", "Ruta de destino fijada.", "fa-crosshairs");
}
