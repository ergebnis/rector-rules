<?php

namespace App\Http;

use App\Controller as Foo;

final class Router
{
    /**
     * @param Controller\Middleware $middleware
     */
    public function dashboard($middleware): Foo\DashboardController
    {
    }
}

?>
