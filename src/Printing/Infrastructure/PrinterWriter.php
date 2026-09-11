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
        $id = $this->db->createCommand(<<<'SQL'
INSERT INTO printer (name, host, location)
VALUES (:name, :host, :location)
ON CONFLICT (name) DO NOTHING
RETURNING id
SQL)->bindValues([':name' => $input->name, ':host' => $input->host, ':location' => $input->location])->queryScalar();
        if ($id === null || $id === false) {
            throw new DomainException('Já existe uma impressora com esse nome.');
        }
        return (int) $id;
    }
}
