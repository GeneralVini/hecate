<?php

declare(strict_types=1);

namespace App\Web\HomePage;

use App\Monitoring\Query\InventoryMetricsQuery;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
        private InventoryMetricsQuery $metrics,
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        return $this->viewRenderer->render(__DIR__ . '/template', ['metrics' => $this->metrics->get()]);
    }
}
