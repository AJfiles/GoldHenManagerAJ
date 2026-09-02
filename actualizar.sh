#!/data/data/com.termux/files/usr/bin/bash

# ====================================================================
# GOLDHEN MANAGER V3.0 🚀 (PS4) - SCRIPT DE ACTUALIZACIÓN INTELIGENTE
# DEVELOPED By SeBaS
# ====================================================================

VERDE='\033[1;32m'
CYAN='\033[1;36m'
AMARILLO='\033[1;33m'
ROJO='\033[1;31m'
BLANCO='\033[1;37m'
NC='\033[0m'

clear
echo -e "${CYAN}=================================================${NC}"
echo -e "${VERDE}  INICIANDO ACTUALIZACIÓN DEL SISTEMA V3.0...    ${NC}"
echo -e "${CYAN}=================================================${NC}\n"

REPO_DIR="$HOME/GoldHenManager-v3"

if [ -d "$REPO_DIR" ]; then
    echo -e "${AMARILLO}[*] Descargando nuevos módulos desde el servidor...${NC}"
    cd "$REPO_DIR"
    git fetch --all
    git reset --hard origin/main
    
    echo -e "\n${AMARILLO}[*] Reconfigurando el núcleo de arranque en Termux...${NC}"
    cat << 'EOF' > $HOME/.bashrc
VERDE='\033[1;32m'
CYAN='\033[1;36m'
AMARILLO='\033[1;33m'
BLANCO='\033[1;37m'
NC='\033[0m'

imprimir_logo() {
    clear
    echo -e "${VERDE}  ____      _    _  _               ${NC}"
    echo -e "${VERDE} / ___| ___| |__| || |___ _ __      ${NC}"
    echo -e "${VERDE}| |  _ / _ \ |/ _\` | '__/ _ \ '_ \  ${NC}"
    echo -e "${VERDE}| |_| |  __/ | (_| | | |  __/ | | | ${NC}"
    echo -e "${VERDE} \____|\___|_|\__,_|_|  \___|_| |_| ${NC}"
    echo -e "${CYAN}        M A N A G E R   V 3 . 0     ${NC}"
    echo -e "${BLANCO}             [ By SeBaS ]           ${NC}\n"
}

pkill -f "php -S" > /dev/null 2>&1

APP_DIR="$HOME/GoldHenManager-v3"
PUERTO=8080

if [ -d "$APP_DIR" ]; then
    cd "$APP_DIR"
    
    PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:${PUERTO} > /dev/null 2>&1 &
    
    imprimir_logo
    echo -e "${AMARILLO} [+] Conexión establecida. Iniciando entorno...${NC}\n"
    echo -e "${CYAN}           ████████   ${NC}"
    echo -e "${CYAN}                 ██   ${NC}"
    echo -e "${CYAN}           ████████   ${NC}"
    echo -e "${CYAN}                 ██   ${NC}"
    echo -e "${CYAN}           ████████   ${NC}\n"
    sleep 1

    imprimir_logo
    echo -e "${AMARILLO} [+] Conexión establecida. Iniciando entorno...${NC}\n"
    echo -e "${CYAN}           ████████   ${NC}"
    echo -e "${CYAN}                 ██   ${NC}"
    echo -e "${CYAN}           ████████   ${NC}"
    echo -e "${CYAN}           ██         ${NC}"
    echo -e "${CYAN}           ████████   ${NC}\n"
    sleep 1

    imprimir_logo
    echo -e "${AMARILLO} [+] Conexión establecida. Iniciando entorno...${NC}\n"
    echo -e "${CYAN}             ████     ${NC}"
    echo -e "${CYAN}           ██  ██     ${NC}"
    echo -e "${CYAN}               ██     ${NC}"
    echo -e "${CYAN}               ██     ${NC}"
    echo -e "${CYAN}             ██████   ${NC}\n"
    sleep 1
    
    imprimir_logo
    echo -e "${VERDE} [+] ¡SISTEMA EN LÍNEA! Ejecutando interfaz...${NC}\n"
    echo -e "${CYAN} [i] Escribe 'exit' para apagar el servidor.${NC}\n"
    
    termux-open-url "http://localhost:${PUERTO}/index.php"
fi
EOF
    
    echo -e "\n${VERDE}[+] ¡ACTUALIZACIÓN EXITOSA!${NC}"
    echo -e "${VERDE}[+] El sistema ahora cuenta con integración PWA Nativa.${NC}"
    echo -e "${CYAN}[i] Por favor, cierra Termux por completo y vuélvelo a abrir.${NC}\n"
else
    echo -e "${ROJO}[!] ERROR FATAL: No se detectó ninguna instalación previa.${NC}"
    echo -e "${ROJO}[!] Debes usar el comando de instalación completa primero.${NC}\n"
fi
