#!/bin/bash
# Arranque do modo kiosk: espera pelo servidor local e abre Chrome em fullscreen.
# Mantém o sistema acordado com caffeinate.

set -u

URL="${KIOSK_URL:-http://localhost:3000}"
USER_DATA="${KIOSK_USER_DATA:-$HOME/.cesae-kiosk-chrome}"
LOG_DIR="$HOME/Library/Logs/cesae-ecra"
mkdir -p "$LOG_DIR" "$USER_DATA"

echo "[$(date)] kiosk: aguardar servidor em $URL/api/health" >> "$LOG_DIR/kiosk.log"
for i in $(seq 1 60); do
  if curl -fsS "$URL/api/health" >/dev/null 2>&1; then
    echo "[$(date)] kiosk: servidor pronto (tentativa $i)" >> "$LOG_DIR/kiosk.log"
    break
  fi
  sleep 1
done

# Mantém display + sistema acordados enquanto este script existir
caffeinate -dimsu -w $$ &
CAFFEINATE_PID=$!
echo "[$(date)] kiosk: caffeinate pid=$CAFFEINATE_PID" >> "$LOG_DIR/kiosk.log"

CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
if [ ! -x "$CHROME" ]; then
  echo "[$(date)] kiosk: ERRO Chrome não encontrado em $CHROME" >> "$LOG_DIR/kiosk.log"
  exit 1
fi

echo "[$(date)] kiosk: a lançar Chrome em $URL" >> "$LOG_DIR/kiosk.log"
exec "$CHROME" \
  --kiosk \
  --app="$URL" \
  --user-data-dir="$USER_DATA" \
  --autoplay-policy=no-user-gesture-required \
  --disable-pinch \
  --disable-features=TranslateUI,InfiniteSessionRestore \
  --no-first-run \
  --noerrdialogs \
  --disable-session-crashed-bubble \
  --disable-infobars \
  --overscroll-history-navigation=0 \
  --check-for-update-interval=604800
