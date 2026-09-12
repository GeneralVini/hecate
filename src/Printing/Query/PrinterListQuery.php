<?php

declare(strict_types=1);

namespace App\Printing\Query;

use App\IdentityAccess\Application\AuthenticatedActor;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Constant\DataType;
use Yiisoft\Db\Expression\Value\Param;

final readonly class PrinterListQuery
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function exists(int $id): bool
    {
        return $this->db->createCommand(
            'SELECT count(*) FROM printer WHERE id = :id AND deleted_at IS NULL',
        )
            ->bindValue(':id', $id)
            ->queryScalar() > 0;
    }

    /**
     * @param array{name?: string, host?: string, location?: string, active?: string} $filters
     * @return list<PrinterListItem>
     */
    public function page(array $filters, string $sort, string $direction, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $orderBy = $this->orderBy($sort, $direction);

        $sql = <<<SQL
SELECT p.id, p.name, p.host, p.location_id, l.name AS location, p.active, p.last_seen_at
FROM printer p
LEFT JOIN location l ON l.id = p.location_id
WHERE {$where}
ORDER BY {$orderBy}, p.id
LIMIT {$limit} OFFSET {$offset}
SQL;

        return $this->map($this->db->createCommand($sql)->bindValues($params)->queryAll());
    }

    /** @param array{name?: string, host?: string, location?: string, active?: string} $filters */
    public function count(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $value = $this->db
            ->createCommand("SELECT COUNT(*) FROM printer p LEFT JOIN location l ON l.id = p.location_id WHERE {$where}")
            ->bindValues($params)
            ->queryScalar();

        return (int) $value;
    }

    /** @return list<PrinterListItem> */
    public function all(): array
    {
        return $this->page([], 'name', 'asc', PHP_INT_MAX, 0);
    }

    /** @return list<PrinterListItem> */
    public function availableTo(AuthenticatedActor $actor): array
    {
        if (!$actor->can('print.release')) {
            return [];
        }

        return $this->map($this->db->createCommand(<<<'SQL'
SELECT p.id, p.name, p.host, p.location_id, l.name AS location, p.active, p.last_seen_at
FROM printer p
LEFT JOIN location l ON l.id = p.location_id
JOIN division_printer_access a ON a.printer_id = p.id
WHERE a.division_id = :division
  AND p.active = TRUE
  AND p.deleted_at IS NULL
ORDER BY lower(p.name), p.id
SQL)
            ->bindValue(':division', $actor->divisionId)
            ->queryAll());
    }

    /**
     * @param array{name?: string, host?: string, location?: string, active?: string} $filters
     * @return array{0: string, 1: array<string, Param>}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = ['p.deleted_at IS NULL'];
        $params = [];
        $columns = [
            'name' => 'p.name',
            'host' => 'p.host',
            'location' => 'l.name',
        ];

        foreach ($columns as $field => $column) {
            $value = trim($filters[$field] ?? '');
            if ($value === '') {
                continue;
            }

            $parameter = ':' . $field;
            $clauses[] = sprintf('LOWER(COALESCE(%s, \'\')) LIKE %s', $column, $parameter);
            $params[$parameter] = new Param('%' . mb_strtolower($value) . '%', DataType::STRING);
        }

        $active = $filters['active'] ?? '';
        if ($active === '1') {
            $clauses[] = 'p.active = TRUE';
        } elseif ($active === '0') {
            $clauses[] = 'p.active = FALSE';
        }

        return [implode(' AND ', $clauses), $params];
    }

    private function orderBy(string $sort, string $direction): string
    {
        $column = match ($sort) {
            'host' => 'LOWER(p.host)',
            'location' => 'LOWER(COALESCE(l.name, \'\'))',
            'active' => 'p.active',
            'last_seen_at' => 'p.last_seen_at',
            default => 'LOWER(p.name)',
        };
        $sqlDirection = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $column . ' ' . $sqlDirection;
    }

    /**
     * @param array<array-key, array<string, mixed>> $rows
     * @return list<PrinterListItem>
     */
    private function map(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $active = $row['active'];
            $items[] = new PrinterListItem(
                (int) $row['id'],
                (string) $row['name'],
                (string) $row['host'],
                $row['location_id'] === null ? null : (int) $row['location_id'],
                $row['location'] === null ? null : (string) $row['location'],
                $active === true || $active === 1 || $active === '1' || $active === 't',
                $row['last_seen_at'] === null ? null : (string) $row['last_seen_at'],
            );
        }
        return $items;
    }
}
