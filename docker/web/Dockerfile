# Use a imagem base do PHP-FPM
FROM php:8.3-fpm

# Instala dependências do sistema, incluindo Nginx e Supervisor
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
    supervisor

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

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# CORREÇÃO: Remove o arquivo de configuração padrão do Nginx, sem falhar se ele não existir.
RUN rm -f /etc/nginx/conf.d/default.conf

# Copia os arquivos de configuração para dentro do container
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia o código do Laravel (ajuste o path conforme necessário)
COPY src /var/www/html

# Altera o dono do diretório para o usuário do PHP
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 80 para o Nginx
EXPOSE 80

# Comando para iniciar o Supervisor, que gerenciará Nginx e PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

