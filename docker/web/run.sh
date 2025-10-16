#!/bin/sh

# O script irá parar imediatamente se qualquer comando falhar.
set -e

# --- ETAPA 1: LIMPEZA AGRESSIVA DE CACHES ---
# Esta é a etapa mais crítica para resolver o problema de intermitência.
# Ela força a remoção de quaisquer ficheiros de configuração, rotas ou views
# antigos que possam estar guardados no container, garantindo que a aplicação
# leia as novas configurações (como o TrustProxies).
echo "Limpando todos os caches da aplicação..."
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled
echo "Caches limpos com sucesso."

# --- ETAPA 2: EXECUTAR MIGRAÇÕES DO BANCO DE DADOS ---
# Garante que o banco de dados esteja sempre atualizado com o esquema mais recente.
# A flag --force é necessária para executar em ambiente de produção sem pedir confirmação.
echo "Executando migrações do banco de dados..."
php artisan migrate --force
echo "Migrações concluídas."

# --- ETAPA 3: RECONSTRUIR CACHES OTIMIZADOS ---
# Após a limpeza e as migrações, recriamos os ficheiros de cache otimizados
# para performance máxima em produção.
echo "Recriando caches de configuração, rotas e views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Caches recriados com sucesso."

# --- ETAPA 4: INICIAR OS SERVIÇOS ---
# Inicia o Supervisor, que por sua vez irá gerir os processos do Nginx e do PHP-FPM.
# O 'exec' faz com que o supervisord se torne o processo principal do container.
echo "Iniciando os serviços Nginx e PHP-FPM..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
