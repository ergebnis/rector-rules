<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class MultipleAttributesTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideData')]
    public function onePlusOneShouldBeTwo(int $a, int $b, int $expected): void
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

final class MultipleAttributesTest extends Framework\TestCase
{
    #[Framework\Attributes\DataProvider('provideData')]
    public function testOnePlusOneShouldBeTwo(int $a, int $b, int $expected): void
    {
        self::assertSame($expected, $a + $b);
    }

    public static function provideData(): iterable
    {
        yield [1, 1, 2];
    }
}

?>
