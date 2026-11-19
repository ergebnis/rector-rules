<?php

namespace App\Http;

use App\Controller as Foo;

final class Router
{
    public function dashboard(Controller\Middleware $middleware): Foo\DashboardController
    {
    }
}

?>
