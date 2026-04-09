<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class NotInTestClass
{
    #[Framework\Attributes\Test]
    public function something(): void
    {
    }
}

?>
