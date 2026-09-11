<?php

declare(strict_types=1);

namespace App\Monitoring\Query;

final readonly class InventoryMetrics
{
    public function __construct(
        public int $printers,
        public int $withTelemetry,
        public int $divisions,
        public int $quotaRows,
    ) {
    }
}
