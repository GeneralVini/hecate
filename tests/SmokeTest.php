<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\Contract;
use App\Model\Division;
use App\Model\Printer;
use App\Model\Quota;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SmokeTest extends TestCase
{
    public function testEnvironmentIsReady(): void
    {
        self::assertTrue(PHP_VERSION_ID >= 80200);
    }

    public function testCoreActiveRecordMappings(): void
    {
        self::assertSame('{{%contract}}', $this->tableNameWithoutConstructor(Contract::class));
        self::assertSame('{{%division}}', $this->tableNameWithoutConstructor(Division::class));
        self::assertSame('{{%printer}}', $this->tableNameWithoutConstructor(Printer::class));
        self::assertSame('{{%quota}}', $this->tableNameWithoutConstructor(Quota::class));
    }

    /**
     * @param class-string<Contract|Division|Printer|Quota> $className
     */
    private function tableNameWithoutConstructor(string $className): string
    {
        $model = (new ReflectionClass($className))->newInstanceWithoutConstructor();

        return $model->tableName();
    }
}
