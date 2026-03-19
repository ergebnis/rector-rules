<?php

namespace App\Controller\User;

use App\Controller\Contract\Arrayable;
use App\Controller\Contract\Jsonable;
use App\Controller\DashboardAction;
use App\Controller\Exception\NotFoundException;
use App\Controller\Routing\Attribute\Route;
use App\Controller\Security\Voter;
use App\Controller\Status\UserStatus;
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
        $status = UserStatus::Active;

        $factory = DashboardAction::create(...);

        try {
            return $this->handle($id);
        } catch (NotFoundException $exception) {
            throw $exception;
        }
    }

    public function resolve(): Arrayable&Jsonable
    {
        throw new NotFoundException();
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
        $status = Controller\Status\UserStatus::Active;

        $factory = Controller\DashboardAction::create(...);

        try {
            return $this->handle($id);
        } catch (Controller\Exception\NotFoundException $exception) {
            throw $exception;
        }
    }

    public function resolve(): Controller\Contract\Arrayable&Controller\Contract\Jsonable
    {
        throw new Controller\Exception\NotFoundException();
    }
}

?>
