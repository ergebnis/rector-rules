<?php

namespace App\Controller\User;

use App\Controller\DashboardAction;
use Other\Foo\Repository\DepartmentRepository;

final class ListAction
{
    public function __construct(
        private DashboardAction $dashboard,
        private DepartmentRepository $repository,
    ) {
    }

    public function handle(): void
    {
        $event = FindAction\Event\UserFound::create();
        $handler = new FindAction\Handler();

        throw FindAction\Exception\UserNotFoundException::identifiedBy('123');
    }

    /**
     * @param FindAction\Event\UserFound $event
     */
    public function apply($event): void
    {
    }
}

?>
-----
<?php

namespace App\Controller\User;

use App\Controller;
use Other\Foo\Repository\DepartmentRepository;

final class ListAction
{
    public function __construct(
        private Controller\DashboardAction $dashboard,
        private DepartmentRepository $repository,
    ) {
    }

    public function handle(): void
    {
        $event = Controller\User\FindAction\Event\UserFound::create();
        $handler = new Controller\User\FindAction\Handler();

        throw Controller\User\FindAction\Exception\UserNotFoundException::identifiedBy('123');
    }

    /**
     * @param Controller\User\FindAction\Event\UserFound $event
     */
    public function apply($event): void
    {
    }
}

?>
