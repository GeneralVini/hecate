<?php

declare(strict_types=1);

namespace App\Organization\Query;

final readonly class LocationListItem
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public ?string $description,
        public bool $active,
    ) {
    }
}
