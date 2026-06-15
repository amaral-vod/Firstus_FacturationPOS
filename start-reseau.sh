#!/bin/bash
# Démarre Firstus POS accessible sur le réseau local (0.0.0.0:8000)
cd "$(dirname "$0")"
IP=$(hostname -I 2>/dev/null | awk '{print $1}')
IP=${IP:-192.168.1.79}
echo "🛒 Firstus POS — accessible sur :"
echo "   http://${IP}:8000/login"
echo "   http://127.0.0.1:8000/login"
echo "   GitLab : https://gitlab.com/frioldfr/Firstus-FacturationPOS"
echo "   (depuis une autre machine du même réseau Wi-Fi/LAN)"
php artisan serve --host=0.0.0.0 --port=8000
