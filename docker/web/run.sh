#!/bin/sh

# O script irá parar se qualquer comando falhar
set -e

# 1. Roda as migrações do banco de dados.
# O artisan só será executado se a tabela de migrações já existir,
# evitando erros no primeiro deploy.
php artisan migrate --force

# 2. Gera os arquivos de cache de otimização.
# Agora, estes comandos rodam DENTRO do container em execução,
# com acesso a todas as variáveis de ambiente corretas.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Inicia o Supervisor para gerenciar Nginx e PHP-FPM.
# O 'exec' é importante para que o supervisord se torne o processo principal.
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
