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
 * @covers \Ergebnis\Rector\Rules\Files\Spelling
 */
final class SpellingTest extends Framework\TestCase
{
    public function testFromStringReturnsSpelling(): void
    {
        $value = 'Smarty\Smarty';

        $spelling = Rules\Files\Spelling::fromString($value);

        self::assertSame($value, $spelling->toString());
    }

    public function testEqualsReturnsFalseWhenValuesAreDifferent(): void
    {
        $one = Rules\Files\Spelling::fromString('\Smarty\Smarty');
        $two = Rules\Files\Spelling::fromString('Smarty\Smarty');

        self::assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueWhenValuesAreSame(): void
    {
        $one = Rules\Files\Spelling::fromString('Smarty\Smarty');
        $two = Rules\Files\Spelling::fromString('Smarty\Smarty');

        self::assertTrue($one->equals($two));
    }

    public function testFullyQualifiedSpelling(): void
    {
        $spelling = Rules\Files\Spelling::fromString('\Smarty\Smarty');

        self::assertTrue($spelling->isFullyQualified());
        self::assertFalse($spelling->isUnqualified());
        self::assertSame('Smarty', $spelling->leadingSegment());
        self::assertSame('Smarty\Smarty', $spelling->withoutLeadingBackslash());
    }

    public function testQualifiedSpelling(): void
    {
        $spelling = Rules\Files\Spelling::fromString('Mailer\Transport');

        self::assertFalse($spelling->isFullyQualified());
        self::assertFalse($spelling->isUnqualified());
        self::assertSame('Mailer', $spelling->leadingSegment());
        self::assertSame('Mailer\Transport', $spelling->withoutLeadingBackslash());
    }

    public function testUnqualifiedSpelling(): void
    {
        $spelling = Rules\Files\Spelling::fromString('Notifier');

        self::assertFalse($spelling->isFullyQualified());
        self::assertTrue($spelling->isUnqualified());
        self::assertSame('Notifier', $spelling->leadingSegment());
        self::assertSame('Notifier', $spelling->withoutLeadingBackslash());
    }
}
