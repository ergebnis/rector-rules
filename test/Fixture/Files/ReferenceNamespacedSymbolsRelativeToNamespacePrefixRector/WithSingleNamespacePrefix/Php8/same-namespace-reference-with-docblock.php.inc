<?php

namespace Example\Core\Controller;

final class FrontController
{
    /**
     * @return AbstractController
     */
    public function delegate()
    {
        return new AbstractController();
    }
}

?>
