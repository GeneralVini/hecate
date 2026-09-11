<?php

declare(strict_types=1);

namespace App\Printing\Application;

/** Implement only against a homologated SavaPage interface. No direct CUPS fallback. */
interface HeldJobGateway
{
    public function getHeldJob(string $reference): HeldJob;

    /**
     * Returns only after explicit acceptance. Any exception means outcome is unknown.
     * Acceptance is not proof of printing or final accounting.
     */
    public function release(string $reference, int $printerId, string $requestId): void;
}
