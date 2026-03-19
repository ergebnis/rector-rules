<?php

namespace App;

use Symfony\Component\Routing\Attribute\Route;

final class Routing
{
    public function handle(): void
    {
        Route::create('/');
    }
}

?>
