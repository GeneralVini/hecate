<?php

declare(strict_types=1);

namespace App\Web\Demo\SelectScenario;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Router\UrlGeneratorInterface;

final readonly class Action
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $edition = strtolower((string) ($_ENV['HECATE_EDITION'] ?? $_SERVER['HECATE_EDITION'] ?? 'local'));
        if ($edition !== 'demo') {
            return $this->redirectToHome();
        }

        $scenario = strtolower((string) ($request->getQueryParams()['scenario'] ?? ''));
        if (!in_array($scenario, ['dctim', 'ctim'], true)) {
            return $this->redirectToHome();
        }

        return $this->redirectToHome()
            ->withAddedHeader(
                'Set-Cookie',
                sprintf('hecate_demo_scenario=%s; Path=/; SameSite=Lax', $scenario),
            );
    }

    private function redirectToHome(): ResponseInterface
    {
        $location = $this->urlGenerator->generate('home');
        $baseUrl = $_ENV['HECATE_BASE_URL'] ?? $_SERVER['HECATE_BASE_URL'] ?? '';
        if ($baseUrl !== '' && str_starts_with($location, '/')) {
            $basePath = '/' . trim((string) $baseUrl, '/');
            if (!str_starts_with($location, $basePath . '/')) {
                $location = $basePath . $location;
            }
        }

        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $location);
    }
}
