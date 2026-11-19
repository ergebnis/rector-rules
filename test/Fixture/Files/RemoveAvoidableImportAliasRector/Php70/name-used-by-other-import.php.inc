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
