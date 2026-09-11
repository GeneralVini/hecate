<?php

declare(strict_types=1);

namespace App\Web\Printer\Create;

use App\Printing\Application\RegisterPrinter;
use App\Printing\Application\RegisterPrinterInput;
use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private RegisterPrinter $registerPrinter,
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
            foreach (array_keys($values) as $field) {
                $value = $data[$field] ?? '';
                if (!is_string($value)) {
                    $errors[$field] = 'Informe um texto válido.';
                    continue;
                }
                $values[$field] = trim($value);
            }

            if ($errors === []) {
                try {
                    $this->registerPrinter->execute(new RegisterPrinterInput(
                        $values['name'],
                        $values['host'],
                        $values['location'] !== '' ? $values['location'] : null,
                    ));
                    return $this->responseFactory
                        ->createResponse(302)
                        ->withHeader('Location', $this->urlGenerator->generate('printer/index'));
                } catch (InvalidArgumentException | DomainException $e) {
                    $errors['form'] = $e->getMessage();
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'values' => $values,
        ]);
    }
}
