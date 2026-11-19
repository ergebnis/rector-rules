<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class TestPrefixedMethodWithAttributeTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function testOnePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideData')]
    public function testSumIsCorrect(int $a, int $b, int $expected): void
    {
        self::assertSame($expected, $a + $b);
    }

    public static function provideData(): iterable
    {
        yield [1, 1, 2];
    }
}

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class TestPrefixedMethodWithAttributeTest extends Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }

    #[Framework\Attributes\DataProvider('provideData')]
    public function testSumIsCorrect(int $a, int $b, int $expected): void
    {
        self::assertSame($expected, $a + $b);
    }

    public static function provideData(): iterable
    {
        yield [1, 1, 2];
    }
}

?>
