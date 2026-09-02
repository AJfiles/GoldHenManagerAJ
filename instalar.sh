#!/data/data/com.termux/files/usr/bin/bash

# ====================================================================
# GOLDHEN MANAGER V3.0 🚀 (PS4) - SCRIPT DE INSTALACIÓN TERMUX
# DEVELOPED By SeBaS
# ====================================================================

VERDE='\033[1;32m'
CYAN='\033[1;36m'
AMARILLO='\033[1;33m'
ROJO='\033[1;31m'
BLANCO='\033[1;37m'
NC='\033[0m'

clear
echo -e "${VERDE}  ____      _    _  _               ${NC}"
echo -e "${VERDE} / ___| ___| |__| || |___ _ __      ${NC}"
echo -e "${VERDE}| |  _ / _ \ |/ _\` | '__/ _ \ '_ \  ${NC}"
echo -e "${VERDE}| |_| |  __/ | (_| | | |  __/ | | | ${NC}"
echo -e "${VERDE} \____|\___|_|\__,_|_|  \___|_| |_| ${NC}"
echo -e "${CYAN}        M A N A G E R   V 3 . 0     ${NC}"
echo -e "${BLANCO}             [ By SeBaS ]           ${NC}\n"

echo -e "${AMARILLO}[*] Abriendo brecha en el sistema (Permisos)...${NC}"
echo -e "${ROJO}[!] ATENCIÓN: Toca 'PERMITIR' en tu pantalla.${NC}"
termux-setup-storage
sleep 4 

echo -e "\n${AMARILLO}[*] Inyectando dependencias (PHP, Git, API)...${NC}"
export DEBIAN_FRONTEND=noninteractive
pkg update -y -o Dpkg::Options::="--force-confold"
pkg install -y -o Dpkg::Options::="--force-confold" git php termux-api

echo -e "\n${AMARILLO}[*] Creando estructura de datos en Android...${NC}"
mkdir -p /sdcard/GoldHenManager/user

REPO_DIR="$HOME/GoldHenManager-v3"
if [ -d "$REPO_DIR" ]; then
    echo -e "${AMARILLO}[*] Sincronizando con el servidor de SeBaS...${NC}"
    cd "$REPO_DIR" && git pull
else
    echo -e "${AMARILLO}[*] Clonando repositorio maestro...${NC}"
    git clone https://github.com/Sebasbms/GoldHenManager-v3.git "$REPO_DIR"
fi

echo -e "${AMARILLO}[*] Estableciendo túnel de archivos (Symlink)...${NC}"
rm -rf "$REPO_DIR/user" 2>/dev/null
ln -s /sdcard/GoldHenManager/user "$REPO_DIR/user"

echo -e "${AMARILLO}[*] Sobrescribiendo terminal de arranque...${NC}"
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

echo -e "\n${VERDE}=================================================${NC}"
echo -e "${VERDE}  ¡INSTALACIÓN COMPLETADA!                       ${NC}"
echo -e "${VERDE}=================================================${NC}"
echo -e "${CYAN}Cierra Termux por completo y vuélvelo a abrir para ver la magia.${NC}"
