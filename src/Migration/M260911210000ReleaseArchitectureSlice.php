<?php

declare(strict_types=1);

namespace App\Migration;

use Override;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M260911210000ReleaseArchitectureSlice implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    #[Override]
    public function up(MigrationBuilder $b): void
    {
        $b->execute(<<<'SQL'
CREATE TABLE division_printer_access (
    division_id INTEGER NOT NULL REFERENCES division(id) ON DELETE CASCADE,
    printer_id INTEGER NOT NULL REFERENCES printer(id) ON DELETE CASCADE,
    PRIMARY KEY (division_id, printer_id)
)
SQL);
        $b->execute(<<<'SQL'
CREATE TABLE print_release_request (
    request_id VARCHAR(80) PRIMARY KEY,
    actor VARCHAR(80) NOT NULL,
    division_id INTEGER NOT NULL REFERENCES division(id),
    job_reference VARCHAR(160) NOT NULL UNIQUE,
    printer_id INTEGER NOT NULL REFERENCES printer(id),
    quota_id INTEGER NOT NULL REFERENCES quota(id),
    bw_pages INTEGER NOT NULL CHECK (bw_pages >= 0),
    color_pages INTEGER NOT NULL CHECK (color_pages >= 0),
    state VARCHAR(16) NOT NULL CHECK (state IN ('pending', 'dispatching', 'accepted', 'unknown')),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (bw_pages > 0 OR color_pages > 0)
)
SQL);
        $b->execute('CREATE INDEX idx_release_request_state ON print_release_request (state, created_at)');
    }

    #[Override]
    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('print_release_request');
        $b->dropTable('division_printer_access');
    }
}
