# docker/backend/local.Dockerfile
FROM turkticaret-base:latest

ARG PROJECT_NAME=turkticaret
ARG HOST_UID=501
ARG HOST_GID=20

ENV PROJECT_NAME=${PROJECT_NAME}
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html/${PROJECT_NAME}-api

USER root

# Create necessary directories and set permissions
RUN mkdir -p \
    /var/www/html/${PROJECT_NAME}-api/storage/framework/{sessions,views,cache} \
    /var/www/html/${PROJECT_NAME}-api/storage/logs \
    /var/www/html/${PROJECT_NAME}-api/bootstrap/cache \
    /var/www/html/${PROJECT_NAME}-api/vendor && \
    chown -R www-data:www-data /var/www/html/${PROJECT_NAME}-api && \
    chmod -R 775 /var/www/html/${PROJECT_NAME}-api/storage && \
    chmod -R 775 /var/www/html/${PROJECT_NAME}-api/bootstrap/cache

# Scripts
COPY --chown=www-data:www-data docker/scripts/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create Laravel log file and set permissions
RUN touch /var/www/html/${PROJECT_NAME}-api/storage/logs/laravel.log && \
    chown www-data:www-data /var/www/html/${PROJECT_NAME}-api/storage/logs/laravel.log && \
    chmod 664 /var/www/html/${PROJECT_NAME}-api/storage/logs/laravel.log

USER www-data
ENV HOME=/var/www

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]