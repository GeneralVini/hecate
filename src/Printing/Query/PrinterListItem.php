<?php

declare(strict_types=1);

namespace App\Printing\Query;

final readonly class PrinterListItem
{
    public function __construct(
        public int $id,
        public string $name,
        public string $host,
        public ?int $locationId,
        public ?string $location,
        public bool $active,
        public ?string $lastSeenAt,
    ) {
    }
}
