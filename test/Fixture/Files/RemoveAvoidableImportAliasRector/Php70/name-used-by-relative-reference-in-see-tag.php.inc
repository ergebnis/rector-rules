<?php

namespace App\Http;

use App\Controller as Foo;

/**
 * @see Controller\Middleware
 */
final class Router
{
    public function dashboard(): Foo\DashboardController
    {
    }
}

?>
