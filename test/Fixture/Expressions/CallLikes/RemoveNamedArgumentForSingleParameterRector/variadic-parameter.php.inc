<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

function acceptsVariadic(string ...$values): void
{
}

acceptsVariadic(values: 'a');

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

function acceptsVariadic(string ...$values): void
{
}

acceptsVariadic('a');

?>
