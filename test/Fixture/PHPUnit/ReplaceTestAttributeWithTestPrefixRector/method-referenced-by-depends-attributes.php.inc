<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class MethodReferencedByDependsAttributesTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function producesOne(): int
    {
        return 1;
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\Depends('producesOne')]
    public function consumesOne(int $one): void
    {
        self::assertSame(1, $one);
    }

    #[Framework\Attributes\Depends('producesOne')]
    #[Framework\Attributes\DependsUsingDeepClone('producesOne')]
    #[Framework\Attributes\DependsUsingShallowClone('producesOne')]
    public function testConsumesOneAgain(int $one): void
    {
        self::assertSame(1, $one);
    }
}

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\PHPUnit\ReplaceTestAttributeWithTestPrefixRector;

use PHPUnit\Framework;

final class MethodReferencedByDependsAttributesTest extends Framework\TestCase
{
    public function testProducesOne(): int
    {
        return 1;
    }

    #[Framework\Attributes\Depends('testProducesOne')]
    public function testConsumesOne(int $one): void
    {
        self::assertSame(1, $one);
    }

    #[Framework\Attributes\Depends('testProducesOne')]
    #[Framework\Attributes\DependsUsingDeepClone('testProducesOne')]
    #[Framework\Attributes\DependsUsingShallowClone('testProducesOne')]
    public function testConsumesOneAgain(int $one): void
    {
        self::assertSame(1, $one);
    }
}

?>
