#!/data/data/com.termux/files/usr/bin/bash
# Lanzador explícito y fiable para Termux. Mantiene el servidor en primer plano
# para que Android no lo suspenda durante una sesión activa.
set -u
PROJECT_DIR="${HOME}/GoldHenManagerAJ"
PORT="${GOLDHEN_PORT:-8080}"

if [ ! -f "$PROJECT_DIR/index.php" ]; then
  echo "GoldHen Manager no se encontró en $PROJECT_DIR. Ejecuta primero goldhen.sh."
  exit 1
fi
if ! command -v php >/dev/null 2>&1; then
  echo "PHP no está instalado. Ejecuta de nuevo el instalador."
  exit 1
fi
if command -v curl >/dev/null 2>&1 && curl -fsS --max-time 1 "http://127.0.0.1:${PORT}/index.php" >/dev/null 2>&1; then
  echo "GoldHen Manager ya está abierto: http://127.0.0.1:${PORT}/index.php"
  exit 0
fi
cd "$PROJECT_DIR" || exit 1
echo "GoldHen Manager AJ v3.3 disponible en http://127.0.0.1:${PORT}/index.php"
echo "Mantén esta sesión de Termux abierta mientras usas la aplicación."
exec php -d memory_limit=256M -d max_execution_time=0 -S "127.0.0.1:${PORT}" -t "$PROJECT_DIR"
