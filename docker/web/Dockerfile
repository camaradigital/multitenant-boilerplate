# Use a imagem base do PHP-FPM
FROM php:8.3-fpm

# --- 1. INSTALAÇÃO DE DEPENDÊNCIAS DO SISTEMA ---
# Essas dependências raramente mudam, então ficam no início.
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    nodejs \
    npm \
    nginx \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Instala a extensão do Redis via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Instala o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- 2. COPIA ARQUIVOS DE CONFIGURAÇÃO ---
# Estes também mudam com pouca frequência.
COPY docker/web/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/web/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/web/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# Copia o código do Laravel (ajuste o path conforme necessário)
COPY src /var/www/html

# Altera o dono do diretório para o usuário do PHP
# --- 3. INSTALAÇÃO DE DEPENDÊNCIAS DO PROJETO ---
# Copia APENAS os arquivos de dependência primeiro.
# O Docker só vai re-executar o 'composer install' se o composer.json/lock mudar.
COPY ../../src/composer.json ../../src/composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --no-dev --prefer-dist

# Faz o mesmo para as dependências de frontend.
COPY ../../src/package.json ../../src/package-lock.json ./
RUN npm install

# --- 4. COPIA O CÓDIGO FONTE DA APLICAÇÃO ---
# Agora sim, copia todo o resto. Se você mudar um arquivo PHP,
# o build vai ser quase instantâneo até este ponto.
COPY ../../src .

# --- 5. FINALIZAÇÃO E OTIMIZAÇÃO ---
# Roda os scripts que dependem do código fonte completo.
RUN composer dump-autoload --optimize
# Se você tiver um passo de build de frontend (ex: Vite, Mix), rode-o aqui:
RUN npm run build
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta e define o comando de início
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]