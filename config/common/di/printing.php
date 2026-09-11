<?php

declare(strict_types=1);

use App\Printing\Application\HeldJobGateway;
use App\Printing\Infrastructure\UnavailableHeldJobGateway;

return [
    HeldJobGateway::class => UnavailableHeldJobGateway::class,
];
