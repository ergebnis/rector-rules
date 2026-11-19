<?php

namespace App\Http;

use function Admin\Helper\render;
use function App\Helper\render as view;

final class Router
{
    public function dashboard(): string
    {
        return render() . view();
    }
}

?>
