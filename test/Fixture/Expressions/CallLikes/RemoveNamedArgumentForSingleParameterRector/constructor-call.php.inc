<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

final class Quux
{
    public function __construct(public string $value)
    {
    }
}

new Quux(value: 'hello');

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

final class Quux
{
    public function __construct(public string $value)
    {
    }
}

new Quux('hello');

?>
