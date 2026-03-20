<?php

namespace Example;

use App\Controller\DashboardAction;
use Symfony\Component\HttpFoundation\Request;

final class Kernel
{
    public function handle(DashboardAction $action, Request $request): void
    {
    }
}

?>
-----
<?php

namespace Example;

use App\Controller;
use Symfony\Component;

final class Kernel
{
    public function handle(Controller\DashboardAction $action, Component\HttpFoundation\Request $request): void
    {
    }
}

?>
