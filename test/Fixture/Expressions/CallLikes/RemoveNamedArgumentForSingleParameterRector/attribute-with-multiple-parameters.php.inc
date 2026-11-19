<?php

namespace Ergebnis\Rector\Rules\Test\Fixture\Expressions\CallLikes\RemoveNamedArgumentForSingleParameterRector;

#[\Attribute]
final class Label
{
    public function __construct(public string $value, public string $color = 'gray')
    {
    }
}

#[Label(value: 'hello')]
final class Grault
{
}

?>
