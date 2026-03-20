<?php

namespace App\Controller;

use App\Controller\User\FindAction;
use App\Controller\User\CreateAction;
use App\Controller\DashboardAction;
use App\Controller\Security\Voter;
use App\Controller\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    /**
     * @var DashboardAction
     */
    private $dashboard;

    /**
     * @var Voter
     */
    private $voter;

    public function __construct(
        DashboardAction $dashboard,
        Voter $voter,
        FindAction $findAction
    ) {
        $this->dashboard = $dashboard;
        $this->voter = $voter;
    }

    /**
     * @param DashboardAction|null $dashboard
     * @param DashboardAction[] $dashboards
     * @param array<string, DashboardAction> $map
     * @param \App\Controller\DashboardAction $fqn
     *
     * @return ResponseInterface
     *
     * @throws NotFoundException
     */
    public function __invoke(
        $dashboard,
        $dashboards,
        $map,
        $fqn
    ): ResponseInterface {
        if ($dashboard instanceof DashboardAction) {
            return $dashboard->__invoke();
        }

        try {
            return $this->handle();
        } catch (NotFoundException $exception) {
            throw $exception;
        }
    }

    public function fromFqn(): void
    {
        $action = new \App\Controller\DashboardAction();
    }

    /**
     * @return CreateAction
     */
    public function create()
    {
        return new CreateAction();
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
     * @param Controller\DashboardAction $fqn
     *
     * @return ResponseInterface
     *
     * @throws Controller\Exception\NotFoundException
     */
    public function __invoke(
        $dashboard,
        $dashboards,
        $map,
        $fqn
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

    public function fromFqn(): void
    {
        $action = new Controller\DashboardAction();
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
