<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application;

use InvalidArgumentException;

/** Created by a trusted authentication adapter, never hydrated from an HTTP payload. */
final readonly class AuthenticatedActor
{
    /** @param list<string> $permissions */
    public function __construct(
        public string $subject,
        public int $divisionId,
        private array $permissions,
    ) {
        if ($subject === '' || strlen($subject) > 80 || $divisionId < 1) {
            throw new InvalidArgumentException('Identidade ou divisão inválida.');
        }
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
