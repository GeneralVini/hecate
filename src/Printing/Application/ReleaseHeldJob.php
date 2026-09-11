<?php

declare(strict_types=1);

namespace App\Printing\Application;

use App\IdentityAccess\Application\AuthenticatedActor;
use App\Printing\Infrastructure\ReleaseRequestStore;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use LogicException;
use Throwable;

/** Architectural slice only: OIDC, PIN and accounting must be integrated before HTTP exposure. */
final readonly class ReleaseHeldJob
{
    public function __construct(private ReleaseRequestStore $requests, private HeldJobGateway $gateway)
    {
    }

    public function execute(AuthenticatedActor $actor, ReleaseHeldJobInput $input): ReleaseReceipt
    {
        if (!$actor->can('print.release')) {
            throw new DomainException('Permissão de liberação ausente.');
        }
        $receipt = $this->requests->find($actor, $input);
        if ($receipt === null) {
            $job = $this->gateway->getHeldJob($input->jobReference);
            if ($job->reference !== $input->jobReference || $job->ownerSubject !== $actor->subject) {
                throw new DomainException('Job não pertence ao solicitante.');
            }
            $period = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m');
            $receipt = $this->requests->prepare($actor, $input, $job, $period);
        }
        if ($receipt->state !== 'pending' || !$this->requests->claim($actor, $input)) {
            return $this->requests->find($actor, $input) ?? throw new LogicException('Solicitação desapareceu.');
        }
        // No database transaction is held while communicating with the external engine.
        try {
            $this->gateway->release($input->jobReference, $input->printerId, $input->requestId);
            $state = 'accepted';
        } catch (Throwable) {
            // Never assume a timeout means rejection, and never log the exception's potentially secret payload.
            $state = 'unknown';
        }
        return $this->requests->finish($actor, $input, $state);
    }
}
