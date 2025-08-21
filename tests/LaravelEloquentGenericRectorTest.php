<?php

declare(strict_types=1);

namespace RectorLaravelCustomRules\Tests;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LaravelEloquentGenericRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
