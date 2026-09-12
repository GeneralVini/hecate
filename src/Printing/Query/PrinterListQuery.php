<?php

declare(strict_types=1);

namespace App\Printing\Query;

use App\IdentityAccess\Application\AuthenticatedActor;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class PrinterListQuery
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function exists(int $id): bool
    {
        return $this->db->createCommand('SELECT count(*) FROM printer WHERE id = :id')
            ->bindValue(':id', $id)
            ->queryScalar() > 0;
    }

    /** @return list<PrinterListItem> */
    public function all(): array
    {
        return $this->map($this->db->createCommand(
            'SELECT id, name, host, location, last_seen_at FROM printer ORDER BY lower(name), id',
        )->queryAll());
    }

    /** @return list<PrinterListItem> */
    public function availableTo(AuthenticatedActor $actor): array
    {
        if (!$actor->can('print.release')) {
            return [];
        }

        return $this->map($this->db->createCommand(<<<'SQL'
SELECT p.id, p.name, p.host, p.location, p.last_seen_at
FROM printer p
JOIN division_printer_access a ON a.printer_id = p.id
WHERE a.division_id = :division AND p.enabled = TRUE
ORDER BY lower(p.name), p.id
SQL)
            ->bindValue(':division', $actor->divisionId)
            ->queryAll());
    }

    /**
     * @param array<array-key, array<string, mixed>> $rows
     * @return list<PrinterListItem>
     */
    private function map(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = new PrinterListItem(
                (int) $row['id'],
                (string) $row['name'],
                (string) $row['host'],
                $row['location'] === null ? null : (string) $row['location'],
                $row['last_seen_at'] === null ? null : (string) $row['last_seen_at'],
            );
        }
        return $items;
    }
}
