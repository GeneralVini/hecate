<?php

declare(strict_types=1);

return [
    'yiisoft/yii-console' => [
        'commands' => require __DIR__ . '/commands.php',
    ],
    'yiisoft/db-migration' => [
        'newMigrationNamespace' => 'App\\Migration',
        'sourceNamespaces' => ['App\\Migration'],
        'newMigrationPath' => dirname(__DIR__, 2) . '/src/Migration',
        'sourcePaths' => [dirname(__DIR__, 2) . '/src/Migration'],
    ],
];
