<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

final class Foo
{
    public function bar(string $value): void
    {
    }
}

$foo = new Foo();

$foo->bar(value: 'hello');

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

final class Foo
{
    public function bar(string $value): void
    {
    }
}

$foo = new Foo();

$foo->bar('hello');

?>
