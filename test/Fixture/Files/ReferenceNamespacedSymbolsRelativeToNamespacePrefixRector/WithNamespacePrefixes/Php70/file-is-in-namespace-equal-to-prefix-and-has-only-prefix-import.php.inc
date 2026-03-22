<?php

namespace App\Controller;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    /**
     * @var Controller\DashboardAction
     */
    private $dashboard;

    /**
     * @var Controller\Security\Voter
     */
    private $voter;

    public function __construct(
        Controller\DashboardAction $dashboard,
        Controller\Security\Voter $voter,
        Controller\User\FindAction $findAction
    ) {
        $this->dashboard = $dashboard;
        $this->voter = $voter;
    }

    /**
     * @param Controller\DashboardAction|null $dashboard
     * @param Controller\DashboardAction[] $dashboards
     * @param array<string, Controller\DashboardAction> $map
     *
     * @return ResponseInterface
     *
     * @throws Controller\Exception\NotFoundException
     */
    public function __invoke(
        $dashboard,
        $dashboards,
        $map
    ): ResponseInterface {
        if ($dashboard instanceof Controller\DashboardAction) {
            return $dashboard->__invoke();
        }

        try {
            return $this->handle();
        } catch (Controller\Exception\NotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * @return Controller\User\CreateAction
     */
    public function create()
    {
        return new Controller\User\CreateAction();
    }
}

?>
-----
<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    /**
     * @var DashboardAction
     */
    private $dashboard;

    /**
     * @var Security\Voter
     */
    private $voter;

    public function __construct(
        DashboardAction $dashboard,
        Security\Voter $voter,
        User\FindAction $findAction
    ) {
        $this->dashboard = $dashboard;
        $this->voter = $voter;
    }

    /**
     * @param DashboardAction|null $dashboard
     * @param DashboardAction[] $dashboards
     * @param array<string, DashboardAction> $map
     *
     * @return ResponseInterface
     *
     * @throws Exception\NotFoundException
     */
    public function __invoke(
        $dashboard,
        $dashboards,
        $map
    ): ResponseInterface {
        if ($dashboard instanceof DashboardAction) {
            return $dashboard->__invoke();
        }

        try {
            return $this->handle();
        } catch (Exception\NotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * @return User\CreateAction
     */
    public function create()
    {
        return new User\CreateAction();
    }
}

?>
