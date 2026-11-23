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

use Ergebnis\Rector\Rules;
use PHPUnit\Framework;

/**
 * @covers \Ergebnis\Rector\Rules\Files\ClassLikeName
 */
final class ClassLikeNameTest extends Framework\TestCase
{
    public function testFromStringReturnsClassLikeName(): void
    {
        $value = 'Notifier';

        $classLikeName = Rules\Files\ClassLikeName::fromString($value);

        self::assertSame($value, $classLikeName->toString());
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Rules\Files\ClassLikeName::fromString('Notifier');
        $two = Rules\Files\ClassLikeName::fromString('Mailer');

        self::assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesDifferInCase(): void
    {
        $one = Rules\Files\ClassLikeName::fromString('Mailer');
        $two = Rules\Files\ClassLikeName::fromString('mailer');

        self::assertTrue($one->equals($two));
    }
}
