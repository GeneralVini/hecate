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

        $locationId = (int) $id;
        $this->audit('location.create', $locationId, $code);

        return $locationId;
    }

    public function update(int $id, string $code, string $name, ?string $description, bool $active): void
    {
        $duplicate = (int) $this->db->createCommand(<<<'SQL'
SELECT count(*)
FROM location
WHERE code = :code AND id <> :id
SQL)
            ->bindValue(':code', $code)
            ->bindValue(':id', $id)
            ->queryScalar();

        if ($duplicate > 0) {
            throw new DomainException('Já existe um local com esse código.');
        }

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

        $this->audit($active ? 'location.update' : 'location.deactivate', $id, $code);
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

        $code = $this->db->createCommand(
            'SELECT code FROM location WHERE id = :id AND deleted_at IS NULL',
        )
            ->bindValue(':id', $id)
            ->queryScalar();

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

        $this->audit('location.delete', $id, is_string($code) ? $code : null);
    }

    private function audit(string $action, int $id, ?string $code): void
    {
        $this->db->createCommand(<<<'SQL'
INSERT INTO audit_log (action, entity, entity_id, details)
VALUES (:action, 'location', :entity_id, :details)
SQL)
            ->bindValue(':action', $action)
            ->bindValue(':entity_id', (string) $id)
            ->bindValue(':details', $code === null ? null : 'code=' . $code)
            ->execute();
    }
}
