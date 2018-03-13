#!/bin/bash

set -e
php bin/console server:start 8003 --env test --pidfile /tmp/adl-test-server.pid

# Start in headless mode
google-chrome-stable --no-sandbox --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 http://localhost:8003 &

#
# Alternatively, start Chrome in a virtual display with a VNC server.
# You can connect to the server with a VNC viewer from your host machine at localhost:5900.
#

# Xvfb :42 -screen 0 1024x768x16 &
# x11vnc -display :42 -bg -nopw -listen 0.0.0.0 -xkb
# DISPLAY=:42 fluxbox &
# DISPLAY=:42 google-chrome-stable --no-sandbox --start-maximized --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 http://localhost:8003 &
