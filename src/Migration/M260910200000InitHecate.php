<?php

declare(strict_types=1);

namespace App\Migration;

use Override;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M260910200000InitHecate implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    #[Override]
    public function up(MigrationBuilder $b): void
    {
        $b->execute(<<<'SQL'
CREATE TABLE division (
    id SERIAL PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
SQL);

        $b->execute(<<<'SQL'
CREATE TABLE printer (
    id SERIAL PRIMARY KEY,
    name VARCHAR(160) NOT NULL UNIQUE,
    host VARCHAR(160) NOT NULL,
    vendor VARCHAR(160),
    model VARCHAR(160),
    location VARCHAR(160),
    is_color BOOLEAN NOT NULL DEFAULT FALSE,
    is_duplex BOOLEAN NOT NULL DEFAULT FALSE,
    monitor_source VARCHAR(40),
    last_seen_at TIMESTAMP,
    enabled BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
SQL);

        $b->execute(<<<'SQL'
CREATE TABLE quota (
    id SERIAL PRIMARY KEY,
    division_id INTEGER NOT NULL REFERENCES division(id) ON DELETE CASCADE,
    period VARCHAR(7) NOT NULL,
    bw_allocated INTEGER NOT NULL DEFAULT 0,
    bw_used INTEGER NOT NULL DEFAULT 0,
    bw_reserved INTEGER NOT NULL DEFAULT 0,
    color_allocated INTEGER NOT NULL DEFAULT 0,
    color_used INTEGER NOT NULL DEFAULT 0,
    color_reserved INTEGER NOT NULL DEFAULT 0,
    CONSTRAINT uq_quota_division_period UNIQUE (division_id, period)
)
SQL);

        $b->execute(<<<'SQL'
CREATE TABLE contract (
    id SERIAL PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    type VARCHAR(24) NOT NULL,
    bw_quota INTEGER,
    color_quota INTEGER,
    bw_unit_price NUMERIC(12, 4),
    color_unit_price NUMERIC(12, 4),
    bw_overage_price NUMERIC(12, 4),
    color_overage_price NUMERIC(12, 4),
    active BOOLEAN NOT NULL DEFAULT TRUE
)
SQL);

        $b->execute(<<<'SQL'
CREATE TABLE audit_log (
    id BIGSERIAL PRIMARY KEY,
    actor VARCHAR(80),
    action VARCHAR(120) NOT NULL,
    entity VARCHAR(80),
    entity_id VARCHAR(80),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
SQL);
    }

    #[Override]
    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('audit_log');
        $b->dropTable('contract');
        $b->dropTable('quota');
        $b->dropTable('printer');
        $b->dropTable('division');
    }
}
