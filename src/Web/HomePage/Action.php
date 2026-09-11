<?php

declare(strict_types=1);

namespace App\Web\HomePage;

use App\Model\Division;
use App\Model\Printer;
use App\Model\Quota;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(
        private WebViewRenderer $viewRenderer,
    ) {}

    public function __invoke(): ResponseInterface
    {
        $printers = Printer::query()->all();

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'metrics' => [
                'printers' => count($printers),
                'printersOnline' => count(array_filter(
                    $printers,
                    static fn(Printer $printer): bool => $printer->last_seen_at !== null,
                )),
                'divisions' => count(Division::query()->all()),
                'quotaRows' => count(Quota::query()->all()),
            ],
        ]);
    }
}
