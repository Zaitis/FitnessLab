<?php

namespace App\Logging;

use Monolog\Level;
use Monolog\Logger;

class CreateDatabaseLogger
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('database');

        $level = Level::fromName(strtoupper($config['level'] ?? 'error'));

        $logger->pushHandler(new DatabaseLogHandler($level));

        return $logger;
    }
}
