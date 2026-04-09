<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;
use PHPUnit\Framework\Attributes\Test as TestAttribute;

final class AliasedAttributeTest extends Framework\TestCase
{
    #[TestAttribute]
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
use PHPUnit\Framework\Attributes\Test as TestAttribute;

final class AliasedAttributeTest extends Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }
}

?>
