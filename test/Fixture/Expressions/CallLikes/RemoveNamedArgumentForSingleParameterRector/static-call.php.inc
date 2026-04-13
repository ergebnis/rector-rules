<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

final class Baz
{
    public static function qux(string $value): void
    {
    }
}

Baz::qux(value: 'hello');

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

final class Baz
{
    public static function qux(string $value): void
    {
    }
}

Baz::qux('hello');

?>
