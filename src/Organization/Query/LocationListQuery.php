<?php

declare(strict_types=1);

namespace App\Organization\Query;

use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class LocationListQuery
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    /** @return list<LocationListItem> */
    public function all(): array
    {
        $rows = $this->db->createCommand(<<<'SQL'
SELECT id, code, name, description, active
FROM location
WHERE deleted_at IS NULL
ORDER BY lower(name), id
SQL)->queryAll();

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
}
