<?php

namespace App\Controller\User;

use App\Controller\AbstractAction;
use App\Controller\Concern\HasBreadcrumbs;
use App\Controller\DashboardAction;
use App\Controller\Exception\NotFoundException;
use App\Controller\Exception\ValidationException;
use App\Controller\Security\Voter;
use App\Controller\User\FindAction;
use App\Controller\Contract\Arrayable;
use App\Controller\Contract\Jsonable;
use Other\Foo\Repository\DepartmentRepository;
use Psr\Http\Message\ResponseInterface;

final class ListAction extends AbstractAction implements Arrayable, Jsonable
{
    use HasBreadcrumbs;

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
        DepartmentRepository $repository,
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
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    public function fromFqn(): void
    {
        $action = new \App\Controller\DashboardAction();
        $other = new \Other\Vendor\Service();
    }

    /**
     * @return array<string, Voter>
     */
    public function toArray(): array
    {
        return [];
    }
}

?>
-----
<?php

namespace App\Controller\User;

use App\Controller;
use Other\Foo\Repository\DepartmentRepository;
use Psr\Http\Message\ResponseInterface;

final class ListAction extends Controller\AbstractAction implements Controller\Contract\Arrayable, Controller\Contract\Jsonable
{
    use Controller\Concern\HasBreadcrumbs;

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
        DepartmentRepository $repository,
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
        } catch (Controller\Exception\ValidationException $exception) {
            throw $exception;
        }
    }

    public function fromFqn(): void
    {
        $action = new Controller\DashboardAction();
        $other = new \Other\Vendor\Service();
    }

    /**
     * @return array<string, Controller\Security\Voter>
     */
    public function toArray(): array
    {
        return [];
    }
}

?>
