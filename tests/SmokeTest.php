<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testEnvironmentIsReady(): void
    {
        self::assertTrue(true);
    }
}
