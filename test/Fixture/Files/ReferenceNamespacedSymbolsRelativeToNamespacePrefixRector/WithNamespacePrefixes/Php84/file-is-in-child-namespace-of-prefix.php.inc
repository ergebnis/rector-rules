<?php

namespace App\Controller\User;

use App\Controller\Contract\Arrayable;
use App\Controller\DashboardAction;
use App\Controller\Exception\NotFoundException;
use App\Controller\Routing\Attribute\Route;
use App\Controller\Security\Voter;
use Psr\Http\Message\ResponseInterface;

final class EditAction
{
    public DashboardAction $dashboard {
        get => $this->dashboard;
        set (DashboardAction $dashboard) => $dashboard;
    }

    public private(set) Voter $voter;

    public function __construct(
        DashboardAction $dashboard,
        Voter $voter,
    ) {
        $this->dashboard = $dashboard;
        $this->voter = $voter;
    }

    #[Route(
        path: '/users/{id}/edit',
        name: 'user-edit',
        methods: [
            'GET',
        ],
    )]
    public function __invoke(string $id): DashboardAction|ResponseInterface
    {
        $action = new DashboardAction;

        try {
            return $this->handle($id);
        } catch (NotFoundException $exception) {
            throw $exception;
        }
    }
}

?>
-----
<?php

namespace App\Controller\User;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class EditAction
{
    public Controller\DashboardAction $dashboard {
        get => $this->dashboard;
        set (Controller\DashboardAction $dashboard) => $dashboard;
    }

    public private(set) Controller\Security\Voter $voter;

    public function __construct(
        Controller\DashboardAction $dashboard,
        Controller\Security\Voter $voter,
    ) {
        $this->dashboard = $dashboard;
        $this->voter = $voter;
    }

    #[Controller\Routing\Attribute\Route(
        path: '/users/{id}/edit',
        name: 'user-edit',
        methods: [
            'GET',
        ],
    )]
    public function __invoke(string $id): Controller\DashboardAction|ResponseInterface
    {
        $action = new Controller\DashboardAction;

        try {
            return $this->handle($id);
        } catch (Controller\Exception\NotFoundException $exception) {
            throw $exception;
        }
    }
}

?>
