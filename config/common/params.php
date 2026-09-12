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

$edition = strtolower($_ENV['HECATE_EDITION'] ?? $_SERVER['HECATE_EDITION'] ?? 'local');
$demoMode = $edition === 'demo';
$requestedDemoScenario = strtolower(
    $_ENV['HECATE_DEMO_SCENARIO']
    ?? $_SERVER['HECATE_DEMO_SCENARIO']
    ?? $_COOKIE['hecate_demo_scenario']
    ?? 'dctim'
);
$demoScenario = in_array($requestedDemoScenario, ['dctim', 'ctim'], true) ? $requestedDemoScenario : 'dctim';

$dbHost = $_ENV['HECATE_DB_HOST'] ?? $_SERVER['HECATE_DB_HOST'] ?? '127.0.0.1';
$dbPort = $_ENV['HECATE_DB_PORT'] ?? $_SERVER['HECATE_DB_PORT'] ?? '5432';
$dbUser = $_ENV['HECATE_DB_USER'] ?? $_SERVER['HECATE_DB_USER'] ?? 'hecate';
$dbPassword = $_ENV['HECATE_DB_PASSWORD'] ?? $_SERVER['HECATE_DB_PASSWORD'] ?? '';

if ($demoMode) {
    $dbName = $demoScenario === 'ctim'
        ? ($_ENV['HECATE_DEMO_CTIM_DB_NAME'] ?? $_SERVER['HECATE_DEMO_CTIM_DB_NAME'] ?? 'hecate_demo_ctim')
        : ($_ENV['HECATE_DEMO_DCTIM_DB_NAME'] ?? $_SERVER['HECATE_DEMO_DCTIM_DB_NAME'] ?? 'hecate_demo_dctim');
} else {
    $dbName = $_ENV['HECATE_DB_NAME'] ?? $_SERVER['HECATE_DB_NAME'] ?? 'hecate';
}

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
            'demoMode' => $demoMode,
            'demoScenario' => $demoScenario,
        ],
    ],

    'yiisoft/yii-view-renderer' => [
        'viewPath' => null,
        'layout' => '@src/Web/Shared/Layout/Main/layout.php',
        'injections' => [Reference::to(CsrfViewInjection::class)],
    ],
];
