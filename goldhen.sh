#!/data/data/com.termux/files/usr/bin/bash

# ====================================================================
# GOLDHEN MANAGER AJ v3.1 🚀 (PS4) - INSTALADOR UNIFICADO
# DEVELOPED By AJ - Basado en SeBaS
# ====================================================================

VERDE='\033[1;32m'
CYAN='\033[1;36m'
AMARILLO='\033[1;33m'
ROJO='\033[1;31m'
BLANCO='\033[1;37m'
NC='\033[0m'

# === SPINNER ELEGANTE ===
spinner() {
    local pid=$1 message=$2 spin='⣾⣽⣻⢿⡿⣟⣯⣷' i=0
    while kill -0 "$pid" 2>/dev/null; do
        i=$(( (i + 1) % 8 ))
        printf "\r\033[K${CYAN}%s${NC} %s" "${spin:$i:1}" "$message"
        sleep 0.1
    done
}

ejecutar_paso() {
    local mensaje=$1
    shift
    "$@" >"$HOME/.goldhen-install.log" 2>&1 &
    local pid=$!
    spinner "$pid" "$mensaje"
    if wait "$pid"; then
        printf "\r\033[K${VERDE}✓${NC} %s\n" "$mensaje"
    else
        printf "\n${ROJO}✗ Error durante: %s${NC}\n" "$mensaje"
        echo "Últimas líneas del log:"
        tail -n 10 "$HOME/.goldhen-install.log"
        exit 1
    fi
}

clear
echo -e "${CYAN}╭────────────────────────────────────╮${NC}"
echo -e "${CYAN}│${NC}      ${BLANCO}GOLDHEN MANAGER AJ${NC} ${CYAN}v3.1      │${NC}"
echo -e "${CYAN}│${NC}       ${AMARILLO}PS4 • Termux • Local FTP${NC}       ${CYAN}│${NC}"
echo -e "${CYAN}╰────────────────────────────────────╯${NC}\n"

REPO_DIR="$HOME/GoldHenManagerAJ"

if [ -d "$REPO_DIR" ]; then
    echo -e "${AMARILLO}[*] Actualización detectada.${NC}"
    cd "$REPO_DIR"
    ejecutar_paso "Buscando actualizaciones..." git fetch --all
    ejecutar_paso "Aplicando cambios..." git reset --hard origin/main
else
    echo -e "${AMARILLO}[*] Instalación desde cero.${NC}"
    
    # Permiso de almacenamiento (si ya existe, no pasa nada)
    echo -e "${AMARILLO}Concediendo acceso a almacenamiento...${NC}"
    termux-setup-storage > /dev/null 2>&1 || true

    export DEBIAN_FRONTEND=noninteractive
    ejecutar_paso "Actualizando repositorios..." pkg update -y -o Dpkg::Options::="--force-confold"
    ejecutar_paso "Instalando dependencias..." pkg install -y -o Dpkg::Options::="--force-confold" git php php-zip termux-api

    echo -e "${CYAN}• Creando estructura de datos...${NC}"
    mkdir -p /sdcard/GoldHenManager/user

    ejecutar_paso "Descargando GoldHen Manager AJ..." git clone --depth 1 https://github.com/AJfiles/GoldHenManagerAJ.git "$REPO_DIR"
fi

# Symlink
echo -e "${AMARILLO}• Configurando enlace de datos...${NC}"
rm -rf "$REPO_DIR/user" 2>/dev/null
ln -s /sdcard/GoldHenManager/user "$REPO_DIR/user"

# Configurar .bashrc
echo -e "${AMARILLO}• Configurando arranque automático...${NC}"
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
    echo -e "${CYAN}        GOLDHEN MANAGER AJ v3.1      ${NC}\n"
}

pkill -f "php -S" > /dev/null 2>&1
APP_DIR="$HOME/GoldHenManagerAJ"
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
    echo -e "${VERDE} [+] ¡SISTEMA EN LÍNEA!${NC}\n"
    echo -e "${CYAN} [i] Escribe 'exit' para apagar el servidor.${NC}\n"
    termux-open-url "http://localhost:${PUERTO}/index.php"
fi
EOF

echo -e "\n${VERDE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${VERDE}  ✅ ¡Instalación completada con éxito!${NC}"
echo -e "${CYAN}  🔄 Cierra Termux y vuelve a abrirlo.${NC}"
echo -e "${VERDE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
