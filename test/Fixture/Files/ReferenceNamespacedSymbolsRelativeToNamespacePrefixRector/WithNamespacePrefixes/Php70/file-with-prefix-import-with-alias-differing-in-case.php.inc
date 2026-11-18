<?php

namespace App\Http;

use App\Controller as controller;
use App\Controller\DashboardController;

final class Router
{
    public function __construct(
        controller\UserController $userController,
        DashboardController $dashboardController
    ) {
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller as controller;

final class Router
{
    public function __construct(
        Controller\UserController $userController,
        Controller\DashboardController $dashboardController
    ) {
    }
}

?>
