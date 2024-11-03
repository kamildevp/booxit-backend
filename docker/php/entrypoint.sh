#!/bin/sh

if [ ! -d /var/www/vendor ]; then
    composer install
fi

chown -R www-data:1000 /var/www/storage
chown -R www-data:1000 /var/www/var

exec docker-php-entrypoint "$@"