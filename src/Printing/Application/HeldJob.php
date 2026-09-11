<?php

declare(strict_types=1);

namespace App\Printing\Application;

use InvalidArgumentException;

/** Trusted metadata returned by the hold/release adapter; contains no document content. */
final readonly class HeldJob
{
    public function __construct(
        public string $reference,
        public string $ownerSubject,
        public int $bwPages,
        public int $colorPages,
    ) {
        if (
            $reference === '' || strlen($reference) > 160 || $ownerSubject === ''
            || $bwPages < 0 || $colorPages < 0 || $bwPages + $colorPages < 1
            || $bwPages > 2147483647 || $colorPages > 2147483647
        ) {
            throw new InvalidArgumentException('Metadados do job retido inválidos.');
        }
    }
}
