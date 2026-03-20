<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    public function handle(): ResponseInterface
    {
        $event = User\Event\UserCreated::create();
        $handler = new User\Handler();

        throw User\Exception\UserNotFoundException::identifiedBy('123');
    }

    /**
     * @param User\Event\UserCreated $event
     */
    public function apply($event): void
    {
    }
}

?>
-----
<?php

namespace App\Controller;

use App\Controller;
use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    public function handle(): ResponseInterface
    {
        $event = Controller\User\Event\UserCreated::create();
        $handler = new Controller\User\Handler();

        throw Controller\User\Exception\UserNotFoundException::identifiedBy('123');
    }

    /**
     * @param Controller\User\Event\UserCreated $event
     */
    public function apply($event): void
    {
    }
}

?>
