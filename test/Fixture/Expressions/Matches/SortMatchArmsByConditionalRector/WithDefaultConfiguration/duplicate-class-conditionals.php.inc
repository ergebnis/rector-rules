<?php

namespace App;

use App\Foo;

$a = match ($className) {
    Zebra::class => handleZebra(),
    Foo::class => handleFoo(),
    \App\Foo::class => handleFooAgain(),
};

$b = match ($className) {
    Zebra::class => handleZebra(),
    Foo::class => handleFoo(),
    'App\Foo' => handleFooAgain(),
};

?>
