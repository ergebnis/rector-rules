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
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;
use Rector\BetterPhpDocParser;
use Rector\Testing;

abstract class AbstractFileTestCase extends Testing\PHPUnit\AbstractLazyTestCase
{
    final protected function read(string $code): Rules\Files\File
    {
        $fileReader = new Rules\Files\FileReader(
            $this->make(BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory::class),
            new Rules\Files\NameResolver(),
        );

        return $fileReader->read(self::parse($code));
    }

    final protected static function parse(string $code): Node\Stmt\Namespace_
    {
        $parser = (new ParserFactory())->createForHostVersion();

        $statements = $parser->parse($code);

        if (null === $statements) {
            throw new \RuntimeException('Could not parse code.');
        }

        $nodeTraverser = new NodeTraverser();

        $nodeTraverser->addVisitor(new NodeVisitor\NameResolver(
            null,
            [
                'preserveOriginalNames' => true,
            ],
        ));

        $statements = $nodeTraverser->traverse($statements);

        foreach ($statements as $statement) {
            if ($statement instanceof Node\Stmt\Namespace_) {
                return $statement;
            }
        }

        throw new \RuntimeException('Code needs to declare a namespace.');
    }
}
