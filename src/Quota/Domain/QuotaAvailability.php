<?php

declare(strict_types=1);

namespace App\Quota\Domain;

use DomainException;

/** Initial policy: block on exhaustion. Warning/approval policies remain EAP work. */
final class QuotaAvailability
{
    public function assertCanReserve(
        int $bwAvailable,
        int $colorAvailable,
        int $bwRequested,
        int $colorRequested,
    ): void {
        if (
            $bwRequested < 0 || $colorRequested < 0 || $bwRequested + $colorRequested < 1
            || $bwRequested > $bwAvailable || $colorRequested > $colorAvailable
        ) {
            throw new DomainException('Cota insuficiente ou quantidade inválida.');
        }
    }
}
