<?php

namespace App\Http;

use App\Controller as controller;

final class Router
{
    public function dashboard(): controller\DashboardController
    {
    }
}

?>
-----
<?php

namespace App\Http;

use App\Controller;

final class Router
{
    public function dashboard(): Controller\DashboardController
    {
    }
}

?>
