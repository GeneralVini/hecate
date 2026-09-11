<?php

declare(strict_types=1);

namespace App\Web\Printer\Index;

use App\Printing\Query\PrinterListQuery;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final readonly class Action
{
    public function __construct(private WebViewRenderer $viewRenderer, private PrinterListQuery $printers)
    {
    }

    public function __invoke(): ResponseInterface
    {
        $printers = $this->printers->all();

        return $this->viewRenderer->render(__DIR__ . '/template', ['printers' => $printers]);
    }
}
