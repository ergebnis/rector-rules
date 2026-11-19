<?php

namespace App;

use App\Foo;

final class Bar
{
    public function data(): array
    {
        return [
            [
                'b' => 1,
                'a' => 2,
                'b' => 3,
            ],
            [
                Foo::class => 'first',
                \App\Foo::class => 'second',
            ],
            [
                Foo::class => 'first',
                'App\Foo' => 'second',
            ],
            [
                Zebra::class => 'first',
                self::class => 'second',
                Bar::class => 'third',
            ],
        ];
    }
}

?>
