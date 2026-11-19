<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

#[\Attribute]
final class Description
{
    public function __construct(public string $value)
    {
    }
}

#[Description(value: 'hello')]
final class Corge
{
}

?>
-----
<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

#[\Attribute]
final class Description
{
    public function __construct(public string $value)
    {
    }
}

#[Description('hello')]
final class Corge
{
}

?>
