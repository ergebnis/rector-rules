<?php

namespace App;

use App\Controller\User\FindAction;
use App\Controller\User\CreateAction;
use Psr\Http\Message\ResponseInterface;

final class Kernel
{
    /**
     * @var FindAction
     */
    private $findAction;

    public function __construct(
        FindAction $findAction,
        CreateAction $createAction
    ) {
        $this->findAction = $findAction;
    }

    public function dispatch(): ResponseInterface
    {
        $action = new \App\Controller\DashboardAction();
    }
}

?>
-----
<?php

namespace App;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class Kernel
{
    /**
     * @var Controller\User\FindAction
     */
    private $findAction;

    public function __construct(
        Controller\User\FindAction $findAction,
        Controller\User\CreateAction $createAction
    ) {
        $this->findAction = $findAction;
    }

    public function dispatch(): ResponseInterface
    {
        $action = new Controller\DashboardAction();
    }
}

?>
