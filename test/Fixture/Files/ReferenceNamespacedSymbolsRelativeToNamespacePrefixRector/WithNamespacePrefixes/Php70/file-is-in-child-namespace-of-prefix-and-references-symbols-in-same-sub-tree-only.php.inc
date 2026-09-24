<?php

namespace App\Controller\User;

use App\Controller\User\FindAction;
use App\Controller\User\Profile\ShowAction;
use Psr\Http\Message\ResponseInterface;

final class ListAction
{
    /**
     * @param FindAction $findAction
     * @param \App\Controller\User\Profile\ShowAction $showAction
     *
     * @return ResponseInterface
     */
    public function __invoke(
        FindAction $findAction,
        ShowAction $showAction
    ): ResponseInterface {
        $action = new \App\Controller\User\CreateAction();
        $editAction = new \App\Controller\User\Profile\EditAction();
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
     * @param Profile\ShowAction $showAction
     *
     * @return ResponseInterface
     */
    public function __invoke(
        FindAction $findAction,
        Profile\ShowAction $showAction
    ): ResponseInterface {
        $action = new CreateAction();
        $editAction = new Profile\EditAction();
    }
}

?>
