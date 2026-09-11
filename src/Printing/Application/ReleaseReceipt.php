<?php

declare(strict_types=1);

namespace App\Printing\Application;

final readonly class ReleaseReceipt
{
    public function __construct(public string $requestId, public string $state)
    {
    }
}
