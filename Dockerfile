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
# A flag --ignore-platform-reqs resolve o erro de extensões faltando neste estágio.
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

# Instala dependências de sistema, compila as extensões PHP e remove as dependências de build em um único passo.
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
    && apk add --no-cache \
        nginx \
        supervisor \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        oniguruma \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && docker-php-ext-enable opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

# *** CORREÇÃO APLICADA AQUI ***
# Copia o executável do Composer de uma imagem oficial para usá-lo temporariamente.
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copia os arquivos de configuração do ambiente.
COPY docker/web/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/web/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/web/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# --- COPIA OS ARTEFATOS DOS ESTÁGIOS ANTERIORES ---
COPY src/ .
COPY --from=vendor /app/vendor/ ./vendor/
COPY --from=frontend /app/public/build ./public/build

# --- OTIMIZAÇÕES, PERMISSÕES E LIMPEZA FINAIS ---
# Otimiza o autoloader do Composer com o contexto completo da aplicação.
RUN composer dump-autoload --optimize

# Roda as otimizações do Laravel que geram arquivos de cache.
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# *** LIMPEZA ADICIONADA AQUI ***
# Remove o Composer após o uso para manter a imagem final enxuta.
RUN rm /usr/local/bin/composer

# Ajusta as permissões APENAS nas pastas que precisam de escrita.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Adiciona uma verificação de saúde para o PHP-FPM
HEALTHCHECK --interval=5s --timeout=3s --start-period=1s \
  CMD cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1
  
# Expõe a porta do Nginx e inicia o Supervisor.
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
