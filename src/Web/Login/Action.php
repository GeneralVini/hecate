<?php

declare(strict_types=1);

namespace App\Web\Login;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RequestProviderInterface $requestProvider,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $request = $this->requestProvider->get();
        $errors = [];
        $username = '';

        if (strtoupper($request->getMethod()) === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $usernameValue = $data['username'] ?? null;
            $passwordValue = $data['password'] ?? null;
            $username = is_string($usernameValue) ? trim($usernameValue) : '';
            $password = is_string($passwordValue) ? trim($passwordValue) : '';

            if ($username === '') {
                $errors['username'] = 'Informe o usuário.';
            }
            if ($password === '') {
                $errors['password'] = 'Informe a senha.';
            }

            if ($errors === []) {
                $location = $this->urlGenerator->generate('home');
                $baseUrl = $_ENV['HECATE_BASE_URL'] ?? '';
                if ($baseUrl !== '' && str_starts_with($location, '/')) {
                    $location = rtrim($baseUrl, '/') . $location;
                }

                return $this->responseFactory->createResponse(303)->withHeader('Location', $location);
            }
        }

        return $this->viewRenderer
            ->withLayout('@src/Web/Shared/Layout/Login/layout.php')
            ->render(__DIR__ . '/template', ['errors' => $errors, 'username' => $username]);
    }
}
