FROM composer:2 AS composer
FROM node:25.1.0 AS node

FROM dunglas/frankenphp:1.9.1-builder-php8.4.14

RUN apt-get update && apt-get install -y --no-install-recommends \
    nano \
    supervisor \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    gd \
    pcntl \
    pdo \
    pdo_mysql \
    redis

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /var/www

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
