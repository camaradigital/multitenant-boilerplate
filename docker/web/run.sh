#!/bin/sh

set -e

# Inicia o Supervisor em background...
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &

# Espera 5 segundos...
echo "A aguardar pelo arranque do PHP-FPM..."
sleep 5

# --- MUDANÇA IMPORTANTE ---
# Navega para o diretório da aplicação.
# O seu Nginx (default.conf) aponta para /var/www/html/public
# Então, a aplicação está em /var/www/html
cd /var/www/html
# --------------------------

# --- LIMPEZA DE CACHES (TODAS AS CAMADAS) ---
echo "A limpar caches da aplicação Laravel..."
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled

# --- SETUP DA APLICAÇÃO ---
echo "A executar migrações da base de dados..."
php artisan migrate --force

# --- ADICIONE ESTA LINHA ---
echo "A criar link simbólico do storage..."
php artisan storage:link
# --------------------------

echo "A recriar caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setup completo. A aplicação está a correr."

# Coloca o processo do Supervisor em foreground...
wait
