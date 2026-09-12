<?php

declare(strict_types=1);

$configuredBaseUrl = $_ENV['HECATE_BASE_URL'] ?? $_SERVER['HECATE_BASE_URL'] ?? null;
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$detectedBaseUrl = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

$baseUrl = is_string($configuredBaseUrl) && $configuredBaseUrl !== ''
    ? '/' . trim($configuredBaseUrl, '/')
    : $detectedBaseUrl;

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
