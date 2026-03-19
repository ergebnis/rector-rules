<?php

namespace App\Controller;

use App\Controller\User\FindAction;
use App\Controller\User\FindAction\Handler;
use App\Controller\DashboardAction;

final class FrontController
{
    public function __construct(
        private DashboardAction $dashboard,
        private FindAction $findAction,
        private Handler $handler,
    ) {
    }
}

?>
-----
<?php

namespace App\Controller;

use App\Controller;

final class FrontController
{
    public function __construct(
        private Controller\DashboardAction $dashboard,
        private Controller\User\FindAction $findAction,
        private Controller\User\FindAction\Handler $handler,
    ) {
    }
}

?>
