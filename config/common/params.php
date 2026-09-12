<?php

declare(strict_types=1);

use App\Shared\ApplicationParams;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Db\Pgsql\Dsn;
use Yiisoft\Definitions\Reference;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;

$dbHost = $_ENV['HECATE_DB_HOST'] ?? $_SERVER['HECATE_DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['HECATE_DB_NAME'] ?? $_SERVER['HECATE_DB_NAME'] ?? 'hecate';
$dbPort = $_ENV['HECATE_DB_PORT'] ?? $_SERVER['HECATE_DB_PORT'] ?? '5432';
$dbUser = $_ENV['HECATE_DB_USER'] ?? $_SERVER['HECATE_DB_USER'] ?? 'hecate';
$dbPassword = $_ENV['HECATE_DB_PASSWORD'] ?? $_SERVER['HECATE_DB_PASSWORD'] ?? '';

return [
    'application' => require __DIR__ . '/application.php',

    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . '/aliases.php',
    ],

    'yiisoft/db-pgsql' => [
        'dsn' => new Dsn(
            'pgsql',
            $dbHost,
            $dbName,
            $dbPort,
        ),
        'username' => $dbUser,
        'password' => $dbPassword,
    ],

    'yiisoft/view' => [
        'basePath' => null,
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'applicationParams' => Reference::to(ApplicationParams::class),
            'aliases' => Reference::to(Aliases::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
        ],
    ],

    'yiisoft/yii-view-renderer' => [
        'viewPath' => null,
        'layout' => '@src/Web/Shared/Layout/Main/layout.php',
        'injections' => [Reference::to(CsrfViewInjection::class)],
    ],
];
