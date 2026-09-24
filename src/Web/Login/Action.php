<?php

declare(strict_types=1);

namespace App\Web\Login;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Validator\Validator;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RequestProviderInterface $requestProvider,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Validator $validator,
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
            $form = LoginForm::fromArray($data);
            $result = $this->validator->validate($form);

            $errors = $result->getFirstErrorMessagesIndexedByProperty();
            $username = $form->usernameForDisplay();

            if ($result->isValid()) {
                // Keep credential values separated from transport and output contexts.
                // The password is intentionally preserved exactly as submitted and must
                // never be logged, HTML-encoded for authentication, or interpolated into
                // SQL, LDAP filters, shell commands, or URLs.
                $form->passwordValue();

                return $this->responseFactory
                    ->createResponse(303)
                    ->withHeader('Location', $this->urlGenerator->generate('home'));
            }
        }

        return $this->viewRenderer
            ->withLayout('@src/Web/Shared/Layout/Login/layout.php')
            ->render(__DIR__ . '/template', ['errors' => $errors, 'username' => $username]);
    }
}
