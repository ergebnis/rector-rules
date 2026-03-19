<?php

use App\Controller\User\FindAction;
use App\Controller\User\CreateAction;
use Psr\Http\Message\ResponseInterface;

final class Router
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

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class Router
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
