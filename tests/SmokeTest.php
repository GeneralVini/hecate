<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\Contract;
use App\Model\Division;
use App\Model\Printer;
use App\Model\Quota;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testEnvironmentIsReady(): void
    {
        self::assertTrue(PHP_VERSION_ID >= 80200);
    }

    public function testCoreActiveRecordMappings(): void
    {
        self::assertSame('{{%contract}}', Contract::tableName());
        self::assertSame('{{%division}}', Division::tableName());
        self::assertSame('{{%printer}}', Printer::tableName());
        self::assertSame('{{%quota}}', Quota::tableName());
    }
}
