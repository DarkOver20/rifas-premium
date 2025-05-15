#!/bin/bash
# Servidor WebSocket principal
php websocket_server.php &

# Puente ZMQ
php bridge.php &

echo "Servicios iniciados"