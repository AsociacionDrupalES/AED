FROM uselagoon/php-8.3-cli-drupal:latest
ARG COMPOSER_INSTALL_ARGS=""

COPY composer.* /app/
COPY assets /app/assets
RUN composer install $COMPOSER_INSTALL_ARGS

# Copiar el resto del código
COPY . /app

# Crear el directorio de files con permisos correctos
RUN mkdir -p -v -m775 /app/web/sites/default/files

# Fijar el webroot
ENV WEBROOT=web
