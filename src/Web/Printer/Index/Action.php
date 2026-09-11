<?php

declare(strict_types=1);

namespace App\Web\Printer\Index;

use App\Model\Printer;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(private WebViewRenderer $viewRenderer) {}

    public function __invoke(): ResponseInterface
    {
        $printers = Printer::query()->all();
        usort(
            $printers,
            static fn(Printer $a, Printer $b): int => strcasecmp($a->name, $b->name),
        );

        return $this->viewRenderer->render(__DIR__ . '/template', ['printers' => $printers]);
    }
}
