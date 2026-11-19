<?php

namespace App\Http;

use App\Controller\UserController;

final class Router
{
    public function dashboard(UserController $userController): string
    {
        return Controller();
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
        return Controller();
    }
}

?>
