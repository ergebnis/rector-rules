<?php

namespace App\Http;

use App\Controller;
use App\Controller as Foo;

final class Router
{
    public function __construct(
        Foo\UserController $userController,
        Controller\DashboardController $dashboardController,
        \App\Controller\SettingsController $settingsController
    ) {
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;
use App\Controller as Foo;

final class Router
{
    public function __construct(
        Controller\UserController $userController,
        Controller\DashboardController $dashboardController,
        Controller\SettingsController $settingsController
    ) {
    }
}

?>
