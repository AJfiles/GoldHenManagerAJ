#!/data/data/com.termux/files/usr/bin/bash
# GoldHen Manager AJ v3.3 — instalador fiable para Termux
set -u

VERDE='\033[1;32m'; CYAN='\033[1;36m'; AMARILLO='\033[1;33m'; ROJO='\033[1;31m'; BLANCO='\033[1;37m'; NC='\033[0m'
REPO_DIR="$HOME/GoldHenManagerAJ"
INSTALL_LOG="$HOME/.goldhen-install.log"

spinner() {
    local pid="$1" message="$2" spin='⣾⣽⣻⢿⡿⣟⣯⣷' index=0
    while kill -0 "$pid" 2>/dev/null; do
        index=$(( (index + 1) % 8 ))
        printf "\r\033[K${CYAN}%s${NC} %s" "${spin:$index:1}" "$message"
        sleep 0.1
    done
}

ejecutar_paso() {
    local message="$1"; shift
    "$@" >"$INSTALL_LOG" 2>&1 &
    local pid=$!; spinner "$pid" "$message"
    if wait "$pid"; then
        printf "\r\033[K${VERDE}✓${NC} %s\n" "$message"; return 0
    fi
    printf "\r\033[K${ROJO}✗${NC} %s\n" "$message"; return 1
}

usar_mirror() {
    local base_url="$1"
    [ -n "${PREFIX:-}" ] || return 1
    mkdir -p "$PREFIX/etc/apt"
    printf 'deb %s stable main\n' "$base_url" > "$PREFIX/etc/apt/sources.list"
    apt clean >/dev/null 2>&1 || true
    rm -rf "$PREFIX/var/lib/apt/lists/"* 2>/dev/null || true
}

actualizar_repositorios() {
    if ejecutar_paso "Preparando repositorios…" pkg update -y -o Dpkg::Options::="--force-confold"; then return 0; fi
    printf "${AMARILLO}↻ Sincronizando un mirror estable…${NC}\n"
    usar_mirror 'https://packages.termux.dev/apt/termux-main' || return 1
    if ejecutar_paso "Reintentando repositorios…" pkg update -y -o Dpkg::Options::="--force-confold"; then return 0; fi
    printf "${AMARILLO}↻ Probando un segundo mirror estable…${NC}\n"
    usar_mirror 'https://packages-cf.termux.dev/apt/termux-main' || return 1
    ejecutar_paso "Comprobando repositorios…" pkg update -y -o Dpkg::Options::="--force-confold"
}

instalar_comandos() {
    # Los ejecutables en PREFIX/bin funcionan de inmediato: no dependen de
    # que Termux cargue ~/.bashrc después de ejecutar `curl | bash`.
    cat > "$PREFIX/bin/goldhen" <<'EOF'
#!/data/data/com.termux/files/usr/bin/bash
exec bash "$HOME/GoldHenManagerAJ/start-goldhen.sh"
EOF
    cat > "$PREFIX/bin/store-admin" <<'EOF'
#!/data/data/com.termux/files/usr/bin/bash
exec bash "$HOME/GoldHenManagerAJ/store/admin.sh"
EOF
    chmod 755 "$PREFIX/bin/goldhen" "$PREFIX/bin/store-admin"
}

componentes_faltantes() {
    PAQUETES_FALTANTES=()
    local package
    for package in git php php-gd termux-api zip unzip; do
        if ! dpkg-query -W -f='${db:Status-Status}' "$package" 2>/dev/null | grep -qx installed; then
            PAQUETES_FALTANTES+=("$package")
        fi
    done
}

clear
printf "${CYAN}╭────────────────────────────────────╮${NC}\n"
printf "${CYAN}│${NC}      ${BLANCO}GOLDHEN MANAGER AJ${NC} ${CYAN}v3.3      │${NC}\n"
printf "${CYAN}│${NC}       ${AMARILLO}PS4 • Termux • Local FTP${NC}       ${CYAN}│${NC}\n"
printf "${CYAN}╰────────────────────────────────────╯${NC}\n\n"

if ! command -v pkg >/dev/null 2>&1; then
    printf "${ROJO}Este instalador necesita Termux.${NC}\n"
    printf "Instala la edición actual desde https://f-droid.org/packages/com.termux/ y vuelve a intentarlo.\n"
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive
termux-setup-storage >/dev/null 2>&1 || true
componentes_faltantes
if [ "${#PAQUETES_FALTANTES[@]}" -gt 0 ]; then
    if ! actualizar_repositorios; then
        printf "${ROJO}No se pudieron preparar los repositorios.${NC} Revisa la conexión y vuelve a ejecutar el mismo comando.\n"
        exit 1
    fi
    if ! ejecutar_paso "Instalando componentes necesarios…" pkg install -y -o Dpkg::Options::="--force-confold" "${PAQUETES_FALTANTES[@]}"; then
        printf "${ROJO}No se pudieron instalar los componentes requeridos.${NC}\n"
        exit 1
    fi
else
    printf "${VERDE}✓${NC} Componentes necesarios ya disponibles\n"
fi

if [ -d "$REPO_DIR/.git" ]; then
    if ! ejecutar_paso "Actualizando GoldHen Manager AJ…" git -C "$REPO_DIR" pull --ff-only origin main; then
        printf "${AMARILLO}Hay cambios locales distintos; la instalación se conservó sin sobrescribirlos.${NC}\n"
    fi
else
    [ ! -e "$REPO_DIR" ] || { printf "${ROJO}La ruta $REPO_DIR existe pero no es una instalación válida.${NC}\n"; exit 1; }
    ejecutar_paso "Descargando GoldHen Manager AJ…" git clone --depth 1 https://github.com/AJfiles/GoldHenManagerAJ.git "$REPO_DIR" || exit 1
fi

mkdir -p /sdcard/GoldHenManager/user
if [ -L "$REPO_DIR/user" ]; then rm "$REPO_DIR/user"; fi
if [ ! -e "$REPO_DIR/user" ]; then ln -s /sdcard/GoldHenManager/user "$REPO_DIR/user"; fi
chmod +x "$REPO_DIR/start-goldhen.sh" "$REPO_DIR/store/admin.sh" 2>/dev/null || true
instalar_comandos

printf "\n${VERDE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
printf "${VERDE}  ✓ Instalación completada${NC}\n"
printf "${CYAN}  Abriendo GoldHen Manager AJ…${NC}\n"
printf "${VERDE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
exec bash "$REPO_DIR/start-goldhen.sh"
