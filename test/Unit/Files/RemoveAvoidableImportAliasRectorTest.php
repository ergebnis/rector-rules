<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/rector-rules
 */

namespace Ergebnis\Rector\Rules\Test\Unit\Files;

use Rector\Testing;

/**
 * @covers \Ergebnis\Rector\Rules\Files\RemoveAvoidableImportAliasRector
 */
final class RemoveAvoidableImportAliasRectorTest extends Testing\PHPUnit\AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataOnPhp70
     */
    public function testOnPhp70(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp70(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/RemoveAvoidableImportAliasRector/Php70');
    }

    /**
     * @dataProvider provideDataOnPhp80
     *
     * @requires PHP >= 8.0.0
     */
    public function testOnPhp80(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return \Iterator<string, array{string}>
     */
    public static function provideDataOnPhp80(): iterable
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixture/Files/RemoveAvoidableImportAliasRector/Php80');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixture/Files/RemoveAvoidableImportAliasRector/config.php';
    }
}
