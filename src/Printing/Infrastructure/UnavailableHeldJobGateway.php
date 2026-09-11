<?php

declare(strict_types=1);

namespace App\Printing\Infrastructure;

use App\Printing\Application\HeldJob;
use App\Printing\Application\HeldJobGateway;
use LogicException;

/** Fail closed until a real, homologated adapter is installed; fakes exist only in tests. */
final class UnavailableHeldJobGateway implements HeldJobGateway
{
    public function getHeldJob(string $reference): HeldJob
    {
        throw new LogicException('Integração de hold/release ainda não homologada.');
    }

    public function release(string $reference, int $printerId, string $requestId): void
    {
        throw new LogicException('Integração de hold/release ainda não homologada.');
    }
}
