#!/bin/bash
set -e

APP_DIR="/var/www/phone.sddev.uz"
LOG_FILE="/tmp/phone_pbx_bridge.log"

cd "$APP_DIR"
nohup node "$APP_DIR/nodejs/app.mjs" >"$LOG_FILE" 2>&1 </dev/null &
