<?php

declare(strict_types=1);

use App\Environment;
use Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (empty($_ENV['APP_ENV']) && class_exists(Dotenv::class)) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

Environment::prepare();
