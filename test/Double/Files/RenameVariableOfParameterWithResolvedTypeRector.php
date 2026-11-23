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

namespace Ergebnis\Rector\Rules\Test\Double\Files;

use PhpParser\Node;
use Rector\Rector;
use Symplify\RuleDocGenerator;

final class RenameVariableOfParameterWithResolvedTypeRector extends Rector\AbstractRector
{
    public function getRuleDefinition(): RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDocGenerator\ValueObject\RuleDefinition(
            'Renames the variable of a parameter whose type rector resolves to App\Controller\User\FindAction.',
            [],
        );
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Param::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Node\Param) {
            return null;
        }

        if (!$node->type instanceof Node\Name) {
            return null;
        }

        if (!$this->isName($node->type, 'App\Controller\User\FindAction')) {
            return null;
        }

        if (!$node->var instanceof Node\Expr\Variable) {
            return null;
        }

        if ($this->isName($node->var, 'findAction')) {
            return null;
        }

        $node->var->name = 'findAction';

        return $node;
    }
}
