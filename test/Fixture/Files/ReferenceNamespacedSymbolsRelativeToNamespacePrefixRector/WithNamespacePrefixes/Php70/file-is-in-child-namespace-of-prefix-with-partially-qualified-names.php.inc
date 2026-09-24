<?php

namespace App\Controller\User;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class ListAction
{
    /**
     * @param Controller\User\FindAction $findAction
     * @param Controller\User\Profile\ShowAction $showAction
     * @param Controller\Security\Voter $voter
     *
     * @return ResponseInterface
     */
    public function __invoke(
        Controller\User\FindAction $findAction,
        Controller\User\Profile\ShowAction $showAction,
        Controller\Security\Voter $voter
    ): ResponseInterface {
        $action = new Controller\User\CreateAction();
        $dashboardAction = new Controller\DashboardAction();
    }
}

?>
-----
<?php

namespace App\Controller\User;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class ListAction
{
    /**
     * @param FindAction $findAction
     * @param Profile\ShowAction $showAction
     * @param Controller\Security\Voter $voter
     *
     * @return ResponseInterface
     */
    public function __invoke(
        FindAction $findAction,
        Profile\ShowAction $showAction,
        Controller\Security\Voter $voter
    ): ResponseInterface {
        $action = new CreateAction();
        $dashboardAction = new Controller\DashboardAction();
    }
}

?>
