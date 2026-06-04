#!/bin/bash
set -e

APP_SCRIPT="/var/www/phone.sddev.uz/nodejs/app.mjs"

if pgrep -f "$APP_SCRIPT" >/dev/null; then
  exit 0
fi

/bin/bash /var/www/phone.sddev.uz/nodejs/run_bridge.sh
