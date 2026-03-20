<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function handle(Request $request): ResponseInterface
    {
        Route::create('/');
    }
}

?>
-----
<?php

namespace App;

use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Psr\Http\Message\ResponseInterface;

final class ExampleController
{
    public function handle(HttpFoundation\Request $request): ResponseInterface
    {
        Routing\Attribute\Route::create('/');
    }
}

?>
