<?php

$params = [];
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'hecate',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
    'components' => [
        'request' => ['cookieValidationKey' => getenv('HECATE_COOKIE_KEY') ?: 'change-me-in-production'],
        'cache' => ['class' => 'yii\\caching\\FileCache'],
        'user' => ['identityClass' => 'app\\models\\User', 'enableAutoLogin' => false],
        'errorHandler' => ['errorAction' => 'site/error'],
        'mailer' => ['class' => 'yii\\symfonymailer\\Mailer', 'viewPath' => '@app/mail', 'useFileTransport' => true],
        'log' => ['traceLevel' => YII_DEBUG ? 3 : 0, 'targets' => [['class' => 'yii\\log\\FileTarget', 'levels' => ['error', 'warning']]]],
        'db' => $db,
        'urlManager' => ['enablePrettyUrl' => true, 'showScriptName' => false],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = ['class' => 'yii\\debug\\Module'];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => 'yii\\gii\\Module'];
}

return $config;
