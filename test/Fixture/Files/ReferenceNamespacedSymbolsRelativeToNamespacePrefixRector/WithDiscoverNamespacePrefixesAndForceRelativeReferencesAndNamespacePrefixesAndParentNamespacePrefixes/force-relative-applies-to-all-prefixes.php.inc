<?php

namespace App\Controller;

use App\Controller\DashboardAction;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

final class FrontController
{
    public function handle(
        DashboardAction $action,
        Request $request,
        LoggerInterface $logger,
    ): void {
    }
}

?>
-----
<?php

namespace App\Controller;

use App\Controller;
use Symfony\Component\HttpFoundation;
use Psr\Log;

final class FrontController
{
    public function handle(
        Controller\DashboardAction $action,
        HttpFoundation\Request $request,
        Log\LoggerInterface $logger,
    ): void {
    }
}

?>
