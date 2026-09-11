<?php

declare(strict_types=1);

namespace App\Printing\Infrastructure;

use App\Audit\Application\AuditEntry;
use App\Audit\Infrastructure\SqlAuditLog;
use App\IdentityAccess\Application\AuthenticatedActor;
use App\Printing\Application\HeldJob;
use App\Printing\Application\ReleaseHeldJobInput;
use App\Printing\Application\ReleaseReceipt;
use App\Quota\Infrastructure\QuotaReservation;
use DomainException;
use LogicException;
use Yiisoft\Db\Connection\ConnectionInterface;

/** Owns the local transaction across release request, quota reservation and audit. */
final readonly class ReleaseRequestStore
{
    public function __construct(
        private ConnectionInterface $db,
        private QuotaReservation $quotas,
        private SqlAuditLog $audit,
    ) {
    }

    public function find(AuthenticatedActor $actor, ReleaseHeldJobInput $input): ?ReleaseReceipt
    {
        $row = $this->db->createCommand(<<<'SQL'
SELECT actor, division_id, job_reference, printer_id, state FROM print_release_request WHERE request_id = :request
SQL)->bindValues([':request' => $input->requestId])->queryOne();
        if ($row === null) {
            return null;
        }
        if (
            $row['actor'] !== $actor->subject || (int) $row['division_id'] !== $actor->divisionId
            || $row['job_reference'] !== $input->jobReference || (int) $row['printer_id'] !== $input->printerId
        ) {
            throw new DomainException('Identificador de solicitação já utilizado para outra operação.');
        }
        return new ReleaseReceipt($input->requestId, (string) $row['state']);
    }

    public function prepare(
        AuthenticatedActor $actor,
        ReleaseHeldJobInput $input,
        HeldJob $job,
        string $period,
    ): ReleaseReceipt {
        return $this->db->transaction(function () use ($actor, $input, $job, $period): ReleaseReceipt {
            // Serializes retries of one request, including before the request row exists.
            $this->db->createCommand('SELECT pg_advisory_xact_lock(hashtextextended(:request, 0))')
                ->bindValues([':request' => $input->requestId])->queryScalar();
            $existing = $this->find($actor, $input);
            if ($existing !== null) {
                return $existing;
            }
            $allowed = $this->db->createCommand(<<<'SQL'
SELECT p.id FROM printer p JOIN division_printer_access a ON a.printer_id = p.id
WHERE p.id = :printer AND a.division_id = :division AND p.enabled = TRUE
FOR SHARE OF p, a
SQL)->bindValues([':printer' => $input->printerId, ':division' => $actor->divisionId])->queryScalar();
            if ($allowed === null || $allowed === false) {
                throw new DomainException('Impressora não autorizada para a divisão.');
            }
            $quotaId = $this->quotas->reserve($actor->divisionId, $period, $job->bwPages, $job->colorPages);
            $inserted = $this->db->createCommand(<<<'SQL'
INSERT INTO print_release_request
(request_id, actor, division_id, job_reference, printer_id, quota_id, bw_pages, color_pages, state)
VALUES (:request, :actor, :division, :job, :printer, :quota, :bw, :color, 'pending')
ON CONFLICT DO NOTHING RETURNING request_id
SQL)->bindValues([
                ':request' => $input->requestId,
                ':actor' => $actor->subject,
                ':division' => $actor->divisionId,
                ':job' => $job->reference,
                ':printer' => $input->printerId,
                ':quota' => $quotaId,
                ':bw' => $job->bwPages,
                ':color' => $job->colorPages,
            ])->queryScalar();
            if ($inserted === null || $inserted === false) {
                throw new DomainException('Job já possui solicitação de liberação.');
            }
            $this->audit->record(new AuditEntry($actor->subject, 'print.release.reserved', $input->requestId));
            return new ReleaseReceipt($input->requestId, 'pending');
        });
    }

    /** Exactly one caller may send a pending request. Dispatching is never retried automatically. */
    public function claim(AuthenticatedActor $actor, ReleaseHeldJobInput $input): bool
    {
        return $this->db->transaction(function () use ($actor, $input): bool {
            $changed = $this->db->createCommand(<<<'SQL'
UPDATE print_release_request SET state = 'dispatching', updated_at = CURRENT_TIMESTAMP
WHERE request_id = :request AND actor = :actor AND state = 'pending'
SQL)->bindValues([':request' => $input->requestId, ':actor' => $actor->subject])->execute();
            if ($changed !== 1) {
                return false;
            }
            $this->audit->record(new AuditEntry($actor->subject, 'print.release.dispatching', $input->requestId));
            return true;
        });
    }

    public function finish(AuthenticatedActor $actor, ReleaseHeldJobInput $input, string $state): ReleaseReceipt
    {
        if (!in_array($state, ['accepted', 'unknown'], true)) {
            throw new LogicException('Resultado de liberação inválido.');
        }
        return $this->db->transaction(function () use ($actor, $input, $state): ReleaseReceipt {
            $changed = $this->db->createCommand(<<<'SQL'
UPDATE print_release_request SET state = :state, updated_at = CURRENT_TIMESTAMP
WHERE request_id = :request AND actor = :actor AND state = 'dispatching'
SQL)->bindValues([':state' => $state, ':request' => $input->requestId, ':actor' => $actor->subject])->execute();
            if ($changed !== 1) {
                throw new LogicException('Solicitação não está em envio.');
            }
            $this->audit->record(new AuditEntry($actor->subject, 'print.release.' . $state, $input->requestId));
            return new ReleaseReceipt($input->requestId, $state);
        });
    }
}
