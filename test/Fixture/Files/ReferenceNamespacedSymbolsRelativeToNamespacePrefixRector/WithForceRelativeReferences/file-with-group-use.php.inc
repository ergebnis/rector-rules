<?php

namespace App\Controller;

use App\Controller\{DashboardAction, User\FindAction};
use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    public function __construct(
        private DashboardAction $dashboard,
        private FindAction $findAction,
    ) {
    }

    public function handle(): ResponseInterface
    {
    }
}

?>
-----
<?php

namespace App\Controller;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    public function __construct(
        private Controller\DashboardAction $dashboard,
        private Controller\User\FindAction $findAction,
    ) {
    }

    public function handle(): ResponseInterface
    {
    }
}

?>
