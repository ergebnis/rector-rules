<?php

namespace App;

class Bar
{
}

final class Foo extends Bar
{
    public function self(string $className): int
    {
        return match ($className) {
            Zebra::class => 1,
            self::class => 2,
            Foo::class => 3,
        };
    }

    public function static(string $className): int
    {
        return match ($className) {
            Zebra::class => 1,
            static::class => 2,
            Foo::class => 3,
        };
    }

    public function parent(string $className): int
    {
        return match ($className) {
            Zebra::class => 1,
            parent::class => 2,
            Bar::class => 3,
        };
    }
}

?>
