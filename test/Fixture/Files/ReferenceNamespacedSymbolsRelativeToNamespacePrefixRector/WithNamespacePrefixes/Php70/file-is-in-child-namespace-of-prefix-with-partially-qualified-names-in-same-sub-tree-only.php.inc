<?php

namespace App\Controller\User;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class ListAction
{
    /**
     * @param Controller\User\FindAction $findAction
     *
     * @return ResponseInterface
     */
    public function __invoke(
        Controller\User\FindAction $findAction,
        Controller\User\Profile\ShowAction $showAction
    ): ResponseInterface {
        $action = new Controller\User\CreateAction();
    }
}

?>
-----
<?php

namespace App\Controller\User;

use Psr\Http\Message\ResponseInterface;

final class ListAction
{
    /**
     * @param FindAction $findAction
     *
     * @return ResponseInterface
     */
    public function __invoke(
        FindAction $findAction,
        Profile\ShowAction $showAction
    ): ResponseInterface {
        $action = new CreateAction();
    }
}

?>
