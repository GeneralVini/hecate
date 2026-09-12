<?php

declare(strict_types=1);

namespace App\Organization\Infrastructure;

use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class LocationStore
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function create(string $code, string $name, ?string $description): int
    {
        $id = $this->db->createCommand(<<<'SQL'
INSERT INTO location (code, name, description)
VALUES (:code, :name, :description)
ON CONFLICT (code) DO NOTHING
RETURNING id
SQL)
            ->bindValue(':code', $code)
            ->bindValue(':name', $name)
            ->bindValue(':description', $description)
            ->queryScalar();

        if ($id === null || $id === false) {
            throw new DomainException('Já existe um local com esse código.');
        }

        return (int) $id;
    }

    public function update(int $id, string $code, string $name, ?string $description, bool $active): void
    {
        $affected = $this->db->createCommand(<<<'SQL'
UPDATE location
SET code = :code,
    name = :name,
    description = :description,
    active = :active,
    updated_at = CURRENT_TIMESTAMP
WHERE id = :id AND deleted_at IS NULL
SQL)
            ->bindValue(':id', $id)
            ->bindValue(':code', $code)
            ->bindValue(':name', $name)
            ->bindValue(':description', $description)
            ->bindValue(':active', $active)
            ->execute();

        if ($affected === 0) {
            throw new DomainException('Local não encontrado ou já removido.');
        }
    }

    public function softDelete(int $id): void
    {
        $inUse = (int) $this->db->createCommand(<<<'SQL'
SELECT count(*)
FROM printer
WHERE location_id = :id AND deleted_at IS NULL
SQL)
            ->bindValue(':id', $id)
            ->queryScalar();

        if ($inUse > 0) {
            throw new DomainException('O local possui impressoras vinculadas e não pode ser removido.');
        }

        $affected = $this->db->createCommand(<<<'SQL'
UPDATE location
SET active = FALSE,
    deleted_at = CURRENT_TIMESTAMP,
    updated_at = CURRENT_TIMESTAMP
WHERE id = :id AND deleted_at IS NULL
SQL)
            ->bindValue(':id', $id)
            ->execute();

        if ($affected === 0) {
            throw new DomainException('Local não encontrado ou já removido.');
        }
    }
}
