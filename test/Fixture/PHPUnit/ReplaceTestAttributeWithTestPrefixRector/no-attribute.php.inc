<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class NoAttributeTest extends Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo(): void
    {
        self::assertSame(2, 1 + 1);
    }
}

?>
