#!/bin/bash
# Démarre Firstus POS accessible sur le réseau local (0.0.0.0:8000)
cd /var/www/html/Firstus_FacturationPOS
echo "🛒 Firstus POS — accessible sur :"
echo "   http://192.168.1.79:8000/login"
echo "   (depuis une autre machine du même réseau Wi-Fi/LAN)"
php artisan serve --host=0.0.0.0 --port=8000
