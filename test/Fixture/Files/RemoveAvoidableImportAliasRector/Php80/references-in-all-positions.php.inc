<?php

namespace App\Http;

use App\Controller as Foo;

#[Foo\Attribute\Route('/dashboard')]
final class Router extends Foo\AbstractRouter implements Foo\Contract\Routable
{
    use Foo\Concern\HasMiddleware;

    /**
     * @param array<int, Foo\UserController> $controllers
     *
     * @return list<Foo\Http\Response>
     */
    public function dispatch(array $controllers, object $controller): array
    {
        if ($controller instanceof Foo\UserController) {
            return Foo\Http\Response::list();
        }

        try {
            Foo\Helper\render(Foo\Config\TIMEOUT);
        } catch (Foo\Exception\NotFound $exception) {
        }

        return [
            Foo\UserController::class,
        ];
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;

#[Controller\Attribute\Route('/dashboard')]
final class Router extends Controller\AbstractRouter implements Controller\Contract\Routable
{
    use Controller\Concern\HasMiddleware;

    /**
     * @param array<int, Controller\UserController> $controllers
     *
     * @return list<Controller\Http\Response>
     */
    public function dispatch(array $controllers, object $controller): array
    {
        if ($controller instanceof Controller\UserController) {
            return Controller\Http\Response::list();
        }

        try {
            Controller\Helper\render(Controller\Config\TIMEOUT);
        } catch (Controller\Exception\NotFound $exception) {
        }

        return [
            Controller\UserController::class,
        ];
    }
}

?>
