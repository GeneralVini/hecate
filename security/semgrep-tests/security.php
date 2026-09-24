<?php

declare(strict_types=1);

use Yiisoft\Html\NoEncode;

function dangerousShell(string $command): void
{
    // ruleid: hecate.php.dangerous-shell-execution
    exec($command);
}

function unsafeDeserialize(string $payload): mixed
{
    // ruleid: hecate.php.unsafe-unserialize
    return unserialize($payload);
}

function unsafeSql(object $db, string $where): void
{
    // ruleid: hecate.php.sql-string-concatenation
    $db->createCommand('SELECT * FROM users WHERE ' . $where);

    // ruleid: hecate.php.sql-sprintf
    $db->createCommand(sprintf('SELECT * FROM users ORDER BY %s', $where));
}

function dynamicInclude(string $path): void
{
    // ruleid: hecate.php.dynamic-include
    include $path;
}

function unsafePath(string $name): void
{
    // ruleid: hecate.php.filesystem-path-concatenation
    file_get_contents('/tmp/' . $name);
}

function unsafeCurl($curl, string $url): void
{
    // ruleid: hecate.php.curl-dynamic-url
    curl_setopt($curl, CURLOPT_URL, $url);
}

function unsafeRemoteFile(string $url): string|false
{
    // ruleid: hecate.php.remote-file-dynamic-url
    return file_get_contents($url);
}

function unsafeHeader(string $next): void
{
    // ruleid: hecate.php.header-concatenation
    header('Location: ' . $next);
}

function variableHeader(string $header): void
{
    // ruleid: hecate.php.location-header-variable
    header($header);
}

function disableEncoding(object $tag, string $html): void
{
    // ruleid: hecate.php.html-no-encode
    $tag->encode(false);

    // ruleid: hecate.php.html-no-encode
    NoEncode::string($html);
}

function rawRequestOutput(): void
{
    // ruleid: hecate.php.raw-echo-request
    echo $_GET['q'];

    // ruleid: hecate.php.raw-superglobal-html-concat
    echo '<div>' . $_POST['name'] . '</div>';
}

function logSensitive(object $logger, string $password, string $token): void
{
    // ruleid: hecate.php.log-password
    $logger->error($password);

    // ruleid: hecate.php.log-token-secret
    $logger->warning($token);
}

function debugSensitive(array $data): void
{
    // ruleid: hecate.php.debug-output-sensitive
    var_dump($data);
}

function expectedSafePatterns(object $db): void
{
    // ok: hecate.php.sql-string-concatenation
    $db->createCommand('SELECT * FROM users WHERE nip = :nip');

    // ok: hecate.php.dynamic-include
    require __DIR__ . '/fixed.php';

    // ok: hecate.php.raw-echo-request
    echo 'literal';
}
