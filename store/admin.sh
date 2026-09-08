#!/data/data/com.termux/files/usr/bin/bash
# Lanza exclusivamente el panel local de administración de GoldHen Store.
set -eu

STORE_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
ADMIN_DIR="$STORE_DIR/admin"
PORT=8081

if ! command -v php >/dev/null 2>&1; then
    echo "PHP no está instalado. Ejecuta primero goldhen.sh."
    exit 1
fi

# Un fallo de sintaxis no debe terminar convertido en una página en blanco o
# un error críptico del navegador. Se detiene antes de abrir el panel.
for php_file in "$ADMIN_DIR/index.php" "$ADMIN_DIR/funciones.php" "$ADMIN_DIR/config.php" "$ADMIN_DIR/logout.php"; do
    if ! php -l "$php_file" >/dev/null 2>&1; then
        echo "El panel contiene un error PHP en: $php_file"
        php -l "$php_file"
        exit 1
    fi
done
if ! command -v zip >/dev/null 2>&1; then
    echo "Instalando la utilidad ZIP para exportar cambios…"
    pkg install -y zip || echo "No se pudo instalar ZIP; el panel avisará si no puede crear paquetes."
fi
if ! php -m 2>/dev/null | grep -qi '^gd$'; then
    echo "Aviso: PHP GD/WebP no está disponible; podrás editar el catálogo, pero no convertir carátulas."
fi
if ! php -m 2>/dev/null | grep -qi '^zip$'; then
    echo "Aviso: ZIP no está disponible; la exportación ZIP requerirá habilitar ZipArchive."
fi

if command -v openssl >/dev/null 2>&1; then
    STORE_ADMIN_TOKEN="$(openssl rand -hex 24)"
else
    STORE_ADMIN_TOKEN="$(date +%s)-$$-$RANDOM"
fi
export STORE_ADMIN_TOKEN

clear
printf '\033[1;36m╭────────────────────────────────────╮\033[0m\n'
printf '\033[1;36m│\033[0m      \033[1;32mGOLDHEN STORE ADMIN\033[0m          \033[1;36m│\033[0m\n'
printf '\033[1;36m│\033[0m     Panel local del mantenedor       \033[1;36m│\033[0m\n'
printf '\033[1;36m╰────────────────────────────────────╯\033[0m\n\n'
echo "Abriendo http://127.0.0.1:${PORT}"
echo "Pulsa Ctrl+C para cerrar el panel."

(sleep 1; command -v termux-open-url >/dev/null 2>&1 && termux-open-url "http://127.0.0.1:${PORT}/?token=${STORE_ADMIN_TOKEN}") &
exec php -S "127.0.0.1:${PORT}" -t "$ADMIN_DIR"
