<?php

namespace App\Controller\Admin;

use App\Controller as Foo;
use App\Controller\UserController;

final class Router
{
    public function __construct(
        Foo\DashboardController $dashboardController,
        UserController $userController
    ) {
    }
}

?>
