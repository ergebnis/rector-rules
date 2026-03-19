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
    public const Arrayable SERIALIZATION_FORMAT = 'json';

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
    #[\Override]
    public function __invoke(string $id): DashboardAction|ResponseInterface
    {
        $constant = DashboardAction::{$id};

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
    public const Controller\Contract\Arrayable SERIALIZATION_FORMAT = 'json';

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
    #[\Override]
    public function __invoke(string $id): Controller\DashboardAction|ResponseInterface
    {
        $constant = Controller\DashboardAction::{$id};

        try {
            return $this->handle($id);
        } catch (Controller\Exception\NotFoundException $exception) {
            throw $exception;
        }
    }
}

?>
