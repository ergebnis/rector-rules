<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

function acceptsVariadicMultiple(string ...$values): void
{
}

acceptsVariadicMultiple(values: 'a', 'b', 'c');

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

function acceptsVariadicMultiple(string ...$values): void
{
}

acceptsVariadicMultiple('a', 'b', 'c');

?>
