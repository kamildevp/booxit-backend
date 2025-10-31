FROM php:8.2-fpm AS app_base

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    librabbitmq-dev

ADD entrypoint.sh /var/docker/

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libfcgi-bin \
        libpq-dev && \
    docker-php-ext-install -j"$(nproc)" pdo_pgsql && \
    apt-get install -y --no-install-recommends \
        libpq5 && \
    pecl install amqp && \
    docker-php-ext-enable amqp && \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
        libpq-dev && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

ENTRYPOINT [ "bash", "/var/docker/entrypoint.sh" ]

CMD [ "php-fpm" ]

FROM app_base AS app_dev

RUN pecl install xdebug && docker-php-ext-enable xdebug

FROM app_base AS app_consumer

RUN apt-get update && apt-get install -y supervisor