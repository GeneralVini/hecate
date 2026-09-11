<?php

declare(strict_types=1);

namespace App\Audit\Application;

final readonly class AuditEntry
{
    public function __construct(public string $actor, public string $action, public string $requestId)
    {
    }
}
