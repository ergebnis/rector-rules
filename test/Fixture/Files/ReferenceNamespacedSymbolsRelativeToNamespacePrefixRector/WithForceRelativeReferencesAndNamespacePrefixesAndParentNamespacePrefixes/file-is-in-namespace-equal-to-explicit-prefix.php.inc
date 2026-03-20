<?php

namespace Symfony\Component\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

final class Router
{
    /**
     * @param Route[] $routes
     *
     * @throws RouteNotFoundException
     */
    public function match(
        Request $request,
        array $routes
    ): Route {
        throw new RouteNotFoundException();
    }
}

?>
-----
<?php

namespace Symfony\Component\Routing;

use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class Router
{
    /**
     * @param Routing\Attribute\Route[] $routes
     *
     * @throws Routing\Exception\RouteNotFoundException
     */
    public function match(
        HttpFoundation\Request $request,
        array $routes
    ): Routing\Attribute\Route {
        throw new Routing\Exception\RouteNotFoundException();
    }
}

?>
