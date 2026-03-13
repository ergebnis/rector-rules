<?php

namespace Example\Core\Controller;

final class FrontController
{
    public function delegate(): AbstractController
    {
        return new AbstractController();
    }
}

?>
