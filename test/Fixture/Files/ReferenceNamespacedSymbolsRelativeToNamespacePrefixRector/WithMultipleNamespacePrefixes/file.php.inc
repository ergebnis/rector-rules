<?php

namespace App\Service;

use App\Controller\User\FindAction;
use App\Controller\DashboardAction;
use App\Entity\User;
use App\Entity\Role;
use Psr\Http\Message\ResponseInterface;

final class UserService
{
    /**
     * @var User
     */
    private $user;

    public function __construct(
        FindAction $findAction,
        User $user,
        Role $role
    ) {
        $this->user = $user;
    }

    public function dashboard(): DashboardAction
    {
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;
use App\Entity;
use Psr\Http\Message\ResponseInterface;

final class UserService
{
    /**
     * @var Entity\User
     */
    private $user;

    public function __construct(
        Controller\User\FindAction $findAction,
        Entity\User $user,
        Entity\Role $role
    ) {
        $this->user = $user;
    }

    public function dashboard(): Controller\DashboardAction
    {
    }
}

?>
