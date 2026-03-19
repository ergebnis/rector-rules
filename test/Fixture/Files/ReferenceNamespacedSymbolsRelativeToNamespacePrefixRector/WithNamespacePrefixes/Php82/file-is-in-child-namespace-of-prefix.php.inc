<?php

namespace App\Controller\User;

use App\Controller\Contract\Arrayable;
use App\Controller\Contract\Jsonable;
use App\Controller\DashboardAction;
use App\Controller\Exception\NotFoundException;
use App\Controller\Routing\Attribute\Route;
use App\Controller\Security\Voter;
use Psr\Http\Message\ResponseInterface;

final readonly class EditAction
{
    public function __construct(
        private DashboardAction $dashboard,
        private Voter $voter,
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
        try {
            return $this->handle($id);
        } catch (NotFoundException $exception) {
            throw $exception;
        }
    }

    public function resolve(): (Arrayable&Jsonable)|false
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

final readonly class EditAction
{
    public function __construct(
        private Controller\DashboardAction $dashboard,
        private Controller\Security\Voter $voter,
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
        try {
            return $this->handle($id);
        } catch (Controller\Exception\NotFoundException $exception) {
            throw $exception;
        }
    }

    public function resolve(): (Controller\Contract\Arrayable&Controller\Contract\Jsonable)|false
    {
        throw new Controller\Exception\NotFoundException();
    }
}

?>
