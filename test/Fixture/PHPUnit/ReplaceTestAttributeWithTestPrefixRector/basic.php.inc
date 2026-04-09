<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class BasicTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function onePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }
}

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class BasicTest extends Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }
}

?>
