<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\Html\NoEncode;

function dangerousShell(string $command): void
{
    // ruleid: hecate.php.dangerous-shell-execution
    exec($command);
}

function dynamicCode(string $code): mixed
{
    // ruleid: hecate.php.dynamic-code-execution
    return eval($code);
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

function sqlFromPsr7(object $request, object $db): void
{
    $body = $request->getParsedBody();
    $sql = is_array($body) ? ($body['sql'] ?? '') : '';

    // ruleid: hecate.php.sql-injection-from-http
    $db->createCommand($sql);
}

function safeParameterizedSql(object $request, object $db): void
{
    $body = $request->getParsedBody();
    $nip = is_array($body) ? ($body['nip'] ?? '') : '';

    // ok: hecate.php.sql-injection-from-http
    $db->createCommand('SELECT * FROM users WHERE nip = :nip')->bindValue(':nip', $nip);
}

function pathTraversalFromHttp(): void
{
    $path = $_GET['path'];
    // ruleid: hecate.php.path-traversal-from-http
    include $path;
}

function pathTraversalFromPsr7(object $request): void
{
    $query = $request->getQueryParams();
    $path = $query['path'] ?? '';

    // ruleid: hecate.php.path-traversal-from-http
    file_get_contents($path);
}

function safeLocalBootstrap(): void
{
    $root = dirname(__DIR__);
    // ok: hecate.php.path-traversal-from-http
    require_once $root . '/src/bootstrap.php';
}

function ssrfFromHttp($curl): void
{
    $url = $_POST['url'];
    // ruleid: hecate.php.ssrf-from-http
    curl_setopt($curl, CURLOPT_URL, $url);
}

function ssrfFromPsr7(object $request): mixed
{
    $body = $request->getParsedBody();
    $url = is_array($body) ? ($body['url'] ?? '') : '';

    // ruleid: hecate.php.ssrf-from-http
    return curl_init($url);
}

function remoteFileFromHttp(): string|false
{
    $url = $_REQUEST['url'];
    // ruleid: hecate.php.ssrf-from-http, hecate.php.path-traversal-from-http
    return file_get_contents($url);
}

function openRedirectFromHttp(): void
{
    $next = $_GET['next'];
    // ruleid: hecate.php.open-redirect-from-http
    header('Location: ' . $next);
}

function openRedirectFromPsr7(object $request, object $response): object
{
    $query = $request->getQueryParams();
    $next = $query['next'] ?? '';

    // ruleid: hecate.php.open-redirect-from-http
    return $response->withHeader('Location', $next);
}

function safeRedirect(object $response, object $urlGenerator): object
{
    // ok: hecate.php.open-redirect-from-http
    return $response->withHeader('Location', $urlGenerator->generate('home'));
}

function headerInjectionFromPsr7(object $request, object $response): object
{
    $value = $request->getHeaderLine('X-Forwarded-Host');

    // ruleid: hecate.php.header-injection-from-http
    return $response->withHeader('X-Hecate-Origin', $value);
}

function xssFromHttp(): void
{
    $name = $_GET['name'];
    // ruleid: hecate.php.xss-from-http
    echo $name;
}

function xssFromPsr7(object $request): void
{
    $query = $request->getQueryParams();
    $name = $query['name'] ?? '';

    // ruleid: hecate.php.xss-from-http
    echo $name;
}

function escapedHttpOutput(): void
{
    $name = $_GET['name'];
    // ok: hecate.php.xss-from-http
    echo Html::encode($name);

    // ok: hecate.php.xss-from-http
    echo htmlspecialchars($_POST['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function escapedPsr7Output(object $request): void
{
    $query = $request->getQueryParams();
    $name = $query['name'] ?? '';

    // ok: hecate.php.xss-from-http
    echo Html::encode($name);
}

function disableEncoding(object $tag, string $html): void
{
    // ruleid: hecate.php.html-no-encode
    $tag->encode(false);

    // ruleid: hecate.php.html-no-encode
    NoEncode::string($html);
}

function filesystemHotspot(string $name): void
{
    // ruleid: hecate.php.filesystem-path-concatenation
    file_get_contents('/tmp/' . $name);
}

function weakCredentialHash(string $password, string $pin): void
{
    // ruleid: hecate.php.weak-credential-hash
    md5($password);

    // ruleid: hecate.php.weak-credential-hash
    sha1($pin);

    // ok: hecate.php.weak-credential-hash
    password_hash($password, PASSWORD_DEFAULT);
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

    // ok: hecate.php.dangerous-shell-execution
    strlen('literal');

    // ok: hecate.php.debug-output-sensitive
    echo 'literal';
}
