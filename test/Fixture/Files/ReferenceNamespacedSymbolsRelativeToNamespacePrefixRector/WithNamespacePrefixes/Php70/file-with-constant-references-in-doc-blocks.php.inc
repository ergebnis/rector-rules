<?php

namespace App\Service;

use App\Controller\User;

final class Finder
{
    /**
     * @var User\FindAction::TYPE_*
     */
    private $type;

    /**
     * @param User\FindAction::TYPE_* $type
     * @param self::MODE_* $mode
     */
    public function find($type, $mode): User\FindAction
    {
        return new User\FindAction($type);
    }
}

?>
-----
<?php

namespace App\Service;

use App\Controller;

final class Finder
{
    /**
     * @var Controller\User\FindAction::TYPE_*
     */
    private $type;

    /**
     * @param Controller\User\FindAction::TYPE_* $type
     * @param self::MODE_* $mode
     */
    public function find($type, $mode): Controller\User\FindAction
    {
        return new Controller\User\FindAction($type);
    }
}

?>
