<?php

namespace App\Http;

use Admin\Controller as AdminController;
use App\Controller as AppController;

final class Router
{
    public function dashboard(AdminController\DashboardController $adminDashboard): AppController\DashboardController
    {
    }
}

?>
-----
<?php

namespace App\Http;

use Admin\Controller;
use App\Controller as AppController;

final class Router
{
    public function dashboard(Controller\DashboardController $adminDashboard): AppController\DashboardController
    {
    }
}

?>
