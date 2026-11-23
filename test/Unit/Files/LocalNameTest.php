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
 * @covers \Ergebnis\Rector\Rules\Files\LocalName
 */
final class LocalNameTest extends Framework\TestCase
{
    public function testFromStringReturnsLocalName(): void
    {
        $value = 'Mailer';

        $localName = Rules\Files\LocalName::fromString($value);

        self::assertSame($value, $localName->toString());
    }
}
