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
        if ($input->locationId !== null) {
            $locationExists = (int) $this->db->createCommand(<<<'SQL'
SELECT count(*)
FROM location
WHERE id = :id AND active = TRUE AND deleted_at IS NULL
SQL)
                ->bindValue(':id', $input->locationId)
                ->queryScalar();

            if ($locationExists === 0) {
                throw new DomainException('O local selecionado não está disponível.');
            }
        }

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
}
