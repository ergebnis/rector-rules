<?php

namespace App\Http;

use App\Controller\UserController;

final class Router
{
    public function dashboard(UserController $userController): string
    {
        $timeout = \App\Controller\Config\TIMEOUT;
        $limit = \App\Controller\LIMIT;

        \App\Controller\render('/dashboard');

        return \App\Controller\Helper\resolve_path('/users');
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;

final class Router
{
    public function dashboard(Controller\UserController $userController): string
    {
        $timeout = Controller\Config\TIMEOUT;
        $limit = Controller\LIMIT;

        Controller\render('/dashboard');

        return Controller\Helper\resolve_path('/users');
    }
}

?>
