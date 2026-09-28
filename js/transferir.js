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
let rpiPhoneIpManual = false;
let rpiLogLines = [];
let rpiDetectedPort = null;

function registrarLogRPI(message, level = 'info') {
    const stamp = new Date().toLocaleTimeString();
    const entry = `[${stamp}] [${level.toUpperCase()}] ${String(message)}`;
    rpiLogLines.push(entry);
    if (rpiLogLines.length > 120) rpiLogLines = rpiLogLines.slice(-120);
    const terminal = document.getElementById('rpi-log-terminal');
    if (!terminal) return;
    const line = document.createElement('div');
    line.className = level === 'error' ? 'text-red-300' : (level === 'success' ? 'text-emerald-300' : 'text-gray-300');
    line.textContent = entry;
    terminal.appendChild(line);
    terminal.scrollTop = terminal.scrollHeight;
}

function limpiarLogRPI() {
    rpiLogLines = [];
    const terminal = document.getElementById('rpi-log-terminal');
    if (terminal) terminal.replaceChildren();
}

async function copiarLogRPI() {
    const text = rpiLogLines.join('\n') || 'GoldHEN Manager: sin eventos RPI registrados.';
    try {
        await navigator.clipboard.writeText(text);
    } catch (_) {
        const field = document.createElement('textarea');
        field.value = text; field.style.position = 'fixed'; field.style.opacity = '0';
        document.body.appendChild(field); field.select(); document.execCommand('copy'); field.remove();
    }
    window.ps5Notification('REGISTRO RPI', 'Mensajes copiados al portapapeles.', 'fa-copy');
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('rpi-console-ip')?.addEventListener('change', () => detectarIPCelular());
    document.getElementById('rpi-phone-ip')?.addEventListener('input', () => { rpiPhoneIpManual = true; });
    const capaTrans = document.getElementById('layer-transferir');
    if (capaTrans) {
        const observador = new MutationObserver(() => {
            if (capaTrans.classList.contains('active')) { cambiarModoTransferencia('rpi'); }
        });
        observador.observe(capaTrans, { attributes: true, attributeFilter: ['class'] });
    }
    document.getElementById('rpi-install-port')?.addEventListener('change', () => { rpiDetectedPort = null; });
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
        const savedPs4 = localStorage.getItem('sebas_ip_final_libre') || '';
        const ps4Input = document.getElementById('rpi-console-ip');
        if (ps4Input && !ps4Input.value) ps4Input.value = savedPs4;
        detectarIPCelular();
        escanearCarpetaRPI();
    }
}

