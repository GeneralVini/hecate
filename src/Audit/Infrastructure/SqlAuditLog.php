<?php

declare(strict_types=1);

namespace App\Audit\Infrastructure;

use App\Audit\Application\AuditEntry;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class SqlAuditLog
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function record(AuditEntry $entry): void
    {
        $this->db->createCommand(<<<'SQL'
INSERT INTO audit_log (actor, action, entity, entity_id)
VALUES (:actor, :action, 'print_release_request', :request)
SQL)->bindValues([
            ':actor' => $entry->actor,
            ':action' => $entry->action,
            ':request' => $entry->requestId,
        ])->execute();
    }
}
