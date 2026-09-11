<?php

declare(strict_types=1);

namespace App\Web\Printer\Detect;

use App\Printing\Query\PrinterListQuery;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\RequestProvider\RequestProviderInterface;
use Yiisoft\Router\UrlGeneratorInterface;

final readonly class Action
{
    public function __construct(
        private RequestProviderInterface $requestProvider,
        private PrinterListQuery $printers,
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $body = $this->requestProvider->get()->getParsedBody();
        $data = is_array($body) ? $body : [];
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

        if ($id !== false && $this->printers->exists($id)) {
            // MVP: o hecate-agent assumirá a descoberta e persistência de telemetria.
        }

        return $this->responseFactory
            ->createResponse(302)
            ->withHeader('Location', $this->urlGenerator->generate('printer/index'));
    }
}
