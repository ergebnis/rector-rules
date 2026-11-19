<?php

namespace App\Http;

use function App\Helper\render as view;

final class Router
{
    public function dashboard(): string
    {
        return view() . RENDER();
    }
}

?>
