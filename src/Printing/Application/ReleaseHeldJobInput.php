<?php

declare(strict_types=1);

namespace App\Printing\Application;

use InvalidArgumentException;

final readonly class ReleaseHeldJobInput
{
    public function __construct(public string $requestId, public string $jobReference, public int $printerId)
    {
        if (
            preg_match('/^[A-Za-z0-9_-]{16,80}$/D', $requestId) !== 1
            || $jobReference === '' || strlen($jobReference) > 160 || $printerId < 1
        ) {
            throw new InvalidArgumentException('Solicitação de liberação inválida.');
        }
    }
}
