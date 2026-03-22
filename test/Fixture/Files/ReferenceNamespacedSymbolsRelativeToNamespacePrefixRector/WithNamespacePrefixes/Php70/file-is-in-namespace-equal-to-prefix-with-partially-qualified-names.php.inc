<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

final class FrontController
{
    public function find(): User\FindAction
    {
        return new User\FindAction();
    }

    /**
     * @return User\CreateAction
     */
    public function create()
    {
        return new User\CreateAction();
    }

    public function handle(): ResponseInterface
    {
    }
}

?>
