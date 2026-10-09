FROM node:22-bookworm-slim AS node_runtime

FROM composer:2 AS composer_runtime

FROM php:8.4-cli-bookworm

COPY --from=node_runtime /usr/local/ /usr/local/
COPY --from=composer_runtime /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libsqlite3-dev \
        procps \
        unzip \
    && docker-php-ext-install dom mbstring pcntl xml xmlwriter zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /workspace/saolabs

CMD ["bash", "/workspace/saolabs/docker/dev/start.sh"]
