<?php

declare(strict_types=1);

namespace App\Quota\Infrastructure;

use App\Quota\Domain\QuotaAvailability;
use DomainException;
use LogicException;
use Yiisoft\Db\Connection\ConnectionInterface;

final readonly class QuotaReservation
{
    public function __construct(private ConnectionInterface $db, private QuotaAvailability $availability)
    {
    }

    /** Caller owns the transaction, shared with request and audit persistence. */
    public function reserve(int $divisionId, string $period, int $bwPages, int $colorPages): int
    {
        if ($this->db->getTransaction() === null) {
            throw new LogicException('Reserva exige transação ativa.');
        }
        $row = $this->db->createCommand(<<<'SQL'
SELECT id, bw_allocated - bw_used - bw_reserved AS bw_available,
       color_allocated - color_used - color_reserved AS color_available
FROM quota WHERE division_id = :division AND period = :period FOR UPDATE
SQL)
            ->bindValue(':division', $divisionId)
            ->bindValue(':period', $period)
            ->queryOne();
        if ($row === null) {
            throw new DomainException('Cota da competência não configurada.');
        }
        $this->availability->assertCanReserve(
            (int) $row['bw_available'],
            (int) $row['color_available'],
            $bwPages,
            $colorPages,
        );
        $id = (int) $row['id'];
        $this->db->createCommand(<<<'SQL'
UPDATE quota SET bw_reserved = bw_reserved + :bw, color_reserved = color_reserved + :color WHERE id = :id
SQL)
            ->bindValue(':bw', $bwPages)
            ->bindValue(':color', $colorPages)
            ->bindValue(':id', $id)
            ->execute();
        return $id;
    }
}
