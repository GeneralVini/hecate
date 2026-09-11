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
    ) {
    }

    public function __invoke(): ResponseInterface
    {
        $printerRows = Printer::query()->all();
        $printerCount = 0;
        $printersOnline = 0;

        foreach ($printerRows as $printer) {
            if (!$printer instanceof Printer) {
                continue;
            }

            $printerCount++;
            if ($printer->last_seen_at !== null) {
                $printersOnline++;
            }
        }

        return $this->viewRenderer->render(__DIR__ . '/template', [
            'metrics' => [
                'printers' => $printerCount,
                'printersOnline' => $printersOnline,
                'divisions' => count(Division::query()->all()),
                'quotaRows' => count(Quota::query()->all()),
            ],
        ]);
    }
}
