#!/bin/sh

if [ ! -d /var/www/vendor ]; then
    composer install
    php bin/console doctrine:migrations:migrate
fi

chown -R www-data:$UID /var/www/storage
chown -R www-data:$UID /var/www/var

exec docker-php-entrypoint "$@"