// =======================================================
// RPI
// =======================================================
async function detectarIPCelular() {
    const inputIp = document.getElementById('rpi-phone-ip');
    if (!inputIp) return;
    rpiPhoneIpManual = false;
    inputIp.placeholder = "Detectando IP...";
    try {
        let fd = new FormData();
        fd.append('action', 'get_phone_ip');
        fd.append('host_ip', document.getElementById('rpi-console-ip')?.value.trim() || '');
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

function cambiarOrigenRPI() {
    escanearCarpetaRPI();
}

async function comprobarPuertoRPI(ip, port) {
    const fd = new FormData();
    fd.append('action', 'check_rpi_port');
    fd.append('host_ip', ip);
    fd.append('rpi_port', String(port));
    try {
        const response = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        const data = await response.json();
        return { ok: data.status === 'success' && data.open === true, message: data.message || 'Abre Package Installer en primer plano y prueba 12800 o 12801.' };
    } catch (_) {
        return { ok: false, message: 'No se pudo comprobar el puerto RPI.' };
    }
}

async function detectarPS4ParaRPI() {
    const status = document.getElementById('rpi-radar-status');
    const ipInput = document.getElementById('rpi-console-ip');
    const portInput = document.getElementById('rpi-install-port');
    rpiDetectedPort = null;
    if (status) status.textContent = 'Buscando PS4 y puertos RPI…';
    registrarLogRPI('Radar RPI iniciado; se prueban interfaces LAN y puertos 12800/12801.');
    try {
        const preference = portInput?.value || 'auto';
        const response = await fetch('api/radar_api.php?timeout=8000&port=12800');
        const data = await response.json();
        if (!response.ok || data.status !== 'success') throw new Error(data.message || 'No se pudo explorar la red.');
        registrarLogRPI(`Interfaces: ${data.local_ips?.join(', ') || data.local_ip}. Subred: ${data.segmento}.`);
        const candidates = (data.devices || []).slice().sort((a, b) => {
            const hasRpiA = (a.ports || []).some(port => [12800, 12801].includes(Number(port)));
            const hasRpiB = (b.ports || []).some(port => [12800, 12801].includes(Number(port)));
            return Number(hasRpiB) - Number(hasRpiA);
        });
        let ip = '';
        let foundPort = 0;
        let ftpPort = 0;
        for (const device of candidates) {
            const knownRpi = (device.ports || []).map(Number).filter(port => [12800, 12801].includes(port));
            const order = preference === 'auto' ? [12801, 12800] : [Number(preference)];
            for (const port of order) {
                if (knownRpi.includes(port) || (await comprobarPuertoRPI(device.ip, port)).ok) {
                    ip = device.ip; foundPort = port; break;
                }
            }
            if (!ip) {
                const detectedFtp = (device.ports || []).map(Number).find(port => [2121, 2122].includes(port));
                if (detectedFtp) { ip = device.ip; ftpPort = detectedFtp; }
            }
            if (foundPort) break;
        }
        if (!ip) throw new Error(`No se halló un servicio en ${data.segmento}. Abre Package Installer y vuelve a escanear.`);
        if (ipInput) ipInput.value = ip;
        localStorage.setItem('sebas_ip_final_libre', ip);
        if (typeof globalAppConfig !== 'undefined') globalAppConfig.ipConsola = ip;
        const mainIp = document.getElementById('ps-ip-full-input');
        if (mainIp) mainIp.value = ip;
        await detectarIPCelular();
        if (foundPort) {
            rpiDetectedPort = foundPort;
            registrarLogRPI(`RPI disponible en ${ip}:${foundPort}.`, 'success');
            if (status) status.textContent = `RPI detectado: ${ip}:${foundPort}`;
            window.ps5Notification('RADAR PS4', `RPI respondió en ${ip}:${foundPort}.`, 'fa-satellite-dish');
        } else {
            registrarLogRPI(`FTP encontrado en ${ip}:${ftpPort || globalAppConfig.portFTP}; Package Installer no respondió en el puerto elegido.`, 'error');
            if (status) status.textContent = `PS4 ${ip} · RPI no disponible`;
            window.ps5Notification('PS4 DETECTADA', `Encontré ${ip} por FTP. Abre Package Installer para iniciar RPI.`, 'fa-triangle-exclamation');
        }
    } catch (error) {
        registrarLogRPI(error.message || 'Fallo del Radar RPI.', 'error');
        if (status) status.textContent = error.message || 'Radar no disponible';
        window.ps5Notification('RADAR PS4', error.message || 'No se encontró la consola.', 'fa-triangle-exclamation');
    }
}

async function escanearCarpetaRPI() {
    const container = document.getElementById('rpi-list-container');
    if (!container) return;
    const source = document.getElementById('rpi-package-source')?.value || 'pkgs_rpi';
    registrarLogRPI(`Buscando archivos .pkg en ${source === 'pkgs_rpi' ? 'user/pkgs_rpi' : source === 'downloads' ? 'Descargas' : 'ambas ubicaciones'}.`);
    container.innerHTML = `<div class="w-full py-6 text-center text-cyan-400 opacity-50"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i><br><span class="text-[9px] font-bold uppercase tracking-widest">Escaneando PKG...</span></div>`;

    try {
        let fd = new FormData();
        fd.append('action', 'scan_local_pkgs');
        fd.append('source', document.getElementById('rpi-package-source')?.value || 'pkgs_rpi');
        let res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        let data = await res.json();

        if (!res.ok || data.status !== 'success') throw new Error(data.message || 'No se pudo escanear la carpeta.');
        container.innerHTML = '';
        registrarLogRPI(`${data.data?.length || 0} PKG encontrados.`);
        if (!data.data?.length) {
            container.innerHTML = `<div class="w-full p-6 text-center border-2 border-dashed border-white/5 rounded-xl opacity-50"><i class="fa-solid fa-box-open text-2xl text-gray-500 mb-2"></i><br><span class="text-[9px] uppercase font-bold tracking-widest text-gray-500">No se encontraron archivos PKG legibles.</span></div>`;
            return;
        }
        data.data.forEach(pkg => {
            const row = document.createElement('div');
            row.className = 'w-full flex items-center justify-between p-3 bg-[#111827] rounded-xl border border-white/5 hover:border-cyan-500/30 transition-all';
            const info = document.createElement('div');
            info.className = 'flex items-center gap-3 overflow-hidden';
            const icon = document.createElement('div');
            icon.className = 'w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center text-cyan-400 shrink-0 border border-cyan-500/20';
            icon.innerHTML = '<i class="fa-solid fa-box text-lg"></i>';
            const text = document.createElement('div');
            text.className = 'flex flex-col overflow-hidden';
            const name = document.createElement('span');
            name.className = 'text-[11px] font-black text-gray-200 uppercase truncate';
            name.textContent = pkg.name;
            const meta = document.createElement('span');
            meta.className = 'text-[9px] font-mono text-cyan-500';
            meta.textContent = `${pkg.source_label} · ${formatearTamanoBytes(pkg.size)}`;
            text.append(name, meta);
            info.append(icon, text);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center hover:bg-emerald-500/20 active:scale-90 border border-emerald-500/20 shrink-0 ml-2';
            button.setAttribute('aria-label', `Instalar ${pkg.name}`);
            button.innerHTML = '<i class="fa-solid fa-download"></i>';
            button.addEventListener('click', () => instalarRPIDirecto(pkg.id, pkg.name, pkg.size));
            row.append(info, button);
            container.appendChild(row);
        });
    } catch(e) { registrarLogRPI(e.message || 'Error al escanear los PKG.', 'error'); const error = document.createElement('div'); error.className = 'w-full py-4 text-center text-red-400 text-[10px] uppercase font-bold'; error.textContent = e.message || 'Error al escanear'; container.replaceChildren(error); }
}

async function instalarRPIDirecto(fileId, nombrePkg, fileSize = 0) {
    const ps4Ip = document.getElementById('rpi-console-ip')?.value.trim();
    if (!ps4Ip) { window.ps5Notification("ERROR", "No hay PS4 conectada.", "fa-wifi"); return; }
    if (typeof validarEstructuraIP === 'function' && !validarEstructuraIP(ps4Ip)) { window.ps5Notification('ERROR', 'La IP de PS4 no es válida.', 'fa-triangle-exclamation'); return; }

    if (!rpiPhoneIpManual) await detectarIPCelular();
    const phoneIp = document.getElementById('rpi-phone-ip').value.trim();
    if (!phoneIp || phoneIp === '127.0.0.1') { 
        window.ps5Notification("ERROR", "El servidor debe usar la IP de WiFi.", "fa-exclamation-triangle"); return; 
    }

    const serverPort = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
    if (window.location.protocol !== 'http:') { window.ps5Notification('ERROR RPI', 'El servidor local debe abrirse por HTTP para que la PS4 pueda descargar el PKG.', 'fa-triangle-exclamation'); return; }
    const portChoice = document.getElementById('rpi-install-port')?.value || 'auto';
    const portCandidates = portChoice === 'auto'
        ? Array.from(new Set([rpiDetectedPort, 12800, 12801].filter(port => [12800, 12801].includes(Number(port)))))
        : [Number(portChoice)];
    if (!portCandidates.length || portCandidates.some(port => ![12800, 12801].includes(port))) { window.ps5Notification('ERROR', 'Elige Auto, 12800 o 12801.', 'fa-triangle-exclamation'); return; }
    localStorage.setItem('sebas_ip_final_libre', ps4Ip);

    let rpiPort = 0;
    for (const candidatePort of portCandidates) {
        const check = await comprobarPuertoRPI(ps4Ip, candidatePort);
        if (check.ok) { rpiPort = candidatePort; break; }
    }
    if (!rpiPort) {
        const portCheck = await comprobarPuertoRPI(ps4Ip, portCandidates[0]);
        registrarLogRPI(`${nombrePkg}: RPI no disponible en ${ps4Ip}; ${portCheck.message}`, 'error');
        window.ps5Notification('RPI NO DISPONIBLE', portCheck.message, 'fa-triangle-exclamation');
        return;
    }

    registrarLogRPI(`PKG seleccionado: ${nombrePkg} (${formatearTamanoBytes(Number(fileSize) || 0)}).`);
    registrarLogRPI(`Destino RPI: ${ps4Ip}:${rpiPort}. Servidor PKG: ${phoneIp}:${serverPort}.`);
    registrarLogRPI('Enviando la orden RPI en segundo plano; el servidor queda libre para servir el paquete.');
    window.ps5Notification("RPI", "Enviando orden al instalador de PS4...", "fa-paper-plane");
    
    try {
        let fd = new FormData();
        fd.append('action', 'rpi_install');
        fd.append('host_ip', ps4Ip);
        fd.append('phone_ip', phoneIp);
        fd.append('server_port', serverPort);
        fd.append('file_id', fileId);
        fd.append('filename', nombrePkg);
        fd.append('rpi_port', String(rpiPort));
        let res = await fetch('api/transferir_api.php', { method: 'POST', body: fd });
        let data = await res.json();
        if (data.status !== 'success' || !data.job_id) throw new Error(data.message || 'PHP no pudo iniciar el envío RPI.');
        registrarLogRPI(`Operación ${data.job_id} iniciada. Esperando la respuesta de la consola.`);
        await monitorearOperacionRPI(data.job_id, nombrePkg, ps4Ip, rpiPort);
    } catch(e) { 
        registrarLogRPI(e.message || 'Fallo de comunicación RPI.', 'error');
        window.ps5Notification("ERROR RPI", e.message || "Fallo de comunicación RPI.", "fa-wifi");
    }
}

async function monitorearOperacionRPI(jobId, filename, ps4Ip, port) {
    const started = Date.now();
    let lastAccessCount = 0;
    let lastHeartbeat = 0;
    let previousState = '';
    let previousBytes = 0;
    let previousSampleAt = started;
    let lastPercent = -1;
    let lastLoggedPercent = -5;
    reiniciarProgresoRPI();
    while (Date.now() - started < 21600000) {
        await new Promise(resolve => setTimeout(resolve, 2000));
        const form = new FormData(); form.append('action', 'rpi_status'); form.append('job_id', jobId);
        const response = await fetch('api/transferir_api.php', { method: 'POST', body: form });
        const data = await response.json();
        if (!response.ok || data.status !== 'success') throw new Error(data.message || 'Se perdió el registro de instalación RPI.');
        const job = data.job || {};
        const access = data.file_requests || [];
        const download = data.download_progress || {};
        const bgft = job.progress || {};
        const total = Number(bgft.total || download.bytes_total || job.file_size || 0);
        const transferred = Number(bgft.transferred || 0);
        const served = Number(download.bytes_served || 0);
        const bytesForRate = transferred > 0 ? transferred : served;
        const now = Date.now();
        const sampleSeconds = Math.max(0.5, (now - previousSampleAt) / 1000);
        const speed = Math.max(0, bytesForRate - previousBytes) / sampleSeconds;
        previousBytes = bytesForRate;
        previousSampleAt = now;
        const percent = total > 0 ? Math.min(100, Math.max(0, Math.floor((bytesForRate / total) * 100))) : Number(bgft.percent || 0);
        const remaining = bgft.remaining_seconds !== null && bgft.remaining_seconds !== undefined
            ? Number(bgft.remaining_seconds)
            : (speed > 0 && total > bytesForRate ? Math.ceil((total - bytesForRate) / speed) : null);
        pintarProgresoRPI(percent, speed, remaining, Math.floor((now - started) / 1000), job.message || 'Preparando RPI');
        if (Number(bgft.percent) !== lastPercent && Number.isFinite(Number(bgft.percent))) {
            lastPercent = Number(bgft.percent);
            if (lastPercent >= 100 || lastPercent >= lastLoggedPercent + 5) {
                lastLoggedPercent = lastPercent;
                registrarLogRPI(`Progreso BGFT: ${lastPercent}% · ${formatearTamanoBytes(transferred)} de ${formatearTamanoBytes(total)}.`, 'info');
            }
        }
        if (job.state !== previousState) {
            previousState = job.state;
            registrarLogRPI(`Estado ${job.state}: ${job.message || 'procesando'}.`);
        }
        if (access.length > lastAccessCount) {
            access.slice(lastAccessCount).forEach(item => registrarLogRPI(`La PS4 solicitó ${item.file || filename}: ${item.method}${item.range ? ' · ' + item.range : ''} · ${formatearTamanoBytes(Number(item.bytes || 0))} · origen ${item.remote || 'desconocido'}.`, 'success'));
            lastAccessCount = access.length;
        }
        if (job.state === 'success') {
            pintarProgresoRPI(100, speed, 0, Math.floor((Date.now() - started) / 1000), job.message || 'Transferencia completada');
            registrarLogRPI(`BGFT completó la transferencia para ${filename}.`, 'success');
            window.ps5Notification('TRANSFERENCIA COMPLETADA', job.message || `BGFT terminó ${filename}. Confirma que el título aparezca instalado.`, 'fa-check');
            return;
        }
        if (job.state === 'accepted') {
            registrarLogRPI(job.message || 'RPI aceptó la orden, pero no expuso progreso BGFT.', 'success');
            window.ps5Notification('ORDEN ACEPTADA', job.message || 'Confirma el progreso en Package Installer.', 'fa-check');
            return;
        }
        if (job.state === 'error') {
            registrarLogRPI(`Fallo en ${ps4Ip}:${port} · HTTP ${job.http_code || 0} · ${job.curl_error || job.message || 'sin detalle'}`, 'error');
            if (job.response) registrarLogRPI(`Respuesta de PS4: ${job.response}`, 'error');
            window.ps5Notification('ERROR RPI', job.message || 'La consola no aceptó la solicitud.', 'fa-times');
            return;
        }
        if (access.length === 0 && Date.now() - lastHeartbeat >= 20000) {
            lastHeartbeat = Date.now();
            registrarLogRPI('La orden sigue abierta; la PS4 aún no solicita el PKG. Se mantiene el servidor listo.');
        }
    }
    registrarLogRPI('El panel dejó de esperar tras 6 horas. Revisa el estado final en la PS4.', 'error');
}

function reiniciarProgresoRPI() {
    const bar = document.getElementById('rpi-progress-bar');
    if (bar) { bar.style.width = '0%'; bar.setAttribute('aria-valuenow', '0'); }
    const values = { 'rpi-progress-percent': 'Preparando', 'rpi-progress-speed': '—', 'rpi-progress-remaining': '—', 'rpi-progress-elapsed': '0:00' };
    Object.entries(values).forEach(([id, value]) => { const el = document.getElementById(id); if (el) el.textContent = value; });
}

function pintarProgresoRPI(percent, speed, remaining, elapsed, state) {
    const safePercent = Math.min(100, Math.max(0, Number(percent) || 0));
    const bar = document.getElementById('rpi-progress-bar');
    if (bar) { bar.style.width = `${safePercent}%`; bar.setAttribute('aria-valuenow', String(safePercent)); }
    const set = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
    const duration = seconds => {
        if (!Number.isFinite(seconds) || seconds < 0) return 'Calculando';
        const h = Math.floor(seconds / 3600), m = Math.floor((seconds % 3600) / 60), s = Math.floor(seconds % 60);
        return h ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}` : `${m}:${String(s).padStart(2, '0')}`;
    };
    set('rpi-progress-percent', `${safePercent}% · ${String(state || 'RPI')}`);
    set('rpi-progress-speed', speed > 0 ? `${formatearTamanoBytes(speed)}/s` : 'Esperando PS4');
    set('rpi-progress-remaining', remaining === null ? 'Calculando' : duration(remaining));
    set('rpi-progress-elapsed', duration(elapsed));
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
