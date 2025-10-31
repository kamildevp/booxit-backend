<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

passthru(sprintf(
    'php "%s/../bin/console" doctrine:database:create --env=test --if-not-exists',
    __DIR__
));

passthru(sprintf(
    'php "%s/../bin/console" --env=test doctrine:schema:update --complete --force',
    __DIR__
));

passthru(sprintf(
    'APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup',
    $_ENV['APP_ENV'],
    __DIR__
));