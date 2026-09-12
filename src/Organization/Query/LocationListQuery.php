<?php

declare(strict_types=1);

namespace App\Organization\Query;

use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class LocationListQuery
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    /**
     * @param array{code?: string, name?: string, description?: string, active?: string} $filters
     * @return list<LocationListItem>
     */
    public function page(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $sql = <<<SQL
SELECT id, code, name, description, active
FROM location
WHERE {$where}
ORDER BY lower(name), id
LIMIT {$limit} OFFSET {$offset}
SQL;

        $rows = $this->db->createCommand($sql)->bindValues($params)->queryAll();

        $items = [];
        foreach ($rows as $row) {
            $active = $row['active'];
            $items[] = new LocationListItem(
                (int) $row['id'],
                (string) $row['code'],
                (string) $row['name'],
                $row['description'] === null ? null : (string) $row['description'],
                $active === true || $active === 1 || $active === '1' || $active === 't',
            );
        }

        return $items;
    }

    /** @param array{code?: string, name?: string, description?: string, active?: string} $filters */
    public function count(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $value = $this->db
            ->createCommand("SELECT COUNT(*) FROM location WHERE {$where}")
            ->bindValues($params)
            ->queryScalar();

        return (int) $value;
    }

    /** @return list<LocationListItem> */
    public function all(): array
    {
        return $this->page([], PHP_INT_MAX, 0);
    }

    /** @return array<int,string> */
    public function activeOptions(): array
    {
        $rows = $this->db->createCommand(<<<'SQL'
SELECT id, code, name
FROM location
WHERE active = TRUE AND deleted_at IS NULL
ORDER BY lower(name), id
SQL)->queryAll();

        $options = [];
        foreach ($rows as $row) {
            $options[(int) $row['id']] = sprintf('%s — %s', (string) $row['code'], (string) $row['name']);
        }

        return $options;
    }

    /**
     * @param array{code?: string, name?: string, description?: string, active?: string} $filters
     * @return array{0: string, 1: array<string, string|bool>}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = ['deleted_at IS NULL'];
        $params = [];

        foreach (['code', 'name', 'description'] as $field) {
            $value = trim($filters[$field] ?? '');
            if ($value === '') {
                continue;
            }

            $parameter = ':' . $field;
            $clauses[] = sprintf('LOWER(COALESCE(%s, \'\')) LIKE %s', $field, $parameter);
            $params[$parameter] = '%' . mb_strtolower($value) . '%';
        }

        $active = $filters['active'] ?? '';
        if ($active === '1') {
            $clauses[] = 'active = TRUE';
        } elseif ($active === '0') {
            $clauses[] = 'active = FALSE';
        }

        return [implode(' AND ', $clauses), $params];
    }
}
