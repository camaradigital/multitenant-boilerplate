#!/bin/sh

set -e

# Inicia o Supervisor em background para que o Nginx e o PHP-FPM arranquem.
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &

# Espera 5 segundos para garantir que o PHP-FPM está pronto a aceitar comandos.
echo "A aguardar pelo arranque do PHP-FPM..."
sleep 5

# --- LIMPEZA DE CACHES (TODAS AS CAMADAS) ---
echo "A limpar caches da aplicação Laravel..."
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled

# Limpa o OPcache (cache de opcode em memória do PHP)
# Esta é a etapa crucial que deve resolver a intermitência.
echo "A limpar o OPcache do PHP-FPM..."
cachetool opcache:reset --fcgi=127.0.0.1:9000 || echo "Falhou ao limpar o OPcache, mas a continuar."

# --- SETUP DA APLICAÇÃO ---
echo "A executar migrações da base de dados..."
php artisan migrate --force

echo "A recriar caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setup completo. A aplicação está a correr."

# Coloca o processo do Supervisor em foreground para manter o container vivo.
# A partir daqui, pode ver os logs do Nginx e FPM.
wait
