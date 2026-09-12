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

    public function create(string $name, ?string $description): int
    {
        $id = $this->db->createCommand(<<<'SQL'
INSERT INTO location (name, description)
VALUES (:name, :description)
RETURNING id
SQL)
            ->bindValue(':name', $name)
            ->bindValue(':description', $description)
            ->queryScalar();

        if ($id === null || $id === false) {
            throw new DomainException('Não foi possível cadastrar o local.');
        }

        $locationId = (int) $id;
        $this->audit('location.create', $locationId);

        return $locationId;
    }

    public function update(int $id, string $name, ?string $description, bool $active): void
    {
        $affected = $this->db->createCommand(<<<'SQL'
UPDATE location
SET name = :name,
    description = :description,
    active = :active,
    updated_at = CURRENT_TIMESTAMP
WHERE id = :id AND deleted_at IS NULL
SQL)
            ->bindValue(':id', $id)
            ->bindValue(':name', $name)
            ->bindValue(':description', $description)
            ->bindValue(':active', $active)
            ->execute();

        if ($affected === 0) {
            throw new DomainException('Local não encontrado ou já removido.');
        }

        $this->audit($active ? 'location.update' : 'location.deactivate', $id);
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

        $this->audit('location.delete', $id);
    }

    private function audit(string $action, int $id): void
    {
        $this->db->createCommand(<<<'SQL'
INSERT INTO audit_log (action, entity, entity_id)
VALUES (:action, 'location', :entity_id)
SQL)
            ->bindValue(':action', $action)
            ->bindValue(':entity_id', (string) $id)
            ->execute();
    }
}
