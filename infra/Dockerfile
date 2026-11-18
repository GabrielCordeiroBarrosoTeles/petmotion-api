# PetMotion (API + site + monitor) — PHP-FPM 8.5 + nginx, sem porta publicada na VPS.
# Portas internas escolhidas para não coincidir com nada do ToLembrado: nginx 8095, FPM 9095.

# ---------- dependências do Composer (sem as de desenvolvimento)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ---------- PHP-FPM com a aplicação
FROM php:8.5-fpm-alpine AS php
# PHP 8.5 já vem com OPcache embutido e com pdo_sqlite: só configuramos.
# pcntl: o Reverb (WebSocket do passeio ao vivo) usa sinais do SO para desligar com calma.
RUN apk add --no-cache sqlite \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
 && docker-php-ext-install pcntl \
 && apk del .build-deps \
 && { echo 'opcache.enable=1'; echo 'opcache.validate_timestamps=0'; echo 'opcache.memory_consumption=64'; } > /usr/local/etc/php/conf.d/opcache.ini \
 && { echo '[www]'; echo 'listen = 9095'; echo 'pm = dynamic'; echo 'pm.max_children = 6'; echo 'pm.start_servers = 2'; echo 'pm.min_spare_servers = 1'; echo 'pm.max_spare_servers = 3'; echo 'clear_env = no'; } > /usr/local/etc/php-fpm.d/zz-petmotion.conf
WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app /var/www/html
RUN rm -rf tests infra .env graphify-out storage/logs/* \
 && rm -f public/storage public/_*
COPY --chmod=755 infra/entrypoint.sh /usr/local/bin/petmotion-entrypoint
EXPOSE 9095
ENTRYPOINT ["petmotion-entrypoint"]
CMD ["php-fpm"]

# ---------- nginx servindo os arquivos públicos e repassando o PHP para o FPM
FROM nginx:1.27-alpine AS web
COPY infra/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=vendor /app/public /var/www/html/public
RUN rm -f /var/www/html/public/storage
EXPOSE 8095
