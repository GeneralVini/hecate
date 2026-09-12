<?php

declare(strict_types=1);

namespace App\Printing\Infrastructure;

use App\Printing\Application\RegisterPrinterInput;
use DomainException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class PrinterWriter
{
    public function __construct(private ConnectionInterface $db)
    {
    }

    public function insert(RegisterPrinterInput $input): int
    {
        $this->assertLocationAvailable($input->locationId);

        $id = $this->db->createCommand(<<<'SQL'
INSERT INTO printer (name, host, location_id)
VALUES (:name, :host, :location_id)
ON CONFLICT (name) DO NOTHING
RETURNING id
SQL)
            ->bindValue(':name', $input->name)
            ->bindValue(':host', $input->host)
            ->bindValue(':location_id', $input->locationId)
            ->queryScalar();
        if ($id === null || $id === false) {
            throw new DomainException('Já existe uma impressora com esse nome.');
        }
        return (int) $id;
    }

    public function update(int $id, string $name, string $host, ?int $locationId, bool $active): void
    {
        $this->assertLocationAvailable($locationId);

        $affected = $this->db->createCommand(<<<'SQL'
UPDATE printer
SET name = :name,
    host = :host,
    location_id = :location_id,
    active = :active,
    updated_at = NOW()
WHERE id = :id
  AND deleted_at IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM printer other
      WHERE other.name = :name
        AND other.id <> :id
        AND other.deleted_at IS NULL
  )
SQL)
            ->bindValue(':id', $id)
            ->bindValue(':name', $name)
            ->bindValue(':host', $host)
            ->bindValue(':location_id', $locationId)
            ->bindValue(':active', $active)
            ->execute();

        if ($affected === 0) {
            throw new DomainException('Impressora não encontrada ou nome já utilizado.');
        }
    }

    public function softDelete(int $id): void
    {
        $affected = $this->db->createCommand(<<<'SQL'
UPDATE printer
SET active = FALSE,
    deleted_at = NOW(),
    updated_at = NOW()
WHERE id = :id
  AND deleted_at IS NULL
SQL)
            ->bindValue(':id', $id)
            ->execute();

        if ($affected === 0) {
            throw new DomainException('Impressora não encontrada.');
        }
    }

    private function assertLocationAvailable(?int $locationId): void
    {
        if ($locationId === null) {
            return;
        }

        $locationExists = (int) $this->db->createCommand(<<<'SQL'
SELECT count(*)
FROM location
WHERE id = :id AND active = TRUE AND deleted_at IS NULL
SQL)
            ->bindValue(':id', $locationId)
            ->queryScalar();

        if ($locationExists === 0) {
            throw new DomainException('O local selecionado não está disponível.');
        }
    }
}
