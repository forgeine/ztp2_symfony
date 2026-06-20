<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$console = escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__).'/bin/console');
$commands = [
    'doctrine:schema:drop --full-database --force',
    'doctrine:migrations:sync-metadata-storage --no-interaction',
    'doctrine:migrations:version --delete --all --no-interaction',
    'doctrine:migrations:migrate --no-interaction',
];

foreach ($commands as $command) {
    passthru($console.' --env=test '.$command, $exitCode);

    if (0 !== $exitCode) {
        throw new RuntimeException(sprintf('Preparing the test database failed while running "%s".', $command));
    }
}
