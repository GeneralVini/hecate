<?php

declare(strict_types=1);

namespace App\Web\Printer\Create;

use App\Model\Printer;
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
        $values = ['name' => '', 'host' => '', 'location' => ''];

        if (strtoupper($request->getMethod()) === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];
            $values = [
                'name' => trim((string) ($data['name'] ?? '')),
                'host' => trim((string) ($data['host'] ?? '')),
                'location' => trim((string) ($data['location'] ?? '')),
            ];

            if ($values['name'] === '') {
                $errors['name'] = 'Informe o nome lógico da impressora.';
            }
            if ($values['host'] === '') {
                $errors['host'] = 'Informe o IP ou FQDN da impressora.';
            }

            if ($errors === []) {
                $printer = new Printer();
                $printer->name = $values['name'];
                $printer->host = $values['host'];
                $printer->location = $values['location'] !== '' ? $values['location'] : null;
                $printer->enabled = true;
                $printer->save();

                return $this->responseFactory
                    ->createResponse(302)
                    ->withHeader('Location', $this->urlGenerator->generate('printer/index'));
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'values' => $values,
        ]);
    }
}
