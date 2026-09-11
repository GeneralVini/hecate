<?php

declare(strict_types=1);

namespace App\Monitoring\Query;

use RuntimeException;
use Yiisoft\Db\Connection\ConnectionInterface;

/** Read-only projection; owns no Printing or Quota business rules. */
final readonly class InventoryMetricsQuery
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function get(): InventoryMetrics
    {
        $row = $this->db->createCommand(<<<'SQL'
SELECT (SELECT count(*) FROM printer) AS printers,
       (SELECT count(*) FROM printer WHERE last_seen_at IS NOT NULL) AS telemetry,
       (SELECT count(*) FROM division) AS divisions,
       (SELECT count(*) FROM quota) AS quotas
SQL)->queryOne();
        if ($row === null) {
            throw new RuntimeException('Não foi possível consultar os indicadores.');
        }
        return new InventoryMetrics(
            (int) $row['printers'],
            (int) $row['telemetry'],
            (int) $row['divisions'],
            (int) $row['quotas'],
        );
    }
}
