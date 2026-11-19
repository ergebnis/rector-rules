<?php

namespace App\Http;

use App\Controller\UserController as Controller;

final class Router
{
    public function user(): Controller
    {
        return new Controller();
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller\UserController;

final class Router
{
    public function user(): UserController
    {
        return new UserController();
    }
}

?>
