# --- ESTÁGIO 1: DEPENDÊNCIAS PHP (COMPOSER) ---
# Usamos uma imagem oficial do Composer para instalar as dependências.
# Chamamos este estágio de "vendor".
FROM composer:2.7 as vendor

WORKDIR /app
# Copia apenas os arquivos necessários para o 'composer install'.
# Isso otimiza o cache: o install só roda se estes arquivos mudarem.
COPY src/database/ database/
COPY src/composer.json src/composer.lock ./

# Instala apenas as dependências de produção, otimizando o autoloader.
# ADICIONAMOS A FLAG --ignore-platform-reqs PARA RESOLVER O ERRO.
RUN composer install \
    --no-interaction \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# --- ESTÁGIO 2: ASSETS DE FRONTEND (NPM/VITE) ---
# Usamos uma imagem do Node.js para compilar os assets.
# Chamamos este estágio de "frontend".
FROM node:20-alpine as frontend

WORKDIR /app
# Copia os arquivos de configuração do frontend.
COPY src/package.json src/package-lock.json ./

# Instala as dependências de frontend.
RUN npm install

# Copia o restante do código-fonte para ter acesso aos assets.
COPY src/ .

# Compila os assets para produção.
RUN npm run build


# --- ESTÁGIO 3: IMAGEM FINAL DE PRODUÇÃO ---
# Começamos com uma imagem base limpa e leve (alpine).
FROM php:8.3-fpm-alpine

# Instala apenas as dependências de sistema ESTRITAMENTE necessárias para rodar a aplicação.
# Usamos 'apk' em vez de 'apt-get' por ser uma imagem Alpine.
RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev

# Instala as extensões PHP necessárias e habilita OPcache (essencial para performance).
# A extensão 'gd' é instalada corretamente aqui.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && docker-php-ext-enable opcache \
    && pecl install redis && docker-php-ext-enable redis

# Copia os arquivos de configuração do ambiente.
COPY docker/web/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/web/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/web/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# --- COPIA OS ARTEFATOS DOS ESTÁGIOS ANTERIORES ---
# Copia o código-fonte COMPLETO do diretório 'src'.
COPY src/ .
# Copia a pasta 'vendor' já pronta do estágio 'vendor'.
COPY --from=vendor /app/vendor/ ./vendor/
# Copia a pasta 'build' com os assets compilados do estágio 'frontend'.
COPY --from=frontend /app/public/build ./public/build

# --- OTIMIZAÇÕES E PERMISSÕES FINAIS ---
# Roda as otimizações do Laravel que geram arquivos de cache.
RUN composer dump-autoload --optimize

RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Ajusta as permissões APENAS nas pastas que precisam de escrita.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expõe a porta do Nginx e inicia o Supervisor.
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
