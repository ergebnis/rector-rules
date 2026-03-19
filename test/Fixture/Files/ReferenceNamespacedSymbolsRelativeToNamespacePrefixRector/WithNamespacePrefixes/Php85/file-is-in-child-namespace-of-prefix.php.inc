<?php

namespace App\Controller\User;

use App\Controller\DashboardAction;
use App\Controller\Exception\NotFoundException;
use App\Controller\Routing\Attribute\Route;
use App\Controller\Security\Voter;
use function App\Controller\Helper\resolve_path;
use Psr\Http\Message\ResponseInterface;

final class EditAction
{
    public function __construct(
        private readonly DashboardAction $dashboard,
        private readonly Voter $voter,
    ) {
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
        $result = $id |> resolve_path(...);

        $clone = clone($this->dashboard, []);

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
    public function __construct(
        private readonly Controller\DashboardAction $dashboard,
        private readonly Controller\Security\Voter $voter,
    ) {
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
        $result = $id |> Controller\Helper\resolve_path(...);

        $clone = clone($this->dashboard, []);

        try {
            return $this->handle($id);
        } catch (Controller\Exception\NotFoundException $exception) {
            throw $exception;
        }
    }
}

?>
