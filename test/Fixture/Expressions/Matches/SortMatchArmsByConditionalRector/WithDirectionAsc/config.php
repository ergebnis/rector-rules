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

use Ergebnis\Rector\Rules;
use Rector\Config;
use Rector\ValueObject;

return static function (Config\RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(ValueObject\PhpVersion::PHP_80);

    $rectorConfig->ruleWithConfiguration(Rules\Expressions\Matches\SortMatchArmsByConditionalRector::class, [
        'direction' => 'asc',
    ]);
};
