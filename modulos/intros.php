<style>
    .intro-container { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.5s; overflow: hidden; background: #05050a; }
    .intro-container.active { display: flex; opacity: 1; }
    .scanlines { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0) 50%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.3)); background-size: 100% 4px; pointer-events: none; z-index: 50; }

    /* PS5 */
    .ps5-glow { position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(34,211,238,0.3) 0%, transparent 70%); border-radius: 50%; filter: blur(40px); animation: ps5Breath 4s infinite alternate; }
    .ps5-content { text-align: center; z-index: 10; animation: fadeHoldExit 5.5s ease-in-out forwards; }
    .ps5-particle { position: absolute; bottom: -10px; width: 3px; height: 3px; background: #22d3ee; border-radius: 50%; box-shadow: 0 0 10px #22d3ee; animation: ps5FloatUp linear forwards; }
    @keyframes fadeHoldExit { 0% { scale: 0.8; opacity: 0; filter: blur(10px); } 15% { scale: 1; opacity: 1; filter: blur(0px); } 90% { scale: 1.05; opacity: 1; filter: blur(0px); } 100% { scale: 1.2; opacity: 0; filter: blur(20px); } }
    @keyframes ps5Breath { 0% { transform: scale(0.8); opacity: 0.5; } 100% { transform: scale(1.2); opacity: 1; } }
    @keyframes ps5FloatUp { to { transform: translateY(-100vh); opacity: 0; } }

    /* PS4 */
    #intro-ps4 { background: linear-gradient(135deg, #001233 0%, #003791 100%); }
    .ps4-wave { position: absolute; width: 200%; height: 200%; background: radial-gradient(ellipse at center, rgba(255,255,255,0.1) 0%, transparent 50%); animation: ps4Spin 15s linear infinite; }
    .ps4-symbol { position: absolute; font-size: 2rem; color: rgba(255,255,255,0.2); animation: ps4Float 6s ease-in-out infinite; }
    .ps4-content { text-align: center; z-index: 10; animation: ps4Fade 5.5s ease forwards; }
    @keyframes ps4Spin { 100% { transform: rotate(360deg); } }
    @keyframes ps4Float { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-30px) rotate(20deg); } }
    @keyframes ps4Fade { 0% { opacity: 0; transform: translateY(30px); } 15% { opacity: 1; transform: translateY(0); } 90% { opacity: 1; transform: translateY(0); } 100% { opacity: 0; transform: translateY(-30px); } }

    /* GLITCH */
    .glitch-wrapper { text-align: center; position: relative; z-index: 10; animation: glitchExit 5.5s forwards; font-family: 'Share Tech Mono', monospace; }
    .glitch-logo { font-size: 7rem; color: #fff; position: relative; display: inline-block; }
    .glitch-logo::before, .glitch-logo::after { content: '\f3df'; font-family: "Font Awesome 6 Brands"; position: absolute; top: 0; left: 0; opacity: 0.8; }
    .glitch-logo::before { color: #0ff; z-index: -1; animation: glitchAnim 0.3s cubic-bezier(.25, .46, .45, .94) both infinite; }
    .glitch-logo::after { color: #f0f; z-index: -2; animation: glitchAnim 0.3s cubic-bezier(.25, .46, .45, .94) reverse both infinite; }
    @keyframes glitchAnim { 0% { transform: translate(0) } 20% { transform: translate(-3px, 3px) } 40% { transform: translate(-3px, -3px) } 60% { transform: translate(3px, 3px) } 80% { transform: translate(3px, -3px) } 100% { transform: translate(0) } }
    @keyframes glitchExit { 0% { opacity: 0; scale: 0.8; } 10% { opacity: 1; scale: 1; } 90% { opacity: 1; scale: 1; filter: contrast(1); } 98% { opacity: 0; scale: 1.5; filter: contrast(5) invert(1); } 100% { opacity: 0; display: none; } }

    /* PS2 */
    #intro-ps2 { background: radial-gradient(circle at center, #000033 0%, #000 100%); perspective: 800px; }
    .ps2-cube { position: absolute; border: 1px solid rgba(100, 200, 255, 0.4); background: rgba(0, 50, 255, 0.1); box-shadow: inset 0 0 20px rgba(0, 100, 255, 0.2); }
    .ps2-logo-container { z-index: 10; text-align: center; animation: ps2Emerge 5.5s ease-out forwards; }
    @keyframes ps2Emerge { 0% { transform: translateZ(-500px); opacity: 0; filter: blur(10px); } 15% { transform: translateZ(0); opacity: 1; filter: blur(0); } 90% { transform: translateZ(0); opacity: 1; filter: blur(0); } 100% { opacity: 0; filter: blur(20px); scale: 1.5; } }
    @keyframes cubeFly { 0% { transform: translateZ(-800px) rotateX(0deg) rotateY(0deg); opacity: 0; } 50% { opacity: 1; } 100% { transform: translateZ(400px) rotateX(360deg) rotateY(360deg); opacity: 0; } }

    /* HUD */
    .hud-wrapper { position: relative; width: 300px; height: 300px; display: flex; align-items: center; justify-content: center; animation: hudFade 5.5s forwards; }
    .hud-ring { position: absolute; border-radius: 50%; border: 2px solid transparent; }
    .hud-ring-1 { width: 100%; height: 100%; border-top: 2px solid #00d2ff; border-bottom: 2px solid #00d2ff; animation: spin 4s linear infinite; }
    .hud-ring-2 { width: 85%; height: 85%; border-left: 2px dashed #3b82f6; border-right: 2px dashed #3b82f6; animation: spinReverse 6s linear infinite; }
    .hud-ring-3 { width: 70%; height: 70%; border-top: 4px solid #0f172a; border-bottom: 4px solid #38bdf8; box-shadow: 0 0 15px #00d2ff; animation: spin 2s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite; }
    .hud-content { z-index: 10; text-align: center; animation: hudPulse 2s infinite alternate; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    @keyframes spinReverse { 100% { transform: rotate(-360deg); } }
    @keyframes hudPulse { 0% { opacity: 0.7; transform: scale(0.95); } 100% { opacity: 1; transform: scale(1.05); } }
    @keyframes hudFade { 0% { opacity: 0; scale: 0.5; } 10% { opacity: 1; scale: 1; filter: blur(0); } 94% { opacity: 1; scale: 1; filter: blur(0); } 100% { opacity: 0; scale: 1.5; filter: blur(15px); } }

    /* NEON */
    .neon-grid { position: absolute; bottom: -50%; left: -50%; width: 200%; height: 100%; background-image: linear-gradient(transparent 65%, rgba(255, 0, 255, 0.8) 68%, transparent 70%), linear-gradient(90deg, transparent 65%, rgba(0, 255, 255, 0.5) 68%, transparent 70%); background-size: 40px 40px; transform: perspective(300px) rotateX(60deg); animation: gridMove 2s linear infinite; z-index: 0; }
    .neon-wrapper { position: relative; z-index: 10; text-align: center; padding: 40px; border: 4px solid #ff00ff; box-shadow: 0 0 20px #ff00ff, inset 0 0 20px #ff00ff; background: rgba(20, 0, 20, 0.8); backdrop-filter: blur(5px); animation: neonFlicker 5.5s forwards; }
    @keyframes gridMove { 100% { background-position: 0 40px; } }
    @keyframes neonFlicker { 0%, 2%, 4%, 8%, 11% { opacity: 1; } 1%, 3%, 9% { opacity: 0.3; } 10% { opacity: 0; } 12% { opacity: 1; transform: scale(1); } 90% { transform: scale(1); opacity: 1; } 100% { transform: scale(1.3); opacity: 0; } }

    /* DECRYPT */
    #intro-decrypt { flex-direction: column; animation: decExit 5.5s forwards; }
    .decrypt-logo-anim { animation: glitchIn 0.5s ease forwards; opacity: 0; }
    @keyframes glitchIn { 0% { transform: scale(2) skewX(20deg); filter: blur(20px); opacity: 0; } 50% { transform: scale(0.9) skewX(-10deg); filter: blur(5px); opacity: 1; } 100% { transform: scale(1) skewX(0); filter: blur(0); opacity: 1; } }
    @keyframes decExit { 0%, 94% { opacity: 1; filter: blur(0); } 100% { opacity: 0; filter: blur(10px); scale: 1.2; } }

    /* ARCADE */
    #intro-arcade { flex-direction: column; background-image: radial-gradient(#333 1px, transparent 1px); background-size: 20px 20px; }
    .arcade-content { text-align: center; animation: arcadeZoom 5.5s forwards; }
    @keyframes blinkCoin { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    @keyframes arcadeZoom { 0% { transform: scale(0.1); opacity: 0; } 15% { transform: scale(1); opacity: 1; filter: blur(0); } 94% { transform: scale(1); opacity: 1; filter: blur(0); } 100% { transform: scale(3); opacity: 0; filter: blur(10px); } }

    /* MATRIX RAIN */
    #matrix-canvas-intro { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }
    .matrix-overlay { position: relative; z-index: 10; text-align: center; background: rgba(0,0,0,0.7); padding: 30px 20px; width: 85%; max-width: 400px; margin: 0 auto; border: 1px solid #0f0; box-shadow: 0 0 30px #0f0, inset 0 0 20px #0f0; animation: matrixFadeIn 5.5s forwards; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    @keyframes matrixFadeIn { 0% { opacity: 0; transform: scale(0.8); } 15% { opacity: 1; transform: scale(1); filter: blur(0); } 90% { opacity: 1; transform: scale(1); filter: blur(0); } 100% { opacity: 0; transform: scale(1.5); filter: blur(10px); } }

    /* CRT */
    #intro-crt { flex-direction: column; align-items: flex-start; justify-content: flex-start; padding: 40px; color: #ffb000; text-shadow: 0 0 5px #ffb000; font-family: 'Share Tech Mono', monospace; font-size: 1.2rem; }
    .crt-line { opacity: 0; margin-bottom: 8px; }
    .crt-logo-box { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; animation: crtLogoReveal 5.5s forwards; z-index: 10; }
    @keyframes crtLogoReveal { 0%, 45% { opacity: 0; display: none; } 46% { opacity: 1; transform: scaleY(0.1); } 50% { transform: scaleY(1); opacity: 1; } 92% { opacity: 1; filter: blur(0); scale: 1; } 100% { opacity: 0; filter: blur(20px); scale: 1.2; } }

    /* GAME BOY */
    #intro-gb { background: #8bac0f; flex-direction: column; font-family: 'Press Start 2P', cursive; color: #0f380f; }
    .gb-logo-container { text-align: center; animation: gbDrop 5.5s forwards; position: relative; top: -100%; }
    @keyframes gbDrop { 0% { top: -100%; opacity: 0; } 15% { top: 0; opacity: 1; filter: blur(0); } 90% { top: 0; opacity: 1; filter: blur(0); } 100% { top: 0; opacity: 0; filter: blur(10px); } }

    /* BREACH */
    #intro-breach { flex-direction: column; font-family: 'Share Tech Mono', monospace; }
    .breach-box { border: 4px solid #ff0000; padding: 40px 60px; text-align: center; color: #ff0000; box-shadow: 0 0 30px rgba(255,0,0,0.4), inset 0 0 30px rgba(255,0,0,0.4); animation: breachProcess 5.5s forwards; }
    @keyframes breachProcess { 0% { border-color: #ff0000; color: #ff0000; box-shadow: 0 0 30px rgba(255,0,0,0.4), inset 0 0 30px rgba(255,0,0,0.4); } 15% { transform: translate(5px, 5px); } 18% { transform: translate(-5px, -5px); } 20% { transform: translate(5px, -5px); } 25% { transform: translate(0, 0); border-color: #0f0; color: #0f0; box-shadow: 0 0 30px rgba(0,255,0,0.4), inset 0 0 30px rgba(0,255,0,0.4); } 90% { opacity: 1; transform: scale(1); filter: blur(0); } 100% { opacity: 0; transform: scale(1.5); filter: blur(10px); } }
</style>

<div id="intro-ps5" class="intro-container"><div class="ps5-glow"></div><div class="ps5-glow ps5-glow-2"></div><div class="ps5-content"><i class="fa-brands fa-playstation text-white text-7xl drop-shadow-lg"></i><div class="text-2xl font-black tracking-[8px] mt-5 bg-gradient-to-r from-white via-purple-400 to-cyan-400 text-transparent bg-clip-text">GOLDHEN MANAGER <span class="text-xl">v3.0</span></div></div><div id="ps5-particles-container"></div></div>
<div id="intro-ps4" class="intro-container"><div class="ps4-wave"></div><i class="fa-solid fa-xmark ps4-symbol" style="top: 20%; left: 20%; animation-delay: 0s;"></i><i class="fa-regular fa-circle ps4-symbol" style="top: 70%; left: 30%; animation-delay: 1s;"></i><i class="fa-regular fa-square ps4-symbol" style="top: 30%; left: 80%; animation-delay: 2s;"></i><i class="fa-solid fa-play ps4-symbol" style="top: 80%; left: 70%; animation-delay: 3s; transform: rotate(-90deg);"></i><div class="ps4-content"><i class="fa-brands fa-playstation text-white text-7xl mb-3 block"></i><div class="text-xl font-light tracking-[12px] uppercase text-white">GoldHen Manager <span class="font-bold">v3.0</span></div></div></div>
<div id="intro-glitch" class="intro-container"><div class="scanlines"></div><div class="glitch-wrapper"><i class="fa-brands fa-playstation glitch-logo"></i><div class="text-3xl font-black tracking-[5px] text-white uppercase mt-4" style="text-shadow: 2px 2px 0px #0ff, -2px -2px 0px #f0f;">GOLDHEN MANAGER v3.0</div></div></div>
<div id="intro-ps2" class="intro-container"><div id="ps2-cubes-container-intro" style="position: absolute; inset: 0;"></div><div class="ps2-logo-container"><i class="fa-brands fa-playstation text-7xl text-white block drop-shadow-[0_0_20px_#00aaff]"></i><div class="text-3xl font-light tracking-[15px] mt-4 uppercase text-[#ccffff]">GoldHen Manager</div><div class="text-[#00aaff] text-sm tracking-[8px] mt-2 font-bold">VERSIÓN 3.0</div></div></div>
<div id="intro-hud" class="intro-container"><div class="hud-wrapper"><div class="hud-ring hud-ring-1"></div><div class="hud-ring hud-ring-2"></div><div class="hud-ring hud-ring-3"></div><div class="hud-content"><i class="fa-brands fa-playstation text-6xl text-[#00d2ff] drop-shadow-[0_0_10px_#00d2ff]"></i><div class="text-xs font-black tracking-[4px] text-[#bae6fd] mt-3 uppercase">GoldHen<br>Manager v3.0</div></div></div></div>
<div id="intro-neon" class="intro-container"><div class="neon-grid"></div><div class="neon-wrapper"><i class="fa-brands fa-playstation text-7xl text-[#00ffff] drop-shadow-[0_0_15px_#00ffff] mb-3 block"></i><div class="text-3xl font-black text-white uppercase tracking-[4px] drop-shadow-[0_0_15px_#ff00ff]">GOLDHEN MANAGER</div><div class="text-sm text-[#00ffff] tracking-[6px] mt-2 font-bold">V 3 . 0</div></div></div>
<div id="intro-decrypt" class="intro-container"><div class="flex flex-col items-center justify-center w-full px-4 text-center"><i class="fa-brands fa-playstation text-7xl text-[#0f0] mb-4 drop-shadow-[0_0_15px_#0f0] decrypt-logo-anim"></i><div id="decrypt-target" class="font-mono text-[#0f0] uppercase" style="font-size: 1.5rem; text-shadow: 0 0 10px #0f0; letter-spacing: 2px;"></div></div></div>
<div id="intro-arcade" class="intro-container"><div class="arcade-content"><i class="fa-brands fa-playstation text-7xl text-[#ff0000] drop-shadow-[6px_6px_0_#ffcc00] mb-5 inline-block" style="animation: pixelBounce 0.5s infinite alternate steps(2);"></i><div style="font-family: 'Press Start 2P', cursive; font-size: 1.2rem; color: #ffcc00; text-shadow: 4px 4px 0px #ff0000; line-height: 2;">GOLDHEN MANAGER<br><span style="font-size: 0.8rem; color: #fff; text-shadow: none;">v3.0</span></div><div style="font-family: 'Press Start 2P', cursive; font-size: 0.8rem; color: #fff; margin-top: 40px; animation: blinkCoin 1s infinite steps(2);">INSERT COIN</div></div></div>
<div id="intro-matrix-rain" class="intro-container">
    <canvas id="matrix-canvas-intro"></canvas>
    <div class="matrix-overlay">
        <i class="fa-brands fa-playstation text-6xl md:text-7xl text-[#0f0] drop-shadow-[0_0_15px_#0f0] mb-3 block"></i>
        <div style="font-size: 1.4rem; color: #0f0; text-shadow: 0 0 15px #0f0; letter-spacing: 2px; font-weight: bold; text-align: center; line-height: 1.2;">
            GOLDHEN MANAGER<br><span style="font-size: 1rem;">v3.0</span>
        </div>
    </div>
</div>
<div id="intro-crt" class="intro-container"><div class="scanlines"></div><div id="crt-terminal-text" style="z-index: 10; position: relative;"></div><div class="crt-logo-box"><i class="fa-brands fa-playstation text-8xl mb-5 text-[#ffb000]"></i><div style="font-size: 2.5rem; font-weight: bold; letter-spacing: 8px; text-align: center; color: #ffb000;">GOLDHEN MANAGER v3.0</div></div></div>
<div id="intro-gb" class="intro-container"><div class="gb-logo-container"><i class="fa-brands fa-playstation text-7xl mb-5 block text-[#0f380f]"></i><div style="font-size: 1.2rem; letter-spacing: 2px;">GoldHen Manager<br><span style="font-size: 0.8rem; margin-top: 10px; display: block;">v3.0</span></div></div></div>
<div id="intro-breach" class="intro-container"><div class="scanlines"></div><div class="breach-box" id="breach-box-anim"><i id="breach-icon" class="fa-solid fa-lock text-7xl mb-5 block text-red-600"></i><div id="breach-text" style="font-size: 2rem; font-weight: bold; letter-spacing: 4px;">SYSTEM LOCKED</div></div></div>

<script>
    let currentIntroInterval = null;

    function generatePS5Particles() {
        const pContainer = document.getElementById('ps5-particles-container'); if(!pContainer) return; pContainer.innerHTML = ''; 
        for(let i=0; i<30; i++) {
            let particle = document.createElement('div'); particle.className = 'ps5-particle';
            particle.style.left = Math.random() * 100 + 'vw'; particle.style.animationDuration = (Math.random() * 2 + 2) + 's'; particle.style.animationDelay = (Math.random() * 2) + 's';
            pContainer.appendChild(particle);
        }
    }
    function generatePS2CubesIntro() {
        const cContainer = document.getElementById('ps2-cubes-container-intro'); if(!cContainer) return; cContainer.innerHTML = ''; 
        for(let i=0; i<40; i++) {
            let cube = document.createElement('div'); let size = Math.random() * 50 + 20; cube.className = 'ps2-cube';
            cube.style.width = size + 'px'; cube.style.height = size + 'px'; cube.style.left = (Math.random() * 120 - 10) + 'vw'; cube.style.top = (Math.random() * 120 - 10) + 'vh'; cube.style.animation = `cubeFly ${Math.random() * 3 + 3}s linear forwards`; cube.style.animationDelay = (Math.random() * 2) + 's';
            cContainer.appendChild(cube);
        }
    }
    function runDecryptEffect() {
        const target = document.getElementById('decrypt-target'); let iterations = 0; target.innerText = "";
        const finalWord = "GOLDHEN MANAGER v3.0"; const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%&*";
        setTimeout(() => {
            currentIntroInterval = setInterval(() => {
                target.innerText = finalWord.split("").map((letter, index) => { if(index < iterations) return finalWord[index]; return chars[Math.floor(Math.random() * chars.length)]; }).join("");
                if(iterations >= finalWord.length) clearInterval(currentIntroInterval); iterations += 1 / 3;
            }, 30);
        }, 600);
    }
    function initMatrixIntro() {
        const canvas = document.getElementById('matrix-canvas-intro'); if(!canvas) return; const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$+-*/=%""\'#&_(),.;:?!\\|{}<>[]^~';
        const fontSize = 16; const columns = canvas.width / fontSize; const drops = [];
        for (let x = 0; x < columns; x++) drops[x] = 1;
        const draw = () => {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)'; ctx.fillRect(0, 0, canvas.width, canvas.height); ctx.fillStyle = '#0F0'; ctx.font = fontSize + 'px monospace';
            for (let i = 0; i < drops.length; i++) {
                const text = alphabet.charAt(Math.floor(Math.random() * alphabet.length));
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
        };
        currentIntroInterval = setInterval(draw, 30);
    }
    function runCRTScript() {
        const term = document.getElementById('crt-terminal-text'); term.innerHTML = ''; term.style.display = 'block';
        const lines = ["BIOS Date 04/07/26 19:46:12 Ver 3.0", "CPU: Cell Broadband Engine", "Memory Test: 8388608K OK", "Loading kernel modules................ OK", "Mounting /dev/sda1 as root............ OK", "Executing GoldHen payload inject...... DONE", "Starting graphical interface..."];
        let delay = 0;
        lines.forEach((line, index) => { setTimeout(() => { const div = document.createElement('div'); div.className = 'crt-line text-[#ffb000]'; div.innerText = line; term.appendChild(div); setTimeout(() => div.style.opacity = '1', 50); }, delay); delay += (index === 2 || index === 5) ? 300 : 150; });
        setTimeout(() => { term.style.display = 'none'; }, 2400); 
    }
    function runBreach() {
        const icon = document.getElementById('breach-icon'); const text = document.getElementById('breach-text');
        icon.className = "fa-solid fa-lock text-7xl mb-5 block text-red-600"; text.innerText = "SYSTEM LOCKED"; text.style.color = "red";
        setTimeout(() => { icon.className = "fa-solid fa-unlock text-7xl mb-5 block text-green-500"; text.innerText = "ACCESS GRANTED"; text.style.color = "#0f0"; }, 1200); 
        setTimeout(() => { icon.className = "fa-brands fa-playstation text-7xl mb-5 block text-white"; text.innerText = "GOLDHEN MANAGER v3.0"; text.style.color = "white"; }, 2000); 
    }

    function bootSelectedIntro() {
        const savedIntro = localStorage.getItem('ps4_selected_intro') || 'none';
        const wrap = document.getElementById('intro-wrapper');
        
        if (savedIntro === 'none' || !savedIntro) { 
            if (wrap) wrap.style.display = 'none'; 
            try { AudioEngine.playPS5Boot(); } catch(e) {}
            return; 
        }
        
        const intro = document.getElementById(savedIntro);
        if(!intro) { if (wrap) wrap.style.display = 'none'; return; }
        
        intro.classList.add('active');

        if (savedIntro === 'intro-ps5') generatePS5Particles();
        if (savedIntro === 'intro-ps2') generatePS2CubesIntro();
        if (savedIntro === 'intro-decrypt') runDecryptEffect();
        if (savedIntro === 'intro-matrix-rain') initMatrixIntro();
        if (savedIntro === 'intro-crt') runCRTScript();
        if (savedIntro === 'intro-breach') runBreach();

        setTimeout(() => {
            intro.style.opacity = '0';
            if (currentIntroInterval) clearInterval(currentIntroInterval);
            setTimeout(() => { if (wrap) wrap.style.display = 'none'; }, 500);
        }, 5500);
    }
</script>
