<?php

declare(strict_types=1);

namespace App\Printing\Application;

use InvalidArgumentException;

final readonly class RegisterPrinterInput
{
    public function __construct(public string $name, public string $host, public ?int $locationId)
    {
        if (trim($name) === '' || preg_match('/^.{1,160}$/us', $name) !== 1) {
            throw new InvalidArgumentException('Nome deve conter entre 1 e 160 caracteres.');
        }
        if (
            strlen($host) > 160 || $host === ''
            || (filter_var($host, FILTER_VALIDATE_IP) === false
                && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false)
        ) {
            throw new InvalidArgumentException('Informe um IP ou hostname válido de até 160 caracteres.');
        }
        if ($locationId !== null && $locationId < 1) {
            throw new InvalidArgumentException('Selecione um local válido.');
        }
    }
}
