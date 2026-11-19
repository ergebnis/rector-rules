<?php

namespace App\Http;

use App\Controller as Foo;

final class Router
{
    /**
     * @var Foo\UserController
     */
    private $userController;

    public function __construct(Foo\UserController $userController)
    {
        $this->userController = $userController;
    }

    public function dashboard(): Foo\DashboardController
    {
        return new Foo\DashboardController();
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;

final class Router
{
    /**
     * @var Controller\UserController
     */
    private $userController;

    public function __construct(Controller\UserController $userController)
    {
        $this->userController = $userController;
    }

    public function dashboard(): Controller\DashboardController
    {
        return new Controller\DashboardController();
    }
}

?>
