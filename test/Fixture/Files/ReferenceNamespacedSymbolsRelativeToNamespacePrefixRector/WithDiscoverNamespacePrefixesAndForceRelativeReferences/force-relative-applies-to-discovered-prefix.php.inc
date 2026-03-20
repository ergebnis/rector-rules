<?php

namespace App\Controller;

use App\Controller\User\FindAction;
use App\Controller\DashboardAction;
use Symfony\Component\HttpFoundation\Request;

final class FrontController
{
    public function __construct(
        private DashboardAction $dashboard,
        private FindAction $findAction,
        private Request $request,
    ) {
    }
}

?>
-----
<?php

namespace App\Controller;

use App\Controller;
use Symfony\Component;

final class FrontController
{
    public function __construct(
        private Controller\DashboardAction $dashboard,
        private Controller\User\FindAction $findAction,
        private Component\HttpFoundation\Request $request,
    ) {
    }
}

?>
