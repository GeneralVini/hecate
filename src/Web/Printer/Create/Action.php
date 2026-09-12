<?php

declare(strict_types=1);

namespace App\Web\Printer\Create;

use App\Organization\Query\LocationListQuery;
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
        private LocationListQuery $locations,
        private RequestProviderInterface $requestProvider,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $request = $this->requestProvider->get();
        $errors = [];
        $values = ['name' => '', 'host' => '', 'location_id' => ''];

        if (strtoupper($request->getMethod()) === 'POST') {
            $body = $request->getParsedBody();
            $data = is_array($body) ? $body : [];

            foreach (['name', 'host', 'location_id'] as $field) {
                $value = $data[$field] ?? '';
                if (!is_string($value)) {
                    $errors[$field] = 'Informe um valor válido.';
                    continue;
                }
                $values[$field] = trim($value);
            }

            if ($errors === []) {
                try {
                    $locationId = $values['location_id'] === ''
                        ? null
                        : filter_var($values['location_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                    if ($locationId === false) {
                        throw new InvalidArgumentException('Selecione um local válido.');
                    }

                    $this->registerPrinter->execute(new RegisterPrinterInput(
                        $values['name'],
                        $values['host'],
                        $locationId === null ? null : (int) $locationId,
                    ));

                    $location = $this->urlGenerator->generate('printer/index');
                    $baseUrl = $_ENV['HECATE_BASE_URL'] ?? '';
                    if (is_string($baseUrl) && $baseUrl !== '' && str_starts_with($location, '/')) {
                        $location = rtrim($baseUrl, '/') . $location;
                    }

                    return $this->responseFactory
                        ->createResponse(302)
                        ->withHeader('Location', $location);
                } catch (InvalidArgumentException | DomainException $e) {
                    $errors['form'] = $e->getMessage();
                }
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'errors' => $errors,
            'values' => $values,
            'locations' => $this->locations->activeOptions(),
        ]);
    }
}
