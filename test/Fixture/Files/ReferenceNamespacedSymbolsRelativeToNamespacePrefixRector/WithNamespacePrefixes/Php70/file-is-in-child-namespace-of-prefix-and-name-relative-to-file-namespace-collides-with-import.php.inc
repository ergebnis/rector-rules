<?php

namespace App\Controller\User;

use App\Controller\User\CreateAction;
use Other\Vendor\FindAction;
use Other\Vendor\Profile;

final class ListAction
{
    /**
     * @param \App\Controller\User\FindAction $findAction
     */
    public function __invoke(
        $findAction,
        FindAction $otherFindAction,
        Profile $profile
    ) {
        $action = new CreateAction();
        $findAction = new \App\Controller\User\FindAction();
        $showAction = new \App\Controller\User\Profile\ShowAction();
    }
}

?>
-----
<?php

namespace App\Controller\User;

use App\Controller;
use Other\Vendor\FindAction;
use Other\Vendor\Profile;

final class ListAction
{
    /**
     * @param Controller\User\FindAction $findAction
     */
    public function __invoke(
        $findAction,
        FindAction $otherFindAction,
        Profile $profile
    ) {
        $action = new CreateAction();
        $findAction = new Controller\User\FindAction();
        $showAction = new Controller\User\Profile\ShowAction();
    }
}

?>
