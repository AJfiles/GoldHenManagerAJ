#!/data/data/com.termux/files/usr/bin/bash
# Lanzador explícito y fiable para Termux. El servidor se deja en segundo plano
# para que el mantenedor conserve la consola y pueda usar `store-admin`.
set -u
PROJECT_DIR="${HOME}/GoldHenManagerAJ"
PORT="${GOLDHEN_PORT:-8080}"
LOG_FILE="${HOME}/.goldhen-server.log"
PID_FILE="${HOME}/.goldhen-server.pid"

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
  command -v termux-open-url >/dev/null 2>&1 && termux-open-url "http://127.0.0.1:${PORT}/index.php"
  exit 0
fi
cd "$PROJECT_DIR" || exit 1
if [ -f "$PID_FILE" ] && ! kill -0 "$(cat "$PID_FILE" 2>/dev/null)" 2>/dev/null; then
  rm -f "$PID_FILE"
fi

# Sin los logs de peticiones en pantalla: quedan disponibles si alguna vez hay
# que diagnosticar algo en ~/.goldhen-server.log.
nohup php -d memory_limit=256M -d max_execution_time=0 -S "127.0.0.1:${PORT}" -t "$PROJECT_DIR" >"$LOG_FILE" 2>&1 &
SERVER_PID=$!
printf '%s\n' "$SERVER_PID" > "$PID_FILE"

READY=0
for _ in $(seq 1 30); do
  if command -v curl >/dev/null 2>&1 && curl -fsS --max-time 1 "http://127.0.0.1:${PORT}/index.php" >/dev/null 2>&1; then
    READY=1
    break
  fi
  sleep 0.2
done

if [ "$READY" -eq 1 ]; then
  echo "GoldHen Manager AJ v3.3 abierto en http://127.0.0.1:${PORT}/index.php"
  command -v termux-open-url >/dev/null 2>&1 && termux-open-url "http://127.0.0.1:${PORT}/index.php"
  exit 0
fi

echo "No se pudo iniciar el servidor. Revisa: $LOG_FILE"
exit 1
