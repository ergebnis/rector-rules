<?php

namespace App;

use Symfony\Component\Routing\Attribute\Route;
use Other\Routing;

final class ExampleController
{
    public function handle(Routing $router): void
    {
        Route::create('/');
    }
}

?>
