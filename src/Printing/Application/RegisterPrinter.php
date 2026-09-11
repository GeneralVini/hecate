<?php

declare(strict_types=1);

namespace App\Printing\Application;

use App\Printing\Infrastructure\PrinterWriter;

/** Transitional inventory use case; the existing HTTP route still requires OIDC/RBAC. */
final readonly class RegisterPrinter
{
    public function __construct(private PrinterWriter $writer)
    {
    }

    public function execute(RegisterPrinterInput $input): int
    {
        return $this->writer->insert($input);
    }
}
