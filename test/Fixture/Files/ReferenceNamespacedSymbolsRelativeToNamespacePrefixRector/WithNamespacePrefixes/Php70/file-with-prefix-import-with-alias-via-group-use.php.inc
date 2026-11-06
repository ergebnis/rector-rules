<?php

namespace App\Http;

use App\{Controller as Foo};

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
    }
}

?>
