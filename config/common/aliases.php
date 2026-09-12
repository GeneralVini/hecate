<?php

declare(strict_types=1);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($baseUrl === '.' || $baseUrl === '/') {
    $baseUrl = '';
}

return [
    '@root' => dirname(__DIR__, 2),
    '@src' => '@root/src',
    '@assets' => '@root/public/assets',
    '@assetsUrl' => '@baseUrl/assets',
    '@assetsSource' => '@root/assets',
    '@baseUrl' => $baseUrl,
    '@public' => '@root/public',
    '@runtime' => '@root/runtime',
    '@vendor' => '@root/vendor',
];
