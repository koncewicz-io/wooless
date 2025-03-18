FROM node:23.5.0 AS node
FROM dunglas/frankenphp:1.3.6-builder-php8.3.15

COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN apt-get update && apt-get install -y \
    supervisor \
    zip \
    unzip \
    npm

RUN install-php-extensions \
    gd \
    pcntl \
    pdo \
    pdo_mysql \
    redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
